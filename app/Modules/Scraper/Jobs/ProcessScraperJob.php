<?php

namespace App\Modules\Scraper\Jobs;

use App\Modules\Fee\Models\FeeHistory;
use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Scraper\Events\ScraperRunCompleted;
use App\Modules\Scraper\Models\ScraperLog;
use App\Modules\Scraper\Models\ScraperRun;
use App\Modules\Scraper\Models\ScraperSource;
use App\Modules\Scraper\Notifications\ScraperFailedNotification;
use App\Modules\Scraper\Services\ChangeDetector;
use App\Modules\Scraper\Services\ConfidenceScorer;
use App\Modules\Scraper\Services\ScraperAdapterFactory;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ProcessScraperJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [30, 120, 300];

    public $uniqueFor = 3600;

    public function __construct(
        public ScraperSource $source,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->source->id;
    }

    public function handle(
        ScraperAdapterFactory $factory,
        ChangeDetector $changeDetector,
        ConfidenceScorer $confidenceScorer,
    ): void {
        $run = ScraperRun::create([
            'uuid' => (string) Str::uuid(),
            'scraper_source_id' => $this->source->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $adapter = $factory->make($this->source);

            $this->log($run, 'info', 'Fetching source...');

            $rawContent = $adapter->fetch($this->source);
            $run->update(['raw_payload' => mb_substr($rawContent, 0, 65535)]);

            $this->log($run, 'info', 'Parsing content...');
            $parsed = $adapter->parse($rawContent, $this->source);

            $this->log($run, 'info', 'Normalizing data...');
            $normalized = $adapter->normalize($parsed, $this->source);

            $confidence = $adapter->getConfidence($normalized);
            if ($confidence < 0.30) {
                $this->log($run, 'warning', "Low confidence ({$confidence}), skipping.");
                $run->update(['status' => 'failed', 'finished_at' => now(), 'error_message' => "Confidence too low: {$confidence}"]);

                return;
            }

            $institute = $this->source->institute;
            $changes = [];
            if ($institute) {
                $changes = $changeDetector->detectFeeChanges($institute, [$normalized]);
            }

            if ($this->source->trust_level === 'trusted' && ! $changeDetector->hasSignificantChanges($changes, 15.0)) {
                $this->persistFees($run, $normalized, $confidence, 'approved');
                $this->log($run, 'info', 'Trusted source — auto-approved.');
            } else {
                $this->persistFees($run, $normalized, $confidence, 'pending_review');
                $this->log($run, 'info', 'Sent to moderation queue.');
            }

            $run->update([
                'status' => 'completed',
                'finished_at' => now(),
                'items_processed' => 1,
                'items_changed' => count(array_filter($changes, fn ($c) => $c['type'] !== 'new')),
            ]);

            $this->source->update(['last_successful_run_at' => now()]);

            ScraperRunCompleted::dispatch($run);
            $this->log($run, 'info', 'Run completed successfully.');

        } catch (Exception $e) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => mb_substr($e->getMessage(), 0, 5000),
            ]);

            $this->log($run, 'error', $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            Log::error("Scraper failed for source {$this->source->id}: {$e->getMessage()}");

            $this->notifyAdmins($run);

            throw $e;
        }
    }

    private function persistFees(ScraperRun $run, array $data, float $confidence, string $moderationStatus): void
    {
        if (! isset($data['amount']) || ! isset($data['fee_type_id'])) {
            $this->log($run, 'warning', 'Incomplete fee data, skipping persist.');

            return;
        }

        $feeTypeId = (int) ($data['fee_type_id'] ?? 0);
        $fee = FeeStructure::updateOrCreate(
            [
                'institute_id' => $this->source->institute_id,
                'fee_type_id' => $feeTypeId,
                'academic_session' => $data['academic_session'] ?? date('Y'),
                'grade_range_start' => $data['grade_range_start'] ?? 'all',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'amount' => $data['amount'] ?? 0,
                'currency' => $data['currency'] ?? 'BDT',
                'frequency' => $data['frequency'] ?? 'monthly',
                'moderation_status' => $moderationStatus,
                'confidence_score' => $confidence,
                'source_url' => $this->source->base_url,
                'source_type' => 'scraper',
                'scraper_run_id' => $run->id,
                'is_published' => $moderationStatus === 'approved',
                'published_at' => $moderationStatus === 'approved' ? now() : null,
            ]
        );

        FeeHistory::create([
            'uuid' => (string) Str::uuid(),
            'fee_structure_id' => $fee->id,
            'action' => $moderationStatus === 'approved' ? 'scraped_and_approved' : 'scraped',
            'old_amount' => $fee->getOriginal('amount'),
            'new_amount' => $data['amount'] ?? 0,
            'source' => 'scraper',
            'scraper_run_id' => $run->id,
            'notes' => "Scraped from {$this->source->base_url} [confidence: {$confidence}]",
        ]);
    }

    private function log(ScraperRun $run, string $level, string $message, array $context = []): void
    {
        ScraperLog::create([
            'scraper_run_id' => $run->id,
            'log_level' => $level,
            'message' => $message,
            'context' => $context,
            'created_at' => now(),
        ]);
    }

    private function notifyAdmins(ScraperRun $run): void
    {
        try {
            $admins = config('scraper.notifications.emails', []);
            if (! empty($admins)) {
                Notification::route('mail', $admins)
                    ->notify(new ScraperFailedNotification($run));
            }
        } catch (Exception $e) {
            Log::warning("Failed to send scraper failure notification: {$e->getMessage()}");
        }
    }
}

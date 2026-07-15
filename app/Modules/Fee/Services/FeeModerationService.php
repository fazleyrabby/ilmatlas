<?php

namespace App\Modules\Fee\Services;

use App\Models\User;
use App\Modules\Fee\Models\FeeStructure;
use App\Modules\User\Models\UserAlert;
use App\Modules\User\Notifications\UserAlertNotification;
use Illuminate\Support\Facades\Notification;

class FeeModerationService
{
    public function approve(FeeStructure $fee, User $moderator): FeeStructure
    {
        $fee->update([
            'moderation_status' => 'approved',
            'verified_by' => $moderator->id,
            'verified_at' => now(),
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Eager load relations for sitemap/notification properties safely
        $fee->loadMissing(['institute', 'feeType']);

        // Dispatch alert to watchers
        $watchers = UserAlert::where('institute_id', $fee->institute_id)
            ->where('alert_type', 'fee_changes')
            ->where('is_active', true)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        if ($watchers->isNotEmpty()) {
            $title = 'Fee Update Alert: '.($fee->institute?->name ?? 'Institute');
            $content = "A new fee structure '".($fee->feeType?->name ?? 'Fee')."' of amount {$fee->amount} BDT has been approved and published.";
            $url = route('institutes.show', $fee->institute?->uuid ?? '');

            Notification::send($watchers, new UserAlertNotification($title, $content, $url));
        }

        return $fee;
    }

    public function reject(FeeStructure $fee, User $moderator, ?string $reason = null): FeeStructure
    {
        $fee->update([
            'moderation_status' => 'rejected',
            'verified_by' => $moderator->id,
            'verified_at' => now(),
            'source_notes' => $reason,
        ]);

        return $fee;
    }

    public function requestRevision(FeeStructure $fee, User $moderator, ?string $note = null): FeeStructure
    {
        $fee->update([
            'moderation_status' => 'needs_revision',
            'verified_by' => $moderator->id,
            'source_notes' => $note,
        ]);

        return $fee;
    }

    public function pendingCount(): int
    {
        return FeeStructure::where('moderation_status', 'pending_review')->count();
    }
}

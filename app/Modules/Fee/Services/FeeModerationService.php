<?php

namespace App\Modules\Fee\Services;

use App\Models\User;
use App\Modules\Fee\Models\FeeStructure;

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

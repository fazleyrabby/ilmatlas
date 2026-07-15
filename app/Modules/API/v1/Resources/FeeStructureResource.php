<?php

namespace App\Modules\API\v1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeStructureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'fee_type' => $this->whenLoaded('feeType', fn () => [
                'id' => $this->feeType?->id,
                'name' => $this->feeType?->name,
                'slug' => $this->feeType?->slug,
            ]),
            'academic_session' => $this->academic_session,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'frequency' => $this->frequency,
            'unit_label' => $this->unit_label,
            'is_negotiable' => (bool) $this->is_negotiable,
            'grade_range_start' => $this->grade_range_start,
            'grade_range_end' => $this->grade_range_end,
            'verification_status' => $this->verification_status,
            'moderation_status' => $this->moderation_status,
            'confidence_score' => (float) $this->confidence_score,
            'source_type' => $this->source_type,
            'published_at' => $this->published_at?->toISOString(),
        ];
    }
}

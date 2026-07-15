<?php

namespace App\Modules\API\v1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'fee_type' => $this->whenLoaded('feeType', fn () => [
                'id' => $this->feeType?->id,
                'name' => $this->feeType?->name,
            ]),
            'previous_amount' => (float) $this->previous_amount,
            'new_amount' => (float) $this->new_amount,
            'percentage_change' => (float) $this->percentage_change,
            'effective_date' => $this->effective_date?->toDateString(),
            'academic_session' => $this->academic_session,
            'change_reason' => $this->change_reason,
            'verification_status' => $this->verification_status,
            'source_type' => $this->source_type,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

<?php

namespace App\Modules\API\v1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstituteListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'slug' => $this->slug,
            'type' => $this->whenLoaded('type', fn () => [
                'id' => $this->type?->id,
                'name' => $this->type?->name,
                'slug' => $this->type?->slug,
            ]),
            'district' => $this->whenLoaded('district', fn () => [
                'id' => $this->district?->id,
                'name' => $this->district?->name,
                'slug' => $this->district?->slug,
            ]),
            'estimated_monthly_fee' => (float) ($this->attributes['estimated_monthly_fee'] ?? 0),
            'gender' => $this->gender,
            'religious_orientation' => $this->religious_orientation,
            'verification_status' => $this->verification_status,
            'status' => $this->status,
            'logo_url' => $this->attributes['logo_url'] ?? null,
            'established_year' => $this->established_year,
        ];
    }
}

<?php

namespace App\Modules\API\v1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstituteDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $include = array_filter(explode(',', $request->query('include', '')));

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'slug' => $this->slug,
            'institute_code' => $this->institute_code,
            'established_year' => $this->established_year,
            'description' => $this->description,
            'motto' => $this->motto,
            'gender' => $this->gender,
            'religious_orientation' => $this->religious_orientation,
            'methodology' => $this->methodology,
            'full_address' => $this->full_address,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'google_maps_url' => $this->google_maps_url,
            'nearby_landmark' => $this->nearby_landmark,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'profile_completeness' => $this->profile_completeness,
            'estimated_monthly_fee' => (float) ($this->attributes['estimated_monthly_fee'] ?? 0),
            'logo_url' => $this->attributes['logo_url'] ?? null,
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
            'division' => $this->whenLoaded('division', fn () => [
                'id' => $this->division?->id,
                'name' => $this->division?->name,
                'slug' => $this->division?->slug,
            ]),
            'upazila' => $this->whenLoaded('upazila', fn () => [
                'id' => $this->upazila?->id,
                'name' => $this->upazila?->name,
                'slug' => $this->upazila?->slug,
            ]),
            'contacts' => $this->when(in_array('contacts', $include),
                fn () => $this->contacts->map(fn ($c) => [
                    'id' => $c->id,
                    'contact_type' => $c->contact_type,
                    'value' => $c->value,
                    'label' => $c->label,
                    'is_primary' => $c->is_primary,
                ])->values()
            ),
            'curriculums' => $this->when(in_array('curriculums', $include),
                fn () => $this->curriculums->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'is_primary' => (bool) $c->pivot?->is_primary,
                ])->values()
            ),
            'boards' => $this->when(in_array('boards', $include),
                fn () => $this->boards->map(fn ($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'short_name' => $b->short_name,
                    'is_primary' => (bool) $b->pivot?->is_primary,
                ])->values()
            ),
            'programs' => $this->when(in_array('programs', $include),
                fn () => $this->programs->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'is_available' => (bool) $p->pivot?->is_available,
                    'notes' => $p->pivot?->notes,
                ])->values()
            ),
            'facilities' => $this->when(in_array('facilities', $include),
                fn () => $this->facilities->map(fn ($f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'slug' => $f->slug,
                    'is_available' => (bool) $f->pivot?->is_available,
                    'description' => $f->pivot?->description,
                ])->values()
            ),
            'fees' => $this->when(in_array('fees', $include),
                fn () => FeeStructureResource::collection($this->fees->where('is_published', true))->resolve()
            ),
            'admissions' => $this->when(in_array('admissions', $include),
                fn () => AdmissionResource::collection($this->admissionCirculars->where('is_published', true))->resolve()
            ),
            'published_at' => $this->published_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
        ];
    }
}

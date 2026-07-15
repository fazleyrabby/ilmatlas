<?php

namespace App\Modules\API\v1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'admission_status' => $this->admission_status,
            'application_start_date' => $this->application_start_date?->toDateString(),
            'application_end_date' => $this->application_end_date?->toDateString(),
            'admission_test_required' => (bool) $this->admission_test_required,
            'admission_test_date' => $this->admission_test_date?->toDateString(),
            'interview_required' => (bool) $this->interview_required,
            'online_application_available' => (bool) $this->online_application_available,
            'application_url' => $this->application_url,
            'documents_required' => $this->documents_required,
            'eligibility_criteria' => $this->eligibility_criteria,
            'contact_info' => $this->contact_info,
            'is_published' => (bool) $this->is_published,
            'published_at' => $this->published_at?->toISOString(),
        ];
    }
}

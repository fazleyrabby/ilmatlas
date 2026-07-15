<?php

namespace App\Modules\Admission\Models;

use App\Modules\Institute\Models\Institute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionCircular extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'institute_id', 'admission_session_id', 'title',
        'admission_status',
        'application_start_date', 'application_end_date',
        'admission_test_required', 'admission_test_date',
        'interview_required', 'online_application_available', 'application_url',
        'documents_required', 'eligibility_criteria', 'contact_info',
        'is_published', 'published_at',
        'source_url', 'scraper_run_id',
    ];

    protected function casts(): array
    {
        return [
            'application_start_date' => 'date',
            'application_end_date' => 'date',
            'admission_test_date' => 'date',
            'admission_test_required' => 'boolean',
            'interview_required' => 'boolean',
            'online_application_available' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AdmissionSession::class, 'admission_session_id');
    }
}

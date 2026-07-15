<?php

namespace App\Modules\Institute\Models;

use App\Modules\Admission\Models\AdmissionCircular;
use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Location\Models\Area;
use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Location\Models\Upazila;
use App\Modules\Media\Models\InstituteMedia;
use App\Modules\Scraper\Models\ScraperSource;
use App\Modules\SEO\Models\SeoMetadata;
use App\Modules\Taxonomy\Models\Category;
use App\Modules\Taxonomy\Models\Curriculum;
use App\Modules\Taxonomy\Models\EducationBoard;
use App\Modules\Taxonomy\Models\Facility;
use App\Modules\Taxonomy\Models\InstituteType;
use App\Modules\Taxonomy\Models\Language;
use App\Modules\Taxonomy\Models\Program;
use App\Modules\Taxonomy\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Institute extends Model
{
    use Searchable, SoftDeletes;

    protected $fillable = [
        'uuid', 'name', 'short_name', 'slug', 'institute_code', 'established_year',
        'description', 'motto',
        'institute_type_id', 'primary_category_id',
        'religious_orientation', 'methodology', 'gender',
        'country_id', 'division_id', 'district_id', 'upazila_id', 'area_id',
        'full_address', 'postal_code', 'latitude', 'longitude',
        'google_maps_url', 'nearby_landmark',
        'status', 'published_at', 'verified_at', 'verified_by',
        'verification_status', 'source_url', 'source_type',
        'meta_title', 'meta_description', 'meta_keywords',
        'view_count', 'comparison_count', 'fee_record_count',
        'estimated_monthly_fee', 'logo_url', 'profile_completeness',
        'owner_id',
    ];

    protected $appends = [
        'estimated_monthly_fee',
        'current_admission_status',
        'logo_url',
        'key_facts',
    ];

    protected function casts(): array
    {
        return [
            'established_year' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'published_at' => 'datetime',
            'verified_at' => 'datetime',
            'is_active' => 'boolean',
            'view_count' => 'integer',
            'comparison_count' => 'integer',
            'fee_record_count' => 'integer',
            'estimated_monthly_fee' => 'decimal:2',
            'profile_completeness' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InstituteClaim::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(InstituteType::class, 'institute_type_id');
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function upazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'institute_categories')
            ->withPivot('is_primary');
    }

    public function curriculums(): BelongsToMany
    {
        return $this->belongsToMany(Curriculum::class, 'institute_curriculums')
            ->withPivot('is_primary');
    }

    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(EducationBoard::class, 'institute_boards')
            ->withPivot('is_primary');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'institute_programs')
            ->withPivot('is_available', 'notes');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'institute_subjects');
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'institute_facilities')
            ->withPivot('is_available', 'description');
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'institute_languages')
            ->withPivot('language_type');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(InstituteContact::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(InstituteSocialLink::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(InstituteMedia::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function admissionCirculars(): HasMany
    {
        return $this->hasMany(AdmissionCircular::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(InstituteShift::class);
    }

    public function scraperSources(): HasMany
    {
        return $this->hasMany(ScraperSource::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function seoMetadata(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeByType(Builder $query, string $typeSlug): Builder
    {
        return $query->whereHas('type', fn ($q) => $q->where('slug', $typeSlug));
    }

    public function scopeByDistrict(Builder $query, int $districtId): Builder
    {
        return $query->where('district_id', $districtId);
    }

    public function scopeByCurriculum(Builder $query, int $curriculumId): Builder
    {
        return $query->whereHas('curriculums', fn ($q) => $q->where('curriculum_id', $curriculumId));
    }

    public function scopeWithFeeRange(Builder $query, ?float $min, ?float $max): Builder
    {
        return $query->when($min !== null, fn ($q) => $q->where('estimated_monthly_fee', '>=', $min))
            ->when($max !== null, fn ($q) => $q->where('estimated_monthly_fee', '<=', $max));
    }

    public function scopeAdmissionOpen(Builder $query): Builder
    {
        return $query->whereHas('admissionCirculars', function ($q) {
            $q->where('admission_status', 'open')
                ->where('application_start_date', '<=', now())
                ->where('application_end_date', '>=', now());
        });
    }

    public function searchableAs(): string
    {
        return 'institutes_index';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing([
            'type', 'primaryCategory', 'curriculums', 'boards',
            'district', 'upazila', 'area',
        ]);

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_name' => $this->short_name,
            'institute_code' => $this->institute_code,
            'description' => $this->description,
            'type' => $this->type?->name,
            'type_slug' => $this->type?->slug,
            'category' => $this->primaryCategory?->name,
            'category_slug' => $this->primaryCategory?->slug,
            'curriculums' => $this->curriculums->pluck('name')->toArray(),
            'boards' => $this->boards->pluck('short_name')->toArray(),
            'district' => $this->district?->name,
            'upazila' => $this->upazila?->name,
            'area' => $this->area?->name,
            'gender' => $this->gender,
            'religious_orientation' => $this->religious_orientation,
            'methodology' => $this->methodology,
            'estimated_monthly_fee' => (float) ($this->estimated_monthly_fee ?? 0.00),
            'verification_status' => $this->verification_status,
            'status' => $this->status,
            'established_year' => $this->established_year,
        ];
    }

    protected function estimatedMonthlyFee(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('fees') === false && isset($this->attributes['estimated_monthly_fee'])) {
                    return (float) $this->attributes['estimated_monthly_fee'];
                }

                $this->loadMissing('fees');

                $totalMonthly = 0.00;
                $feesColl = $this->fees instanceof \Illuminate\Support\Collection ? $this->fees : collect($this->fees);
                $activeFees = $feesColl->where('moderation_status', 'approved')->where('is_published', true);

                if ($activeFees->isEmpty()) {
                    return 0.00;
                }

                foreach ($activeFees as $fee) {
                    $feeObj = is_array($fee) ? (object)$fee : $fee;
                    $multiplier = config("edubase.fees.frequency_multipliers.{$feeObj->frequency}", 1);
                    $monthlyAmount = ($feeObj->amount * $multiplier) / 12;
                    $totalMonthly += $monthlyAmount;
                }

                return round($totalMonthly, 2);
            }
        );
    }

    protected function currentAdmissionStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->loadMissing('admissionCirculars');

                $hasOpen = $this->admissionCirculars
                    ->where('admission_status', 'open')
                    ->contains(fn ($circular) => now()->between($circular->application_start_date, $circular->application_end_date));

                return $hasOpen ? 'open' : 'closed';
            }
        );
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (isset($this->attributes['logo_url']) && ! empty($this->attributes['logo_url'])) {
                    return $this->attributes['logo_url'];
                }

                $this->loadMissing('media');
                $logoMedia = $this->media->where('media_type', 'logo')->first();

                if ($logoMedia) {
                    return Storage::disk($logoMedia->disk)->url($logoMedia->file_path);
                }

                return asset("assets/placeholders/logo-type-{$this->institute_type_id}.png");
            }
        );
    }

    protected function keyFacts(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->loadMissing(['curriculums', 'boards', 'programs']);

                return [
                    'curriculum' => $this->curriculums->first()?->name ?? 'N/A',
                    'board' => $this->boards->first()?->name ?? 'N/A',
                    'grades' => $this->programs->isNotEmpty()
                        ? $this->programs->min('name').' to '.$this->programs->max('name')
                        : 'N/A',
                    'orientation' => $this->religious_orientation ?? 'General',
                ];
            }
        );
    }
}

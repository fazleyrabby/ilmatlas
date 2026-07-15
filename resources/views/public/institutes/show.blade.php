@extends('layouts.public')

@section('title', $seo['meta_title'] ?? $institute->name . ' — EduBase')
@section('meta_description', $seo['meta_description'] ?? Str::limit($institute->description ?? 'View detailed information about ' . $institute->name, 160))
@section('meta_keywords', $seo['meta_keywords'] ?? '')
@section('og_title', $seo['og_title'] ?? '')
@section('og_description', $seo['og_description'] ?? '')
@section('og_image', $seo['og_image'] ?? '')
@section('canonical_url', $seo['canonical_url'] ?? url()->current())
@if(isset($seo['noindex']) && $seo['noindex'])
    @section('robots', 'noindex, nofollow')
@endif

@section('content')
<div class="container-reading py-8">
    <x-schema-educational-organization :institute="$institute" />

    <x-schema-breadcrumb :items="[
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Institutes', 'url' => route('institutes.index')],
        ['name' => $institute->name],
    ]" />

    <div class="mb-6">
        <a href="{{ route('institutes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-primary-700 hover:text-primary-800">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> Back to institutes
        </a>
    </div>

    {{-- Identity hero --}}
    <div class="card p-6 sm:p-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-md bg-primary-50 text-3xl font-bold text-primary-700 ring-1 ring-primary-100">
                {{ strtoupper(substr($institute->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-start gap-2">
                    <h1 class="text-3xl font-bold text-text-primary">{{ $institute->name }}</h1>
                    @if(($institute->verification_status ?? null) === 'verified')
                        <span class="badge badge-success mt-2"><i data-lucide="badge-check"></i> Verified</span>
                    @endif
                </div>
                @if($institute->short_name)
                    <p class="mt-1 text-text-muted">{{ $institute->short_name }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-2">
                    @if($institute->type)<span class="badge badge-primary">{{ $institute->type->name }}</span>@endif
                    @if($institute->gender)<span class="badge badge-neutral capitalize">{{ $institute->gender }}</span>@endif
                    @if($institute->religious_orientation && $institute->religious_orientation !== 'not_applicable')
                        <span class="badge badge-neutral capitalize">{{ $institute->religious_orientation }}</span>
                    @endif
                    @if($institute->district)<span class="badge badge-neutral"><i data-lucide="map-pin"></i> {{ $institute->district->name }}</span>@endif
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    <button
                        class="btn btn-outline btn-sm compare-btn"
                        data-uuid="{{ $institute->uuid }}"
                        data-slug="{{ $institute->slug }}"
                        data-name="{{ $institute->name }}"
                    >
                        <i data-lucide="git-compare"></i> + Add to Compare
                    </button>
                    @if($institute->owner_id === null)
                        <button data-toggle="claimFormSection" class="btn btn-secondary btn-sm">
                            <i data-lucide="shield-check"></i> Claim this School
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Facts --}}
        <div class="mt-6 fact-grid">
            @if($institute->established_year)
                <div class="fact"><div class="fact-label">Established</div><div class="fact-value tabular-nums">{{ $institute->established_year }}</div></div>
            @endif
            @if($institute->institute_code)
                <div class="fact"><div class="fact-label">EIIN</div><div class="fact-value tabular-nums">{{ $institute->institute_code }}</div></div>
            @endif
            @if($institute->gender)
                <div class="fact"><div class="fact-label">Gender</div><div class="fact-value capitalize">{{ $institute->gender }}</div></div>
            @endif
            @if($institute->type)
                <div class="fact"><div class="fact-label">Type</div><div class="fact-value">{{ $institute->type->name }}</div></div>
            @endif
            @if($institute->division)
                <div class="fact"><div class="fact-label">Division</div><div class="fact-value">{{ $institute->division->name }}</div></div>
            @endif
            <div class="fact"><div class="fact-label">Est. Monthly Fee</div><div class="fact-value tabular-nums">{{ $institute->estimated_monthly_fee > 0 ? '৳'.number_format($institute->estimated_monthly_fee, 0) : '—' }}</div></div>
        </div>

        <!-- Claim Form Wrapper -->
        <div id="claimFormSection" class="hidden mt-6 card bg-surface-muted p-6 space-y-4">
                    <h3 class="text-base font-semibold text-text-primary">Claim School Ownership</h3>
                    <p class="text-xs text-text-muted">Provide details or official school email verification to claim this profile.</p>
                    @auth
                        <form method="POST" action="{{ route('institutes.claim.store', $institute) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label for="claim_notes" class="eb-label">Verification details (Official role, contact number, details)</label>
                                <textarea id="claim_notes" name="notes" rows="3" required placeholder="Describe your position (e.g. Principal, IT Administrator) and school registration number..."
                                          class="eb-input text-xs"></textarea>
                            </div>
                            <div>
                                <label for="claim_proof" class="eb-label">Proof document (Employment card, license pdf, registration catalog)</label>
                                <input type="file" id="claim_proof" name="proof" class="block w-full text-xs text-text-muted file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:btn-secondary">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                Submit Claim Request
                            </button>
                        </form>
                    @else
                        <p class="text-xs text-text-secondary">Please <a href="{{ route('login') }}" class="link">log in</a> to claim school profiles.</p>
                    @endauth
                </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="md:col-span-2 space-y-6">
            @if($institute->description)
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-text-primary mb-3">About</h2>
                    <p class="text-text-secondary">{{ $institute->description }}</p>
                </div>
            @endif

            @php
                $gallery = $institute->media->filter(fn ($m) => in_array($m->media_type, ['image', 'photo', 'campus', 'gallery']) || Str::startsWith($m->mime_type ?? '', 'image/'));
            @endphp
            <div class="card p-6">
                <div class="mb-4 flex items-center gap-2">
                    <span class="icon-tile icon-tile-brand"><i data-lucide="images"></i></span>
                    <h2 class="text-lg font-semibold text-text-primary">Gallery</h2>
                </div>
                @if($gallery->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach($gallery as $media)
                            <a href="{{ Storage::disk($media->disk)->url($media->file_path) }}" target="_blank" rel="noopener"
                               class="group block overflow-hidden rounded-lg border border-divider aspect-[4/3] bg-surface-muted">
                                <img src="{{ Storage::disk($media->disk)->url($media->file_path) }}" alt="{{ $media->file_name ?? $institute->name }}"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-border py-10 text-center">
                        <i data-lucide="image-off" class="h-8 w-8 text-text-muted"></i>
                        <p class="text-sm text-text-muted">No photos uploaded yet.</p>
                    </div>
                @endif
            </div>


            <div class="card p-6">
                <h2 class="text-lg font-semibold text-text-primary mb-3">Curriculums & Programs</h2>
                @if($institute->curriculums->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($institute->curriculums as $c)
                            <span class="badge badge-success">{{ $c->name }}</span>
                        @endforeach
                    </div>
                @endif
                @if($institute->programs->isNotEmpty())
                    <div class="text-sm text-text-secondary">
                        <strong>Programs:</strong>
                        {{ $institute->programs->pluck('name')->implode(', ') }}
                    </div>
                @endif
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center gap-2">
                    <span class="icon-tile icon-tile-brand"><i data-lucide="map-pin"></i></span>
                    <h2 class="text-lg font-semibold text-text-primary">Contact & Location</h2>
                </div>

                {{-- Map placeholder --}}
                <div class="relative mb-5 flex h-40 items-center justify-center overflow-hidden rounded-lg border border-divider bg-surface-muted">
                    <div class="pointer-events-none absolute inset-0 opacity-60" style="background-image: radial-gradient(var(--color-border) 1px, transparent 1px); background-size: 18px 18px;"></div>
                    <div class="relative flex flex-col items-center gap-1 text-center">
                        <i data-lucide="map" class="h-7 w-7 text-primary-600"></i>
                        <p class="text-sm font-medium text-text-primary">{{ $institute->district?->name }}@if($institute->upazila), {{ $institute->upazila->name }}@endif</p>
                        @if($institute->latitude && $institute->longitude)
                            <a href="https://www.google.com/maps?q={{ $institute->latitude }},{{ $institute->longitude }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm mt-1">
                                <i data-lucide="navigation"></i> View on Map
                            </a>
                        @else
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($institute->name . ', ' . ($institute->district?->name ?? '')) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm mt-1">
                                <i data-lucide="navigation"></i> View on Map
                            </a>
                        @endif
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-text-muted">Address</dt>
                        <dd class="text-text-primary">{{ $institute->full_address ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Area</dt>
                        <dd class="text-text-primary">{{ $institute->area?->name }}, {{ $institute->upazila?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">District</dt>
                        <dd class="text-text-primary">{{ $institute->district?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-muted">Division</dt>
                        <dd class="text-text-primary">{{ $institute->division?->name }}</dd>
                    </div>
                    @if($institute->established_year)
                        <div>
                            <dt class="text-text-muted">Established</dt>
                            <dd class="text-text-primary">{{ $institute->established_year }}</dd>
                        </div>
                    @endif
                    @if($institute->institute_code)
                        <div>
                            <dt class="text-text-muted">EIIN</dt>
                            <dd class="text-text-primary">{{ $institute->institute_code }}</dd>
                        </div>
                    @endif
                </dl>

                @if($institute->contacts->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-divider">
                        <h3 class="text-sm font-medium text-text-primary mb-2">Contacts</h3>
                        @foreach($institute->contacts as $contact)
                            <div class="flex items-center gap-2 text-sm text-text-secondary">
                                <i data-lucide="phone" class="h-4 w-4 text-text-muted"></i>
                                <span class="capitalize font-medium text-text-primary">{{ $contact->contact_type }}:</span>
                                {{ $contact->contact_value }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Reviews & Ratings Section -->
            <div class="card p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-divider pb-4">
                    <h2 class="text-xl font-bold text-text-primary">Reviews & Ratings</h2>
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-semibold text-text-primary">Avg Rating:</span>
                        <span class="px-2 py-0.5 badge badge-warning rounded font-bold text-sm">
                            {{ number_format($reviews->avg('rating') ?? 0, 1) }} / 5.0
                        </span>
                        <span class="text-xs text-text-muted">({{ $reviews->count() }} reviews)</span>
                    </div>
                </div>

                @if(session('success'))
                    <div class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Reviews List -->
                <div class="space-y-4">
                    @forelse($reviews as $rev)
                        <div class="rounded-md bg-surface-muted p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-sm text-text-primary">{{ $rev->user?->name }}</span>
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $rev->rating ? 'text-warning fill-warning' : 'text-border' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-sm text-text-secondary leading-relaxed">{{ $rev->comment }}</p>
                            <span class="text-xs text-text-muted block">{{ $rev->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-text-muted py-4 text-center">No reviews have been written yet. Be the first to share your experience!</p>
                    @endforelse
                </div>

                <!-- Submit Review Form -->
                <div class="border-t border-divider pt-6">
                    @auth
                        <h3 class="text-lg font-bold text-text-primary mb-3">Write a Review</h3>
                        <form method="POST" action="{{ route('institutes.reviews.store', $institute) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="rating" class="eb-label">Rating</label>
                                <select id="rating" name="rating" required class="eb-input">
                                    <option value="5">5 Stars (Excellent)</option>
                                    <option value="4">4 Stars (Good)</option>
                                    <option value="3">3 Stars (Average)</option>
                                    <option value="2">2 Stars (Poor)</option>
                                    <option value="1">1 Star (Very Bad)</option>
                                </select>
                            </div>
                            <div>
                                <label for="comment" class="eb-label">Review Comment</label>
                                <textarea id="comment" name="comment" rows="4" required placeholder="Describe your experience with this school (curriculum, teachers, environment)..."
                                          class="eb-input"></textarea>
                                @error('comment')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                Submit Review
                            </button>
                        </form>
                    @else
                        <div class="rounded-md bg-surface-muted p-4 text-center">
                            <p class="text-sm text-text-secondary">Please <a href="{{ route('login') }}" class="link">log in</a> to write a review for this institute.</p>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-text-primary mb-3">Fee Information</h2>
                @if($institute->fees->isNotEmpty())
                    <div class="mb-4 rounded-md bg-primary-50 p-4 ring-1 ring-primary-100">
                        <p class="text-3xl font-bold text-primary-700 tabular-nums">{{ number_format($institute->estimated_monthly_fee, 0) }}</p>
                        <p class="text-sm text-primary-600">BDT / month (estimated)</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        @foreach($institute->fees->take(5) as $fee)
                            <div class="flex justify-between">
                                <span class="text-text-secondary">{{ $fee->feeType?->name ?? 'Fee' }}</span>
                                <span class="text-text-primary font-medium">{{ number_format($fee->amount, 0) }} BDT</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-text-muted">Fee information not available</p>
                @endif
            </div>

            <!-- Community Fee Contribution -->
            <div class="card p-6 space-y-4">
                <h3 class="text-base font-semibold text-text-primary">Contribute Fee Info</h3>
                <p class="text-xs text-text-muted">Know the fees for this school? Submit them to help the community.</p>
                
                @auth
                    <form method="POST" action="{{ route('institutes.fees.submit', $institute) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="fee_type_id" class="eb-label">Fee Type</label>
                            <select id="fee_type_id" name="fee_type_id" required class="eb-input text-xs">
                                @foreach($feeTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="academic_session" class="eb-label">Academic Session</label>
                            <input type="text" id="academic_session" name="academic_session" required placeholder="e.g. 2026"
                                   class="eb-input text-xs">
                        </div>
                        <div>
                            <label for="amount" class="eb-label">Amount (BDT)</label>
                            <input type="number" id="amount" name="amount" required min="0" placeholder="e.g. 4500"
                                   class="eb-input text-xs">
                        </div>
                        <div>
                            <label for="frequency" class="eb-label">Frequency</label>
                            <select id="frequency" name="frequency" required class="eb-input text-xs">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="one_time">One Time</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>
                        <div>
                            <label for="notes" class="eb-label">Source Notes / Link</label>
                            <textarea id="notes" name="notes" rows="2" placeholder="Where did you find this? e.g. official admission catalog"
                                      class="eb-input text-xs"></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-sm btn-block">
                            Submit Fee Details
                        </button>
                    </form>
                @else
                    <p class="text-xs text-text-muted rounded-md bg-surface-muted p-3 border border-divider text-center">
                        Please <a href="{{ route('login') }}" class="link">log in</a> to contribute fee structures.
                    </p>
                @endauth
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center gap-2">
                    <span class="icon-tile icon-tile-success"><i data-lucide="calendar-clock"></i></span>
                    <h2 class="text-lg font-semibold text-text-primary">Admission</h2>
                </div>
                @if($institute->admissionCirculars->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($institute->admissionCirculars as $circular)
                            @php
                                $status = $circular->admission_status;
                                $statusClass = match($status) {
                                    'open' => 'badge-success', 'ongoing' => 'badge-success',
                                    'upcoming' => 'badge-warning', 'closed' => 'badge-danger',
                                    default => 'badge-neutral'
                                };
                            @endphp
                            <div class="rounded-lg border border-divider bg-surface-muted p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold text-text-primary">{{ $circular->title ?? 'Admission ' . ucfirst($status) }}</p>
                                    <span class="badge {{ $statusClass }} capitalize">{{ str_replace('_', ' ', $status) }}</span>
                                </div>
                                @if($circular->application_start_date)
                                    <p class="mt-2 flex items-center gap-1.5 text-sm text-text-secondary">
                                        <i data-lucide="calendar" class="h-4 w-4 text-text-muted"></i>
                                        {{ $circular->application_start_date->format('M d, Y') }}
                                        @if($circular->application_end_date) – {{ $circular->application_end_date->format('M d, Y') }}@endif
                                    </p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-text-muted">
                                    @if($circular->admission_test_required)<span><i data-lucide="file-check" class="mr-1 inline h-3.5 w-3.5"></i>Admission test</span>@endif
                                    @if($circular->interview_required)<span><i data-lucide="users" class="mr-1 inline h-3.5 w-3.5"></i>Interview</span>@endif
                                    @if($circular->online_application_available)<span><i data-lucide="globe" class="mr-1 inline h-3.5 w-3.5"></i>Online apply</span>@endif
                                </div>
                                @if($circular->application_url)
                                    <a href="{{ $circular->application_url }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm mt-3">
                                        <i data-lucide="external-link"></i> Apply / Details
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-text-muted">No admission information available yet.</p>
                @endif
            </div>

            <div class="card p-6">
                <div class="mb-4 flex items-center gap-2">
                    <span class="icon-tile icon-tile-brand"><i data-lucide="building-2"></i></span>
                    <h2 class="text-lg font-semibold text-text-primary">Facilities</h2>
                </div>
                @if($institute->facilities->isNotEmpty())
                    @php
                        $grouped = $institute->facilities->groupBy(fn ($f) => $f->group?->name ?? 'Other');
                    @endphp
                    <div class="space-y-4">
                        @foreach($grouped as $groupName => $facilities)
                            <div>
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-text-muted">{{ $groupName }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($facilities as $facility)
                                        <span class="chip">
                                            @if($facility->icon)<i data-lucide="{{ $facility->icon }}" class="h-3.5 w-3.5"></i>@endif
                                            {{ $facility->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-border py-8 text-center">
                        <i data-lucide="building-2" class="h-7 w-7 text-text-muted"></i>
                        <p class="text-sm text-text-muted">No facilities listed yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

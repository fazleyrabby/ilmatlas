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
<div class="max-w-4xl mx-auto px-4 py-8">
    <x-schema-educational-organization :institute="$institute" />

    <x-schema-breadcrumb :items="[
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Institutes', 'url' => route('institutes.index')],
        ['name' => $institute->name],
    ]" />

    <div class="mb-8">
        <a href="{{ route('institutes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to institutes</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-8">
        <div class="flex items-start gap-6">
            <div class="w-20 h-20 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-2xl flex-shrink-0">
                {{ substr($institute->name, 0, 1) }}
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $institute->name }}</h1>
                @if($institute->short_name)
                    <p class="text-gray-500 mt-1">{{ $institute->short_name }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-3">
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">{{ $institute->type?->name }}</span>
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">{{ ucfirst($institute->gender) }}</span>
                    @if($institute->religious_orientation !== 'not_applicable')
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">{{ ucfirst($institute->religious_orientation) }}</span>
                    @endif
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        onclick="compareAdd('{{ $institute->uuid }}', '{{ $institute->slug }}', '{{ addslashes($institute->name) }}')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors compare-btn"
                        data-uuid="{{ $institute->uuid }}"
                    >
                        + Add to Compare
                    </button>
                    @if($institute->owner_id === null)
                        <button onclick="document.getElementById('claimFormSection').classList.toggle('hidden')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Claim this School
                        </button>
                    @endif
                </div>

                <!-- Claim Form Wrapper -->
                <div id="claimFormSection" class="hidden mt-6 p-6 bg-gray-50 border rounded-xl space-y-4">
                    <h3 class="font-bold text-gray-900 text-base">Claim School Ownership</h3>
                    <p class="text-xs text-gray-500">Provide details or official school email verification to claim this profile.</p>
                    @auth
                        <form method="POST" action="{{ route('institutes.claim.store', $institute) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label for="claim_notes" class="block text-xs font-medium text-gray-700 mb-1">Verification details (Official role, contact number, details)</label>
                                <textarea id="claim_notes" name="notes" rows="3" required placeholder="Describe your position (e.g. Principal, IT Administrator) and school registration number..."
                                          class="w-full rounded-lg border-gray-300 shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            <div>
                                <label for="claim_proof" class="block text-xs font-medium text-gray-700 mb-1">Proof document (Employment card, license pdf, registration catalog)</label>
                                <input type="file" id="claim_proof" name="proof" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                                Submit Claim Request
                            </button>
                        </form>
                    @else
                        <p class="text-xs text-gray-600">Please <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">log in</a> to claim school profiles.</p>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="md:col-span-2 space-y-6">
            @if($institute->description)
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">About</h2>
                    <p class="text-gray-600">{{ $institute->description }}</p>
                </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Curriculums & Programs</h2>
                @if($institute->curriculums->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($institute->curriculums as $c)
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">{{ $c->name }}</span>
                        @endforeach
                    </div>
                @endif
                @if($institute->programs->isNotEmpty())
                    <div class="text-sm text-gray-600">
                        <strong>Programs:</strong>
                        {{ $institute->programs->pluck('name')->implode(', ') }}
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Contact & Location</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Address</dt>
                        <dd class="text-gray-900">{{ $institute->full_address ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Area</dt>
                        <dd class="text-gray-900">{{ $institute->area?->name }}, {{ $institute->upazila?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">District</dt>
                        <dd class="text-gray-900">{{ $institute->district?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Division</dt>
                        <dd class="text-gray-900">{{ $institute->division?->name }}</dd>
                    </div>
                    @if($institute->established_year)
                        <div>
                            <dt class="text-gray-500">Established</dt>
                            <dd class="text-gray-900">{{ $institute->established_year }}</dd>
                        </div>
                    @endif
                    @if($institute->institute_code)
                        <div>
                            <dt class="text-gray-500">EIIN</dt>
                            <dd class="text-gray-900">{{ $institute->institute_code }}</dd>
                        </div>
                    @endif
                </dl>

                @if($institute->contacts->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Contacts</h3>
                        @foreach($institute->contacts as $contact)
                            <div class="text-sm text-gray-600">
                                <span class="capitalize">{{ $contact->contact_type }}:</span>
                                {{ $contact->contact_value }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Reviews & Ratings Section -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <h2 class="text-xl font-bold text-gray-900">Reviews & Ratings</h2>
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-semibold text-gray-900">Avg Rating:</span>
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded font-bold text-sm">
                            {{ number_format($reviews->avg('rating') ?? 0, 1) }} / 5.0
                        </span>
                        <span class="text-xs text-gray-500">({{ $reviews->count() }} reviews)</span>
                    </div>
                </div>

                @if(session('success'))
                    <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Reviews List -->
                <div class="space-y-4">
                    @forelse($reviews as $rev)
                        <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-sm text-gray-900">{{ $rev->user?->name }}</span>
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $rev->rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $rev->comment }}</p>
                            <span class="text-xs text-gray-400 block">{{ $rev->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-4 text-center">No reviews have been written yet. Be the first to share your experience!</p>
                    @endforelse
                </div>

                <!-- Submit Review Form -->
                <div class="border-t border-gray-100 pt-6">
                    @auth
                        <h3 class="text-lg font-bold text-gray-900 mb-3">Write a Review</h3>
                        <form method="POST" action="{{ route('institutes.reviews.store', $institute) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                                <select id="rating" name="rating" required class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="5">5 Stars (Excellent)</option>
                                    <option value="4">4 Stars (Good)</option>
                                    <option value="3">3 Stars (Average)</option>
                                    <option value="2">2 Stars (Poor)</option>
                                    <option value="1">1 Star (Very Bad)</option>
                                </select>
                            </div>
                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Review Comment</label>
                                <textarea id="comment" name="comment" rows="4" required placeholder="Describe your experience with this school (curriculum, teachers, environment)..."
                                          class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                @error('comment')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                Submit Review
                            </button>
                        </form>
                    @else
                        <div class="p-4 bg-gray-50 border rounded-lg text-center">
                            <p class="text-sm text-gray-600">Please <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-semibold">log in</a> to write a review for this institute.</p>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Fee Information</h2>
                @if($institute->fees->isNotEmpty())
                    <div class="mb-4">
                        <p class="text-3xl font-bold text-indigo-600">{{ number_format($institute->estimated_monthly_fee, 0) }}</p>
                        <p class="text-sm text-gray-500">BDT / month (estimated)</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        @foreach($institute->fees->take(5) as $fee)
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ $fee->feeType?->name ?? 'Fee' }}</span>
                                <span class="text-gray-900 font-medium">{{ number_format($fee->amount, 0) }} BDT</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Fee information not available</p>
                @endif
            </div>

            <!-- Community Fee Contribution -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="font-bold text-gray-900 text-base">Contribute Fee Info</h3>
                <p class="text-xs text-gray-500">Know the fees for this school? Submit them to help the community.</p>
                
                @auth
                    <form method="POST" action="{{ route('institutes.fees.submit', $institute) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="fee_type_id" class="block text-xs font-medium text-gray-700 mb-0.5">Fee Type</label>
                            <select id="fee_type_id" name="fee_type_id" required class="w-full rounded-lg border-gray-300 shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($feeTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="academic_session" class="block text-xs font-medium text-gray-700 mb-0.5">Academic Session</label>
                            <input type="text" id="academic_session" name="academic_session" required placeholder="e.g. 2026"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="amount" class="block text-xs font-medium text-gray-700 mb-0.5">Amount (BDT)</label>
                            <input type="number" id="amount" name="amount" required min="0" placeholder="e.g. 4500"
                                   class="w-full rounded-lg border-gray-300 shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="frequency" class="block text-xs font-medium text-gray-700 mb-0.5">Frequency</label>
                            <select id="frequency" name="frequency" required class="w-full rounded-lg border-gray-300 shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="one_time">One Time</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </div>
                        <div>
                            <label for="notes" class="block text-xs font-medium text-gray-700 mb-0.5">Source Notes / Link</label>
                            <textarea id="notes" name="notes" rows="2" placeholder="Where did you find this? e.g. official admission catalog"
                                      class="w-full rounded-lg border-gray-300 shadow-sm text-xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium text-xs rounded-lg transition">
                            Submit Fee Details
                        </button>
                    </form>
                @else
                    <p class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                        Please <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">log in</a> to contribute fee structures.
                    </p>
                @endauth
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Admission</h2>
                @if($institute->admissionCirculars->isNotEmpty())
                    @foreach($institute->admissionCirculars as $circular)
                        <div class="text-sm">
                            <p class="font-medium text-gray-900">{{ $circular->title ?? 'Admission ' . ucfirst($circular->admission_status) }}</p>
                            <p class="text-gray-500">Status: <span class="capitalize">{{ $circular->admission_status }}</span></p>
                            @if($circular->application_start_date)
                                <p class="text-gray-500">{{ $circular->application_start_date->format('M d, Y') }} - {{ $circular->application_end_date?->format('M d, Y') }}</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-500">No admission information available</p>
                @endif
            </div>

            @if($institute->facilities->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Facilities</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($institute->facilities as $facility)
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">{{ $facility->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.public')

@section('title', $institute->name . ' — ILMATLAS')
@section('meta_description', Str::limit($institute->description ?? 'View detailed information about ' . $institute->name, 160))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
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
                <div class="mt-4">
                    <button
                        onclick="compareAdd('{{ $institute->uuid }}', '{{ $institute->slug }}', '{{ addslashes($institute->name) }}')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors compare-btn"
                        data-uuid="{{ $institute->uuid }}"
                    >
                        + Add to Compare
                    </button>
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

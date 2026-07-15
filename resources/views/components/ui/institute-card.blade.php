@props(['institute', 'compare' => true])

@php
    $verified = ($institute->verification_status ?? null) === 'verified';
    $fee = (float) ($institute->estimated_monthly_fee ?? 0);
@endphp

<article class="card card-hover group flex flex-col overflow-hidden">
    <a href="{{ route('institutes.show', $institute) }}" class="flex flex-1 flex-col p-5">
        <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-md bg-primary-50 text-lg font-bold text-primary-700 ring-1 ring-primary-100">
                {{ strtoupper(substr($institute->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="truncate font-semibold leading-snug text-text-primary group-hover:text-primary-700">
                        {{ $institute->name }}
                    </h3>
                    @if($verified)
                        <span class="badge badge-success flex-shrink-0" title="Verified">
                            <i data-lucide="badge-check"></i>
                        </span>
                    @endif
                </div>
                <p class="mt-1 flex items-center gap-1.5 text-metadata">
                    <i data-lucide="map-pin" class="h-3.5 w-3.5"></i>
                    <span class="truncate">{{ $institute->district?->name }}{{ $institute->upazila ? ', '.$institute->upazila->name : '' }}</span>
                </p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1.5">
            @if($institute->type)
                <span class="badge badge-neutral">{{ $institute->type->name }}</span>
            @endif
            @if($institute->gender)
                <span class="badge badge-neutral capitalize">{{ $institute->gender }}</span>
            @endif
        </div>

        <div class="mt-4 flex items-end justify-between border-t border-divider pt-4">
            <div>
                <div class="text-xs text-text-muted">Est. monthly fee</div>
                <div class="text-lg font-bold tabular-nums text-text-primary">
                    @if($fee > 0)
                        ৳{{ number_format($fee, 0) }}
                    @else
                        <span class="text-sm font-medium text-text-muted">Not available</span>
                    @endif
                </div>
            </div>
            <span class="inline-flex items-center gap-1 text-sm font-medium text-primary-700">
                View <i data-lucide="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-0.5"></i>
            </span>
        </div>
    </a>

    @if($compare)
        <div class="border-t border-divider px-5 py-2.5">
            <button
                class="compare-btn flex w-full items-center justify-center gap-1.5 rounded-md py-2 text-sm font-medium text-primary-700 transition-colors hover:bg-primary-50"
                data-uuid="{{ $institute->uuid }}"
                data-slug="{{ $institute->slug }}"
                data-name="{{ $institute->name }}"
            >
                <i data-lucide="git-compare" class="h-4 w-4"></i>
                + Add to Compare
            </button>
        </div>
    @endif
</article>

@props([
    'title' => 'Find the right school for your child.',
    'subtitle' => 'Search schools, madrasas, colleges, fees and admissions — and compare institutions across Bangladesh.',
    'searchAction' => '',
    'popular' => ['English Version', 'Madrasa', 'English Medium', 'Girls School', 'Qawmi'],
    'stats' => [],
    'video' => '',
    'image' => asset('assets/media/hero-fallback.png'),
])

@php
    $hasMedia = !empty($video) || !empty($image);
    if ($hasMedia) { $overHero = true; }
@endphp

<section class="hero {{ $hasMedia ? 'hero-media' : '' }} relative overflow-hidden border-b border-border bg-surface"
         @if($hasMedia) x-data x-init="document.body.classList.add('has-hero')" @endif>
    @if($hasMedia)
        <div class="hero-bg" aria-hidden="true">
            @if(!empty($image))
                <img src="{{ $image }}" alt="" class="hero-image">
            @endif
        </div>
        <div class="hero-overlay"></div>
    @else
        <div class="pointer-events-none absolute inset-0 opacity-70"
             style="background:
                radial-gradient(60rem 30rem at 15% -10%, var(--color-primary-50), transparent 60%),
                radial-gradient(50rem 28rem at 100% 0%, color-mix(in srgb, var(--color-primary-100) 60%, transparent), transparent 55%);">
        </div>
    @endif

    <div class="container-page relative z-10 py-16 sm:py-20 lg:py-24">
        <div class="max-w-3xl">
            <span class="badge badge-glass-light mb-5">
                <i data-lucide="shield-check"></i>
                Trusted Education Directory
            </span>

            <h1 class="text-display {{ $hasMedia ? 'text-white' : 'text-text-primary' }}">{{ $title }}</h1>
            <p class="mt-5 max-w-2xl text-lg leading-relaxed {{ $hasMedia ? 'text-white/85' : 'text-text-secondary' }}">{{ $subtitle }}</p>

            <form method="GET" action="{{ $searchAction }}" class="mt-8 max-w-2xl relative"
                  x-data="{
                      q: '',
                      suggestions: [],
                      open: false,
                      loading: false,
                      active: -1,
                      timer: null,
                      fetchSuggestions() {
                          clearTimeout(this.timer);
                          this.timer = setTimeout(() => {
                              if (this.q.length < 2) { this.suggestions = []; this.open = false; return; }
                              this.loading = true;
                              fetch('{{ url('/api/search/autocomplete') }}?q=' + encodeURIComponent(this.q))
                                  .then(r => r.json())
                                  .then(data => { this.suggestions = data; this.active = -1; this.open = data.length > 0; })
                                  .catch(() => { this.suggestions = []; this.open = false; })
                                  .finally(() => { this.loading = false; });
                          }, 200);
                      },
                      go(s) { if (s) { window.location = '{{ $searchAction }}?q=' + encodeURIComponent(s.name); } },
                      submitFor(s) { window.location = '{{ $searchAction }}?q=' + encodeURIComponent(s.name); }
                  }"
                  @click.outside="open = false">
                <div class="search-shell {{ $hasMedia ? 'search-shell-glass' : '' }}">
                    <i data-lucide="search" class="text-text-muted"></i>
                    <input type="text" name="q" x-model="q" @input="fetchSuggestions()"
                           @keydown.arrow-down.prevent="open && (active = Math.min(active + 1, suggestions.length - 1))"
                           @keydown.arrow-up.prevent="open && (active = Math.max(active - 1, -1))"
                           @keydown.enter.prevent="active > -1 ? submitFor(suggestions[active]) : $el.closest('form').submit()"
                           @keydown.escape="open = false"
                           placeholder="Search by name, location, EIIN…"
                           class="search-input" aria-label="Search institutions" autocomplete="off">
                    <span class="hidden sm:inline-flex kbd">/</span>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i data-lucide="search"></i>
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>

                {{-- Autocomplete dropdown --}}
                <div x-show="open" x-transition.opacity
                     class="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-xl border border-divider bg-surface shadow-lg"
                     style="display:none">
                    <template x-for="(s, i) in suggestions" :key="s.uuid">
                        <button type="button" @click="go(s)"
                                @mouseenter="active = i"
                                :class="active === i ? 'bg-primary-50' : ''"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md bg-primary-50 text-primary-700">
                                <i data-lucide="graduation-cap" class="h-4 w-4"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-text-primary" x-text="s.name"></span>
                                <span class="block truncate text-xs text-text-muted">
                                    <span x-text="s.type"></span><span x-show="s.district"> · </span><span x-text="s.district"></span>
                                </span>
                            </span>
                            <i data-lucide="corner-down-left" class="h-4 w-4 text-text-muted"></i>
                        </button>
                    </template>
                    <div x-show="loading" class="px-4 py-3 text-sm text-text-muted">Searching…</div>
                </div>
            </form>

            @if(!empty($popular))
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="text-metadata mr-1 {{ $hasMedia ? 'text-white/80' : '' }}">Popular</span>
                    @foreach($popular as $term)
                        <a href="{{ $searchAction }}?q={{ urlencode($term) }}" class="chip {{ $hasMedia ? 'chip-invert' : '' }}">{{ $term }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        @if(!empty($stats))
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-4 max-w-3xl">
                @foreach($stats as $stat)
                    <div class="stat-tile">
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

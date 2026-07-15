<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduBase — Education Discovery Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Discover, compare, and analyze educational institutions across Bangladesh')">
    <meta name="keywords" content="@yield('meta_keywords', 'Bangladesh, education, schools, madrasas, colleges, institutes, fees, admission')">
    <meta name="robots" content="@yield('robots', 'index, follow')">

    <meta property="og:title" content="@yield('og_title', 'EduBase — Education Discovery Platform')">
    <meta property="og:description" content="@yield('og_description', 'Discover, compare, and analyze educational institutions across Bangladesh')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('assets/og-default.png'))">
    <meta property="og:site_name" content="EduBase">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'EduBase — Education Discovery Platform')">
    <meta name="twitter:description" content="@yield('og_description', 'Discover, compare, and analyze educational institutions across Bangladesh')">
    <meta name="twitter:image" content="@yield('og_image', asset('assets/og-default.png'))">

    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Spectral:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,200;1,300;1,400;1,500;1,600;1,700;1,800&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <x-schema-website />
</head>
<body class="font-sans antialiased text-text-primary bg-background @if(isset($overHero) && $overHero) has-hero @endif">
    <header x-data="{ scrolled: false, open: false, overHero: false }"
            @scroll.window="scrolled = window.scrollY > 8"
            @over-hero.window="overHero = true"
            :class="(overHero && !scrolled) ? 'is-transparent' : 'is-solid'"
            class="site-header sticky top-0 z-40 transition-[box-shadow,background-color,border-color]">
        <div class="container-page flex h-16 items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight text-primary-700" style="font-family: var(--font-serif);">
                <i data-lucide="graduation-cap" class="h-6 w-6"></i>
                EduBase
            </a>

            <nav class="hidden items-center gap-1 md:flex">
                <a href="{{ route('institutes.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-surface-muted hover:text-text-primary">Institutes</a>
                <a href="{{ route('search') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-surface-muted hover:text-text-primary">Search</a>
                <a href="{{ route('about') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-surface-muted hover:text-text-primary">About</a>
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('search') }}" class="btn btn-ghost btn-icon md:hidden" aria-label="Search">
                    <i data-lucide="search"></i>
                </a>
                <button type="button" data-compare-indicator
                        class="relative hidden items-center gap-2 btn btn-secondary btn-sm sm:inline-flex">
                    <i data-lucide="git-compare"></i>
                    <span>Compare</span>
                    <span data-compare-count class="ml-0.5 hidden min-w-5 rounded-full bg-primary-600 px-1.5 py-0.5 text-center text-xs font-semibold text-white"></span>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">Register</a>
                @endauth

                <button type="button" class="btn btn-ghost btn-icon md:hidden" @click="open = !open" aria-label="Menu">
                    <i data-lucide="menu" x-show="!open"></i>
                    <i data-lucide="x" x-show="open" x-cloak></i>
                </button>
            </div>
        </div>

        <div x-show="open" x-collapse x-cloak class="border-t border-border bg-surface md:hidden">
            <nav class="container-page flex flex-col gap-1 py-3">
                <a href="{{ route('institutes.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary hover:bg-surface-muted">Institutes</a>
                <a href="{{ route('search') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary hover:bg-surface-muted">Search</a>
                <a href="{{ route('about') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary hover:bg-surface-muted">About</a>
                <div class="my-2 border-t border-divider"></div>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary hover:bg-surface-muted">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-danger">Logout</button></form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-text-secondary hover:bg-surface-muted">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm mt-1">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <div id="compareTray" class="hidden fixed bottom-0 left-0 right-0 z-50 border-t border-border bg-surface/95 px-4 py-3 shadow-lg backdrop-blur">
        <div class="container-page flex items-center justify-between gap-4">
            <div class="flex flex-1 flex-wrap items-center gap-2 compare-list"></div>
            <div class="flex flex-shrink-0 items-center gap-2">
                <button data-clear-compare class="btn btn-ghost btn-sm">Clear</button>
                <button class="compare-cta btn btn-primary btn-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="git-compare"></i>
                    Compare
                </button>
            </div>
        </div>
    </div>

    <footer class="mt-20 border-t border-border bg-surface">
        <div class="container-page py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="max-w-xs">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 text-lg font-bold text-primary-700" style="font-family: var(--font-serif);">
                        <i data-lucide="graduation-cap" class="h-5 w-5"></i> EduBase
                    </a>
                    <p class="mt-3 text-sm leading-relaxed text-text-secondary">
                        The trusted directory for discovering, comparing and researching educational institutions across Bangladesh.
                    </p>
                </div>
                <div>
                    <h4 class="text-eyebrow mb-3">Discover</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('institutes.index') }}" class="text-text-secondary hover:text-primary-700">All Institutes</a></li>
                        <li><a href="{{ route('search') }}" class="text-text-secondary hover:text-primary-700">Search</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-eyebrow mb-3">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('about') }}" class="text-text-secondary hover:text-primary-700">About</a></li>
                        <li><a href="{{ route('contact') }}" class="text-text-secondary hover:text-primary-700">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-eyebrow mb-3">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('privacy') }}" class="text-text-secondary hover:text-primary-700">Privacy</a></li>
                        <li><a href="{{ route('terms') }}" class="text-text-secondary hover:text-primary-700">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-divider pt-6 text-sm text-text-muted">
                &copy; {{ date('Y') }} EduBase. All rights reserved.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

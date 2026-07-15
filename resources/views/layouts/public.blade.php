<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ILMATLAS — Education Discovery Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Discover, compare, and analyze educational institutions across Bangladesh')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-900 bg-white">
    <header class="border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-600">ILMATLAS</a>
            <nav class="flex items-center gap-6">
                <a href="{{ route('institutes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Institutes</a>
                <a href="{{ route('search') }}" class="text-sm text-gray-600 hover:text-gray-900">Search</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <div id="compareTray" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-wrap compare-list"></div>
            <div class="flex items-center gap-3">
                <button onclick="compareClear()" class="text-sm text-gray-500 hover:text-gray-700">Clear</button>
                <button class="compare-cta px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Compare
                </button>
            </div>
        </div>
    </div>

    <footer class="border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-8 text-sm text-gray-500">
            &copy; {{ date('Y') }} ILMATLAS. All rights reserved.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduBase Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-white border-r border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-indigo-600">EduBase</a>
            </div>
            <nav class="p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Dashboard
                </a>
                <a href="{{ route('admin.institutes.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Institutes
                </a>
                <a href="{{ route('admin.taxonomies.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Taxonomies
                </a>
                <a href="{{ route('admin.fees.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Fees
                </a>
                <a href="{{ route('admin.admissions.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Admissions
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Reviews Queue
                </a>
                <a href="{{ route('admin.claims.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Claims Queue
                </a>
                <a href="{{ route('admin.scrapers.sources.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Scrapers
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" class="pt-4 border-t border-gray-200">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg">
                        Logout
                    </button>
                </form>
            </nav>
        </aside>
        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduBase Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-ubuntu antialiased">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-white border-r border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-indigo-600">EduBase</a>
            </div>
            <nav class="p-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Dashboard
                </a>
                <a href="{{ route('admin.institutes.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    Institutes
                </a>
                <a href="{{ route('admin.taxonomies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="tags" class="w-4 h-4"></i>
                    Taxonomies
                </a>
                <a href="{{ route('admin.fees.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                    Fees
                </a>
                <a href="{{ route('admin.admissions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    Admissions
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    Reviews Queue
                </a>
                <a href="{{ route('admin.claims.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Claims Queue
                </a>
                <a href="{{ route('admin.scrapers.sources.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                    <i data-lucide="spider" class="w-4 h-4"></i>
                    Scrapers
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" class="pt-4 border-t border-gray-200">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </button>
                </form>
            </nav>
        </aside>
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>

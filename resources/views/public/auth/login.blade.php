@extends('layouts.public')

@section('title', 'Log In — EduBase')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome Back</h1>
        <p class="text-sm text-gray-500 mb-6">Log in to manage your favorites, saved comparisons, and alerts.</p>

        <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <x-input type="email" id="email" name="email" :value="old('email')" required autofocus />
                @error('email')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <x-input type="password" id="password" name="password" required />
                @error('password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                </div>
            </div>

            <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 text-white font-medium text-sm rounded-lg hover:bg-indigo-700 transition">
                Log In
            </button>

            <button type="button"
                onclick="document.getElementById('email').value='admin@edubase.com'; document.getElementById('password').value='password'; document.getElementById('loginForm').submit();"
                class="w-full mt-2 bg-gray-100 hover:bg-gray-200 text-gray-800 py-2.5 px-4 rounded-lg text-sm font-medium transition duration-150">
                Quick Demo Admin Login
            </button>
        </form>

        <p class="text-sm text-gray-500 mt-6 text-center">
            Don't have an account? <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Create one</a>
        </p>
    </div>
</div>
@endsection

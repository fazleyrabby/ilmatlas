<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduBase Admin Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h1 class="text-2xl font-bold text-center mb-1">EduBase</h1>
                <p class="text-sm text-gray-500 text-center mb-6">Admin Panel</p>

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form id="adminLoginForm" method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
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

                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
                        Sign in
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide text-center mb-3">Quick Demo Login</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" data-role="super_admin"
                            class="demo-login-btn px-3 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-medium transition duration-150 border border-purple-200">
                            Super Admin
                        </button>
                        <button type="button" data-role="admin"
                            class="demo-login-btn px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition duration-150 border border-indigo-200">
                            Admin
                        </button>
                        <button type="button" data-role="editor"
                            class="demo-login-btn px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition duration-150 border border-blue-200">
                            Editor
                        </button>
                        <button type="button" data-role="moderator"
                            class="demo-login-btn px-3 py-2 bg-teal-50 hover:bg-teal-100 text-teal-700 rounded-lg text-xs font-medium transition duration-150 border border-teal-200">
                            Moderator
                        </button>
                        <button type="button" data-role="data_operator"
                            class="demo-login-btn px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-medium transition duration-150 border border-amber-200">
                            Data Operator
                        </button>
                        <button type="button" data-role="analyst"
                            class="demo-login-btn px-3 py-2 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 rounded-lg text-xs font-medium transition duration-150 border border-cyan-200">
                            Analyst
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script @nonce>
        document.addEventListener('DOMContentLoaded', () => {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const form = document.getElementById('adminLoginForm');

            document.querySelectorAll('.demo-login-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const role = btn.dataset.role;
                    if (emailInput && passwordInput) {
                        emailInput.value = role + '@edubase.com';
                        passwordInput.value = 'password';
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>

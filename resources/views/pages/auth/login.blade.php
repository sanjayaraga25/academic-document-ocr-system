<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-bg">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-primary text-white text-2xl font-bold mb-4">
                    O
                </div>
                <h1 class="text-xl font-bold text-text-primary">Academic OCR</h1>
                <p class="mt-1 text-sm text-text-secondary">Sign in to your account</p>
            </div>
            <form method="POST" action="{{ route('login') }}" class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-lg border border-border bg-bg px-3 py-2.5 text-sm text-text-primary placeholder-text-secondary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        @error('email')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary mb-1">Password</label>
                        <input type="password" name="password" required
                               class="w-full rounded-lg border border-border bg-bg px-3 py-2.5 text-sm text-text-primary placeholder-text-secondary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        @error('password')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="rounded border-border text-primary focus:ring-primary">
                            <span class="text-sm text-text-secondary">Remember me</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                        Sign In
                    </button>
                </div>
            </form>
            <p class="mt-4 text-center text-sm text-text-secondary">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-primary hover:text-primary-dark">Register</a>
            </p>
        </div>
    </div>
</body>
</html>

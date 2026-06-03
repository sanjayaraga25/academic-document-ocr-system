<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Academic OCR') }} - @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-bg text-text-primary">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">
        <x-sidebar />
        <div class="flex flex-1 flex-col overflow-hidden" :class="sidebarOpen ? 'ml-[280px]' : 'ml-0'">
            <x-navbar />
            <main class="flex-1 overflow-y-auto p-6">
                <div class="mx-auto" style="max-width: var(--container-max)">
                    @if(session('success'))
                        <div class="mb-4 rounded-lg border border-success bg-success-light bg-opacity-50 px-4 py-3 text-sm font-medium text-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 rounded-lg border border-danger bg-danger-light bg-opacity-50 px-4 py-3 text-sm font-medium text-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>

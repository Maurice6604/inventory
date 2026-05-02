<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'InventoryMS') }} - @yield('title', 'Dashboard')</title>

        <!-- Fonts (Inter) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts & Tailwind CSS -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-[hsl(var(--text-main))] bg-[hsl(var(--background))] flex h-screen overflow-hidden">

        <!-- Page load progress bar -->
        <div id="page-progress"></div>

        <!-- Sidebar Navigation -->
        @include('layouts.sidebar')

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white border-b border-[hsl(var(--border))] h-16 flex items-center justify-between px-6 shrink-0 z-10">
                <div class="flex items-center gap-3">
                    <h1 class="text-lg font-bold text-[hsl(var(--text-main))]">@yield('header')</h1>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Dark mode toggle (commented out to lock dark mode) -->
                    <!-- <button id="dark-mode-btn" onclick="toggleDarkMode()" class="btn-icon" title="Toggle Dark Mode"></button> -->

                    <!-- Activity link -->
                    <a href="{{ route('stock.history') }}" class="btn-icon" title="Stock Activity">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </a>

                    <!-- User Menu Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2.5 pl-3 pr-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-xl border border-[hsl(var(--border))] transition-colors focus:outline-none">
                            <div class="w-7 h-7 bg-[hsl(var(--primary))] rounded-full flex items-center justify-center text-white font-bold text-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-semibold text-[hsl(var(--text-main))]  hidden sm:block">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 text-[hsl(var(--text-muted))]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" @click.away="open = false" style="display: none;"
                             class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-[hsl(var(--border))] py-2 z-50">
                            <div class="px-4 py-2 border-b border-[hsl(var(--border))] mb-1">
                                <p class="text-sm font-semibold text-[hsl(var(--text-main))]">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-[hsl(var(--text-muted))] capitalize">{{ Auth::user()->role }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-[hsl(var(--text-main))] hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-[hsl(var(--text-muted))]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                My Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-[hsl(var(--danger))] hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb bar -->
            @hasSection('breadcrumbs')
            <div class="px-6 py-2 border-b border-[hsl(var(--border))] bg-gray-50/50 text-xs text-[hsl(var(--text-muted))] flex items-center gap-1.5">
                <a href="{{ route('dashboard') }}" class="hover:text-[hsl(var(--primary))] transition-colors">Dashboard</a>
                @yield('breadcrumbs')
            </div>
            @endif

            <!-- Main Scrollable Area -->
            <main class="flex-1 overflow-y-auto p-6 relative">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 premium-card bg-green-50/80 border-green-200 text-[hsl(var(--success))] p-4 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 premium-card bg-red-50/80 border-red-200 text-[hsl(var(--danger))] p-4 flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Page Content -->
                <div class="animate-fade-in">
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
        @stack('scripts')
    </body>
</html>

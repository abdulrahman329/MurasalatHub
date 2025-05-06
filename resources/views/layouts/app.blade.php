<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{}" x-cloak dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Alpine.js for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Vite for compiling Tailwind CSS and app JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">

    <!-- System-wide banner -->
    <x-banner />

    <div class="min-h-screen flex">

        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white border-l dark:bg-gray-900 dark:border-gray-700 h-full z-30 relative lg:fixed">
            <div class="p-6 text-xl font-semibold text-gray-800 dark:text-white">
                {{ config('app.name') }}
            </div>
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="block px-6 py-2 text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700 transition duration-150 ease-in-out">Dashboard</a>
                <a href="#" class="block px-6 py-2 text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700 transition duration-150 ease-in-out">Users</a>
                <a href="#" class="block px-6 py-2 text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700 transition duration-150 ease-in-out">Settings</a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 lg:mr-64">
            
            <!-- Top Navigation Bar -->
            <nav class="bg-gray-100 border-b dark:bg-gray-800 w-full dark:border-gray-700 px-4 py-4">
                <div class="flex justify-between items-center">
                    
                    <!-- Page Title -->
                    <div>
                        <h1 class="text-xl font-bold text-gray-600 dark:text-white">Dashboard</h1>
                    </div>

                    <!-- Profile Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img src="{{ Auth::user()->profile_photo_url }}" class="w-8 h-8 rounded-full" alt="{{ Auth::user()->name }}">
                            @else
                                <span class="text-gray-800 dark:text-white">{{ Auth::user()->name }}</span>
                            @endif
                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded shadow z-50">
                            <div class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                                {{ __('Manage Account') }}
                            </div>
                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif
                            <div class="border-t dark:border-gray-600"></div>
                            <!-- Logout Link -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Optional Page Header Section -->
            @if (isset($header))
                <header class="bg-white shadow dark:bg-gray-900 dark:shadow-md">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content Slot -->
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Livewire Modals Stack -->
    @stack('modals')

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>

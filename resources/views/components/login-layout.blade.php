{{-- Login layout – Lineone sign-in-2 style (split layout with illustration) --}}
@props([
    // 'auth' (default): split layout with illustration + narrow form panel
    // 'legal': no illustration + wide readable panel
    'variant' => 'auth',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}@isset($title) - {{ $title }}@endisset</title>

        @include('partials.favicons')

        <script>
            localStorage.getItem("_x_darkMode_on") === "true" && document.documentElement.classList.add("dark");
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans text-slate-700 antialiased bg-slate-50 dark:bg-navy-900 dark:text-navy-200" x-data x-bind="$store.global.documentBody">
        @php
            $isLegal = $variant === 'legal';
        @endphp
        <div id="root" class="min-h-screen flex flex-col lg:flex-row">
            {{-- Fixed top logo (desktop) --}}
            <div class="fixed top-0 z-10 hidden p-6 lg:block lg:px-12">
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <x-application-logo class="size-12 w-auto object-contain" />
                    <p class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100">
                        {{ config('app.name') }}
                    </p>
                </a>
            </div>

            {{-- Locale switcher --}}
            <div class="fixed top-4 z-20 flex items-center gap-2 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }}">
                <a href="{{ route('locale.switch', 'en') }}" class="text-sm {{ app()->getLocale() === 'en' ? 'font-semibold text-primary dark:text-accent-light' : 'text-slate-500 hover:text-slate-700 dark:text-navy-400 dark:hover:text-navy-200' }}">{{ __('English') }}</a>
                <span class="text-slate-300 dark:text-navy-500">|</span>
                <a href="{{ route('locale.switch', 'ar') }}" class="text-sm {{ app()->getLocale() === 'ar' ? 'font-semibold text-primary dark:text-accent-light' : 'text-slate-500 hover:text-slate-700 dark:text-navy-400 dark:hover:text-navy-200' }}">{{ __('Arabic') }}</a>
            </div>

            {{-- Left: Illustration (hidden on mobile) --}}
            @unless ($isLegal)
                <div class="hidden w-full place-items-center lg:grid lg:flex-1">
                    <div class="w-full max-w-lg p-6">
                        <img class="w-full" x-show="!$store.global.isDarkModeEnabled" src="{{ asset('images/illustrations/dashboard-check.svg') }}" alt="" />
                        <img class="w-full" x-show="$store.global.isDarkModeEnabled" x-cloak src="{{ asset('images/illustrations/dashboard-check-dark.svg') }}" alt="" />
                    </div>
                </div>
            @endunless

            {{-- Right: panel — legal pages use full remaining width for readable long-form content --}}
            <main class="flex w-full flex-col items-center justify-center bg-white dark:bg-navy-700 {{ $isLegal ? 'lg:flex-1 lg:max-w-none' : 'lg:max-w-md' }} lg:min-h-screen">
                <div class="flex w-full {{ $isLegal ? 'max-w-6xl grow flex-col justify-start py-8 px-4 sm:px-8' : 'max-w-sm grow flex-col justify-center p-5' }}">
                    {{-- Mobile logo --}}
                    <div class="text-center">
                        <x-application-logo class="mx-auto size-16 w-auto object-contain lg:hidden" />
                        <div class="mt-4">
                            @unless ($isLegal)
                                <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                                    {{ $heading ?? __('Welcome Back') }}
                                </h2>
                                <p class="text-slate-400 dark:text-navy-300">
                                    {{ $subheading ?? __('Please sign in to continue') }}
                                </p>
                            @endunless
                        </div>
                    </div>
                    <div class="{{ $isLegal ? 'mt-6' : 'mt-10' }}">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>

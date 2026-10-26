{{-- Register layout – Lineone sign-up-1 style (centered card) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name', 'NUBL') }}@isset($title) - {{ $title }}@endisset</title>

        @include('partials.favicons')

        <script>
            localStorage.getItem("_x_darkMode_on") === "true" && document.documentElement.classList.add("dark");
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans text-slate-700 antialiased bg-slate-50 dark:bg-navy-900 dark:text-navy-200" x-data x-bind="$store.global.documentBody">
        <main class="grid min-h-screen w-full grow grid-cols-1 place-items-center px-4 py-6">
            {{-- Locale switcher --}}
            <div class="absolute top-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} z-20 flex items-center gap-2">
                <a href="{{ route('locale.switch', 'en') }}" class="text-sm {{ app()->getLocale() === 'en' ? 'font-semibold text-primary dark:text-accent-light' : 'text-slate-500 hover:text-slate-700 dark:text-navy-400 dark:hover:text-navy-200' }}">{{ __('English') }}</a>
                <span class="text-slate-300 dark:text-navy-500">|</span>
                <a href="{{ route('locale.switch', 'ar') }}" class="text-sm {{ app()->getLocale() === 'ar' ? 'font-semibold text-primary dark:text-accent-light' : 'text-slate-500 hover:text-slate-700 dark:text-navy-400 dark:hover:text-navy-200' }}">{{ __('Arabic') }}</a>
            </div>

            <div class="w-full {{ $maxWidth === 'wide' ? 'max-w-4xl' : 'max-w-2xl' }} p-4 sm:px-5">
                <div class="text-center">
                    <a href="{{ url('/') }}">
                        <x-application-logo class="mx-auto size-16 w-auto object-contain" />
                    </a>
                    <div class="mt-4">
                        <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                            {{ $heading ?? __('Welcome To') . ' ' . config('app.name') }}
                        </h2>
                        <p class="text-slate-400 dark:text-navy-300">
                            {{ $subheading ?? __('Please sign up to continue') }}
                        </p>
                    </div>
                </div>
                <div class="card mt-5 rounded-lg p-5 lg:p-7">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </body>
</html>

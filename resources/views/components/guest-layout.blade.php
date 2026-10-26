{{-- Layout for guest pages: register, login, approval-pending, etc. --}}
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
    <body class="font-sans text-slate-700 antialiased bg-slate-50 dark:bg-navy-900 dark:text-navy-200">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <div class="absolute top-4 end-4 flex items-center gap-2">
                <a href="{{ route('locale.switch', 'en') }}" class="text-sm {{ app()->getLocale() === 'en' ? 'font-semibold text-primary dark:text-accent-light' : 'text-slate-500 hover:text-slate-700 dark:text-navy-400 dark:hover:text-navy-200' }}">{{ __('English') }}</a>
                <span class="text-slate-300 dark:text-navy-500">|</span>
                <a href="{{ route('locale.switch', 'ar') }}" class="text-sm {{ app()->getLocale() === 'ar' ? 'font-semibold text-primary dark:text-accent-light' : 'text-slate-500 hover:text-slate-700 dark:text-navy-400 dark:hover:text-navy-200' }}">{{ __('Arabic') }}</a>
            </div>
            <div>
                <a href="/">
                    <x-application-logo class="h-20 w-auto max-w-[180px]" />
                </a>
            </div>

            <div class="w-full {{ $maxWidth === 'wide' ? 'sm:max-w-2xl' : 'sm:max-w-md' }} mt-6 px-6 py-6 sm:px-8 sm:py-8 bg-white dark:bg-navy-750 shadow-soft border border-slate-150 dark:border-navy-600 rounded-xl overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

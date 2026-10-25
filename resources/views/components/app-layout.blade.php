@props([
    'title' => null,
    /** التحكم بالشريط الجانبي */
    'isSidebarOpen' => 'false',
    'isHeaderBlur' => 'true',
    'hasMinSidebar' => null,
    'headerSticky' => null,
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    @include('partials.favicons')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }} @isset($title) - {{ $title }} @endisset</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        localStorage.getItem("_x_darkMode_on") === "true" && document.documentElement.classList.add("dark");
    </script>
</head>
<body x-data x-bind="$store.global.documentBody"
    class="@isset($isSidebarOpen) {{ $isSidebarOpen === 'true' ? 'is-sidebar-open' : '' }} @endisset @isset($isHeaderBlur) {{ $isHeaderBlur === 'true' ? 'is-header-blur' : '' }} @endisset @isset($hasMinSidebar) {{ $hasMinSidebar === 'true' ? 'has-min-sidebar' : '' }} @endisset @isset($headerSticky) {{ $headerSticky === 'false' ? 'is-header-not-sticky' : '' }} @endisset">

    <x-app-preloader />

    <div id="root" class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900" x-cloak>
        <div class="sidebar print:hidden">
            <x-app-partials.main-sidebar />
            <x-app-partials.sidebar-panel />
        </div>

        @include('components.app-partials.header', ['header' => $header ?? null])
        <x-app-partials.right-sidebar />

        <main class="main-content w-full px-[var(--margin-x)] pb-8">
            @if(session('success') || session('error') || session('warning') || session('info'))
                <div class="pt-4">
                    @if(session('success'))
                        <x-lineone-alert type="success" dismissible>{{ session('success') }}</x-lineone-alert>
                    @endif
                    @if(session('error'))
                        <x-lineone-alert type="danger" dismissible>{{ session('error') }}</x-lineone-alert>
                    @endif
                    @if(session('warning'))
                        <x-lineone-alert type="warning" dismissible>{{ session('warning') }}</x-lineone-alert>
                    @endif
                    @if(session('info'))
                        <x-lineone-alert type="info" dismissible>{{ session('info') }}</x-lineone-alert>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <div id="x-teleport-target"></div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Sticky wrapper for header + breadcrumb -->
        <div id="sticky-wrapper" class="relative pt-12">
            <!-- Page Heading -->
            @isset($header)
            <header id="page-header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <!-- Breadcrumb -->
            @isset($breadcrumb)
            <div id="breadcrumb-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $breadcrumb }}
            </div>
            @endisset
        </div>

        <!-- Page Content -->
        <main>
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        @include('components.dashboard-fab')
    </div>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('sticky-wrapper');
            const navHeight = 48;
            
            function handleScroll() {
                const scrollTop = window.scrollY;
                
                if (scrollTop > navHeight) {
                    if (wrapper) {
                        wrapper.style.position = 'fixed';
                        wrapper.style.top = '0';
                        wrapper.style.left = '0';
                        wrapper.style.right = '0';
                        wrapper.style.zIndex = '50';
                        wrapper.style.backgroundColor = '#f3f4f6';
                        wrapper.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
                        wrapper.style.paddingTop = '0.5rem';
                        wrapper.style.paddingBottom = '';
                    }
                } else {
                    if (wrapper) {
                        wrapper.style.position = '';
                        wrapper.style.top = '';
                        wrapper.style.left = '';
                        wrapper.style.right = '';
                        wrapper.style.zIndex = '';
                        wrapper.style.backgroundColor = '';
                        wrapper.style.boxShadow = '';
                        wrapper.style.paddingTop = '';
                        wrapper.style.paddingBottom = '0.5rem';
                    }
                }
            }

            window.addEventListener('scroll', handleScroll);
            handleScroll();
        });
    </script>
</body>

</html>

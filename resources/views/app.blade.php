<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Pengolahan Nilai Rapor Kurikulum Merdeka') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Tailwind CDN fallback: load only if built Tailwind didn't apply (e.g. Docker build tanpa config) -->
        <script>
        (function() {
            function loadTailwindCdn() {
                var s = document.createElement('script');
                s.src = 'https://cdn.tailwindcss.com?plugins=forms';
                s.async = false;
                document.head.appendChild(s);
            }
            function isTailwindActive() {
                var test = document.createElement('div');
                test.className = 'bg-gray-50';
                test.style.cssText = 'position:absolute;left:-9999px;';
                document.body.appendChild(test);
                var bg = getComputedStyle(test).backgroundColor;
                document.body.removeChild(test);
                return bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent';
            }
            setTimeout(function() {
                if (!isTailwindActive()) loadTailwindCdn();
            }, 800);
        })();
        </script>
    </head>
    <body class="font-sans antialiased">
        <div id="app"></div>
    </body>
</html>
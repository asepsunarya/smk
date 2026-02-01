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
            function injectFallbackComponentStyles() {
                var style = document.createElement('style');
                style.textContent = [
                    'body{background-color:rgb(249 250 251)}',
                    '.btn{display:inline-block;padding:.5rem 1rem;border-radius:.375rem;font-weight:500;transition:color .15s,background-color .15s,border-color .15s;outline:2px solid transparent;outline-offset:2px}',
                    '.btn:focus{outline:2px solid transparent;outline-offset:2px;box-shadow:0 0 0 2px #fff,0 0 0 4px #3b82f6}',
                    '.btn-primary{background-color:#2563eb;color:#fff}',
                    '.btn-primary:hover{background-color:#1d4ed8}',
                    '.btn-primary:focus{box-shadow:0 0 0 2px #fff,0 0 0 4px #3b82f6}',
                    '.btn-secondary{background-color:#4b5563;color:#fff}',
                    '.btn-secondary:hover{background-color:#374151}',
                    '.btn-secondary:focus{box-shadow:0 0 0 2px #fff,0 0 0 4px #6b7280}',
                    '.btn-success{background-color:#16a34a;color:#fff}',
                    '.btn-success:hover{background-color:#15803d}',
                    '.btn-success:focus{box-shadow:0 0 0 2px #fff,0 0 0 4px #22c55e}',
                    '.btn-danger{background-color:#dc2626;color:#fff}',
                    '.btn-danger:hover{background-color:#b91c1c}',
                    '.btn-danger:focus{box-shadow:0 0 0 2px #fff,0 0 0 4px #ef4444}',
                    '.btn-warning{background-color:#ca8a04;color:#fff}',
                    '.btn-warning:hover{background-color:#a16207}',
                    '.btn-warning:focus{box-shadow:0 0 0 2px #fff,0 0 0 4px #eab308}',
                    '.card{background-color:#fff;box-shadow:0 1px 3px 0 rgb(0 0 0/.1),0 1px 2px -1px rgb(0 0 0/.1);border-radius:.5rem}',
                    '.card-header{padding:1rem 1.25rem;border-bottom:1px solid #e5e7eb}',
                    '.card-body{padding:1rem 1.25rem}',
                    '.form-input,.form-select,.form-textarea{display:block;width:100%;border-radius:.375rem;border:1px solid #d1d5db;padding:.5rem .75rem;font-size:.875rem}',
                    '.form-input:focus,.form-select:focus,.form-textarea:focus{outline:2px solid transparent;outline-offset:2px;border-color:#3b82f6;box-shadow:0 0 0 1px #3b82f6}',
                    '.badge{display:inline-flex;align-items:center;padding:.125rem .625rem;border-radius:9999px;font-size:.75rem;font-weight:500}',
                    '.badge-primary{background-color:#dbeafe;color:#1e40af}',
                    '.badge-success{background-color:#dcfce7;color:#166534}',
                    '.badge-warning{background-color:#fef9c3;color:#854d0e}',
                    '.badge-danger{background-color:#fee2e2;color:#991b1b}',
                    '.badge-gray{background-color:#f3f4f6;color:#1f2937}',
                    '.table{width:100%;border-collapse:collapse;border-bottom:1px solid #e5e7eb}',
                    '.table thead{background-color:#f9fafb}',
                    '.table th{padding:.75rem 1.5rem;text-align:left;font-size:.75rem;font-weight:500;color:#6b7280;text-transform:uppercase;letter-spacing:.05em}',
                    '.table td{padding:1rem 1.5rem;font-size:.875rem;color:#111827;white-space:nowrap}',
                    '.table tbody tr:nth-child(even){background-color:#f9fafb}',
                    '.table tbody tr:hover{background-color:#f3f4f6}'
                ].join('');
                document.head.appendChild(style);
            }
            function loadTailwindCdn() {
                injectFallbackComponentStyles();
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
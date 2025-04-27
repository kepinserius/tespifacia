<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <!-- Menambahkan meta tag untuk keamanan -->
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ config('app.name', 'Laravel Vue App') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blue: {
                            600: '#3b82f6',
                            700: '#2563eb',
                            800: '#1d4ed8',
                        },
                        indigo: {
                            800: '#3730a3',
                        },
                        red: {
                            600: '#dc2626',
                            700: '#b91c1c',
                        },
                        gray: {
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            700: '#374151',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Script untuk mengatasi masalah CSRF token - Dipindah ke head untuk memastikan tersedia sebelum Vue dimuat -->
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ url("/") }}'
        };
    </script>
</head>
<body class="bg-gray-100">
    <div id="app">
        <!-- Menambahkan loading spinner untuk menunjukkan aplikasi sedang dimuat -->
        <div class="flex justify-center items-center" style="height: 100vh;">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-700"></div>
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</body>
</html>

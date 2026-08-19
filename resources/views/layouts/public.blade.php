<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('platform.brand.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(config('platform.brand.favicon')) }}">

    <!-- Layout publico (Fase 19): a diferencia de layouts.admin, no asume un
         usuario logueado -- nada de Auth::user() en sidebar/topbar. Mismo
         Tailwind CDN + Lucide que el panel para mantener la identidad visual. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ config('platform.brand.colors.primary', '#a855f7') }}',
                        dark: '#0F0F12',
                        'dark-alt': '#1F1F23',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-full bg-[#F5F5FA] text-gray-900">
    @yield('content')

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="es" class="h-full dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Clave - Mito Yamile</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo-mito-yamile-blanco.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#a855f7',
                        secondary: '#7c3aed',
                        dark: '#0F0F12',
                        'dark-alt': '#1F1F23',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .btn-violet {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            transition: all .3s;
        }

        .btn-violet:hover {
            box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.3);
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        .text-violet-gradient {
            -webkit-text-fill-color: transparent;
            background: linear-gradient(90deg, #7c3aed, #a855f7, #c084fc);
            -webkit-background-clip: text;
            background-clip: text;
        }
    </style>
</head>

<body class="min-h-screen bg-dark flex flex-col justify-center py-8 md:py-12 px-4">

    <div class="w-full max-w-md mx-auto">

        <!-- Logo Area -->
        <div class="flex flex-col items-center mb-6 md:mb-8 text-center px-4">
            <img src="{{ asset('img/logo-mito-yamile-blanco.svg') }}" alt="Mito Yamile"
                class="h-24 md:h-36 w-auto object-contain mb-0 hover:scale-105 transition-transform duration-300">
        </div>

        <!-- Reset Password Card -->
        <div
            class="bg-dark-alt rounded-[30px] md:rounded-[40px] p-6 md:p-10 border border-white/5 shadow-2xl animate-in fade-in slide-in-from-bottom-5 duration-500">

            <div class="mb-8 text-center">
                <h1 class="text-2xl font-black text-white mb-2">Restablecer Clave</h1>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-loose">
                    Ingresá tu nueva contraseña.
                </p>
            </div>

            <form action="{{ route('password.update') }}" method="POST" class="space-y-5 md:space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                @if($errors->any())
                    <div
                        class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-2xl text-xs font-bold animate-pulse">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-2">
                    <label
                        class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Email</label>
                    <div class="relative group">
                        <i data-lucide="mail"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                        <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                            class="w-full pl-12 pr-4 py-3 md:py-4 rounded-2xl bg-dark text-white placeholder-gray-400 border border-transparent focus:border-primary/50 focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="tu@negocio.com">
                    </div>
                </div>

                <div class="space-y-2" x-data="{ show: false }">
                    <label
                        class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Nueva Contraseña</label>
                    <div class="relative group">
                        <i data-lucide="lock"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                        <input :type="show ? 'text' : 'password'" name="password" required autofocus
                            class="w-full pl-12 pr-12 py-3 md:py-4 rounded-2xl bg-dark text-white placeholder-gray-400 border border-transparent focus:border-primary/50 focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i x-show="!show" data-lucide="eye" class="w-4 h-4"></i>
                            <i x-show="show" data-lucide="eye-off" class="w-4 h-4" x-cloak></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2" x-data="{ show: false }">
                    <label
                        class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Confirmar Contraseña</label>
                    <div class="relative group">
                        <i data-lucide="lock"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" required
                            class="w-full pl-12 pr-12 py-3 md:py-4 rounded-2xl bg-dark text-white placeholder-gray-400 border border-transparent focus:border-primary/50 focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i x-show="!show" data-lucide="eye" class="w-4 h-4"></i>
                            <i x-show="show" data-lucide="eye-off" class="w-4 h-4" x-cloak></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full btn-violet text-white font-black py-3 md:py-4 rounded-2xl active:scale-95 text-sm uppercase tracking-widest flex items-center justify-center gap-2 group">
                        <span>Actualizar Contraseña</span>
                        <i data-lucide="check" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                    </button>
                </div>
            </form>

        </div>

        <p
            class="mt-8 text-center text-[10px] font-black text-gray-600 uppercase tracking-widest leading-loose">
            Mito Yamile &copy; {{ date('Y') }}
        </p>

    </div>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>lucide.createIcons();</script>

</body>

</html>

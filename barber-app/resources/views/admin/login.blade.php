<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Barbería</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="fixed top-[-10%] left-[-10%] w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 pointer-events-none"></div>

    <div class="glass w-full max-w-sm rounded-3xl p-8 relative z-10">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold">🔐 Panel Admin</h1>
            <p class="text-gray-400 mt-2 text-sm">Ingresá la contraseña para acceder</p>
        </div>

        @if($errors->any())
            <div class="bg-red-500/20 text-red-400 border border-red-500/50 p-3 rounded-xl text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.authenticate') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            <input type="email" name="email" placeholder="Correo electrónico..." class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" required>
            <input type="password" name="password" placeholder="Contraseña..." class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" required>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-[0_0_15px_rgba(37,99,235,0.3)]">
                Ingresar
            </button>
        </form>
    </div>

</body>
</html>

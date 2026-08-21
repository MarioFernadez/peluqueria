<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo — BarberPro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0d0f14;
            --surface: #141720;
            --surface2: #1c2030;
            --border: rgba(255,255,255,0.08);
            --border2: rgba(255,255,255,0.13);
            --accent: #6366f1;
            --accent2: #a5b4fc;
            --text: #f1f5f9;
            --muted: #64748b;
            --red: #f87171;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Ambient glow background */
        .bg-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .bg-glow::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        .bg-glow::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Login card */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
            background: var(--surface);
            border: 1px solid var(--border2);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5);
        }

        /* Logo / Brand */
        .login-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .login-logo {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(99,102,241,0.4);
        }
        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.01em;
        }
        .login-subtitle {
            font-size: 0.82rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* Divider */
        .login-divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 1.75rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 0.4rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .form-input {
            width: 100%;
            padding: 0.65rem 0.9rem;
            background: var(--surface2);
            border: 1px solid var(--border2);
            border-radius: 10px;
            color: var(--text);
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.18);
        }
        .form-input::placeholder { color: var(--muted); }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
            box-shadow: 0 4px 14px rgba(99,102,241,0.35);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99,102,241,0.5);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Error alert */
        .alert-error {
            background: rgba(248,113,113,0.08);
            border: 1px solid rgba(248,113,113,0.25);
            color: var(--red);
            padding: 0.65rem 0.9rem;
            border-radius: 9px;
            font-size: 0.82rem;
            margin-bottom: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
        }

        /* Back link */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-top: 1.5rem;
            font-size: 0.78rem;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--accent2); }
    </style>
</head>
<body>

    <div class="bg-glow"></div>

    <div class="login-card">
        <div class="login-brand">
            <div class="login-logo">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                    <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                    <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                    <line x1="8.12" y1="8.12" x2="12" y2="12"/>
                </svg>
            </div>
            <div>
                <div class="login-title">BarberPro</div>
                <div class="login-subtitle">Panel de Administración — Acceso Seguro</div>
            </div>
        </div>

        <div class="login-divider"></div>

        @if($errors->any())
            <div class="alert-error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.authenticate') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-input"
                    placeholder="admin@barberia.com"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                >
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>
            <button type="submit" class="btn-submit">
                Iniciar sesión
            </button>
        </form>

        <a href="/" class="back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver al sitio principal
        </a>
    </div>

</body>
</html>

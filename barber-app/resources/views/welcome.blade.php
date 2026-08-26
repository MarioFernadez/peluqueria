<!DOCTYPE html>
<html lang="es" data-theme="dark" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $settings['hero_title'] ?? 'Athenea Barber' }} — Barbería premium en Encarnación, Paraguay. Reservá tu turno online y disfruta de los mejores servicios de grooming.">
    <title>{{ $settings['hero_title'] ?? 'Athenea Barber' }} — Barbería Premium</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root, [data-theme="dark"] {
            --gold: #D4A843;
            --gold-light: #F0C060;
            --gold-dark: #A07828;
            --bg-dark: #0A0A0A;
            --bg-card: #111111;
            --bg-card2: #181818;
            --border: rgba(255,255,255,0.08);
            --text-muted: #888;
            --text-secondary: #bbb;
            --text-primary: #ffffff;
            --text-strong: #e8e8e8;
            --text-medium: #e0e0e0;
            --btn-outline-bg: rgba(255,255,255,0.06);
            --btn-outline-color: #e0e0e0;
            --btn-outline-hover-bg: rgba(255,255,255,0.1);
            --card-inner-bg: rgba(255,255,255,0.03);
            --nav-bg: rgba(10,10,10,0.85);
            --nav-bg-scrolled: rgba(10,10,10,0.98);
        }

        [data-theme="light"] {
            --gold: #C59B27;
            --gold-light: #DAA520;
            --gold-dark: #8B6914;
            --bg-dark: #f8fafc;
            --bg-card: #ffffff;
            --bg-card2: #f1f5f9;
            --border: rgba(0,0,0,0.06);
            --text-muted: #64748b;
            --text-secondary: #475569;
            --text-primary: #0f172a;
            --text-strong: #1e293b;
            --text-medium: #334155;
            --btn-outline-bg: rgba(0,0,0,0.03);
            --btn-outline-color: #334155;
            --btn-outline-hover-bg: rgba(0,0,0,0.06);
            --card-inner-bg: rgba(0,0,0,0.02);
            --nav-bg: rgba(255,255,255,0.92);
            --nav-bg-scrolled: rgba(255,255,255,0.98);
        }

        html { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-primary); }
        body { transition: background 0.25s ease, color 0.25s ease; }

        h1, h2, h3, h4, h5 { font-family: 'Outfit', sans-serif; }

        /* ─── Scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: var(--gold-dark); border-radius: 3px; }

        /* ─── Navbar ─── */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2rem; height: 64px;
            background: var(--nav-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        .navbar.scrolled {
            background: var(--nav-bg-scrolled);
            box-shadow: 0 4px 30px rgba(0,0,0,0.12);
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo-icon {
            width: 36px; height: 36px; border-radius: 9px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .nav-logo-text { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem; color: var(--text-secondary); }
        [data-theme="dark"] .nav-logo-text { color: #fff; }
        .nav-links { display: flex; gap: 2rem; list-style: none; }
        .nav-links a { color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: color 0.2s; position: relative; }
        .nav-links a:hover, .nav-links a.active { color: var(--text-secondary); filter: brightness(1.5); }
        [data-theme="dark"] .nav-links a:hover, [data-theme="dark"] .nav-links a.active { color: #fff; filter: none; }
        .nav-links a.active::after {
            content: ''; position: absolute; bottom: -4px; left: 0; right: 0;
            height: 2px; background: var(--gold); border-radius: 1px;
        }
        .nav-right { display: flex; align-items: center; gap: 0.75rem; }
        /* Theme toggle public */
        .pub-theme-toggle {
            width: 34px; height: 34px; border-radius: 8px;
            border: 1px solid var(--border); background: transparent;
            color: var(--text-secondary); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .pub-theme-toggle:hover { border-color: var(--gold); color: var(--gold); }
        .pub-theme-toggle svg { width: 16px; height: 16px; }
        .pub-icon-sun { display: none; }
        .pub-icon-moon { display: block; }
        [data-theme="light"] .pub-icon-sun { display: block; }
        [data-theme="light"] .pub-icon-moon { display: none; }
        .btn-login {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #000; font-weight: 700; font-size: 0.8rem;
            padding: 8px 18px; border-radius: 9px; border: none; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s ease;
            font-family: 'Outfit', sans-serif; letter-spacing: 0.01em;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(212,168,67,0.35); }

        /* ─── Hero ─── */
        .hero {
            position: relative; min-height: 100vh;
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 0 0 3rem;
        }
        .hero-bg {
            position: absolute; inset: 0;
            background-image: url('{{ isset($settings["hero_bg_image"]) ? asset($settings["hero_bg_image"]) : "" }}');
            background-size: cover; background-position: center;
            filter: brightness(0.45);
            z-index: 0;
            background-color: #0d0d0d;
        }
        .hero-gradient {
            position: absolute; inset: 0; z-index: 1;
            background: linear-gradient(
                to bottom,
                rgba(10,10,10,0.15) 0%,
                rgba(10,10,10,0.05) 40%,
                rgba(10,10,10,0.88) 80%,
                rgba(10,10,10,1) 100%
            );
        }
        .hero-content { position: relative; z-index: 2; padding: 0 2.5rem; max-width: 900px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.08); backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 50px; padding: 6px 14px;
            font-size: 0.75rem; color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }
        .hero-badge-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: pulse-dot 2s infinite; }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        .hero-profile { display: flex; align-items: center; gap: 1.2rem; margin-bottom: 1.5rem; }
        .hero-avatar {
            width: 80px; height: 80px; border-radius: 16px;
            border: 3px solid rgba(212,168,67,0.5);
            object-fit: cover;
            background: linear-gradient(135deg, var(--gold), #8B6914);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            overflow: hidden;
        }
        .hero-title { font-family: 'Outfit', sans-serif; font-size: 2.8rem; font-weight: 800; line-height: 1.1; color: #ffffff; text-shadow: 0 2px 12px rgba(0,0,0,0.6); }
        .hero-subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .hero-rating { display: flex; align-items: center; gap: 4px; color: var(--gold); font-size: 0.875rem; font-weight: 600; }
        .hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #000; font-weight: 700; padding: 12px 28px; border-radius: 12px;
            border: none; cursor: pointer; font-size: 0.9rem; font-family: 'Outfit', sans-serif;
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none; transition: all 0.25s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(212,168,67,0.45); }
        .btn-open {
            background: rgba(34,197,94,0.15); color: #22c55e;
            border: 1px solid rgba(34,197,94,0.3); padding: 12px 20px; border-radius: 12px;
            font-weight: 600; font-size: 0.9rem; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px;
            font-family: 'Outfit', sans-serif; transition: all 0.2s;
        }
        .btn-open:hover { background: rgba(34,197,94,0.25); }

        /* ─── Section Commons ─── */
        .section { padding: 5rem 2.5rem; max-width: 1200px; margin: 0 auto; }
        .section-tag {
            display: inline-block; background: rgba(212,168,67,0.1);
            color: var(--gold); border: 1px solid rgba(212,168,67,0.2);
            font-size: 0.75rem; font-weight: 600; letter-spacing: 0.1em;
            text-transform: uppercase; padding: 4px 14px; border-radius: 50px;
            margin-bottom: 1rem;
        }
        .section-title { font-size: 2.2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem; }
        .section-subtitle { color: var(--text-muted); font-size: 0.95rem; max-width: 500px; }
        .divider { border: none; border-top: 1px solid var(--border); }

        /* ─── Gallery ─── */
        .gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 2.5rem; }
        .gallery-card {
            position: relative; border-radius: 16px; overflow: hidden;
            aspect-ratio: 3/4; cursor: pointer;
            transition: transform 0.3s ease;
        }
        .gallery-card:hover { transform: scale(1.02); }
        .gallery-card img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.5s ease; }
        .gallery-card:hover img { transform: scale(1.08); }
        .gallery-card-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 50%);
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 1.2rem;
        }
        .gallery-card-title { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem; color: #ffffff; }
        .gallery-card-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; line-height: 1.4; }
        .gallery-card-badge {
            position: absolute; top: 12px; left: 12px;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff; font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 50px;
        }

        /* ─── Staff & Services ─── */
        .staff-services-grid { display: grid; grid-template-columns: 1fr 340px; gap: 2rem; margin-top: 2.5rem; align-items: start; }
        .staff-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .staff-avatars { display: flex; gap: -8px; }
        .staff-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            border: 2px solid var(--bg-card); object-fit: cover;
            background: linear-gradient(135deg, var(--gold), #8B6914);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; font-weight: 700; color: #000;
            margin-left: -8px; first: margin-left: 0;
        }

        .service-list { display: flex; flex-direction: column; gap: 0; }
        .service-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.1rem 0; border-bottom: 1px solid var(--border);
        }
        .service-item:last-child { border-bottom: none; }
        .service-info {}
        .service-name { font-weight: 600; font-size: 0.95rem; color: var(--text-strong); }
        .service-duration { font-size: 0.78rem; color: var(--text-muted); margin-top: 3px; }
        .service-price { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1rem; color: var(--gold); }

        /* Fidelity card */
        .fidelity-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px; padding: 1.5rem;
            position: sticky; top: 80px;
        }
        .fidelity-title { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.1rem; color: var(--text-primary); margin-bottom: 0.5rem; }
        .fidelity-sub { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5; }
        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 12px; border-radius: 10px;
            background: var(--btn-outline-bg); border: 1px solid var(--border);
            color: var(--text-strong); font-size: 0.875rem; font-weight: 500; cursor: pointer;
            transition: all 0.2s; margin-bottom: 0.75rem; text-decoration: none;
        }
        .btn-google:hover { background: var(--btn-outline-hover-bg); }
        .btn-email {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 12px; border-radius: 10px;
            background: var(--card-inner-bg); border: 1px solid var(--border);
            color: var(--text-secondary); font-size: 0.875rem; cursor: pointer;
            transition: all 0.2s; margin-bottom: 1rem; text-decoration: none;
        }
        .btn-email:hover { background: var(--btn-outline-hover-bg); }
        .btn-benefits {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 12px; border-radius: 10px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #000; font-weight: 700; font-size: 0.875rem; cursor: pointer;
            border: none; font-family: 'Outfit', sans-serif; transition: all 0.2s;
        }
        .btn-benefits:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(212,168,67,0.35); }

        /* ─── Location ─── */
        .location-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-top: 2.5rem; align-items: start; }
        .location-info {}
        .location-item { display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-start; }
        .location-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            background: rgba(212,168,67,0.1); border: 1px solid rgba(212,168,67,0.2);
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .location-label { font-weight: 600; color: var(--text-medium); font-size: 0.9rem; margin-bottom: 4px; }
        .location-value { color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; }
        .btn-directions {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--btn-outline-bg); border: 1px solid var(--border);
            color: var(--btn-outline-color); padding: 10px 20px; border-radius: 10px; font-size: 0.875rem;
            cursor: pointer; text-decoration: none; transition: all 0.2s; margin-top: 0.5rem;
        }
        .btn-directions:hover { background: var(--btn-outline-hover-bg); color: var(--text-primary); }

        .map-container {
            border-radius: 20px; overflow: hidden; border: 1px solid var(--border);
            height: 320px;
        }
        .map-container iframe { width: 100%; height: 100%; border: none; }

        /* Hours table */
        .hours-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
        .hours-row { display: contents; }
        .hours-day, .hours-time {
            padding: 8px 0; font-size: 0.82rem; border-bottom: 1px solid var(--border);
        }
        .hours-day { color: var(--text-muted); }
        .hours-time { color: var(--text-medium); text-align: right; }
        .hours-closed { color: #ef4444; }

        /* ─── Footer ─── */
        .footer { background: var(--bg-card); border-top: 1px solid var(--border); padding: 3rem 2.5rem 2rem; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 3rem; max-width: 1200px; margin: 0 auto 2rem; }
        .footer-brand {}
        .footer-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
        .footer-desc { color: var(--text-muted); font-size: 0.85rem; line-height: 1.7; max-width: 300px; margin-bottom: 1.5rem; }
        .social-links { display: flex; gap: 0.75rem; }
        .social-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); font-size: 1rem; cursor: pointer;
            transition: all 0.2s; text-decoration: none;
        }
        .social-btn:hover { background: rgba(212,168,67,0.15); border-color: rgba(212,168,67,0.3); color: var(--gold); }
        .footer-col-title { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 0.9rem; color: var(--text-medium); margin-bottom: 1rem; }
        .footer-links { list-style: none; display: flex; flex-direction: column; gap: 0.6rem; }
        .footer-links a { color: var(--text-muted); text-decoration: none; font-size: 0.85rem; transition: color 0.2s; }
        .footer-links a:hover { color: var(--gold); }
        .footer-bottom { max-width: 1200px; margin: 0 auto; padding-top: 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .footer-copy { color: var(--text-muted); font-size: 0.8rem; }

        /* ─── Animations ─── */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in-up { animation: fadeInUp 0.7s ease forwards; }
        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.25s; opacity: 0; }
        .delay-3 { animation-delay: 0.4s; opacity: 0; }
        .delay-4 { animation-delay: 0.55s; opacity: 0; }

        /* ─── Hamburger button ─── */
        .hamburger-btn {
            display: none;
            background: none;
            border: 1px solid var(--border);
            color: var(--text-primary);
            width: 40px; height: 40px;
            border-radius: 10px;
            align-items: center; justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }
        [data-theme="dark"] .hamburger-btn { color: #fff; border-color: rgba(255,255,255,0.2); }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hamburger-btn { display: flex !important; }
            .hero-title { font-size: 2rem; }
            .hero-content { padding: 0 1.5rem; }
            .gallery-grid { grid-template-columns: 1fr; }
            .staff-services-grid { grid-template-columns: 1fr; }
            .location-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .footer-bottom { flex-direction: column; gap: 1rem; text-align: center; }
            .section { padding: 3rem 1.5rem; }
            #whatsapp-btn { bottom: 1.25rem !important; right: 1.25rem !important; width: 50px !important; height: 50px !important; }
        }
        @media (max-width: 480px) {
            .gallery-grid { grid-template-columns: 1fr; }
            .navbar { padding: 0 1rem; }
            .hero-profile { gap: 0.8rem; }
            .hero-avatar { width: 60px; height: 60px; font-size: 1.5rem; }
        }
        @media (max-width: 400px) {
            .hero-title { font-size: 1.65rem; }
            .hero-content { padding: 0 1rem; }
            .hero-actions { flex-direction: column; gap: 0.75rem; }
            .hero-actions .btn-primary,
            .hero-actions .btn-open { width: 100%; justify-content: center; }
            .section { padding: 2.5rem 1rem; }
            .section-title { font-size: 1.75rem; }
            .navbar { padding: 0 0.75rem; }
        }
        @media (max-width: 360px) {
            .hero-title { font-size: 1.45rem; }
            .btn-login { font-size: 0.72rem; padding: 7px 12px; }
        }
    </style>
</head>
<body x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 20">

    <!-- ─── NAVBAR ─── -->
    <nav class="navbar" :class="{ 'scrolled': scrolled }">
        <a href="/" class="nav-logo">
            <div class="nav-logo-icon" {!! isset($settings['logo_image']) ? 'style="background: transparent;"' : '' !!}>
                @if(isset($settings['logo_image']))
                    <img src="{{ asset($settings['logo_image']) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 4px;">
                @else
                    <!-- Scissors icon -->
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                        <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                        <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                        <line x1="8.12" y1="8.12" x2="12" y2="12"/>
                    </svg>
                @endif
            </div>
            <span class="nav-logo-text">{{ $settings['hero_title'] ?? 'Athenea Barber' }}</span>
        </a>

        <ul class="nav-links">
            <li><a href="#inicio" class="active">Inicio</a></li>
            <li><a href="#servicios">Servicios</a></li>
            <li><a href="#galeria">Galería</a></li>
            <li><a href="#ubicacion">Sobre Nosotros</a></li>
        </ul>

        <div class="nav-right">
            <!-- Toggle modo oscuro/claro -->
            <button class="pub-theme-toggle" onclick="togglePublicTheme()" title="Cambiar tema">
                <svg class="pub-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <svg class="pub-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </button>

            <!-- Hamburger mobile -->
            <button class="hamburger-btn" onclick="openMobileMenu()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </nav>

    <!-- ─── HERO ─── -->
    <section class="hero" id="inicio">
        <div class="hero-bg"></div>
        <div class="hero-gradient"></div>

        <div class="hero-content">
            <div class="hero-badge fade-in-up delay-1">
                <span class="hero-badge-dot"></span>
                <span>{{ $settings['hero_address_badge'] ?? '14 de enero calle Gral. Artigas y Juan L. Mallorquín' }}</span>
            </div>

            <div class="hero-profile fade-in-up delay-2">
                <div class="hero-avatar" {!! isset($settings['logo_image']) ? 'style="background: transparent; border-color: rgba(255,255,255,0.1);"' : '' !!}>
                    @if(isset($settings['logo_image']))
                        <img src="{{ asset($settings['logo_image']) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                    @else
                        <!-- Barber pole / formal icon -->
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(212,168,67,0.9)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                            <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                            <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                            <line x1="8.12" y1="8.12" x2="12" y2="12"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <h1 class="hero-title">{{ $settings['hero_title'] ?? 'Athenea Barber' }}</h1>
                    <div class="hero-subtitle">
                        <span>{{ $settings['hero_subtitle'] ?? 'Encarnación, Paraguay' }}</span>
                    </div>
                </div>
            </div>

            <div class="hero-actions fade-in-up delay-3">
                <a href="/booking" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Reservar turno
                </a>
                <button id="open-badge" class="btn-open" style="cursor:default;">
                    <span class="status-dot" style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                    Abierto ahora
                </button>
            </div>
        </div>
    </section>

    {{-- ─── GALERÍA ─── --}}
    @if(isset($galleryWorks) && $galleryWorks->isNotEmpty())
    <section class="section" id="galeria">
        <hr class="divider" style="margin-bottom: 3rem;">
        <div>
            <span class="section-tag">Galería</span>
            <h2 class="section-title">Nuestros trabajos</h2>
            <p class="section-subtitle">El resultado habla por sí solo.</p>
        </div>

        <div class="gallery-grid">
            @foreach($galleryWorks as $index => $work)
                <div class="gallery-card fade-in-up delay-{{ min($index + 1, 4) }}">
                    @if($work->badge)
                        <span class="gallery-card-badge">{{ $work->badge }}</span>
                    @endif
                    <img src="{{ asset($work->image_path) }}" alt="{{ $work->title }}" loading="lazy">
                    <div class="gallery-card-overlay">
                        <div class="gallery-card-title">{{ $work->title }}</div>
                        <div class="gallery-card-sub">{{ $work->subtitle }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- ─── STAFF & SERVICIOS (DINÁMICO) ─── -->
    <section class="section" id="servicios" style="padding-top: 0;">
        <hr class="divider" style="margin-bottom: 3rem;">

        @if(isset($barbers) && $barbers->isNotEmpty())
        <div x-data="staffSection()" x-init="init()" class="staff-wrapper">

            <!-- Header con título y thumbnails de barberos -->
            <div class="staff-header">
                <div>
                    <span class="section-tag">Equipo</span>
                    <h2 class="section-title">Staff y servicios</h2>
                    <p class="section-subtitle">Nuestros expertos están listos para brindarte la mejor experiencia de grooming.</p>
                </div>
                <!-- Avatares clickeables -->
                <div style="display:flex; gap: 4px; flex-wrap: wrap;">
                    @foreach($barbers as $i => $barber)
                    <button
                        @click="selectBarber({{ $i }})"
                        :class="activeIndex === {{ $i }} ? 'staff-avatar-active' : ''"
                        class="staff-avatar staff-avatar-btn"
                        style="margin-left:{{ $i === 0 ? '0' : '-8px' }}; cursor:pointer; border:none; padding:0; background: {{ ['linear-gradient(135deg,#D4A843,#8B6914)', 'linear-gradient(135deg,#6B7280,#374151)', 'linear-gradient(135deg,#7C3AED,#4C1D95)', 'linear-gradient(135deg,#059669,#064E3B)'][$i % 4] }};"
                        title="{{ $barber->name }}">
                        @if($barber->photo)
                            <img src="{{ asset('storage/' . $barber->photo) }}" alt="{{ $barber->name }}"
                                 style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        @else
                            {{ strtoupper(substr($barber->name, 0, 1)) }}
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Tabs de barberos -->
            <div class="staff-services-grid">
                @foreach($barbers as $i => $barber)
                <div x-show="activeIndex === {{ $i }}"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 1.5rem;">

                    <!-- Info del barbero -->
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
                        <div class="staff-avatar" style="width:56px; height:56px; font-size:1.2rem; margin-left:0; flex-shrink:0; overflow:hidden;
                             background: {{ ['linear-gradient(135deg,#D4A843,#8B6914)', 'linear-gradient(135deg,#6B7280,#374151)', 'linear-gradient(135deg,#7C3AED,#4C1D95)', 'linear-gradient(135deg,#059669,#064E3B)'][$i % 4] }};">
                            @if($barber->photo)
                                <img src="{{ asset('storage/' . $barber->photo) }}" alt="{{ $barber->name }}"
                                     style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            @else
                                {{ strtoupper(substr($barber->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary); font-family: 'Outfit', sans-serif;">{{ $barber->name }}</div>
                            <div style="font-size: 0.78rem; color: var(--gold);">
                                @if($barber->specialties)
                                    @php
                                        $specs = is_array($barber->specialties) ? $barber->specialties : json_decode($barber->specialties, true);
                                    @endphp
                                    {{ is_array($specs) ? implode(' · ', array_slice($specs, 0, 3)) : 'Barbero profesional' }}
                                @else
                                    Master Barber
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Lista de servicios -->
                    <div class="service-list">
                        @if(isset($services) && $services->isNotEmpty())
                            @foreach($services as $j => $service)
                            <div class="service-item" @if($loop->last) style="border-bottom: none;" @endif>
                                <div class="service-info">
                                    <div class="service-name">{{ $service->name }}</div>
                                    <div class="service-duration">{{ $service->duration_min }} min</div>
                                </div>
                                <div class="service-price">Gs. {{ number_format($service->price, 0, ',', '.') }}</div>
                            </div>
                            @endforeach
                        @else
                            <div style="text-align:center; color: var(--text-muted); padding: 1rem 0; font-size: 0.85rem;">
                                Aún no hay servicios cargados.
                            </div>
                        @endif
                    </div>

                    <!-- Botón reservar -->
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                        <a href="/booking" class="btn-primary" style="width:100%; justify-content: center;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Reservar con {{ $barber->name }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @else
        <!-- Fallback si no hay barberos cargados -->
        <div class="staff-header">
            <div>
                <span class="section-tag">Equipo</span>
                <h2 class="section-title">Staff y servicios</h2>
                <p class="section-subtitle">Nuestros expertos están listos para brindarte la mejor experiencia de grooming.</p>
            </div>
        </div>
        <div style="text-align:center; padding: 3rem; color: var(--text-muted);">
            Próximamente conocé a nuestro equipo.
        </div>
        @endif
    </section>

    <!-- ─── UBICACIÓN ─── -->
    <section class="section" id="ubicacion" style="padding-top: 0;">
        <hr class="divider" style="margin-bottom: 3rem;">
        <span class="section-tag">Ubicación</span>
        <h2 class="section-title">Encuéntranos</h2>

        <div class="location-grid">
            <!-- Info -->
            <div>
                <div class="location-item">
                    <div class="location-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <div class="location-label">Ubicación</div>
                        <div class="location-value">{{ $settings['contact_address'] ?? '14 de enero calle Gral. Artigas y Juan L. Mallorquín, Encarnación, Paraguay' }}</div>
                    </div>
                </div>

                <div class="location-item">
                    <div class="location-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="location-label">Horarios</div>
                        <div class="hours-grid">
                            <span class="hours-day">Lunes — Viernes</span><span class="hours-time">09:00 – 20:00</span>
                            <span class="hours-day">Sábado</span><span class="hours-time">09:00 – 19:00</span>
                            <span class="hours-day">Domingo</span><span class="hours-time hours-closed">Cerrado</span>
                        </div>
                    </div>
                </div>

                <a href="https://maps.google.com/?q=Encarnación+Paraguay" target="_blank" class="btn-directions">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                    Cómo llegar
                </a>
            </div>

            <!-- Mapa -->
            <div class="map-container">
                <iframe
                    src="{{ $settings['map_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28916.35!2d-55.8456!3d-27.3364!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x945cfeb6a68f6f85%3A0x8b96b94d79d5869c!2sEncarnaci%C3%B3n%2C%20Paraguay!5e0!3m2!1ses!2sar!4v1' }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mapa de {{ $settings['hero_title'] ?? 'Athenea Barber' }}"
                ></iframe>
            </div>
        </div>
    </section>

    <!-- ─── FOOTER ─── -->
    <!-- ─── WHATSAPP FLOTANTE ─── -->
    <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '595000000000' }}?text={{ urlencode($settings['whatsapp_message'] ?? 'Hola! Quiero reservar un turno en ' . ($settings['hero_title'] ?? 'Athenea Barber')) }}" target="_blank" id="whatsapp-btn"
        style="position:fixed; bottom:2rem; right:2rem; z-index:200;
               width:58px; height:58px; border-radius:50%;
               background: linear-gradient(135deg, #25D366, #128C7E);
               display:flex; align-items:center; justify-content:center;
               box-shadow: 0 4px 20px rgba(37,211,102,0.5);
               text-decoration:none; transition: all 0.3s ease;
               animation: whatsapp-pulse 2.5s infinite;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
            <path d="M11.974 0C5.348 0 0 5.349 0 11.974c0 2.113.558 4.1 1.535 5.823L0 23.999l6.347-1.51A11.913 11.913 0 0 0 11.974 24C18.6 24 24 18.65 24 11.974 24 5.348 18.6 0 11.974 0zm0 21.888c-1.974 0-3.817-.536-5.403-1.466l-.388-.23-4.017.957.999-3.934-.253-.403a9.876 9.876 0 0 1-1.512-5.312c0-5.475 4.462-9.937 9.937-9.937 5.474 0 9.937 4.462 9.937 9.937-.001 5.475-4.463 9.388-9.3 9.388z"/>
        </svg>
        <!-- Tooltip -->
        <span style="position:absolute; right:68px; background:#1a1a1a; color:#fff; font-size:0.78rem; font-weight:600; padding:6px 12px; border-radius:8px; white-space:nowrap; opacity:0; transition:opacity 0.2s; pointer-events:none; border: 1px solid rgba(255,255,255,0.1);" class="wa-tooltip">¡Escribinos!</span>
    </a>

    <!-- ─── MOBILE MENU OVERLAY ─── -->
    <div id="mobile-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.95); z-index:300; flex-direction:column; align-items:center; justify-content:center; gap:2rem;">
        <button onclick="closeMobileMenu()" style="position:absolute; top:1.2rem; right:1.5rem; background:none; border:none; color:#fff; font-size:1.8rem; cursor:pointer;">✕</button>
        <a href="#inicio" onclick="closeMobileMenu()" style="color:#fff; text-decoration:none; font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:700;">Inicio</a>
        <a href="#servicios" onclick="closeMobileMenu()" style="color:#aaa; text-decoration:none; font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:700;">Servicios</a>
        <a href="#galeria" onclick="closeMobileMenu()" style="color:#aaa; text-decoration:none; font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:700;">Galería</a>
        <a href="#ubicacion" onclick="closeMobileMenu()" style="color:#aaa; text-decoration:none; font-family:'Outfit',sans-serif; font-size:1.8rem; font-weight:700;">Encuéntranos</a>
        <a href="/booking" style="background: linear-gradient(135deg, var(--gold), var(--gold-dark)); color:#000; padding:14px 36px; border-radius:12px; font-family:'Outfit',sans-serif; font-weight:700; font-size:1rem; text-decoration:none;">Reservar ahora</a>
    </div>

    <footer class="footer">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <div class="nav-logo-icon" {!! isset($settings['logo_image']) ? 'style="background: transparent;"' : '' !!}>
                        @if(isset($settings['logo_image']))
                            <img src="{{ asset($settings['logo_image']) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 4px;">
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                                <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                                <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                                <line x1="8.12" y1="8.12" x2="12" y2="12"/>
                            </svg>
                        @endif
                    </div>
                    <span style="font-family: 'Outfit'; font-weight: 700; font-size: 1rem; color: var(--text-primary);">{{ $settings['hero_title'] ?? 'Athenea Barber' }}</span>
                </div>
                <p class="footer-desc">Elevamos el estándar del grooming masculino con precisión, producto y estilo artesanal.</p>
                <div class="social-links">
                    <a href="#" class="social-btn" title="Instagram">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="#" class="social-btn" title="Facebook">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="#" class="social-btn" title="WhatsApp">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.974 0C5.348 0 0 5.349 0 11.974c0 2.113.558 4.1 1.535 5.823L0 23.999l6.347-1.51A11.913 11.913 0 0 0 11.974 24C18.6 24 24 18.65 24 11.974 24 5.348 18.6 0 11.974 0zm0 21.888c-1.974 0-3.817-.536-5.403-1.466l-.388-.23-4.017.957.999-3.934-.253-.403a9.876 9.876 0 0 1-1.512-5.312c0-5.475 4.462-9.937 9.937-9.937 5.474 0 9.937 4.462 9.937 9.937-.001 5.475-4.463 9.388-9.3 9.388z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Empresa -->
            <div>
                <div class="footer-col-title">Empresa</div>
                <ul class="footer-links">
                    <li><a href="#">Sobre nosotros</a></li>
                    <li><a href="#galeria">Galería</a></li>
                    <li><a href="#">Carreras</a></li>
                </ul>
            </div>

            <!-- Soporte / Legal -->
            <div>
                <div class="footer-col-title">Soporte</div>
                <ul class="footer-links">
                    <li><a href="/booking">Hacer una reserva</a></li>
                    <li><a href="#">Contacto</a></li>
                    <li><a href="#">Ayuda</a></li>
                </ul>
                <div class="footer-col-title" style="margin-top: 1.5rem;">Legal</div>
                <ul class="footer-links">
                    <li><a href="#">Privacidad</a></li>
                    <li><a href="#">Términos</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span class="footer-copy">© 2024 {{ $settings['hero_title'] ?? 'Athenea Barber' }}. All rights reserved.</span>
            <div class="footer-logo">
                    <div class="nav-logo-icon" style="width:28px; height:28px; {!! isset($settings['logo_image']) ? 'background: transparent;' : '' !!}">
                        @if(isset($settings['logo_image']))
                            <img src="{{ asset($settings['logo_image']) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 4px;">
                        @else
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                                <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                                <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                                <line x1="8.12" y1="8.12" x2="12" y2="12"/>
                            </svg>
                        @endif
                    </div>
                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $settings['hero_title'] ?? 'Athenea Barber' }}</span>
            </div>
        </div>
    </footer>

    <script>
        // ── Theme toggle público ──
        function applyPublicTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('barberpro_pub_theme', theme);
        }
        function togglePublicTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            applyPublicTheme(current === 'dark' ? 'light' : 'dark');
        }
        // Restaurar preferencia guardada
        (function() {
            const saved = localStorage.getItem('barberpro_pub_theme') || 'dark';
            applyPublicTheme(saved);
        })();

        // ── Smooth scroll ──
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            });
        });

        // ── WhatsApp tooltip hover ──
        const waBtn = document.getElementById('whatsapp-btn');
        const waTooltip = waBtn?.querySelector('.wa-tooltip');
        waBtn?.addEventListener('mouseenter', () => { if(waTooltip) waTooltip.style.opacity = '1'; });
        waBtn?.addEventListener('mouseleave', () => { if(waTooltip) waTooltip.style.opacity = '0'; });

        // ── Mobile menu ──
        function openMobileMenu() { const o = document.getElementById('mobile-overlay'); if(o){ o.style.display='flex'; } }
        function closeMobileMenu() { const o = document.getElementById('mobile-overlay'); if(o){ o.style.display='none'; } }

        // ── Indicador abierto/cerrado ──
        function updateOpenStatus() {
            const now = new Date();
            const day = now.getDay();
            const time = now.getHours() * 60 + now.getMinutes();
            let isOpen = false;
            if (day >= 1 && day <= 5) isOpen = time >= 9*60 && time < 20*60;
            else if (day === 6) isOpen = time >= 9*60 && time < 19*60;
            const badge = document.getElementById('open-badge');
            if (badge) {
                badge.innerHTML = `<span class="status-dot" style="width:8px;height:8px;border-radius:50%;background:${isOpen?'#22c55e':'#ef4444'};display:inline-block;"></span> ${isOpen ? 'Abierto ahora' : 'Cerrado ahora'}`;
                badge.style.background = isOpen ? 'rgba(34,197,94,0.15)' : 'rgba(239,68,68,0.15)';
                badge.style.color = isOpen ? '#22c55e' : '#ef4444';
                badge.style.border = isOpen ? '1px solid rgba(34,197,94,0.3)' : '1px solid rgba(239,68,68,0.3)';
            }
        }
        updateOpenStatus();
        setInterval(updateOpenStatus, 60000);

        // ── Scroll fade-in ──
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.scroll-reveal').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
        // ── Staff section tabs (barberos) ──
        function staffSection() {
            return {
                activeIndex: 0,
                init() { this.activeIndex = 0; },
                selectBarber(index) {
                    this.activeIndex = index;
                }
            };
        }
    </script>
    <style>
        .staff-avatar-btn {
            transition: transform 0.18s ease, box-shadow 0.18s ease, z-index 0s;
            position: relative;
            z-index: 1;
        }
        .staff-avatar-btn:hover {
            transform: scale(1.12) translateY(-2px);
            z-index: 5;
            box-shadow: 0 4px 16px rgba(0,0,0,0.35);
        }
        .staff-avatar-active {
            transform: scale(1.15) translateY(-3px) !important;
            z-index: 10 !important;
            box-shadow: 0 0 0 3px var(--gold), 0 6px 20px rgba(212,168,67,0.4) !important;
        }
        .staff-wrapper { width: 100%; }
    </style>
    <style>
        @keyframes whatsapp-pulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(37,211,102,0.5); }
            50% { box-shadow: 0 4px 30px rgba(37,211,102,0.8), 0 0 0 8px rgba(37,211,102,0.1); }
        }
        #whatsapp-btn:hover { transform: scale(1.1); }
    </style>
</body>
</html>

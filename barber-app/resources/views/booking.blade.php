<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Reservá tu turno en {{ $businessName }} — Reserva online fácil y rápida.">
    <title>Reservar turno — {{ $businessName }}</title>
    <script>
        window.WA_NUMBER = '{{ preg_replace("/[^0-9]/", "", $waNumber) }}';
        window.BUSINESS_NAME = '{{ addslashes($businessName) }}';
        window.VAPID_PUBLIC_KEY = '{{ config("app.vapid_public_key", "") }}';
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold: #D4A843;
            --gold-dark: #A07828;
            --bg: #F4F4F5;
            --white: #FFFFFF;
            --border: #E4E4E7;
            --text: #18181B;
            --muted: #71717A;
            --light: #A1A1AA;
            --sidebar: #FAFAFA;
            --green: #22c55e;
            --navbar-h: 58px;
        }

        html, body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); overflow-x: hidden; width: 100%; }
        h1, h2, h3 { font-family: 'Outfit', sans-serif; }

        /* ── NAVBAR ── */
        .navbar {
            position: sticky; top: 0; z-index: 200;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1rem; height: var(--navbar-h);
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
        }
        .nav-logo { display: flex; align-items: center; gap: 9px; text-decoration: none; flex-shrink: 0; }
        .nav-logo-icon {
            width: 36px; height: 36px; border-radius: 9px;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #111; font-size: 14px;
            overflow: hidden;
        }
        .nav-logo-icon img { width: 100%; height: 100%; object-fit: cover; }
        .nav-logo-text { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 0.95rem; color: var(--text); white-space: nowrap; }
        .nav-actions { display: flex; align-items: center; gap: 0.5rem; }
        .nav-login {
            display: flex; align-items: center; gap: 5px;
            background: var(--text); color: #fff;
            padding: 7px 14px; border-radius: 8px; border: none;
            font-size: 0.8rem; font-weight: 600; cursor: pointer;
            text-decoration: none; white-space: nowrap;
            font-family: 'Inter', sans-serif;
        }
        .nav-login:hover { background: #374151; }
        .nav-back {
            display: flex; align-items: center; gap: 5px;
            background: none; border: 1px solid var(--border);
            color: var(--muted); padding: 7px 12px; border-radius: 8px;
            font-size: 0.8rem; cursor: pointer; text-decoration: none;
            font-family: 'Inter', sans-serif; white-space: nowrap;
        }
        .nav-my-bookings {
            display: flex; align-items: center; gap: 5px;
            background: rgba(212,168,67,0.1); border: 1px solid rgba(212,168,67,0.3);
            color: var(--gold-dark); padding: 7px 12px; border-radius: 8px;
            font-size: 0.8rem; font-weight: 600; cursor: pointer;
            font-family: 'Inter', sans-serif; white-space: nowrap;
            position: relative; transition: all 0.15s;
        }
        .nav-my-bookings:hover { background: rgba(212,168,67,0.18); }
        .nav-booking-dot {
            position: absolute; top: 4px; right: 4px;
            width: 8px; height: 8px;
            background: #22c55e; border-radius: 50%;
            border: 1.5px solid white;
        }

        /* ── PAGE SHELL ── */
        .page-shell {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - var(--navbar-h));
        }

        /* ── MAIN AREA ── */
        .booking-wrap {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        /* ── SIDEBAR (mobile: hidden; desktop: right column) ── */
        .booking-sidebar {
            display: none; /* hidden on mobile */
        }
        /* ── MOBILE SUMMARY BAR ── */
        .mobile-summary {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .mobile-chips { display: flex; align-items: center; gap: 0.4rem; flex: 1; flex-wrap: wrap; }
        .mobile-chip {
            display: flex; align-items: center; gap: 4px;
            background: var(--bg); border: 1px solid var(--border);
            padding: 4px 10px; border-radius: 20px;
            font-size: 0.75rem; color: var(--muted);
        }
        .mobile-chip strong { color: var(--text); font-weight: 600; }
        .mobile-total { font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 800; color: var(--text); white-space: nowrap; }

        /* ── MAIN CONTENT ── */
        .booking-main {
            flex: 1;
            padding: 1.5rem 1rem 2rem;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }

        /* ── STEPPER ── */
        .stepper {
            display: flex; align-items: center; margin-bottom: 2rem;
        }
        .step-node {
            width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; transition: all 0.3s;
            font-family: 'Outfit', sans-serif;
        }
        .step-node.done { background: var(--green); color: #fff; border: 2px solid var(--green); }
        .step-node.active {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #000; border: 2px solid var(--gold);
            box-shadow: 0 0 0 3px rgba(212,168,67,0.2);
        }
        .step-node.pending { background: var(--white); color: var(--muted); border: 2px solid var(--border); }
        .step-line { flex: 1; height: 2px; transition: background 0.4s; }
        .step-line.done { background: var(--green); }
        .step-line.pending { background: var(--border); }

        /* ── STEP HEADER ── */
        .step-tag { font-size: 0.75rem; color: var(--muted); margin-bottom: 0.4rem; }
        .step-tag a { color: var(--gold); text-decoration: none; font-weight: 600; }
        .step-title { font-size: 1.75rem; font-weight: 800; color: var(--text); margin-bottom: 0.2rem; line-height: 1.15; }
        .step-sub { font-size: 0.85rem; color: var(--muted); margin-bottom: 1.5rem; }

        /* ── CARDS ── */
        .cards-list { display: flex; flex-direction: column; gap: 0.65rem; }
        .select-card {
            display: flex; align-items: center; gap: 0.9rem;
            padding: 0.9rem 1rem; background: var(--white);
            border: 2px solid var(--border); border-radius: 12px;
            cursor: pointer; transition: all 0.2s; width: 100%; text-align: left;
        }
        .select-card:hover { border-color: rgba(212,168,67,0.5); background: rgba(212,168,67,0.02); }
        .select-card:hover { transform: translateY(-1px); }
        .select-card.selected { border-color: var(--gold); background: rgba(212,168,67,0.04); }
        .card-avatar {
            width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--gold), #8B6914);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1rem; color: #000; font-family: 'Outfit', sans-serif;
        }
        .card-body { flex: 1; min-width: 0; }
        .card-name { font-weight: 700; font-size: 0.9rem; color: var(--text); font-family: 'Outfit', sans-serif; }
        .card-sub { font-size: 0.75rem; color: var(--muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-right { flex-shrink: 0; text-align: right; }
        .card-price { font-weight: 800; color: var(--gold); font-family: 'Outfit', sans-serif; font-size: 0.9rem; white-space: nowrap; }
        .card-pts { font-size: 0.7rem; color: #F59E0B; display: flex; align-items: center; justify-content: flex-end; gap: 2px; margin-top: 2px; }
        .card-check { color: var(--border); flex-shrink: 0; }
        .card-check.active { color: var(--gold); }

        /* ── DATE GRID ── */
        .date-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }
        .date-card {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 0.7rem 0.3rem; background: var(--white);
            border: 2px solid var(--border); border-radius: 10px;
            cursor: pointer; transition: all 0.2s; position: relative;
            min-height: 68px; gap: 1px;
        }
        .date-card:hover:not(.disabled) { border-color: rgba(212,168,67,0.6); background: rgba(212,168,67,0.03); }
        .date-card.selected { border-color: var(--gold); background: rgba(212,168,67,0.07); }
        .date-card.disabled { background: #F4F4F5; cursor: not-allowed; opacity: 0.55; }
        .date-day-name { font-size: 0.65rem; color: var(--muted); font-weight: 500; }
        .date-num { font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 800; color: var(--text); line-height: 1; }
        .date-card.selected .date-num { color: var(--gold-dark); }
        .date-card.disabled .date-num { color: var(--light); }
        .date-full { font-size: 0.58rem; color: var(--muted); font-weight: 600; }
        .date-discount {
            position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);
            background: #FEE2E2; color: #DC2626; font-size: 0.55rem; font-weight: 700;
            padding: 1px 5px; border-radius: 20px; white-space: nowrap;
        }

        /* ── TIME GRID ── */
        .time-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.45rem; margin-top: 1rem; }
        .time-slot {
            padding: 8px 4px; background: var(--white); border: 2px solid var(--border);
            border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 600;
            text-align: center; transition: all 0.2s; color: var(--text);
        }
        .time-slot:hover { border-color: rgba(212,168,67,0.5); }
        .time-slot.selected { background: var(--gold); border-color: var(--gold); color: #000; }
        .time-slot.booked {
            background: #F9FAFB; border-color: #E5E7EB; color: #9CA3AF;
            cursor: not-allowed; position: relative;
        }
        .time-slot.mine {
            background: #EFF6FF; border-color: #3B82F6; color: #1D4ED8; cursor: default;
        }

        /* ── CONFIRMATION CARD ── */
        .confirm-card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.25rem; margin-bottom: 1.25rem;
        }
        .c-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 0.55rem 0; border-bottom: 1px solid var(--border); }
        .c-row:last-child { border-bottom: none; }
        .c-key { font-size: 0.83rem; color: var(--muted); }
        .c-val { font-size: 0.83rem; font-weight: 600; color: var(--text); text-align: right; max-width: 58%; }
        .c-pts { font-size: 0.68rem; color: #F59E0B; display: flex; align-items: center; justify-content: flex-end; gap: 2px; margin-top: 2px; }
        .c-total-row { display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 2px solid var(--border); }
        .c-total-key { font-weight: 700; font-size: 0.95rem; font-family: 'Outfit', sans-serif; }
        .c-total-val { font-weight: 800; font-size: 1.2rem; font-family: 'Outfit', sans-serif; }

        /* ── INPUTS ── */
        .field-label { display: block; font-size: 0.83rem; font-weight: 600; color: var(--text); margin-bottom: 0.4rem; }
        .field-label span { font-weight: 400; color: var(--muted); }
        .field-wrap { position: relative; margin-bottom: 1rem; }
        .field-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); font-size: 0.9rem; pointer-events: none; }
        .field-input {
            width: 100%; padding: 11px 14px; background: var(--white);
            border: 2px solid var(--border); border-radius: 10px;
            font-size: 0.875rem; outline: none; font-family: 'Inter', sans-serif;
            transition: border-color 0.2s; color: var(--text);
        }
        .field-input.has-icon { padding-left: 40px; }
        .field-input:focus { border-color: var(--gold); }

        /* ── BUTTONS ── */
        .btn-nav { display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }
        .btn-back {
            background: none; border: 1px solid var(--border); color: var(--muted);
            padding: 10px 16px; border-radius: 9px; cursor: pointer; font-size: 0.83rem;
            font-family: 'Inter', sans-serif; transition: all 0.2s;
            display: flex; align-items: center; gap: 5px;
        }
        .btn-back:hover { border-color: var(--text); color: var(--text); }
        .btn-next {
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #000; font-weight: 700; padding: 10px 22px; border-radius: 9px;
            border: none; cursor: pointer; font-size: 0.875rem; font-family: 'Outfit', sans-serif;
            transition: all 0.25s; display: flex; align-items: center; gap: 7px;
            flex-shrink: 0;
        }
        .btn-next:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 5px 18px rgba(212,168,67,0.4); }
        .btn-next:disabled { opacity: 0.35; cursor: not-allowed; }
        .btn-confirm {
            width: 100%; background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: #000; font-weight: 700; padding: 13px; border-radius: 10px;
            border: none; cursor: pointer; font-size: 0.95rem; font-family: 'Outfit', sans-serif;
            transition: all 0.25s; margin-bottom: 1rem;
        }
        .btn-confirm:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 5px 18px rgba(212,168,67,0.4); }
        .btn-confirm:disabled { opacity: 0.4; cursor: not-allowed; }

        /* ── SUCCESS ── */
        .success-wrap { text-align: center; padding: 1.5rem 0; }
        .success-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem; font-size: 2rem;
        }
        .success-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.4rem; }
        .success-sub { color: var(--muted); font-size: 0.875rem; margin-bottom: 1.5rem; }



        /* ── TOAST ── */
        .toast {
            position: fixed; top: calc(var(--navbar-h) + 10px); right: 1rem;
            z-index: 400; display: none;
            background: #1a1a1a; color: #fff;
            padding: 12px 18px; border-radius: 10px; font-size: 0.83rem; font-weight: 500;
            align-items: center; gap: 8px; max-width: calc(100vw - 2rem);
            box-shadow: 0 6px 24px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.08);
            transform: translateX(110%);
            transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1);
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        [x-cloak] { display: none !important; }
        .step-panel { animation: fadeUp 0.3s ease forwards; }

        /* ── DESKTOP ── */
        @media (min-width: 768px) {
            .navbar { padding: 0 2rem; height: 64px; --navbar-h: 64px; }
            .nav-logo-icon { width: 38px; height: 38px; font-size: 16px; }

            /* Show desktop sidebar, hide mobile bar */
            .mobile-summary { display: none !important; }
            .booking-sidebar {
                display: flex;
                flex-direction: column;
                width: 340px;
                flex-shrink: 0;
                background: var(--sidebar);
                border-left: 1px solid var(--border);
                order: 2;
                position: sticky;
                top: 64px;
                height: calc(100vh - 64px);
                overflow-y: auto;
                padding: 2rem 1.5rem;
            }

            .booking-wrap { flex-direction: row; align-items: flex-start; }
            .booking-main { flex: 1; padding: 2.5rem 3rem 3rem 2.5rem; max-width: none; }
            .date-grid { grid-template-columns: repeat(5, 1fr); }
            .time-grid { grid-template-columns: repeat(4, 1fr); }
            .step-title { font-size: 2.1rem; }
        }

        @media (min-width: 1024px) {
            .booking-sidebar { width: 380px; }
            .booking-main { padding: 3rem 3rem 3rem 3.5rem; }
        }

        /* Pantallas muy pequeñas (< 380px) */
        @media (max-width: 380px) {
            .date-grid { grid-template-columns: repeat(3, 1fr); }
            .step-title { font-size: 1.5rem; }
            .booking-main { padding: 1rem 0.75rem 2rem; }
            .mobile-summary { padding: 0.6rem 0.75rem; }
            .mobile-chips { gap: 0.3rem; }
            .mobile-chip { font-size: 0.7rem; padding: 3px 8px; }
        }

        /* Pantallas muy pequeñas (< 360px) */
        @media (max-width: 360px) {
            .date-grid { grid-template-columns: repeat(3, 1fr); }
            .time-grid { grid-template-columns: repeat(3, 1fr); }
            .step-title { font-size: 1.35rem; }
            .nav-logo-text { font-size: 0.82rem; }
            /* Ocultar texto del botón admin en móvil muy pequeño */
            .nav-login span { display: none; }
            .nav-login { padding: 7px 10px; }
        }
    </style>
</head>
<body x-data="bookingApp()" x-init="init()" x-cloak>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="/" class="nav-logo">
            <div class="nav-logo-icon" {!! isset($settings['logo_image']) ? 'style="background: transparent;"' : '' !!}>
                @if(isset($settings['logo_image']))
                    <img src="{{ asset($settings['logo_image']) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 4px;">
                @else
                    A
                @endif
            </div>
            <span class="nav-logo-text">{{ $businessName }}</span>
        </a>
        <div class="nav-actions">
            <a href="/" class="nav-back" style="display:none;" id="nav-back-home">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Inicio
            </a>
            <!-- Mis Turnos (solo si tiene reservas en localStorage) -->
            <button class="nav-my-bookings" id="btn-my-bookings" x-data x-show="$store.myBk.count > 0" @click="$dispatch('open-my-bookings')" style="display:none;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Mis Turnos
                <div class="nav-booking-dot"></div>
            </button>
        </div>
    </nav>

    <!-- Panel: Mis Turnos -->
    <div x-data="{ open: false }" @open-my-bookings.window="open = true">
        <!-- Overlay -->
        <div x-show="open" x-transition.opacity @click="open = false"
             style="position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:500;backdrop-filter:blur(3px);"
             x-cloak></div>
        <!-- Drawer -->
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="transform translate-y-full"
             x-transition:enter-end="transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="transform translate-y-0"
             x-transition:leave-end="transform translate-y-full"
             style="position:fixed;bottom:0;left:0;right:0;background:white;border-radius:20px 20px 0 0;z-index:501;max-height:85vh;overflow-y:auto;box-shadow:0 -8px 40px rgba(0,0,0,0.2);">
            <div style="padding:1.25rem 1.25rem 0.75rem;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;">
                <div style="font-family:'Outfit',sans-serif;font-weight:700;font-size:1rem;">📅 Mis Turnos</div>
                <button @click="open = false" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#666;line-height:1;">&times;</button>
            </div>
            <div id="my-bookings-list" style="padding:1rem 1.25rem 2rem;">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- PAGE SHELL -->
    <div class="page-shell">
        <div class="booking-wrap">

            <!-- MOBILE SUMMARY BAR (hidden on desktop via @media) -->
            <div class="mobile-summary" x-show="step > 1 && step < 5">
                <div class="mobile-chips">
                    <div class="mobile-chip" x-show="selectedBarberId">
                        💈 <strong x-text="getBarberName()"></strong>
                    </div>
                    <div class="mobile-chip" x-show="selectedServiceId">
                        ✂️ <strong x-text="getServiceName()"></strong>
                    </div>
                    <div class="mobile-chip" x-show="selectedDate">
                        📅 <strong x-text="selectedDate"></strong>
                    </div>
                    <div class="mobile-chip" x-show="selectedTime">
                        🕐 <strong x-text="selectedTime"></strong>
                    </div>
                </div>
                <div class="mobile-total" x-show="totalPrice > 0" x-text="'Gs.'+Number(totalPrice).toLocaleString('es-PY')"></div>
            </div>

            <!-- MAIN FLOW -->
            <div class="booking-main">

                <!-- STEPPER -->
                <div class="stepper">
                    <template x-for="(s, i) in [1,2,3,4]" :key="i">
                        <div style="display:contents;">
                            <div class="step-node"
                                :class="{ done: step > i+1, active: step === i+1, pending: step < i+1 }">
                                <template x-if="step > i+1">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                                </template>
                                <template x-if="step <= i+1">
                                    <span x-text="i+1"></span>
                                </template>
                            </div>
                            <template x-if="i < 3">
                                <div class="step-line" :class="step > i+1 ? 'done' : 'pending'"></div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- PASO 1: Barbero -->
                <div x-show="step === 1" class="step-panel">
                    <p class="step-tag">Paso 1 de 4</p>
                    <h1 class="step-title">Seleccioná tu barbero</h1>
                    <p class="step-sub">¿Con quién querés atenderte hoy?</p>

                    <div class="cards-list">
                        <template x-for="b in barbers" :key="b.id">
                            <button class="select-card" :class="selectedBarberId === b.id ? 'selected' : ''" @click="selectBarber(b.id)">
                                <div class="card-avatar" x-text="b.name.charAt(0)"></div>
                                <div class="card-body">
                                    <div class="card-name" x-text="b.name"></div>
                                    <div class="card-sub" x-text="getSpecialties(b)"></div>
                                </div>
                                <svg class="card-check" :class="selectedBarberId === b.id ? 'active' : ''" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path x-show="selectedBarberId === b.id" d="M9 12l2 2 4-4"/></svg>
                            </button>
                        </template>
                    </div>

                    <div class="btn-nav" style="justify-content: flex-end; border: none; padding-top: 1.5rem;">
                        <button class="btn-next" :disabled="!selectedBarberId" @click="nextStep()">
                            Continuar
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- PASO 2: Servicio -->
                <div x-show="step === 2" class="step-panel" style="display:none">
                    <p class="step-tag">Paso 2 de 4 · <a href="#" @click.prevent="step=1" x-text="getBarberName()"></a></p>
                    <h1 class="step-title">¿Qué servicio querés?</h1>
                    <p class="step-sub">Elegí el servicio que más se adapte a vos.</p>

                    <div class="cards-list">
                        <template x-for="svc in services" :key="svc.id">
                            <button class="select-card" :class="selectedServiceId === svc.id ? 'selected' : ''" @click="selectService(svc.id)">
                                <div class="card-body">
                                    <div class="card-name" x-text="svc.name"></div>
                                    <div class="card-sub" x-text="svc.duration_min + ' min'"></div>
                                </div>
                                <div class="card-right">
                                    <div class="card-price" x-text="'Gs. ' + Number(svc.price).toLocaleString('es-PY')"></div>
                                    <div class="card-pts">
                                        <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 21 12 17.77 5.82 21 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
                                        <span x-text="Math.round(svc.price / 4000) + ' pts'"></span>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div class="btn-nav">
                        <button class="btn-back" @click="prevStep()">← Volver</button>
                        <button class="btn-next" :disabled="!selectedServiceId" @click="nextStep()">
                            Continuar
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- PASO 3: Fecha & Hora -->
                <div x-show="step === 3" class="step-panel" style="display:none">
                    <p class="step-tag">Paso 3 de 4 · <a href="#" @click.prevent="step=2" x-text="getServiceName()"></a></p>
                    <h1 class="step-title">Elegí fecha y hora</h1>
                    <p class="step-sub">Seleccioná el día y horario disponible.</p>

                    <div class="date-grid">
                        <template x-for="day in availableDays" :key="day.date">
                            <button
                                class="date-card"
                                :class="{ disabled: day.full || day.blocked, selected: selectedDate === day.date, 'date-blocked': day.blocked }"
                                :disabled="day.full || day.blocked"
                                @click="!day.full && !day.blocked && selectDate(day.date)"
                            >
                                <div class="date-day-name" x-text="day.dayName"></div>
                                <div class="date-num" x-text="day.dayNum"></div>
                                <template x-if="day.blocked"><div class="date-full" style="color:#ef4444;background:rgba(239,68,68,0.1);">Cerrado</div></template>
                                <template x-if="day.full && !day.blocked"><div class="date-full">Completo</div></template>
                                <template x-if="day.discount && !day.full && !day.blocked"><div class="date-discount">-25%</div></template>
                            </button>
                        </template>
                    </div>

                    <!-- Horarios -->
                    <div x-show="selectedDate" style="margin-top:1.5rem;">
                        <p style="font-size:0.83rem; font-weight:600; color:var(--text); margin-bottom:0.5rem;">Horarios disponibles</p>
                        <div x-show="isLoadingTimes" style="color:var(--muted); font-size:0.83rem; padding:0.5rem 0;">Cargando horarios…</div>
                        <div x-show="!isLoadingTimes && allSlots.length === 0" style="color:#EF4444; font-size:0.83rem;">Sin horarios disponibles este día.</div>
                        <div class="time-grid" x-show="!isLoadingTimes && allSlots.length > 0">
                            <template x-for="slot in allSlots" :key="slot.time">
                                <button 
                                    x-show="!slot.is_blocked_by_admin && !slot.is_out_of_schedule"
                                    class="time-slot" 
                                    :class="{
                                        selected: selectedTime === slot.time && slot.available,
                                        booked:   !slot.available && !isMySlot(slot),
                                        mine:     !slot.available && isMySlot(slot)
                                    }"
                                    @click="slot.available ? selectedTime = slot.time : (isMySlot(slot) ? promptCancelSlot(slot) : null)"
                                    :disabled="!slot.available && !isMySlot(slot)"
                                >
                                    <span x-text="slot.time"></span>
                                    <span x-show="!slot.available && isMySlot(slot)" style="display:block; font-size:0.62rem; margin-top:1px;">Mi turno</span>
                                    <span x-show="!slot.available && !isMySlot(slot) && !slot.is_blocked_by_admin" style="display:block; font-size:0.62rem; margin-top:1px;" x-text="slot.booked ? slot.booked.customer_masked : 'Reservado'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="btn-nav">
                        <button class="btn-back" @click="prevStep()">← Volver</button>
                        <button class="btn-next" :disabled="!selectedDate || !selectedTime" @click="nextStep()">
                            Continuar
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- PASO 4: Confirmar -->
                <div x-show="step === 4" class="step-panel" style="display:none">
                    <p class="step-tag">Paso 4 de 4</p>
                    <h1 class="step-title">Confirmá tu reserva</h1>
                    <p class="step-sub">Revisá los detalles antes de confirmar.</p>

                    <div class="confirm-card">
                        <div class="c-row">
                            <span class="c-key">Barbería</span>
                            <span class="c-val">{{ $businessName }}</span>
                        </div>
                        <div class="c-row">
                            <span class="c-key">Barbero</span>
                            <span class="c-val" x-text="getBarberName()"></span>
                        </div>
                        <div class="c-row">
                            <span class="c-key">Servicio</span>
                            <div style="text-align:right; max-width:58%;">
                                <div class="c-val" style="max-width:none;" x-text="getServiceName()"></div>
                                <div class="c-pts">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 21 12 17.77 5.82 21 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
                                    <span x-text="Math.round(totalPrice/4000)+' puntos'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="c-row">
                            <span class="c-key">Fecha</span>
                            <span class="c-val" x-text="formatDate(selectedDate)"></span>
                        </div>
                        <div class="c-row">
                            <span class="c-key">Hora</span>
                            <span class="c-val" x-text="selectedTime"></span>
                        </div>
                        <div class="c-total-row">
                            <span class="c-total-key">Total</span>
                            <span class="c-total-val" x-text="'Gs. '+Number(totalPrice).toLocaleString('es-PY')"></span>
                        </div>
                    </div>
                    
                    <!-- Política de Cancelación -->
                    <div style="background:rgba(212,168,67,0.08); border:1px solid rgba(212,168,67,0.3); border-radius:12px; padding:1rem; margin-bottom:1.5rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                            <span style="font-size:1.2rem;">⚠️</span>
                            <span style="font-weight:700; font-size:0.85rem; color:var(--gold-dark);">Política de cancelación</span>
                        </div>
                        <p style="font-size:0.8rem; color:var(--text); line-height:1.5; margin:0; margin-bottom:0.5rem;">
                            Si necesitás cancelar tu turno, tenés tiempo hasta <strong>5 minutos antes</strong> de la hora reservada. Pasado ese tiempo, el turno ya no se podrá cancelar a través del sistema.
                        </p>
                        <p style="font-size:0.8rem; color:var(--text); line-height:1.5; margin:0;">
                            <strong>Tolerancia máxima:</strong> 30 minutos.<br>
                            <strong>Inasistencia:</strong> Un recargo del 50% aplicará en el próximo corte.
                        </p>
                    </div>

                    <!-- Nombre -->
                    <label class="field-label">Tu nombre completo</label>
                    <div class="field-wrap">
                        <input class="field-input" type="text" x-model="customerName" placeholder="Ej: Juan García">
                    </div>

                    <!-- Teléfono -->
                    <label class="field-label">WhatsApp / Teléfono <span>(opcional)</span></label>
                    <div class="field-wrap" style="margin-bottom:1.5rem;">
                        <span class="field-icon">📱</span>
                        <input class="field-input has-icon" type="tel" x-model="customerPhone" placeholder="+595 9XX XXX XXX">
                    </div>

                    <button class="btn-confirm" :disabled="isSubmitting || !customerName" @click="submitBooking()">
                        <span x-show="!isSubmitting">✅ Confirmar turno</span>
                        <span x-show="isSubmitting">Procesando…</span>
                    </button>

                    <button class="btn-back" @click="prevStep()" style="width:100%; justify-content:center;">← Cambiar algo</button>
                </div>

                <!-- PASO 5: Éxito -->
                <div x-show="step === 5" class="step-panel success-wrap" style="display:none">
                    <div class="success-icon">✅</div>
                    <h2 class="success-title">¡Turno confirmado!</h2>
                    <p class="success-sub">
                        Tu turno fue reservado con éxito.
                    </p>

                    <div class="confirm-card" style="text-align:left; margin-bottom:1.25rem;">
                        <div class="c-row"><span class="c-key">Cliente</span><span class="c-val" x-text="customerName"></span></div>
                        <div class="c-row"><span class="c-key">Servicio</span><span class="c-val" x-text="getServiceName()"></span></div>
                        <div class="c-row"><span class="c-key">Barbero</span><span class="c-val" x-text="getBarberName()"></span></div>
                        <div class="c-row" style="border-bottom:none;"><span class="c-key">Fecha y hora</span><span class="c-val" x-text="formatDate(selectedDate)+' · '+selectedTime"></span></div>
                    </div>

                    <!-- WhatsApp CTA -->
                    <div style="background:rgba(37,211,102,0.06);border:1px solid rgba(37,211,102,0.2);border-radius:14px;padding:1.1rem 1rem;margin-bottom:1.25rem;">
                        <div style="font-size:0.8rem;color:#166534;font-weight:600;margin-bottom:0.6rem;">
                            📱 Paso final: confirmá tu turno por WhatsApp
                        </div>
                        <p style="font-size:0.75rem;color:#4b7a52;margin-bottom:0.85rem;line-height:1.5;">
                            Tocá el botón para avisarnos. Tu mensaje ya está escrito, solo envialo.
                        </p>
                        <a :href="buildWaLink()" target="_blank" rel="noopener"
                           style="display:flex;align-items:center;justify-content:center;gap:0.6rem;width:100%;padding:0.85rem;background:#25D366;color:white;border-radius:10px;font-weight:700;font-size:0.95rem;text-decoration:none;font-family:'Outfit',sans-serif;transition:background 0.15s;box-shadow:0 4px 12px rgba(37,211,102,0.3);"
                           onmouseover="this.style.background='#1eb859'" onmouseout="this.style.background='#25D366'">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                            Enviar WhatsApp de confirmación
                        </a>
                    </div>

                    <p style="font-size:0.78rem; color:var(--muted); margin-bottom:1.25rem; text-align:center;">
                        📌 Si necesitás cancelar tu turno, volvé a esta página desde <strong>este mismo celular</strong> y tocá <strong>"Mis Turnos"</strong>.
                    </p>

                    <button class="btn-next" style="margin:0 auto;" @click="resetFlow()">Hacer otra reserva</button>
                </div>

            </div><!-- /booking-main -->

            <!-- DESKTOP SIDEBAR -->
            <aside class="booking-sidebar">
                <div style="font-size:0.68rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--light); margin-bottom:1rem;">Resumen de Reserva</div>

                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:0.75rem;">
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem;">
                        <span style="color:var(--muted);">Barbería</span>
                        <span style="font-weight:600;">{{ $businessName }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem;">
                        <span style="color:var(--muted);">Barbero</span>
                        <span style="font-weight:600;" x-text="selectedBarberId ? getBarberName() : '—'"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:0.85rem;">
                        <span style="color:var(--muted);">Servicio</span>
                        <div style="text-align:right; max-width:60%;">
                            <div style="font-weight:600;" x-text="selectedServiceId ? getServiceName() : '—'"></div>
                            <div class="c-pts" x-show="selectedServiceId">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 21 12 17.77 5.82 21 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
                                <span x-text="Math.round(totalPrice/4000)+' pts'"></span>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem;" x-show="selectedDate">
                        <span style="color:var(--muted);">Fecha</span>
                        <span style="font-weight:600;" x-text="selectedDate"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem;" x-show="selectedTime">
                        <span style="color:var(--muted);">Hora</span>
                        <span style="font-weight:600;" x-text="selectedTime"></span>
                    </div>
                </div>

                <hr style="border:none; border-top:1px solid var(--border); margin:0.75rem 0;">

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:700; font-size:1rem; font-family:'Outfit',sans-serif;">Total</span>
                    <span style="font-weight:800; font-size:1.3rem; font-family:'Outfit',sans-serif;" x-text="totalPrice > 0 ? 'Gs. '+Number(totalPrice).toLocaleString('es-PY') : 'Gs. 0'"></span>
                </div>

                <button style="background:none; border:none; color:var(--light); font-size:0.75rem; cursor:pointer; display:flex; align-items:center; gap:4px; margin-top:0.75rem; font-family:'Inter',sans-serif; padding:0;" @click="resetFlow()" x-show="step > 1 && step < 5">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Abandonar reserva
                </button>

                <img x-show="selectedServiceId" src="/images/barber_tools.png" alt="Barbería" style="width:100%; border-radius:12px; object-fit:cover; height:170px; margin-top:1.25rem;">

                <div x-show="!selectedServiceId" style="background:rgba(212,168,67,0.05); border:1px solid rgba(212,168,67,0.2); border-radius:12px; padding:1rem; margin-top:1.25rem;">
                    <div style="font-weight:700; font-size:0.83rem; color:var(--gold); margin-bottom:0.5rem;">💡 Cómo funciona</div>
                    <ol style="list-style:none; display:flex; flex-direction:column; gap:0.5rem;">
                        <li style="font-size:0.8rem; color:var(--muted); display:flex; gap:6px;"><span style="color:var(--gold); font-weight:700;">1.</span> Elegí tu barbero</li>
                        <li style="font-size:0.8rem; color:var(--muted); display:flex; gap:6px;"><span style="color:var(--gold); font-weight:700;">2.</span> Seleccioná el servicio</li>
                        <li style="font-size:0.8rem; color:var(--muted); display:flex; gap:6px;"><span style="color:var(--gold); font-weight:700;">3.</span> Escogé la fecha y hora</li>
                        <li style="font-size:0.8rem; color:var(--muted); display:flex; gap:6px;"><span style="color:var(--gold); font-weight:700;">4.</span> Confirmá tu turno</li>
                    </ol>
                </div>
            </aside>

        </div>
    </div>



    <!-- Toast -->
    <div id="bk-toast" class="toast">
        <span id="bk-toast-icon">✅</span>
        <span id="bk-toast-msg">Turno confirmado</span>
    </div>

    <script>
        // Store Alpine global para el contador de mis reservas
        document.addEventListener('alpine:init', () => {
            Alpine.store('myBk', {
                count: JSON.parse(localStorage.getItem('athenea_bookings') || '[]').length,
                refresh() {
                    this.count = JSON.parse(localStorage.getItem('athenea_bookings') || '[]').length;
                }
            });
        });

        function bookingApp() {
            return {
                step: 1,
                customerName: '',
                customerPhone: '',
                selectedBarberId: null,
                selectedServiceId: null,
                selectedDate: '',
                selectedTime: '',
                totalPrice: 0,
                durationMin: 0,
                barbers: [],
                services: [],
                availableTimes: [],
                allSlots: [],
                availableDays: [],
                isLoadingTimes: false,
                isSubmitting: false,
                isCancelling: false,
                appointmentId: null,
                myBookings: JSON.parse(localStorage.getItem('athenea_bookings') || '[]'),
                _blockedDatesCache: {},

                async init() {
                    this.generateDays();
                    await this.fetchData();
                    this.renderMyBookingsList();
                },

                generateDays(blockedDateSet = new Set()) {
                    const names = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                    const today = new Date();
                    this.availableDays = [];
                    for (let i = 0; i < 30; i++) {
                        const d = new Date(today);
                        d.setDate(today.getDate() + i);
                        const dow = d.getDay();
                        if (dow === 0) continue; // No domingos
                        
                        // Obtenemos la fecha en formato YYYY-MM-DD usando la zona horaria local (evita errores de UTC)
                        const year = d.getFullYear();
                        const month = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        const dateStr = `${year}-${month}-${day}`;

                        this.availableDays.push({
                            date: dateStr,
                            dayName: names[dow],
                            dayNum: d.getDate(),
                            full: false,
                            blocked: blockedDateSet.has(dateStr),
                            discount: false
                        });
                        if (this.availableDays.length >= 14) break;
                    }
                },

                async loadBlockedDatesForBarber(barberId) {
                    if (this._blockedDatesCache[barberId] !== undefined) {
                        return this._blockedDatesCache[barberId];
                    }
                    try {
                        const res = await fetch(`/api/booking/blocked-dates?barber_id=${barberId}&days=60`);
                        const data = await res.json();
                        const dates = (data.blocked_dates || []).map(b => b.date);
                        this._blockedDatesCache[barberId] = new Set(dates);
                        return this._blockedDatesCache[barberId];
                    } catch {
                        this._blockedDatesCache[barberId] = new Set();
                        return this._blockedDatesCache[barberId];
                    }
                },

                async fetchData() {
                    try {
                        const res = await fetch('/api/booking/data');
                        const data = await res.json();
                        this.barbers = data.barbers?.length ? data.barbers : this.mockBarbers();
                        this.services = data.services?.length ? data.services : this.mockServices();
                    } catch {
                        this.barbers = this.mockBarbers();
                        this.services = this.mockServices();
                    }
                },

                mockBarbers() {
                    return [
                        { id: 1, name: 'Alesoturi', specialties: JSON.stringify(['Fade','Barba','Cejas']) },
                        { id: 2, name: 'Marcos', specialties: JSON.stringify(['Corte Clásico','Degradado']) },
                        { id: 3, name: 'Vic', specialties: JSON.stringify(['Diseños','Color','Texturizado']) }
                    ];
                },

                mockServices() {
                    return [
                        { id: 1, name: 'Corte + Asesoramiento + Lavado', price: 70000, duration_min: 45 },
                        { id: 2, name: 'Corte + Barba + Lavado', price: 110000, duration_min: 60 },
                        { id: 3, name: 'Barba clásica', price: 50000, duration_min: 30 },
                        { id: 4, name: 'Perfilado de cejas', price: 20000, duration_min: 10 },
                        { id: 5, name: 'Corte niños (hasta 12 años)', price: 45000, duration_min: 30 }
                    ];
                },

                getSpecialties(b) {
                    try { const s = JSON.parse(b.specialties || '[]'); return s.length ? s.join(', ') : 'Barbero profesional'; }
                    catch { return 'Barbero profesional'; }
                },

                async selectDate(date) {
                    this.selectedDate = date;
                    this.selectedTime = '';
                    this.allSlots = [];
                    this.availableTimes = [];
                    this.isLoadingTimes = true;
                    try {
                        const r = await fetch(`/api/booking/availability?barber_id=${this.selectedBarberId}&service_id=${this.selectedServiceId}&date=${date}`);
                        const d = await r.json();
                        if (d.allSlots?.length) {
                            this.allSlots = d.allSlots;
                            this.availableTimes = d.availableTimes || [];
                        } else {
                            const mock = ['09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30'];
                            this.allSlots = mock.map(t => ({ time: t, available: true, booked: null }));
                            this.availableTimes = mock;
                        }
                    } catch {
                        const mock = ['09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30'];
                        this.allSlots = mock.map(t => ({ time: t, available: true, booked: null }));
                        this.availableTimes = mock;
                    } finally {
                        this.isLoadingTimes = false;
                    }
                },

                isMySlot(slot) {
                    if (!slot.booked) return false;
                    return this.myBookings.some(b => b.appointment_id === slot.booked.appointment_id);
                },

                promptCancelSlot(slot) {
                    const booking = this.myBookings.find(b => b.appointment_id === slot.booked?.appointment_id);
                    if (!booking) return;
                    if (!confirm(`¿Cancelar tu turno del ${booking.date} a las ${slot.time}?`)) return;
                    this.cancelBooking(booking.appointment_id, slot.time);
                },

                async cancelBooking(appointmentId, time) {
                    this.isCancelling = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const res = await fetch('/api/booking/cancel', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                            body: JSON.stringify({ appointment_id: appointmentId })
                        });
                        const result = await res.json();
                        if (result.success) {
                            this.myBookings = this.myBookings.filter(b => b.appointment_id !== appointmentId);
                            localStorage.setItem('athenea_bookings', JSON.stringify(this.myBookings));
                            if (Alpine.store) Alpine.store('myBk').refresh();
                            this.renderMyBookingsList();
                            if (this.selectedDate) await this.selectDate(this.selectedDate);
                            this.showToast('✅ Turno cancelado con éxito');
                        } else {
                            this.showToast('❌ ' + (result.message || 'No se pudo cancelar'));
                        }
                    } catch {
                        this.showToast('❌ Error al cancelar. Intentá de nuevo.');
                    } finally {
                        this.isCancelling = false;
                    }
                },

                selectBarber(id) {
                    this.selectedBarberId = id;
                    setTimeout(() => this.nextStep(), 180);
                },

                selectService(id) {
                    this.selectedServiceId = id;
                    const s = this.services.find(x => x.id === id);
                    if (s) { this.totalPrice = s.price; this.durationMin = s.duration_min; }
                    setTimeout(() => this.nextStep(), 180);
                },

                async nextStep() { 
                    if (this.step === 1 && !this.selectedBarberId) return;
                    if (this.step === 2 && !this.selectedServiceId) return;
                    if (this.step === 3 && (!this.selectedDate || !this.selectedTime)) return;
                    if (this.step < 5) {
                        this.step++;
                        // When entering step 3, reload days with blocked dates for this barber
                        if (this.step === 3 && this.selectedBarberId) {
                            const blocked = await this.loadBlockedDatesForBarber(this.selectedBarberId);
                            this.generateDays(blocked);
                            // If previously selected date is now blocked, clear it
                            if (this.selectedDate && blocked.has(this.selectedDate)) {
                                this.selectedDate = '';
                                this.selectedTime = '';
                                this.allSlots = [];
                            }
                        }
                    }
                },
                prevStep() { if (this.step > 1) this.step--; },

                getBarberName() { const b = this.barbers.find(x => x.id === this.selectedBarberId); return b ? b.name : ''; },
                getServiceName() { const s = this.services.find(x => x.id === this.selectedServiceId); return s ? s.name : ''; },

                formatDate(d) {
                    if (!d) return '';
                    try { return new Date(d + 'T12:00:00').toLocaleDateString('es-PY', { weekday:'long', day:'numeric', month:'long' }); }
                    catch { return d; }
                },

                // — Construye el link de WhatsApp con el mensaje pre-armado —
                buildWaLink() {
                    const num = window.WA_NUMBER || '595000000000';
                    const name = this.customerName || 'un cliente';
                    const svc  = this.getServiceName();
                    const barb = this.getBarberName();
                    const date = this.formatDate(this.selectedDate);
                    const time = this.selectedTime;
                    const biz  = window.BUSINESS_NAME || 'la barbería';
                    const msg  = `Hola! Soy ${name}. Acabo de reservar un turno en ${biz} — ${svc} con ${barb} para el ${date} a las ${time} hs.`;
                    return `https://wa.me/${num}?text=${encodeURIComponent(msg)}`;
                },

                // — Renderiza la lista de "Mis Turnos" en el drawer —
                renderMyBookingsList() {
                    const container = document.getElementById('my-bookings-list');
                    if (!container) return;

                    window._currentBookingTab = window._currentBookingTab || 'upcoming';

                    // Clasificamos los turnos en Próximos e Historial
                    const now = new Date();
                    const upcoming = [];
                    const history = [];

                    // Ordenamos por fecha descendente (más recientes primero)
                    const sorted = [...this.myBookings].sort((a, b) => {
                        return new Date(b.date + 'T' + (b.time || '00:00')) - new Date(a.date + 'T' + (a.time || '00:00'));
                    });

                    sorted.forEach(b => {
                        const bDate = new Date(b.date + 'T' + (b.time || '00:00'));
                        // Si es de hoy o futuro, va a próximos. Si no, al historial.
                        if (bDate >= now) {
                            upcoming.push(b);
                        } else {
                            history.push(b);
                        }
                    });

                    // Exponemos la función globalmente para los botones de las tabs
                    window._renderMyBookingsList = this.renderMyBookingsList.bind(this);
                    window._changeBookingTab = (tab) => {
                        window._currentBookingTab = tab;
                        window._renderMyBookingsList();
                    };

                    let html = `
                        <div style="display:flex;gap:10px;margin-bottom:1.5rem;border-bottom:1px solid #eee;padding-bottom:0.5rem;">
                            <button onclick="window._changeBookingTab('upcoming')" style="flex:1;background:none;border:none;font-weight:600;font-size:0.9rem;padding:8px 10px;cursor:pointer;color:${window._currentBookingTab === 'upcoming' ? 'var(--gold-dark)' : '#888'};border-bottom:${window._currentBookingTab === 'upcoming' ? '2px solid var(--gold)' : '2px solid transparent'};transition:all 0.2s;">
                                Próximos (${upcoming.length})
                            </button>
                            <button onclick="window._changeBookingTab('history')" style="flex:1;background:none;border:none;font-weight:600;font-size:0.9rem;padding:8px 10px;cursor:pointer;color:${window._currentBookingTab === 'history' ? 'var(--gold-dark)' : '#888'};border-bottom:${window._currentBookingTab === 'history' ? '2px solid var(--gold)' : '2px solid transparent'};transition:all 0.2s;">
                                Historial (${history.length})
                            </button>
                        </div>
                    `;

                    const displayList = window._currentBookingTab === 'upcoming' ? upcoming : history;

                    if (displayList.length === 0) {
                        html += `<div style="text-align:center;padding:2rem 0;color:#999;">
                            <div style="font-size:2.5rem;margin-bottom:0.5rem">🗓️</div>
                            <div style="font-size:0.85rem;">No hay turnos en esta sección.</div>
                        </div>`;
                    } else {
                        // Limitamos el historial para que no sea pesado
                        const isHistory = window._currentBookingTab === 'history';
                        const renderList = isHistory ? displayList.slice(0, 15) : displayList;

                        html += renderList.map(b => `
                            <div style="background:#f9f9f9;border:1px solid #eee;border-radius:12px;padding:1rem;margin-bottom:0.85rem;opacity:${isHistory ? '0.75' : '1'};">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.65rem;">
                                    <div>
                                        <div style="font-weight:700;font-size:0.95rem;margin-bottom:2px;text-decoration:${isHistory ? 'line-through' : 'none'};">${b.customer_name}</div>
                                        <div style="font-size:0.78rem;color:#888;">${b.service} &bull; ${b.barber}</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-weight:700;color:${isHistory ? '#888' : '#D4A843'};font-size:0.9rem;">${b.date}</div>
                                        <div style="font-size:0.85rem;font-weight:600;">${b.time} hs</div>
                                    </div>
                                </div>
                                ${!isHistory ? `
                                <button onclick="window._cancelFromDrawer(${b.appointment_id})"
                                    style="width:100%;padding:0.6rem;background:rgba(239,68,68,0.08);color:#dc2626;border:1px solid rgba(239,68,68,0.2);border-radius:8px;font-weight:600;font-size:0.8rem;cursor:pointer;transition:background 0.15s;"
                                    onmouseover="this.style.background='rgba(239,68,68,0.15)'" onmouseout="this.style.background='rgba(239,68,68,0.08)'">
                                    ✕ Cancelar turno
                                </button>` : ''}
                            </div>
                        `).join('');

                        if (isHistory && displayList.length > 15) {
                            html += `<div style="text-align:center;font-size:0.75rem;color:#888;margin-top:1rem;">Mostrando los últimos 15 turnos.</div>`;
                        }
                    }

                    container.innerHTML = html;
                },

                async submitBooking() {
                    if (!this.customerName.trim()) return;
                    this.isSubmitting = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const res = await fetch('/api/booking/store', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                            body: JSON.stringify({
                                customer_name: this.customerName,
                                customer_phone: this.customerPhone,
                                barber_id: this.selectedBarberId,
                                service_id: this.selectedServiceId,
                                appointment_date: this.selectedDate,
                                appointment_time: this.selectedTime,
                                total_price: this.totalPrice,
                                duration_min: this.durationMin
                            })
                        });
                        const result = await res.json();
                        this.appointmentId = result.appointment?.id || Math.floor(Math.random() * 90000 + 10000);

                        if (result.success && result.appointment?.id) {
                            const booking = {
                                appointment_id: result.appointment.id,
                                date:           this.selectedDate,
                                time:           this.selectedTime,
                                barber:         this.getBarberName(),
                                service:        this.getServiceName(),
                                customer_name:  this.customerName,
                            };
                            this.myBookings.push(booking);
                            localStorage.setItem('athenea_bookings', JSON.stringify(this.myBookings));
                            if (Alpine.store) Alpine.store('myBk').refresh();
                            this.renderMyBookingsList();

                            // Disparar notificación push al admin
                            this.notifyAdmin(booking);
                            this.step = 5;
                            this.showToast('✅ ¡Turno confirmado!');
                        } else {
                             const errorMsg = result.message || 'Faltan datos en el panel. Asegurate de crear al barbero y servicio.';
                             this.showToast('❌ Error: ' + errorMsg);
                        }
                    } catch {
                        this.showToast('❌ Ocurrió un error al intentar reservar.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async notifyAdmin(booking) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        await fetch('/api/booking/notify-admin', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                            body: JSON.stringify({
                                customer_name: booking.customer_name,
                                date: booking.date,
                                time: booking.time,
                                barber: booking.barber,
                                service: booking.service,
                            })
                        });
                    } catch { /* silencioso */ }
                },

                showToast(msg) {
                    const t = document.getElementById('bk-toast');
                    const m = document.getElementById('bk-toast-msg');
                    if (!t) return;
                    if (m) m.textContent = msg;
                    t.style.display = 'flex';
                    requestAnimationFrame(() => { t.style.transform = 'translateX(0)'; });
                    setTimeout(() => {
                        t.style.transform = 'translateX(110%)';
                        setTimeout(() => { t.style.display = 'none'; }, 350);
                    }, 4000);
                },

                resetFlow() {
                    this.step = 1; this.customerName = ''; this.customerPhone = '';
                    this.selectedBarberId = null; this.selectedServiceId = null;
                    this.selectedDate = ''; this.selectedTime = '';
                    this.totalPrice = 0; this.appointmentId = null; this.availableTimes = [];
                }
        };
        }

        // Cancelar desde el drawer (fuera del contexto de Alpine)
        window._cancelFromDrawer = async function(appointmentId) {
            if (!confirm('¿Cancelar este turno?')) return;
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            try {
                const res = await fetch('/api/booking/cancel', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ appointment_id: appointmentId })
                });
                const result = await res.json();
                if (result.success) {
                    let bks = JSON.parse(localStorage.getItem('athenea_bookings') || '[]');
                    bks = bks.filter(b => b.appointment_id !== appointmentId);
                    localStorage.setItem('athenea_bookings', JSON.stringify(bks));
                    if (typeof Alpine !== 'undefined' && Alpine.store) Alpine.store('myBk').refresh();
                    const container = document.getElementById('my-bookings-list');
                    if (container) {
                        if (bks.length === 0) {
                            container.innerHTML = `<div style="text-align:center;padding:2rem 0;color:#999;">
                                <div style="font-size:2.5rem;margin-bottom:0.5rem">🗓️</div>
                                <div style="font-size:0.85rem;">No tenés turnos activos desde este celular.</div>
                            </div>`;
                        } else {
                            location.reload();
                        }
                    }
                    alert('✅ Turno cancelado con éxito.');
                } else {
                    alert('❌ ' + (result.message || 'No se pudo cancelar el turno.'));
                }
            } catch { alert('❌ Error al conectar. Intentá de nuevo.'); }
        };
    </script>
    <!-- Enlace oculto a Admin -->
    <div style="text-align: center; padding: 1.5rem 0; opacity: 0.4;">
        <a href="/admin/login" style="color: var(--muted); font-size: 0.75rem; text-decoration: none; font-weight: 500;">Admin</a>
    </div>

    <!-- ─── WHATSAPP FLOTANTE ─── -->
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '595000000000') }}?text={{ urlencode($settings['whatsapp_message'] ?? 'Hola! Quiero reservar un turno en ' . ($settings['hero_title'] ?? 'Athenea Barber')) }}" target="_blank" id="whatsapp-btn"
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
    </a>

    <style>
        @keyframes whatsapp-pulse {
            0% { box-shadow: 0 0 0 0 rgba(37,211,102,0.5); }
            70% { box-shadow: 0 0 0 15px rgba(37,211,102,0); }
            100% { box-shadow: 0 0 0 0 rgba(37,211,102,0); }
        }
    </style>

</body>
</html>

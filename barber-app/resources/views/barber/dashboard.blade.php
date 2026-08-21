<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panel de Barbero - {{ $barber->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1117; --surface: #1a1d27; --surface2: #232736; --surface3: #2d3245;
            --border: #2d3245; --text: #f8fafc; --text2: #e2e8f0; --muted: #94a3b8;
            --accent: #6366f1; --accent2: #818cf8; --accent-bg: rgba(99,102,241,0.15);
            --danger: #ef4444; --success: #22c55e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text); min-height: 100vh; }
        
        /* TOPBAR */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0; z-index: 50;
        }
        .topbar-info { display: flex; align-items: center; gap: 0.85rem; }
        .barber-avatar-top {
            width: 40px; height: 40px; border-radius: 10px;
            background: var(--accent-bg); color: var(--accent2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.1rem; overflow: hidden; flex-shrink: 0;
        }
        .barber-avatar-top img { width: 100%; height: 100%; object-fit: cover; }
        .topbar h1 { font-size: 1rem; font-weight: 700; color: var(--text); }
        .topbar p { font-size: 0.75rem; color: var(--muted); }

        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 0.5rem; padding: 0.6rem 1.1rem; border-radius: 12px;
            font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none;
            transition: all 0.2s; text-decoration: none;
        }
        .btn-primary { background: linear-gradient(135deg, var(--accent), var(--accent2)); color: white; box-shadow: 0 4px 15px rgba(99,102,241,0.25); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.35); }
        .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--surface2); color: var(--text); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.75rem; border-radius: 8px; }
        .btn-danger-soft { background: rgba(239,68,68,0.1); color: var(--danger); border: 1px solid rgba(239,68,68,0.2); }
        .btn-danger-soft:hover { background: rgba(239,68,68,0.2); }
        .btn-success-soft { background: rgba(34,197,94,0.1); color: var(--success); border: 1px solid rgba(34,197,94,0.2); }
        .btn-success-soft:hover { background: rgba(34,197,94,0.2); }

        .container { max-width: 1300px; margin: 0 auto; padding: 2rem 5%; }
        
        /* TABS */
        .tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; background: var(--surface); padding: 0.4rem; border-radius: 14px; border: 1px solid var(--border); width: fit-content; }
        .tab-btn { padding: 0.6rem 1.25rem; border-radius: 10px; border: none; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; background: transparent; color: var(--muted); }
        .tab-btn.active { background: var(--accent); color: white; box-shadow: 0 2px 8px rgba(99,102,241,0.4); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .card-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 0.25rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem; }
        .card-subtitle { font-size: 0.8rem; color: var(--muted); margin-bottom: 1.25rem; }
        
        /* SCHEDULE GRID */
        .days-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem; }
        
        .day-card {
            background: var(--surface2); border: 1px solid var(--border); border-radius: 14px;
            padding: 1.1rem; transition: all 0.2s;
        }
        .day-card.day-off { opacity: 0.6; }
        .day-card.day-off .hours-grid { pointer-events: none; opacity: 0.4; }
        
        .day-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; }
        .day-name { font-weight: 700; font-size: 0.9rem; color: var(--text); text-transform: capitalize; }
        .day-toggle { display: flex; align-items: center; gap: 0.5rem; }
        .day-off-label { font-size: 0.72rem; color: var(--danger); font-weight: 600; display: none; }
        .day-card.day-off .day-off-label { display: inline; }
        
        /* HOUR BLOCKS */
        .hours-grid { display: flex; flex-direction: column; gap: 0.35rem; }
        .hour-row { display: flex; align-items: center; gap: 0.5rem; }
        .hour-label { width: 32px; font-size: 0.75rem; color: var(--muted); font-weight: 600; flex-shrink: 0; text-align: right; }
        
        .hour-block {
            flex: 1; padding: 0.4rem 0.6rem; border-radius: 8px; border: none; cursor: pointer;
            font-size: 0.75rem; font-weight: 600; transition: all 0.15s;
            display: flex; align-items: center; justify-content: center; gap: 0.3rem;
        }
        .hour-block.available {
            background: rgba(34, 197, 94, 0.18); color: #4ade80;
            border: 1px solid rgba(34,197,94,0.3);
        }
        .hour-block.available:hover { background: rgba(34, 197, 94, 0.3); transform: scale(1.02); }
        .hour-block.unavailable {
            background: rgba(239, 68, 68, 0.12); color: #f87171;
            border: 1px solid rgba(239,68,68,0.25);
        }
        .hour-block.unavailable:hover { background: rgba(239, 68, 68, 0.22); transform: scale(1.02); }
        
        .hour-block .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
        
        /* APPOINTMENTS */
        .apt-date-heading { font-size: 0.9rem; font-weight: 700; color: var(--accent2); margin: 1.25rem 0 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; }
        .apt-item { display: flex; align-items: flex-start; gap: 0.85rem; padding: 0.75rem 0; border-bottom: 1px solid rgba(45,50,69,0.5); }
        .apt-item:last-child { border-bottom: none; }
        .time-chip { background: var(--accent-bg); color: var(--accent2); padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 700; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0; }
        .apt-name { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.15rem; }
        .apt-meta { font-size: 0.78rem; color: var(--muted); }
        
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--muted); }
        .empty-icon { font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.5; }
        
        .alert-success {
            background: rgba(34,197,94,0.1); color: var(--success);
            padding: 0.85rem 1.1rem; border-radius: 12px; margin-bottom: 1.5rem;
            border: 1px solid rgba(34,197,94,0.2); font-size: 0.9rem;
        }

        .legend { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; font-size: 0.78rem; color: var(--muted); flex-wrap: wrap; }
        .legend-item { display: flex; align-items: center; gap: 0.35rem; }
        .legend-dot { width: 10px; height: 10px; border-radius: 3px; }
        .legend-dot.green { background: rgba(34,197,94,0.5); border: 1px solid rgba(34,197,94,0.4); }
        .legend-dot.red { background: rgba(239,68,68,0.35); border: 1px solid rgba(239,68,68,0.3); }

        .save-bar { position: sticky; bottom: 0; background: var(--bg); border-top: 1px solid var(--border); padding: 1rem 5%; z-index: 40; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .save-bar p { font-size: 0.82rem; color: var(--muted); }

        @media (max-width: 600px) {
            .days-grid { grid-template-columns: 1fr; }
            .topbar { gap: 0.75rem; flex-wrap: wrap; }
            .tabs { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-info">
            <div class="barber-avatar-top">
                @if($barber->image_path)
                    <img src="{{ asset($barber->image_path) }}" alt="{{ $barber->name }}">
                @else
                    {{ strtoupper(substr($barber->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <h1>✂️ {{ $barber->name }}</h1>
                <p>Panel de Barbero</p>
            </div>
        </div>
        <a href="{{ route('admin.logout') }}" class="btn btn-ghost btn-sm">Cerrar Sesión</a>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert-success">✅ {{ session('success') }}</div>
        @endif

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('schedule', this)">🕒 Mis Horarios</button>
            <button class="tab-btn" onclick="switchTab('appointments', this)">📅 Mis Turnos</button>
        </div>

        {{-- ===== TAB: HORARIOS ===== --}}
        <div class="tab-content active" id="tab-schedule">
            <div class="card">
                <div class="card-title">🕒 Configurar Disponibilidad</div>
                <div class="card-subtitle">Tocá cada hora para alternar entre disponible (🟢) y no disponible (🔴). Si estás enfermo o no vas a trabajar un día, podés marcarlo como "Día libre".</div>
                
                <div class="legend">
                    <div class="legend-item"><div class="legend-dot green"></div> Disponible — los clientes pueden reservar</div>
                    <div class="legend-item"><div class="legend-dot red"></div> No disponible — bloqueado</div>
                </div>
            </div>

            <form method="POST" action="{{ route('barber.schedule.update') }}" id="scheduleForm">
                @csrf
                <div class="days-grid" id="daysGrid"></div>
            </form>

            <div class="save-bar">
                <p>Los cambios son inmediatos para los clientes al guardar.</p>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('scheduleForm').submit()">
                    💾 Guardar Horarios
                </button>
            </div>
        </div>

        {{-- ===== TAB: TURNOS ===== --}}
        <div class="tab-content" id="tab-appointments">
            <div class="card">
                <div class="card-title">📅 Próximos Turnos</div>
                <div class="card-subtitle">Tus citas agendadas desde hoy en adelante.</div>
                
                @if($appointments->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon">😴</div>
                        <p>No tenés turnos agendados próximamente.</p>
                    </div>
                @else
                    @php $currentDate = null; @endphp
                    @foreach($appointments as $apt)
                        @if($currentDate !== $apt->appointment_date->toDateString())
                            <div class="apt-date-heading">
                                {{ \Carbon\Carbon::parse($apt->appointment_date)->locale('es')->isoFormat('dddd D [de] MMMM') }}
                            </div>
                            @php $currentDate = $apt->appointment_date->toDateString(); @endphp
                        @endif
                        <div class="apt-item">
                            <div class="time-chip">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}</div>
                            <div>
                                <div class="apt-name">{{ $apt->client ? $apt->client->name : ($apt->customer_name ?? 'Cliente') }}</div>
                                <div class="apt-meta">
                                    {{ $apt->service ? $apt->service->name : 'Servicio' }}
                                    @if($apt->client && $apt->client->phone)
                                        &nbsp;·&nbsp;
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $apt->client->phone) }}" target="_blank" style="color: var(--success); text-decoration: none;">💬 WhatsApp</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <script>
        // ─── Data from backend ──────────────────────────────────────────────
        const savedSchedule = @json($schedule ?? []);

        const DAYS = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
        const DAY_LABELS = { lunes:'Lunes', martes:'Martes', miercoles:'Miércoles', jueves:'Jueves', viernes:'Viernes', sabado:'Sábado', domingo:'Domingo' };
        const HOURS = [
            '07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30',
            '11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30',
            '15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30',
            '19:00','19:30','20:00','20:30','21:00','21:30'
        ];

        // Default: Lun-Sab available 8-11 & 13-20, 12 blocked; Domingo off
        function getDefaultHours(day) {
            if (day === 'domingo') return null; // day off
            return HOURS.reduce((acc, h) => {
                const hourInt = parseInt(h.split(':')[0]);
                acc[h] = (hourInt >= 8 && hourInt <= 11) || (hourInt >= 13 && hourInt <= 20);
                return acc;
            }, {});
        }

        // Current state: { lunes: { 8: true, 9: true, ... }, ... }
        // or { lunes: false } meaning full day off
        let scheduleState = {};

        DAYS.forEach(day => {
            const saved = savedSchedule[day];
            if (saved === undefined || saved === null) {
                // Not set yet — use defaults
                const def = getDefaultHours(day);
                scheduleState[day] = def === null ? false : def;
            } else if (saved === false || saved === 'off') {
                scheduleState[day] = false;
            } else if (typeof saved === 'object') {
                // Saved as { "8": true, "9": false, ... }
                scheduleState[day] = {};
                HOURS.forEach(h => {
                    scheduleState[day][h] = saved[h] === true || saved[h] === 1;
                });
            } else {
                scheduleState[day] = getDefaultHours(day) ?? false;
            }
        });

        // ─── Render ──────────────────────────────────────────────────────────
        function renderSchedule() {
            const grid = document.getElementById('daysGrid');
            grid.innerHTML = '';

            DAYS.forEach(day => {
                const isDayOff = scheduleState[day] === false;
                const hours = isDayOff ? {} : scheduleState[day];

                const card = document.createElement('div');
                card.className = 'day-card' + (isDayOff ? ' day-off' : '');
                card.id = `day-card-${day}`;

                card.innerHTML = `
                    <div class="day-header">
                        <div class="day-name">${DAY_LABELS[day]}</div>
                        <div class="day-toggle">
                            <span class="day-off-label">Día libre</span>
                            <button type="button" class="btn btn-sm ${isDayOff ? 'btn-success-soft' : 'btn-danger-soft'}" onclick="toggleDayOff('${day}')">
                                ${isDayOff ? '✅ Activar día' : '🤒 Día libre'}
                            </button>
                        </div>
                    </div>
                    <div class="hours-grid" id="hours-${day}">
                        ${HOURS.map(h => {
                            const avail = isDayOff ? false : (hours[h] !== false && hours[h] !== undefined ? !!hours[h] : false);
                            const idH = h.replace(':', '_');
                            return `
                                <div class="hour-row">
                                    <div class="hour-label">${h}</div>
                                    <button type="button" class="hour-block ${avail ? 'available' : 'unavailable'}" id="hour-${day}-${idH}" onclick="toggleHour('${day}', '${h}')">
                                        <span class="dot"></span>
                                        ${avail ? 'Disponible' : 'No disponible'}
                                    </button>
                                </div>`;
                        }).join('')}
                    </div>
                `;

                grid.appendChild(card);
            });
        }

        function toggleHour(day, hour) {
            if (scheduleState[day] === false) return;
            scheduleState[day][hour] = !scheduleState[day][hour];
            
            const idH = hour.replace(':', '_');
            const btn = document.getElementById(`hour-${day}-${idH}`);
            const isNowAvail = scheduleState[day][hour];
            btn.className = `hour-block ${isNowAvail ? 'available' : 'unavailable'}`;
            btn.innerHTML = `<span class="dot"></span>${isNowAvail ? 'Disponible' : 'No disponible'}`;
        }

        function toggleDayOff(day) {
            if (scheduleState[day] === false) {
                // Reactivate with defaults
                scheduleState[day] = getDefaultHours(day) ?? {};
            } else {
                scheduleState[day] = false;
            }
            renderSchedule();
            buildHiddenInputs();
        }

        // ─── Build hidden inputs before submit ──────────────────────────────
        function buildHiddenInputs() {
            const form = document.getElementById('scheduleForm');
            form.querySelectorAll('input[type="hidden"]').forEach(i => i.remove());

            DAYS.forEach(day => {
                if (scheduleState[day] === false) {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = `schedule[${day}]`;
                    inp.value = 'off';
                    form.appendChild(inp);
                } else {
                    HOURS.forEach(h => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = `schedule[${day}][${h}]`;
                        inp.value = scheduleState[day][h] ? '1' : '0';
                        form.appendChild(inp);
                    });
                }
            });
        }

        document.getElementById('scheduleForm').addEventListener('submit', function() {
            buildHiddenInputs();
        });

        // ─── Tabs ────────────────────────────────────────────────────────────
        function switchTab(name, el) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(`tab-${name}`).classList.add('active');
            el.classList.add('active');
        }

        // ─── Init ────────────────────────────────────────────────────────────
        renderSchedule();
    </script>
</body>
</html>

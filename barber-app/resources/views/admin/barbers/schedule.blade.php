@extends('admin.layout')

@section('title', 'Horarios — ' . $barber->name)
@section('page-title', 'Horarios de ' . $barber->name)

@section('content')
<style>
    .schedule-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .back-btn { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; font-weight: 600; color: var(--muted); text-decoration: none; padding: 0.45rem 0.9rem; border-radius: 10px; border: 1px solid var(--border); background: var(--surface); transition: all 0.2s; }
    .back-btn:hover { color: var(--text); background: var(--surface2); }

    .legend { display: flex; align-items: center; gap: 1.25rem; font-size: 0.78rem; color: var(--muted); flex-wrap: wrap; margin-bottom: 1.5rem; }
    .legend-item { display: flex; align-items: center; gap: 0.4rem; }
    .legend-dot { width: 12px; height: 12px; border-radius: 4px; }
    .legend-dot.green { background: rgba(34,197,94,0.45); border: 1px solid rgba(34,197,94,0.5); }
    .legend-dot.red   { background: rgba(239,68,68,0.35); border: 1px solid rgba(239,68,68,0.4); }

    .days-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem; }

    .day-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 1.1rem; transition: all 0.2s; }
    .day-card.day-off { border-color: rgba(239,68,68,0.25); }
    .day-card.day-off .hours-grid { opacity: 0.3; pointer-events: none; }

    .day-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
    .day-name { font-weight: 700; font-size: 0.95rem; color: var(--text); }
    .day-off-badge { font-size: 0.68rem; font-weight: 700; color: var(--danger); background: rgba(239,68,68,0.1); padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid rgba(239,68,68,0.2); display:none; }
    .day-card.day-off .day-off-badge { display: inline-flex; }

    .hours-grid { display: flex; flex-direction: column; gap: 0.3rem; }
    .hour-row { display: flex; align-items: center; gap: 0.5rem; }
    .hour-label { width: 30px; font-size: 0.73rem; color: var(--muted); font-weight: 600; text-align: right; flex-shrink: 0; }

    .hour-block {
        flex: 1; padding: 0.38rem 0.6rem; border-radius: 8px; border: none;
        cursor: pointer; font-size: 0.73rem; font-weight: 600;
        display: flex; align-items: center; gap: 0.3rem;
        transition: all 0.12s; user-select: none;
    }
    .hour-block .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
    .hour-block.available {
        background: rgba(34,197,94,0.14); color: #4ade80;
        border: 1px solid rgba(34,197,94,0.25);
    }
    .hour-block.available:hover { background: rgba(34,197,94,0.28); transform: scale(1.01); }
    .hour-block.unavailable {
        background: rgba(239,68,68,0.09); color: #f87171;
        border: 1px solid rgba(239,68,68,0.2);
    }
    .hour-block.unavailable:hover { background: rgba(239,68,68,0.2); transform: scale(1.01); }

    .btn-day-off { background: transparent; border: 1px solid var(--border); color: var(--muted); font-size: 0.7rem; padding: 0.25rem 0.55rem; border-radius: 7px; cursor: pointer; font-weight: 600; transition: all 0.15s; }
    .btn-day-off:hover { background: rgba(239,68,68,0.1); color: var(--danger); border-color: rgba(239,68,68,0.3); }
    .day-card.day-off .btn-day-off { background: rgba(34,197,94,0.1); color: var(--success); border-color: rgba(34,197,94,0.3); }
    .day-card.day-off .btn-day-off:hover { background: rgba(34,197,94,0.2); }

    .save-bar {
        position: sticky; bottom: 0; background: var(--bg);
        border-top: 1px solid var(--border); padding: 1rem 0;
        margin-top: 1.5rem; z-index: 20;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    }
    .save-bar p { font-size: 0.82rem; color: var(--muted); }

    .alert-success { background: rgba(34,197,94,0.1); color: var(--success); padding: 0.85rem 1.1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(34,197,94,0.2); font-size: 0.9rem; }

    @media (max-width: 600px) {
        .days-grid { grid-template-columns: 1fr; }
        .schedule-header { flex-wrap: wrap; }
    }
</style>

{{-- Header --}}
<div class="schedule-header">
    <a href="{{ route('admin.barbers.index') }}" class="back-btn">
        ← Volver a Barberos
    </a>
    <div>
        <h2 style="font-size:1.1rem;font-weight:700;">🕒 Horarios de {{ $barber->name }}</h2>
        <p style="font-size:0.78rem;color:var(--muted);">Tocá cada hora para marcar disponible/no disponible. Podés marcar un día libre completo.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-success">✅ {{ session('success') }}</div>
@endif

<div class="legend">
    <div class="legend-item"><div class="legend-dot green"></div> Disponible — el cliente puede reservar</div>
    <div class="legend-item"><div class="legend-dot red"></div> No disponible — bloqueado</div>
</div>

<form method="POST" action="{{ route('admin.barber.schedule.update', $barber) }}" id="scheduleForm">
    @csrf
    <div class="days-grid" id="daysGrid"></div>
    <div class="save-bar">
        <p>Los cambios se aplican inmediatamente cuando los clientes reservan.</p>
        <button type="button" class="btn btn-primary" onclick="submitSchedule()">💾 Guardar Horarios</button>
    </div>
</form>

<script>
    const savedSchedule = @json($schedule ?? []);

    const DAYS  = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
    const LABELS = { lunes:'Lunes', martes:'Martes', miercoles:'Miércoles', jueves:'Jueves', viernes:'Viernes', sabado:'Sábado', domingo:'Domingo' };
    const HOURS = [
        '07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30',
        '11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30',
        '15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30',
        '19:00','19:30','20:00','20:30','21:00','21:30'
    ];

    // Default: Lun-Sab 8-11 available, 12 blocked, 13-20 available. Dom: off.
    function getDefault(day) {
        if (day === 'domingo') return null;
        return HOURS.reduce((acc, h) => {
            const hourInt = parseInt(h.split(':')[0]);
            acc[h] = (hourInt >= 8 && hourInt <= 11) || (hourInt >= 13 && hourInt <= 20);
            return acc;
        }, {});
    }

    // Build state from saved data
    let state = {};
    DAYS.forEach(day => {
        const s = savedSchedule[day];
        if (s === undefined || s === null) {
            const d = getDefault(day);
            state[day] = d === null ? false : d;
        } else if (s === false || s === 'off' || s === 0) {
            state[day] = false;
        } else if (typeof s === 'object' && !Array.isArray(s)) {
            state[day] = {};
            HOURS.forEach(h => {
                state[day][h] = s[h] === true || s[h] === 1 || s[String(h)] === true || s[String(h)] === 1;
            });
        } else {
            const d = getDefault(day);
            state[day] = d === null ? false : d;
        }
    });

    function render() {
        const grid = document.getElementById('daysGrid');
        grid.innerHTML = '';
        DAYS.forEach(day => {
            const off = state[day] === false;
            const hours = off ? {} : (state[day] || {});

            const card = document.createElement('div');
            card.className = 'day-card' + (off ? ' day-off' : '');
            card.id = 'dc-' + day;

            card.innerHTML = `
                <div class="day-header">
                    <div style="display:flex;align-items:center;gap:0.6rem;">
                        <div class="day-name">${LABELS[day]}</div>
                        <div class="day-off-badge">Día libre</div>
                    </div>
                    <button type="button" class="btn-day-off" onclick="toggleDayOff('${day}')">
                        ${off ? '✅ Activar' : '🔴 Día libre'}
                    </button>
                </div>
                <div class="hours-grid">
                    ${HOURS.map(h => {
                        const avail = off ? false : !!hours[h];
                        // Replace : with _ for ID to be safe
                        const idH = h.replace(':', '_');
                        return `<div class="hour-row">
                            <div class="hour-label">${h}</div>
                            <button type="button" class="hour-block ${avail ? 'available' : 'unavailable'}"
                                id="hb-${day}-${idH}" onclick="toggle('${day}','${h}')">
                                <span class="dot"></span>${avail ? 'Disponible' : 'No disponible'}
                            </button>
                        </div>`;
                    }).join('')}
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function toggle(day, h) {
        if (state[day] === false) return;
        state[day][h] = !state[day][h];
        const idH = h.replace(':', '_');
        const btn = document.getElementById(`hb-${day}-${idH}`);
        const avail = state[day][h];
        btn.className = `hour-block ${avail ? 'available' : 'unavailable'}`;
        btn.innerHTML = `<span class="dot"></span>${avail ? 'Disponible' : 'No disponible'}`;
    }

    function toggleDayOff(day) {
        state[day] = (state[day] === false) ? (getDefault(day) || {}) : false;
        render();
    }

    function submitSchedule() {
        const form = document.getElementById('scheduleForm');
        // Remove old hidden inputs (but keep CSRF)
        form.querySelectorAll('input[data-sched]').forEach(i => i.remove());

        DAYS.forEach(day => {
            if (state[day] === false) {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = `schedule[${day}]`; i.value = 'off'; i.dataset.sched = '1';
                form.appendChild(i);
            } else {
                HOURS.forEach(h => {
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = `schedule[${day}][${h}]`;
                    i.value = state[day][h] ? '1' : '0'; i.dataset.sched = '1';
                    form.appendChild(i);
                });
            }
        });
        form.submit();
    }

    render();
</script>
@endsection

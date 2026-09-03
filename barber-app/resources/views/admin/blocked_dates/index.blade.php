@extends('admin.layout')

@section('title', 'Días Bloqueados (Excepciones)')
@section('page-title', 'Días Bloqueados (Excepciones)')

@section('content')
<style>
    .exc-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.5rem;
        align-items: start;
    }

    /* ── Calendario ── */
    .cal-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.5rem;
    }
    .cal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .cal-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text);
    }
    .cal-nav-btn {
        background: var(--surface2);
        border: 1px solid var(--border);
        color: var(--text2);
        width: 34px; height: 34px;
        border-radius: 8px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.15s;
        font-size: 1rem;
    }
    .cal-nav-btn:hover { background: var(--surface3); color: var(--text); }

    .cal-filter {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }
    .cal-filter label { font-size: 0.82rem; color: var(--muted); }
    .cal-filter select {
        padding: 0.4rem 0.7rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface2);
        color: var(--text);
        font-size: 0.82rem;
        cursor: pointer;
    }

    .cal-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        margin-bottom: 6px;
    }
    .cal-weekday {
        text-align: center;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--muted);
        padding: 0.25rem 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }
    .cal-day {
        aspect-ratio: 1;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        border: 2px solid transparent;
        position: relative;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text);
        background: transparent;
        user-select: none;
    }
    .cal-day:hover:not(.cal-day-empty):not(.cal-day-past) {
        background: var(--surface2);
        border-color: var(--border);
    }
    .cal-day-empty { cursor: default; }
    .cal-day-past {
        color: var(--muted);
        cursor: default;
        opacity: 0.4;
    }
    .cal-day-today {
        border-color: var(--accent) !important;
        color: var(--accent2);
    }
    .cal-day-blocked {
        background: rgba(239, 68, 68, 0.12) !important;
        border-color: rgba(239, 68, 68, 0.3) !important;
        color: #f87171;
    }
    .cal-day-blocked:hover {
        background: rgba(239, 68, 68, 0.22) !important;
    }
    .cal-day-dot {
        width: 5px; height: 5px;
        border-radius: 50%;
        background: #f87171;
        position: absolute;
        bottom: 4px;
    }
    .cal-day-lock {
        font-size: 0.65rem;
        margin-top: 1px;
        opacity: 0.8;
    }

    /* ── Panel lateral ── */
    .side-panel {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .side-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.25rem;
    }
    .side-card h3 {
        font-size: 0.9rem;
        font-weight: 700;
        margin: 0 0 1rem;
        color: var(--text);
    }

    .form-group { margin-bottom: 0.85rem; }
    .form-group label { display: block; font-size: 0.78rem; color: var(--muted); margin-bottom: 0.35rem; font-weight: 600; }
    .form-group input, .form-group select {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface2);
        color: var(--text);
        font-size: 0.85rem;
    }
    .btn-save {
        width: 100%;
        padding: 0.7rem;
        border-radius: 8px;
        border: none;
        background: var(--accent);
        color: #fff;
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.1s;
    }
    .btn-save:hover { opacity: 0.9; transform: translateY(-1px); }

    /* Lista de bloqueados */
    .blocked-list { display: flex; flex-direction: column; gap: 0.5rem; }
    .blocked-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--surface2);
        border-radius: 10px;
        padding: 0.6rem 0.75rem;
        gap: 0.5rem;
    }
    .blocked-item-date { font-size: 0.82rem; font-weight: 700; color: var(--text); }
    .blocked-item-meta { font-size: 0.72rem; color: var(--muted); }
    .blocked-item-badge {
        padding: 0.2rem 0.5rem;
        border-radius: 5px;
        font-size: 0.68rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-global { background: rgba(59,130,246,0.1); color: #3b82f6; }
    .badge-barber { background: rgba(139,92,246,0.1); color: #8b5cf6; }
    .btn-unblock {
        background: transparent;
        border: 1px solid rgba(239,68,68,0.3);
        color: #f87171;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.15s;
        flex-shrink: 0;
    }
    .btn-unblock:hover { background: rgba(239,68,68,0.12); }

    .legend { display: flex; gap: 1.25rem; flex-wrap: wrap; margin-bottom: 1rem; font-size: 0.75rem; color: var(--muted); }
    .legend-item { display: flex; align-items: center; gap: 0.4rem; }
    .legend-dot { width: 12px; height: 12px; border-radius: 4px; }
    .legend-dot.blocked { background: rgba(239,68,68,0.25); border: 1px solid rgba(239,68,68,0.4); }
    .legend-dot.today { border: 2px solid var(--accent); background: transparent; }

    @media (max-width: 900px) {
        .exc-layout { grid-template-columns: 1fr; }
    }
</style>


<div class="exc-layout">

    {{-- ── CALENDARIO ── --}}
    <div class="cal-card">
        <div class="cal-header">
            <button class="cal-nav-btn" id="prevMonth">‹</button>
            <div class="cal-title" id="calMonthTitle">–</div>
            <button class="cal-nav-btn" id="nextMonth">›</button>
        </div>

        <div class="cal-filter">
            <label>Filtrar por barbero:</label>
            <select id="calBarberFilter">
                <option value="">Todos</option>
                @foreach($barbers as $barber)
                    <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="legend">
            <div class="legend-item"><div class="legend-dot blocked"></div> Día bloqueado</div>
            <div class="legend-item"><div class="legend-dot today"></div> Hoy</div>
        </div>

        <div class="cal-weekdays">
            <div class="cal-weekday">Dom</div>
            <div class="cal-weekday">Lun</div>
            <div class="cal-weekday">Mar</div>
            <div class="cal-weekday">Mié</div>
            <div class="cal-weekday">Jue</div>
            <div class="cal-weekday">Vie</div>
            <div class="cal-weekday">Sáb</div>
        </div>
        <div class="cal-grid" id="calGrid"></div>
    </div>

    {{-- ── PANEL LATERAL ── --}}
    <div class="side-panel">
        {{-- Formulario de bloqueo --}}
        <div class="side-card">
            <h3>🔒 Bloquear Fecha</h3>
            <form action="{{ route('admin.blocked_dates.store') }}" method="POST" id="blockForm">
                @csrf
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="date" id="blockDate" required
                           min="{{ now()->toDateString() }}"
                           value="{{ old('date') }}">
                </div>
                <div class="form-group">
                    <label>Aplica a</label>
                    <select name="barber_id">
                        <option value="">Todos los barberos (Cierre general)</option>
                        @foreach($barbers as $barber)
                            <option value="{{ $barber->id }}" {{ old('barber_id') == $barber->id ? 'selected' : '' }}>
                                {{ $barber->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Motivo (opcional)</label>
                    <input type="text" name="reason" placeholder="Ej. Feriado, Vacaciones, Cumpleaños" value="{{ old('reason') }}">
                </div>
                <button type="submit" class="btn-save">Guardar Bloqueo</button>
            </form>
        </div>

        {{-- Lista de días bloqueados --}}
        <div class="side-card">
            <h3>📋 Fechas Bloqueadas</h3>
            @if($blockedDates->isEmpty())
                <p style="font-size:0.82rem; color:var(--muted); text-align:center; padding:1rem 0;">
                    No hay fechas bloqueadas actualmente.
                </p>
            @else
                <div class="blocked-list">
                    @foreach($blockedDates as $blocked)
                        <div class="blocked-item">
                            <div>
                                <div class="blocked-item-date">
                                    🗓 {{ \Carbon\Carbon::parse($blocked->date)->translatedFormat('d/m/Y') }}
                                </div>
                                <div class="blocked-item-meta">
                                    @if($blocked->barber_id)
                                        <span class="blocked-item-badge badge-barber">{{ $blocked->barber->name }}</span>
                                    @else
                                        <span class="blocked-item-badge badge-global">Global</span>
                                    @endif
                                    {{ $blocked->reason ? ' · ' . $blocked->reason : '' }}
                                </div>
                            </div>
                            <form action="{{ route('admin.blocked_dates.destroy', $blocked->id) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este bloqueo?')">
                                @csrf
                                <button type="submit" class="btn-unblock">Eliminar</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

@php
    $blockedDatesJson = $blockedDates->map(function($b) {
        return [
            'date'      => $b->date->toDateString(),
            'barber_id' => $b->barber_id,
            'reason'    => $b->reason,
        ];
    })->values();
@endphp

<script>
    // Blocked dates data from server
    const BLOCKED_DATES = @json($blockedDatesJson);

    const TODAY_STR = '{{ now()->toDateString() }}';
    let currentYear  = new Date().getFullYear();
    let currentMonth = new Date().getMonth(); // 0-indexed
    let selectedBarberId = '';

    const MONTH_NAMES = [
        'Enero','Febrero','Marzo','Abril','Mayo','Junio',
        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
    ];

    function getFilteredBlocked() {
        if (!selectedBarberId) return BLOCKED_DATES;
        return BLOCKED_DATES.filter(b => b.barber_id === null || b.barber_id === parseInt(selectedBarberId));
    }

    function isBlocked(dateStr) {
        const bId = selectedBarberId ? parseInt(selectedBarberId) : null;
        return BLOCKED_DATES.some(b => {
            if (b.date !== dateStr) return false;
            if (!selectedBarberId) return true; // show all
            return b.barber_id === null || b.barber_id === bId;
        });
    }

    function renderCalendar() {
        const grid = document.getElementById('calGrid');
        const title = document.getElementById('calMonthTitle');
        grid.innerHTML = '';
        title.textContent = `${MONTH_NAMES[currentMonth]} ${currentYear}`;

        const firstDay = new Date(currentYear, currentMonth, 1).getDay(); // 0=Sun
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        // Empty cells
        for (let i = 0; i < firstDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'cal-day cal-day-empty';
            grid.appendChild(empty);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const month = String(currentMonth + 1).padStart(2, '0');
            const day   = String(d).padStart(2, '0');
            const dateStr = `${currentYear}-${month}-${day}`;

            const isPast    = dateStr < TODAY_STR;
            const isToday   = dateStr === TODAY_STR;
            const isBlk     = isBlocked(dateStr);

            const el = document.createElement('div');
            el.className = 'cal-day';
            if (isPast)  el.classList.add('cal-day-past');
            if (isToday) el.classList.add('cal-day-today');
            if (isBlk)   el.classList.add('cal-day-blocked');

            el.innerHTML = `<span>${d}</span>${isBlk ? '<div class="cal-day-lock">🔒</div>' : ''}`;

            // Click to prefill the form
            if (!isPast) {
                el.addEventListener('click', () => {
                    document.getElementById('blockDate').value = dateStr;
                    document.getElementById('blockForm').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    // Highlight the date input briefly
                    const inp = document.getElementById('blockDate');
                    inp.style.boxShadow = '0 0 0 3px rgba(99,102,241,0.3)';
                    setTimeout(() => inp.style.boxShadow = '', 1200);
                });
            }

            grid.appendChild(el);
        }
    }

    document.getElementById('prevMonth').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    });
    document.getElementById('nextMonth').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar();
    });
    document.getElementById('calBarberFilter').addEventListener('change', function() {
        selectedBarberId = this.value;
        renderCalendar();
    });

    renderCalendar();
</script>
@endsection

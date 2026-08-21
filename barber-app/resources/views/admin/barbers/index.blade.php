@extends('admin.layout')

@section('title', 'Barberos')
@section('page-title', 'Barberos')

@section('content')

<style>
    .barbers-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .barbers-header h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text);
    }
    .barbers-header p {
        font-size: 0.78rem;
        color: var(--muted);
        margin-top: 2px;
    }

    .barbers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }

    .barber-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.5rem 1.25rem 1.25rem;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .barber-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent), var(--accent2));
        opacity: 0;
        transition: opacity 0.2s;
    }
    .barber-card:hover::before { opacity: 1; }
    .barber-card:hover {
        border-color: rgba(99,102,241,0.3);
        box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }

    .barber-avatar {
        width: 56px; height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.85rem;
        flex-shrink: 0;
    }

    .barber-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.25rem;
    }

    .barber-specialties {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin: 0.65rem 0;
    }
    .specialty-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        background: var(--accent-bg);
        color: var(--accent2);
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 600;
        border: 1px solid rgba(99,102,241,0.2);
    }

    .barber-schedule {
        font-size: 0.73rem;
        color: var(--muted);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .barber-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.85rem;
        border-top: 1px solid var(--border);
        margin-top: 0.75rem;
    }

    .empty-state {
        text-align: center;
        padding: 3.5rem 1.5rem;
        color: var(--muted);
    }
    .empty-state .empty-icon {
        font-size: 3rem;
        margin-bottom: 0.75rem;
        opacity: 0.5;
    }
    .empty-state h3 { font-size: 1rem; font-weight: 600; color: var(--text2); margin-bottom: 0.4rem; }
    .empty-state p { font-size: 0.82rem; }

    @media (max-width: 600px) {
        .barbers-grid { grid-template-columns: 1fr; }
        .barbers-header { flex-wrap: wrap; gap: 0.75rem; }
    }
</style>

{{-- Page header --}}
<div class="barbers-header">
    <div>
        <h2>✂️ Barberos</h2>
        <p>{{ $barbers->count() }} barbero{{ $barbers->count() != 1 ? 's' : '' }} registrado{{ $barbers->count() != 1 ? 's' : '' }}</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-barber-new')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Agregar barbero
    </button>
</div>

{{-- Barbers grid --}}
@if($barbers->isEmpty())
    <div class="card empty-state">
        <div class="empty-icon">✂️</div>
        <h3>Sin barberos todavía</h3>
        <p>Agregá tu primer barbero para comenzar a gestionar turnos.</p>
        <button class="btn btn-primary" style="margin-top:1rem;" onclick="openModal('modal-barber-new')">+ Agregar barbero</button>
    </div>
@else
    <div class="barbers-grid">
        @php
            $avatarColors = [
                ['bg'=>'rgba(99,102,241,0.15)','text'=>'#818cf8'],
                ['bg'=>'rgba(34,197,94,0.15)','text'=>'#4ade80'],
                ['bg'=>'rgba(251,191,36,0.15)','text'=>'#fbbf24'],
                ['bg'=>'rgba(248,113,113,0.15)','text'=>'#f87171'],
                ['bg'=>'rgba(56,189,248,0.15)','text'=>'#38bdf8'],
                ['bg'=>'rgba(167,139,250,0.15)','text'=>'#a78bfa'],
            ];
        @endphp
        @foreach($barbers as $i => $b)
        @php $color = $avatarColors[$i % count($avatarColors)]; @endphp
        <div class="barber-card">
            <div style="display:flex;align-items:center;gap:0.85rem;margin-bottom:0.5rem;">
                <div class="barber-avatar" style="background:{{ $color['bg'] }};color:{{ $color['text'] }}; {{ $b->image_path ? 'padding: 0; overflow: hidden;' : '' }}">
                    @if($b->image_path)
                        <img src="{{ asset($b->image_path) }}" alt="{{ $b->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr($b->name, 0, 1)) }}
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="barber-name">{{ $b->name }}</div>
                    <span class="badge-status" style="{{ $b->is_active ? 'background:rgba(34,197,94,0.1);color:#22c55e;border:1px solid rgba(34,197,94,0.2)' : 'background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.2)' }}">
                        {{ $b->is_active ? '● Activo' : '● Inactivo' }}
                    </span>
                </div>
            </div>

            @if($b->specialties)
                <div class="barber-specialties">
                    @foreach($b->specialties as $spec)
                        <span class="specialty-tag">{{ $spec }}</span>
                    @endforeach
                </div>
            @endif

            @if($b->start_time && $b->end_time)
                <div class="barber-schedule">
                    🕐 {{ $b->start_time }} – {{ $b->end_time }}
                </div>
            @endif

            @if($b->working_days)
                <div style="font-size:0.72rem;color:var(--muted);margin-bottom:0.25rem;">
                    📆 {{ implode(', ', array_map('ucfirst', $b->working_days)) }}
                </div>
            @endif

            <div class="barber-actions">
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <button class="btn btn-ghost btn-sm" onclick="openModal('modal-barber-edit-{{ $b->id }}')">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Editar
                    </button>
                    <a href="{{ route('admin.barber.schedule', $b) }}" class="btn btn-ghost btn-sm" style="background:rgba(99,102,241,0.08);color:var(--accent2);border-color:rgba(99,102,241,0.25);">
                        🕒 Horarios
                    </a>
                </div>
                <form method="POST" action="{{ route('admin.barber.destroy', $b) }}" onsubmit="return confirm('¿Eliminar a {{ $b->name }}?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        Eliminar
                    </button>
                </form>
            </div>
        </div>

        {{-- Modal Editar Barbero --}}
        <div class="modal-overlay" id="modal-barber-edit-{{ $b->id }}">
            <div class="modal">
                <div class="modal-title">✏️ Editar — {{ $b->name }}</div>
                <form method="POST" action="{{ route('admin.barber.update', $b) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Foto del Barbero (Opcional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" class="form-control" value="{{ $b->name }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Especialidades (separadas por coma)</label>
                        <input type="text" name="specialties" class="form-control" value="{{ $b->specialties ? implode(', ', $b->specialties) : '' }}" placeholder="Fade, Corte clásico, Barba">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Hora inicio</label>
                            <input type="time" name="start_time" class="form-control" value="{{ $b->start_time }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hora fin</label>
                            <input type="time" name="end_time" class="form-control" value="{{ $b->end_time }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Inicio de descanso</label>
                            <input type="time" name="lunch_start_time" class="form-control" value="{{ $b->lunch_start_time }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fin de descanso</label>
                            <input type="time" name="lunch_end_time" class="form-control" value="{{ $b->lunch_end_time }}">
                        </div>
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                        <input type="checkbox" name="is_active" id="barber-active-edit-{{ $b->id }}" {{ $b->is_active ? 'checked' : '' }} style="width:16px;height:16px;">
                        <label for="barber-active-edit-{{ $b->id }}" class="form-label" style="margin:0;">Activo</label>
                    </div>
                    <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-barber-edit-{{ $b->id }}')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- Modal: Nuevo Barbero --}}
<div class="modal-overlay" id="modal-barber-new">
    <div class="modal">
        <div class="modal-title">✂️ Agregar Barbero</div>
        <form method="POST" action="{{ route('admin.barber.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Foto del Barbero (Opcional)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-control" required placeholder="Ej: Tomy, Marcos...">
            </div>
            <div class="form-group">
                <label class="form-label">Email (para inicio de sesión del barbero) *</label>
                <input type="email" name="email" class="form-control" required placeholder="barbero@peluqueria.com">
            </div>
            <div class="form-group">
                <label class="form-label">Especialidades (separadas por coma)</label>
                <input type="text" name="specialties" class="form-control" placeholder="Fade, Corte clásico, Barba">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Hora inicio</label>
                    <input type="time" name="start_time" class="form-control" value="09:00">
                </div>
                <div class="form-group">
                    <label class="form-label">Hora fin</label>
                    <input type="time" name="end_time" class="form-control" value="18:00">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Inicio de descanso (Opcional)</label>
                    <input type="time" name="lunch_start_time" class="form-control" value="">
                </div>
                <div class="form-group">
                    <label class="form-label">Fin de descanso (Opcional)</label>
                    <input type="time" name="lunch_end_time" class="form-control" value="">
                </div>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_active" id="barber-active-new" checked style="width:16px;height:16px;">
                <label for="barber-active-new" class="form-label" style="margin:0;">Activo</label>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-barber-new')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection

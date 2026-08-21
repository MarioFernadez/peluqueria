@extends('admin.layout')

@section('title', 'Membresías')
@section('page-title', 'Membresías')

@section('content')

<style>
    .memberships-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .memberships-header h2 { font-size: 1.1rem; font-weight: 700; color: var(--text); }
    .memberships-header p { font-size: 0.78rem; color: var(--muted); margin-top: 2px; }

    .memberships-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }

    .membership-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
    }
    .membership-card:hover {
        border-color: rgba(99,102,241,0.3);
        box-shadow: 0 6px 28px rgba(0,0,0,0.2);
        transform: translateY(-3px);
    }

    .membership-card-header {
        padding: 1.25rem 1.25rem 1rem;
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(99,102,241,0.04));
        border-bottom: 1px solid var(--border);
        position: relative;
    }

    .membership-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.4rem;
    }

    .membership-price {
        display: flex;
        align-items: baseline;
        gap: 0.2rem;
    }
    .membership-price .currency { font-size: 1rem; font-weight: 700; color: var(--accent2); }
    .membership-price .amount  { font-size: 1.8rem; font-weight: 800; color: var(--text); line-height: 1; }
    .membership-price .period  { font-size: 0.73rem; color: var(--muted); margin-left: 0.1rem; }

    .membership-badge {
        position: absolute;
        top: 1rem; right: 1rem;
        background: var(--accent-bg);
        color: var(--accent2);
        border: 1px solid rgba(99,102,241,0.25);
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
    }

    .membership-card-body {
        padding: 1rem 1.25rem;
        flex: 1;
    }

    .membership-visits {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.75rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text2);
    }
    .membership-visits .visits-count {
        background: rgba(34,197,94,0.1);
        color: var(--green);
        border-radius: 8px;
        padding: 0.2rem 0.55rem;
        font-weight: 700;
        font-size: 0.85rem;
        border: 1px solid rgba(34,197,94,0.2);
    }

    .membership-benefits {
        font-size: 0.78rem;
        color: var(--muted);
        line-height: 1.55;
    }

    .membership-card-footer {
        padding: 0.85rem 1.25rem;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .empty-state {
        text-align: center;
        padding: 3.5rem 1.5rem;
        color: var(--muted);
    }
    .empty-state .empty-icon { font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.5; }
    .empty-state h3 { font-size: 1rem; font-weight: 600; color: var(--text2); margin-bottom: 0.4rem; }

    @media (max-width: 600px) {
        .memberships-grid { grid-template-columns: 1fr; }
        .memberships-header { flex-wrap: wrap; gap: 0.75rem; }
    }
</style>

{{-- Page header --}}
<div class="memberships-header">
    <div>
        <h2>💳 Planes de Membresía</h2>
        <p>{{ $memberships->count() }} plan{{ $memberships->count() != 1 ? 'es' : '' }} registrado{{ $memberships->count() != 1 ? 's' : '' }}</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-membership-new')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nuevo plan
    </button>
</div>

@if($memberships->isEmpty())
    <div class="card empty-state">
        <div class="empty-icon">💳</div>
        <h3>Sin planes todavía</h3>
        <p>Creá tus planes de membresía para ofrecer a tus clientes.</p>
        <button class="btn btn-primary" style="margin-top:1rem;" onclick="openModal('modal-membership-new')">+ Nuevo plan</button>
    </div>
@else
    <div class="memberships-grid">
        @php
            $gradients = [
                'linear-gradient(135deg, rgba(99,102,241,0.15), rgba(99,102,241,0.05))',
                'linear-gradient(135deg, rgba(34,197,94,0.12), rgba(34,197,94,0.04))',
                'linear-gradient(135deg, rgba(251,191,36,0.15), rgba(251,191,36,0.04))',
                'linear-gradient(135deg, rgba(248,113,113,0.12), rgba(248,113,113,0.04))',
                'linear-gradient(135deg, rgba(56,189,248,0.12), rgba(56,189,248,0.04))',
            ];
        @endphp
        @foreach($memberships as $i => $m)
        <div class="membership-card">
            <div class="membership-card-header" style="background: {{ $gradients[$i % count($gradients)] }};">
                <div class="membership-badge">{{ $m->visits }} visitas</div>
                <div class="membership-name">{{ $m->name }}</div>
                <div class="membership-price">
                    <span class="currency">$</span>
                    <span class="amount">{{ number_format($m->price, 0, ',', '.') }}</span>
                    <span class="period">/ mes</span>
                </div>
            </div>
            <div class="membership-card-body">
                <div class="membership-visits">
                    <span class="visits-count">{{ $m->visits }}</span>
                    visitas incluidas
                </div>
                @if($m->benefits)
                    <div class="membership-benefits">{{ $m->benefits }}</div>
                @else
                    <div class="membership-benefits" style="font-style:italic;">Sin descripción.</div>
                @endif
            </div>
            <div class="membership-card-footer">
                <span class="badge-status" style="{{ $m->is_active ? 'background:rgba(34,197,94,0.1);color:#22c55e;border:1px solid rgba(34,197,94,0.2)' : 'background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.2)' }}">
                    {{ $m->is_active ? '● Activo' : '● Inactivo' }}
                </span>
                <div style="display:flex;gap:0.35rem;">
                    <button class="btn btn-ghost btn-sm" onclick="openModal('modal-membership-edit-{{ $m->id }}')">✏️ Editar</button>
                    <form method="POST" action="{{ route('admin.membership.destroy', $m) }}" onsubmit="return confirm('¿Eliminar el plan {{ $m->name }}?')">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">✕</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Editar --}}
        <div class="modal-overlay" id="modal-membership-edit-{{ $m->id }}">
            <div class="modal">
                <div class="modal-title">✏️ Editar — {{ $m->name }}</div>
                <form method="POST" action="{{ route('admin.membership.update', $m) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nombre del plan *</label>
                        <input type="text" name="name" class="form-control" value="{{ $m->name }}" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Precio mensual *</label>
                            <input type="number" name="price" step="0.01" class="form-control" value="{{ $m->price }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visitas incluidas *</label>
                            <input type="text" name="visits" class="form-control" value="{{ $m->visits }}" required placeholder="4, 8, ilimitadas">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Beneficios / Descripción</label>
                        <textarea name="benefits" class="form-control" rows="3">{{ $m->benefits }}</textarea>
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                        <input type="checkbox" name="is_active" id="membership-active-{{ $m->id }}" {{ $m->is_active ? 'checked' : '' }} style="width:16px;height:16px;">
                        <label for="membership-active-{{ $m->id }}" class="form-label" style="margin:0;">Activo</label>
                    </div>
                    <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-membership-edit-{{ $m->id }}')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- Modal: Nuevo Plan --}}
<div class="modal-overlay" id="modal-membership-new">
    <div class="modal">
        <div class="modal-title">💳 Nuevo Plan de Membresía</div>
        <form method="POST" action="{{ route('admin.membership.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre del plan *</label>
                <input type="text" name="name" class="form-control" placeholder="Básica, Media, Pro..." required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Precio mensual *</label>
                    <input type="number" name="price" step="0.01" class="form-control" required placeholder="10000">
                </div>
                <div class="form-group">
                    <label class="form-label">Visitas incluidas *</label>
                    <input type="text" name="visits" class="form-control" placeholder="4, 8, ilimitadas" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Beneficios / Descripción</label>
                <textarea name="benefits" class="form-control" rows="3" placeholder="Descripción de lo que incluye el plan..."></textarea>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_active" id="membership-active-new" checked style="width:16px;height:16px;">
                <label for="membership-active-new" class="form-label" style="margin:0;">Activo</label>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-membership-new')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection

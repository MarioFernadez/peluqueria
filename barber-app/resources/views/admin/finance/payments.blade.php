@extends('admin.layout')

@section('title', 'Pagos')
@section('page-title', 'Todos los Pagos')

@section('content')

{{-- Filtros --}}
<div class="card" style="margin-bottom:1.25rem;">
    <form method="GET" action="{{ route('admin.finance.payments') }}" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
        <div>
            <label class="form-label">Método</label>
            <select name="method" class="form-control">
                <option value="">Todos</option>
                <option value="efectivo"      {{ request('method')==='efectivo'      ? 'selected':'' }}>Efectivo</option>
                <option value="transferencia" {{ request('method')==='transferencia' ? 'selected':'' }}>Transferencia</option>
                <option value="tarjeta"       {{ request('method')==='tarjeta'       ? 'selected':'' }}>Tarjeta</option>
                <option value="otro"          {{ request('method')==='otro'          ? 'selected':'' }}>Otro</option>
            </select>
        </div>
        <div>
            <label class="form-label">Tipo</label>
            <select name="type" class="form-control">
                <option value="">Todos</option>
                <option value="cita"      {{ request('type')==='cita'      ? 'selected':'' }}>Citas</option>
                <option value="membresia" {{ request('type')==='membresia' ? 'selected':'' }}>Membresías</option>
            </select>
        </div>
        <div>
            <label class="form-label">Desde</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div>
            <label class="form-label">Hasta</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        @if(request()->anyFilled(['method','type','from','to']))
            <a href="{{ route('admin.finance.payments') }}" class="btn btn-ghost">✕ Limpiar</a>
        @endif
    </form>
</div>

{{-- Total filtrado --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <p style="color:var(--muted);font-size:0.85rem;margin:0;">{{ $payments->total() }} pagos encontrados</p>
    <div style="font-size:0.9rem;font-weight:700;color:var(--green);">
        Total: ${{ number_format($payments->sum('amount'), 0, ',', '.') }}
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Método</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $pay)
            <tr>
                <td style="color:var(--muted);">{{ $pay->paid_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>
                    @if($pay->client)
                        <a href="{{ route('admin.clients.show', $pay->client) }}" style="color:var(--text);font-weight:500;">
                            {{ $pay->client->name }}
                        </a>
                    @else
                        <span style="color:var(--muted);">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge-status" style="{{ $pay->type==='cita' ? 'background:rgba(124,106,255,0.1);color:var(--accent2)' : 'background:rgba(234,179,8,0.1);color:var(--yellow)' }}">
                        {{ $pay->type === 'cita' ? '📅 Cita' : '💳 Membresía' }}
                    </span>
                </td>
                <td style="color:var(--muted);font-size:0.8rem;">{{ $pay->description ?? '—' }}</td>
                <td>
                    <span style="text-transform:capitalize;font-size:0.825rem;">{{ $pay->method }}</span>
                </td>
                <td style="font-weight:700;color:var(--green);">${{ number_format($pay->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:2rem;color:var(--muted);">No hay pagos con los filtros seleccionados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($payments->hasPages())
    <div style="padding:1rem 0 0;display:flex;justify-content:center;">
        {{ $payments->links() }}
    </div>
    @endif
</div>

@endsection

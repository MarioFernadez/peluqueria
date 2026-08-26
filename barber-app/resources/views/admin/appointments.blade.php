@extends('admin.layout')

@section('title', 'Todos los Turnos')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div class="card-title" style="margin:0;">📅 Todos los Turnos</div>
        <form method="GET" action="{{ route('admin.appointments.index') }}" style="display:flex; gap:0.5rem; align-items:center;">
            <select name="barber_id" class="form-control" style="width: auto;" onchange="this.form.submit()">
                <option value="">Todos los Barberos</option>
                @foreach($barbers as $barber)
                    <option value="{{ $barber->id }}" {{ request('barber_id') == $barber->id ? 'selected' : '' }}>{{ $barber->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}" onchange="this.form.submit()" style="width: auto;">
            @if(request('date') || request('barber_id'))
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-sm" style="background:var(--bg-card); color:var(--text); border:1px solid var(--border); padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none;">Limpiar filtros</a>
            @endif
        </form>
    </div>

    @php $currentDate = null; @endphp
    @forelse($appointments as $appt)
        @php
            // Create a Spanish date string manually if Carbon locale is not guaranteed to be 'es'
            $dateObj = \Carbon\Carbon::parse($appt->appointment_date);
            $days = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
            $months = ['January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'];
            $apptDate = $days[$dateObj->englishDayOfWeek] . ', ' . $dateObj->format('d') . ' de ' . $months[$dateObj->englishMonth] . ' de ' . $dateObj->format('Y');
        @endphp

        @if($currentDate !== $apptDate)
            @if($currentDate !== null)
                </tbody></table></div>
            @endif
            <div style="margin-top: 2.5rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <h4 style="margin: 0; color: var(--gold); text-transform: capitalize; font-family: 'Outfit', sans-serif; font-size: 1.25rem;">{{ $apptDate }}</h4>
            </div>
            <div class="table-responsive" style="background: rgba(255,255,255,0.02); border-radius: 12px; padding: 1rem;">
                <table class="table">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <th style="color:var(--muted); font-size:0.75rem; letter-spacing:1px;">HORA</th>
                            <th style="color:var(--muted); font-size:0.75rem; letter-spacing:1px;">CLIENTE</th>
                            <th style="color:var(--muted); font-size:0.75rem; letter-spacing:1px;">WHATSAPP</th>
                            <th style="color:var(--muted); font-size:0.75rem; letter-spacing:1px;">BARBERO</th>
                            <th style="color:var(--muted); font-size:0.75rem; letter-spacing:1px;">SERVICIO</th>
                            <th style="color:var(--muted); font-size:0.75rem; letter-spacing:1px;">ESTADO</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
            @php $currentDate = $apptDate; @endphp
        @endif

        <tr>
            <td style="font-weight:600;color:var(--accent2);">
                {{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}
            </td>
            <td>
                @if($appt->client)
                    <a href="{{ route('admin.clients.show', $appt->client) }}" style="color:var(--text);text-decoration:none;font-weight:500;">{{ $appt->client->name }}</a>
                @else
                    <span style="color:var(--muted);">{{ $appt->customer_name }}</span>
                @endif
            </td>
            <td>
                @php $phone = $appt->client ? $appt->client->phone : $appt->customer_phone; @endphp
                @if($phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" style="color:#25D366;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        WA
                    </a>
                @else
                    <span style="color:var(--muted);font-size:0.8rem;">—</span>
                @endif
            </td>
            <td>{{ $appt->barber->name ?? '—' }}</td>
            <td>{{ $appt->service->name ?? '—' }}</td>
            <td>
                <span class="badge-status badge-{{ strtolower($appt->status) }}">{{ $appt->status }}</span>
            </td>
            <td>
                <form method="POST" action="{{ route('admin.appointment.update', $appt) }}" style="display:inline;">
                    @csrf
                    <select name="status" onchange="this.form.submit()" class="form-control" style="width:auto;display:inline-block;padding:0.25rem 1.5rem 0.25rem 0.75rem;font-size:0.85rem;">
                        @foreach(['Pendiente','Confirmada','Completada','Cancelado','No asistió'] as $st)
                            <option value="{{ $st }}" {{ $appt->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </form>
            </td>
        </tr>
    @empty
        <div class="table-responsive">
            <table class="table">
                <tbody>
                    <tr>
                        <td style="text-align:center; padding:3rem; color:var(--muted);">No hay turnos registrados{{ request('date') ? ' para esta fecha' : '' }}.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforelse

    @if($currentDate !== null)
        </tbody></table></div>
    @endif

    <div style="margin-top: 1.5rem;">
        {{ $appointments->appends(request()->query())->links() }}
    </div>
</div>
@endsection

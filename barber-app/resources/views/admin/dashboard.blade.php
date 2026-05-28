<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barbería</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen" x-data="{ tab: 'turnos', showBarberModal: false, showServiceModal: false, showMembershipModal: false }">

    <!-- Navbar -->
    <nav class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="font-bold text-xl flex items-center gap-2">
                <span>⚙️</span> Admin Panel
            </div>
            <div>
                <a href="{{ route('admin.logout') }}" class="text-sm text-gray-400 hover:text-white transition-colors">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-4 flex flex-col md:flex-row gap-6 mt-6">
        
        <!-- Sidebar -->
        <div class="w-full md:w-64 flex flex-col gap-2">
            <button @click="tab = 'turnos'" :class="tab === 'turnos' ? 'bg-blue-600/20 text-blue-400 border-blue-500/50' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-800'" class="text-left border p-3 rounded-xl transition-colors font-medium">📅 Turnos</button>
            <button @click="tab = 'caja'" :class="tab === 'caja' ? 'bg-blue-600/20 text-blue-400 border-blue-500/50' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-800'" class="text-left border p-3 rounded-xl transition-colors font-medium">💰 Caja y Reportes</button>
            <button @click="tab = 'barberos'" :class="tab === 'barberos' ? 'bg-blue-600/20 text-blue-400 border-blue-500/50' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-800'" class="text-left border p-3 rounded-xl transition-colors font-medium">👥 Barberos</button>
            <button @click="tab = 'servicios'" :class="tab === 'servicios' ? 'bg-blue-600/20 text-blue-400 border-blue-500/50' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-800'" class="text-left border p-3 rounded-xl transition-colors font-medium">✂️ Servicios</button>
            <button @click="tab = 'mensualidades'" :class="tab === 'mensualidades' ? 'bg-blue-600/20 text-blue-400 border-blue-500/50' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-800'" class="text-left border p-3 rounded-xl transition-colors font-medium">💳 Mensualidades</button>
        </div>

        <!-- Content -->
        <div class="flex-1">

            @if($errors->any())
                <div class="bg-red-500/20 border border-red-500/50 text-red-400 p-4 rounded-xl mb-4">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <!-- Turnos Tab -->
            <div x-show="tab === 'turnos'" class="space-y-4">
                <h2 class="text-2xl font-bold mb-4">📅 Turnos Recientes</h2>
                <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-800 text-gray-400">
                            <tr>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Barbero</th>
                                <th class="p-4">Servicio</th>
                                <th class="p-4">Fecha/Hora</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @forelse($appointments as $appt)
                            <tr class="hover:bg-gray-800/50 transition-colors">
                                <td class="p-4 font-medium">{{ $appt->customer_name }}</td>
                                <td class="p-4 text-gray-400">{{ $appt->barber->name ?? 'N/A' }}</td>
                                <td class="p-4 text-gray-400">{{ $appt->service->name ?? 'N/A' }}</td>
                                <td class="p-4 text-gray-400">{{ $appt->appointment_date->format('d/m/Y') }} {{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $appt->status === 'Completado' ? 'bg-green-500/20 text-green-400' : ($appt->status === 'Cancelado' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400') }}">
                                        {{ $appt->status }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <form action="{{ route('admin.appointment.update', $appt->id) }}" method="POST" class="inline">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="bg-gray-950 border border-gray-700 rounded p-1 text-xs text-white">
                                            <option value="Pendiente" {{ $appt->status == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="Completado" {{ $appt->status == 'Completado' ? 'selected' : '' }}>Completado</option>
                                            <option value="Cancelado" {{ $appt->status == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">No hay turnos registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Caja Tab -->
            <div x-show="tab === 'caja'" class="space-y-4" style="display: none;">
                <h2 class="text-2xl font-bold mb-4">💰 Resumen de Caja</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                        <p class="text-gray-400 text-sm">Ingresos Estimados (Total Turnos)</p>
                        <h3 class="text-3xl font-bold text-green-400 mt-2">${{ number_format($totalCaja, 2) }}</h3>
                    </div>
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                        <p class="text-gray-400 text-sm">Total Turnos</p>
                        <h3 class="text-3xl font-bold text-blue-400 mt-2">{{ $totalTurnos }}</h3>
                    </div>
                </div>
            </div>

            <!-- Barberos Tab -->
            <div x-show="tab === 'barberos'" class="space-y-4" style="display: none;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">👥 Barberos</h2>
                    <button @click="showBarberModal = true" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-xl text-sm transition-colors">+ Agregar Barbero</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($barbers as $barber)
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold">{{ $barber->name }}</h3>
                            <p class="text-sm text-gray-400 mt-1">Especialidades: {{ implode(', ', $barber->specialties ?? []) }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $barber->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                {{ $barber->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                            <form action="{{ route('admin.barber.destroy', $barber->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este barbero?');">
                                @csrf
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Servicios Tab -->
            <div x-show="tab === 'servicios'" class="space-y-4" style="display: none;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">✂️ Servicios</h2>
                    <button @click="showServiceModal = true" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-xl text-sm transition-colors">+ Agregar Servicio</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($services as $service)
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold">{{ $service->name }}</h3>
                            <p class="text-sm text-gray-400 mt-1 uppercase tracking-wider">{{ $service->category }} &bull; {{ $service->duration_min }} min</p>
                        </div>
                        <div class="text-right flex flex-col items-end gap-2">
                            <span class="text-yellow-500 font-bold block">${{ number_format($service->price, 2) }}</span>
                            <form action="{{ route('admin.service.destroy', $service->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este servicio?');">
                                @csrf
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Mensualidades Tab -->
            <div x-show="tab === 'mensualidades'" class="space-y-4" style="display: none;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">💳 Planes de Mensualidad</h2>
                    <button @click="showMembershipModal = true" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-xl text-sm transition-colors">+ Crear Plan</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($memberships as $membership)
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 relative">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold">{{ $membership->name }}</h3>
                            <span class="text-yellow-500 font-bold">${{ number_format($membership->price, 2) }}/mes</span>
                        </div>
                        <p class="text-sm text-gray-400">Visitas: {{ $membership->visits }}</p>
                        <p class="text-sm text-gray-400 mt-2">Beneficios: {{ $membership->benefits ?? 'Ninguno' }}</p>
                        
                        <div class="mt-4 flex justify-between items-end">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $membership->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                {{ $membership->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                            <form action="{{ route('admin.membership.destroy', $membership->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este plan?');">
                                @csrf
                                <button type="submit" class="text-xs text-red-500 hover:text-red-400">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Barber -->
    <div x-show="showBarberModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">Agregar Barbero</h3>
            <form action="{{ route('admin.barber.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nombre</label>
                    <input type="text" name="name" required class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Especialidades (separadas por coma)</label>
                    <input type="text" name="specialties" placeholder="Corte, Barba" class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" checked class="w-4 h-4 rounded bg-gray-950 border-gray-700 text-blue-600 focus:ring-blue-600 focus:ring-offset-gray-900">
                    <label class="text-sm text-gray-400">Activo</label>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="showBarberModal = false" class="px-4 py-2 rounded-xl text-gray-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-xl transition-colors">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Service -->
    <div x-show="showServiceModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">Agregar Servicio</h3>
            <form action="{{ route('admin.service.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nombre del Servicio</label>
                    <input type="text" name="name" required class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Categoría</label>
                    <input type="text" name="category" required placeholder="Ej: Corte, Barba, Combo" class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Precio ($)</label>
                        <input type="number" step="0.01" name="price" required class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Duración (min)</label>
                        <input type="number" name="duration_min" required class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" checked class="w-4 h-4 rounded bg-gray-950 border-gray-700 text-blue-600 focus:ring-blue-600 focus:ring-offset-gray-900">
                    <label class="text-sm text-gray-400">Activo</label>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="showServiceModal = false" class="px-4 py-2 rounded-xl text-gray-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-xl transition-colors">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Membership -->
    <div x-show="showMembershipModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4">Crear Plan de Mensualidad</h3>
            <form action="{{ route('admin.membership.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nombre del Plan</label>
                    <input type="text" name="name" required class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Precio ($)</label>
                        <input type="number" step="0.01" name="price" required class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Visitas (ej. "4" o "ilimitadas")</label>
                        <input type="text" name="visits" required class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Beneficios extra</label>
                    <input type="text" name="benefits" placeholder="Prioridad, descuentos..." class="w-full bg-gray-950 border border-gray-700 rounded-xl px-4 py-2 text-white">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" checked class="w-4 h-4 rounded bg-gray-950 border-gray-700 text-blue-600 focus:ring-blue-600 focus:ring-offset-gray-900">
                    <label class="text-sm text-gray-400">Activo</label>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="showMembershipModal = false" class="px-4 py-2 rounded-xl text-gray-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-xl transition-colors">Guardar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

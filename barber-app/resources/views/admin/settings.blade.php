@extends('admin.layout')

@section('title', 'Apariencia de la Web')
@section('page-title', 'Apariencia')

@section('content')

<style>
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }
    @media (max-width: 768px) {
        .settings-grid { grid-template-columns: 1fr !important; }
        .gallery-grid { grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media (max-width: 400px) {
        .gallery-grid { grid-template-columns: 1fr !important; }
    }
</style>

<div class="settings-grid">

    {{-- Hero Settings --}}
    <div class="card">
        <div class="card-title">🌟 Sección Principal (Hero)</div>
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Título principal</label>
                <input type="text" name="hero_title" class="form-control" value="{{ $settings['hero_title'] ?? 'Athenea Barber' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Subtítulo (Ubicación/Rating)</label>
                <input type="text" name="hero_subtitle" class="form-control" value="{{ $settings['hero_subtitle'] ?? '📍 Encarnación, Paraguay' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Dirección (Etiqueta superior)</label>
                <input type="text" name="hero_address_badge" class="form-control" value="{{ $settings['hero_address_badge'] ?? '14 de enero calle Gral. Artigas y Juan L. Mallorquín' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Logo de la Barbería</label>
                @if(isset($settings['logo_image']))
                    <div style="margin-bottom: 0.5rem; background: var(--bg-card); padding: 0.5rem; display: inline-block; border-radius: 8px; border: 1px solid var(--border);">
                        <img src="{{ asset($settings['logo_image']) }}" alt="Logo" style="height: 40px; object-fit: contain;">
                    </div>
                @endif
                <input type="file" name="logo_image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label class="form-label">Imagen de fondo</label>
                @if(isset($settings['hero_bg_image']))
                    <div style="margin-bottom: 0.5rem;">
                        <img src="{{ asset($settings['hero_bg_image']) }}" alt="Fondo" style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px;">
                    </div>
                @endif
                <input type="file" name="hero_bg_image" class="form-control">
            </div>
            <button class="btn btn-primary">Guardar Hero</button>
        </form>
    </div>

    {{-- Contact Info Settings --}}
    <div class="card">
        <div class="card-title">📍 Contacto y Ubicación</div>
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Número de WhatsApp (con código de país, ej. 595...)</label>
                <input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['whatsapp_number'] ?? '595000000000' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Mensaje Predefinido de WhatsApp</label>
                <input type="text" name="whatsapp_message" class="form-control" value="{{ $settings['whatsapp_message'] ?? 'Hola! Quiero reservar un turno en ' . ($settings['hero_title'] ?? 'Athenea Barber') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Dirección Completa (Sección Encuéntranos)</label>
                <textarea name="contact_address" class="form-control" rows="2">{{ $settings['contact_address'] ?? '14 de enero calle Gral. Artigas y Juan L. Mallorquín, Encarnación, Paraguay' }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">URL del mapa (Google Maps iframe src)</label>
                <textarea name="map_url" class="form-control" rows="3">{{ $settings['map_url'] ?? 'https://www.google.com/maps/embed?pb=...' }}</textarea>
            </div>
            <button class="btn btn-primary">Guardar Contacto</button>
        </form>
    </div>

</div>

<div class="card" style="margin-top: 1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div class="card-title" style="margin:0;">📸 Galería de Trabajos</div>
        <button class="btn btn-primary btn-sm" onclick="openModal('modal-gallery-work')">+ Añadir Trabajo</button>
    </div>

    @if($galleryWorks->isEmpty())
        <div style="text-align:center; padding: 2rem; color: var(--muted);">No hay trabajos en la galería.</div>
    @else
        <div class="gallery-grid">
            @foreach($galleryWorks as $work)
                <div style="border: 1px solid var(--border); border-radius: 8px; overflow: hidden; position: relative;">
                    <img src="{{ asset($work->image_path) }}" alt="{{ $work->title }}" style="width: 100%; height: 180px; object-fit: cover;">
                    <div style="padding: 0.5rem; background: var(--surface);">
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ $work->title }}</div>
                        <div style="font-size: 0.75rem; color: var(--muted); margin-bottom: 0.5rem;">{{ Str::limit($work->subtitle, 40) }}</div>
                        <form method="POST" action="{{ route('admin.settings.gallery.destroy', $work->id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este trabajo?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; justify-content: center;">Eliminar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Modal: Add Gallery Work --}}
<div class="modal-overlay" id="modal-gallery-work">
    <div class="modal">
        <div class="modal-title">Añadir a Galería</div>
        <form method="POST" action="{{ route('admin.settings.gallery.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Título (Ej: Mullet) *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea name="subtitle" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Etiqueta (Badge superior izquierda)</label>
                <input type="text" name="badge" class="form-control" placeholder="Ej: Mullet">
            </div>
            <div class="form-group">
                <label class="form-label">Imagen *</label>
                <input type="file" name="image" class="form-control" required>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_active" id="work-active" checked style="width:16px;height:16px;">
                <label for="work-active" class="form-label" style="margin:0;">Activo</label>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modal-gallery-work')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\GalleryWork;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $galleryWorks = GalleryWork::orderBy('order')->get();
        return view('admin.settings', compact('settings', 'galleryWorks'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'hero_bg_image', 'logo_image']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        if ($request->hasFile('hero_bg_image')) {
            $path = $request->file('hero_bg_image')->store('images', 'public');
            Setting::set('hero_bg_image', 'storage/' . $path);
        }

        if ($request->hasFile('logo_image')) {
            $path = $request->file('logo_image')->store('images', 'public');
            Setting::set('logo_image', 'storage/' . $path);
        }

        return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
    }

    public function storeGallery(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:60000',
            'badge' => 'nullable|string|max:255',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('images/gallery', 'public');
        $imagePath = 'storage/' . $path;

        GalleryWork::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'badge' => $request->badge,
            'image_path' => $imagePath,
            'is_active' => $request->has('is_active'),
            'order' => GalleryWork::max('order') + 1,
        ]);

        return redirect()->back()->with('success', 'Trabajo añadido a la galería.');
    }
    public function updateGallery(Request $request, $id)
    {
        $work = GalleryWork::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:60000',
            'badge' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $work->title = $request->title;
        $work->subtitle = $request->subtitle;
        $work->badge = $request->badge;
        $work->is_active = $request->has('is_active');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/gallery', 'public');
            $work->image_path = 'storage/' . $path;
        }

        $work->save();

        return redirect()->back()->with('success', 'Trabajo actualizado correctamente.');
    }

    public function destroyGallery($id)
    {
        $work = GalleryWork::findOrFail($id);
        $work->delete();
        return redirect()->back()->with('success', 'Trabajo eliminado de la galería.');
    }
}

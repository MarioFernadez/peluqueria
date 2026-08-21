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
        $data = $request->except(['_token', 'hero_bg_image']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        if ($request->hasFile('hero_bg_image')) {
            $path = $request->file('hero_bg_image')->store('public/images');
            Setting::set('hero_bg_image', str_replace('public/', 'storage/', $path));
        }

        return redirect()->back()->with('success', 'Configuración actualizada correctamente.');
    }

    public function storeGallery(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('public/images/gallery');
        $imagePath = str_replace('public/', 'storage/', $path);

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

    public function destroyGallery($id)
    {
        $work = GalleryWork::findOrFail($id);
        $work->delete();
        return redirect()->back()->with('success', 'Trabajo eliminado de la galería.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador
        \App\Models\User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@peluqueria.com',
            'password' => \Illuminate\Support\Facades\Hash::make('PASSWORD2026'),
        ]);
        // Barberos
        \App\Models\Barber::create([
            'name' => 'Tomy',
            'specialties' => ['Corte', 'Barba', 'Degradé'],
            'working_days' => ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'],
            'start_time' => '09:00',
            'end_time' => '18:00',
            'is_active' => true,
        ]);

        \App\Models\Barber::create([
            'name' => 'Marcos',
            'specialties' => ['Coloración', 'Keratina', 'Corte'],
            'working_days' => ['martes', 'miércoles', 'jueves', 'viernes', 'sábado'],
            'start_time' => '10:00',
            'end_time' => '19:00',
            'is_active' => true,
        ]);

        // Servicios
        \App\Models\Service::create([
            'name' => 'Corte Clásico',
            'category' => 'Corte',
            'price' => 5000.00,
            'duration_min' => 30,
            'is_active' => true,
        ]);

        \App\Models\Service::create([
            'name' => 'Corte + Barba',
            'category' => 'Combo',
            'price' => 8000.00,
            'duration_min' => 50,
            'is_active' => true,
        ]);

        \App\Models\Service::create([
            'name' => 'Arreglo de barba',
            'category' => 'Barba',
            'price' => 3000.00,
            'duration_min' => 20,
            'is_active' => true,
        ]);
    }
}

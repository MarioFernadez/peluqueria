<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('appointments:conflicts {--cancel-duplicates : Cancela automáticamente el turno más reciente en pares duplicados}', function () {
    $this->info("Buscando turnos superpuestos o duplicados en la base de datos...");
    
    $appointments = \App\Models\Appointment::with(['barber', 'service'])
        ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['cancelado', 'cancelada'])
        ->orderBy('appointment_date')
        ->orderBy('appointment_time')
        ->get();

    $conflicts = [];
    $seenPairs = [];

    for ($i = 0; $i < $appointments->count(); $i++) {
        for ($j = $i + 1; $j < $appointments->count(); $j++) {
            $a = $appointments[$i];
            $b = $appointments[$j];

            if ($a->barber_id !== $b->barber_id) continue;
            
            $dateA = $a->appointment_date instanceof \DateTimeInterface ? $a->appointment_date->format('Y-m-d') : (string) $a->appointment_date;
            $dateB = $b->appointment_date instanceof \DateTimeInterface ? $b->appointment_date->format('Y-m-d') : (string) $b->appointment_date;

            if ($dateA !== $dateB) continue;

            $startA = \Carbon\Carbon::parse($dateA . ' ' . $a->appointment_time);
            $endA   = $startA->copy()->addMinutes(max(15, (int) ($a->duration_min ?: 30)));

            $startB = \Carbon\Carbon::parse($dateB . ' ' . $b->appointment_time);
            $endB   = $startB->copy()->addMinutes(max(15, (int) ($b->duration_min ?: 30)));

            if ($startA->lt($endB) && $endA->gt($startB)) {
                $pairKey = min($a->id, $b->id) . '-' . max($a->id, $b->id);
                if (!isset($seenPairs[$pairKey])) {
                    $seenPairs[$pairKey] = true;
                    $conflicts[] = ['a' => $a, 'b' => $b, 'date' => $dateA];
                }
            }
        }
    }

    if (empty($conflicts)) {
        $this->info("✅ ¡Excelente! No se encontraron turnos superpuestos ni duplicados.");
        return 0;
    }

    $this->warn("⚠️ Se encontraron " . count($conflicts) . " conflictos de horarios superpuestos:");

    $rows = [];
    foreach ($conflicts as $idx => $c) {
        $a = $c['a'];
        $b = $c['b'];
        $rows[] = [
            'Conflicto #' . ($idx + 1),
            $c['date'],
            $a->barber->name ?? ('Barbero #' . $a->barber_id),
            "ID {$a->id}: {$a->customer_name} ({$a->appointment_time}, {$a->duration_min}m)",
            "ID {$b->id}: {$b->customer_name} ({$b->appointment_time}, {$b->duration_min}m)",
        ];

        if ($this->option('cancel-duplicates')) {
            $newer = $a->created_at > $b->created_at ? $a : $b;
            $newer->update(['status' => 'Cancelado']);
            $this->comment(" -> Turno ID {$newer->id} cancelado automáticamente.");
        }
    }

    $this->table(['#', 'Fecha', 'Barbero', 'Turno A', 'Turno B'], $rows);

    if (!$this->option('cancel-duplicates')) {
        $this->comment("Podes resolver los conflictos desde el panel de administración o ejecutar:");
        $this->comment("php artisan appointments:conflicts --cancel-duplicates");
    }

    return 1;
})->purpose('Detecta turnos superpuestos o duplicados en el mismo barbero y fecha');

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable(); // Para bloqueo de día puntual
            $table->tinyInteger('day_of_week')->nullable(); // 0=Dom, 1=Lun...6=Sáb
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_blocked')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_schedules');
    }
};

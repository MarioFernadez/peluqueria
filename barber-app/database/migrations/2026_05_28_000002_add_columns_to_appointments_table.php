<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->string('payment_method')->default('efectivo')->after('status'); // efectivo, transferencia, tarjeta, otro
            $table->string('payment_status')->default('pendiente')->after('payment_method'); // pendiente, pagado
            $table->text('notes')->nullable()->after('duration_min');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'payment_method', 'payment_status', 'notes']);
        });
    }
};

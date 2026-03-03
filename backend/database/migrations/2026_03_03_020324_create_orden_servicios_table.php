<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->foreignId('mecanico_principal_id')->nullable()->constrained('mecanicos')->nullOnDelete();
            $table->string('tipo_servicio', 50);
            $table->longText('descripcion')->nullable();
            $table->decimal('monto', 10, 2)->default(0);
            $table->string('forma_pago', 30)->nullable();
            $table->string('tipo_pago', 30)->default('unico');
            $table->string('estado', 40)->default('recibido');
            $table->date('fecha_ingreso');
            $table->date('fecha_estimada_terminacion')->nullable();
            $table->date('fecha_entrega_real')->nullable();
            $table->text('motivo_retraso')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_servicios');
    }
};

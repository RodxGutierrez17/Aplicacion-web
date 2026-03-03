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
        Schema::create('actualizaciones_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_servicio_id')->constrained('orden_servicios')->cascadeOnDelete();
            $table->foreignId('mecanico_id')->nullable()->constrained('mecanicos')->nullOnDelete();
            $table->string('estado', 40);
            $table->text('mensaje')->nullable();
            $table->string('imagen_url')->nullable();
            $table->boolean('notificar_cliente')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualizaciones_orden');
    }
};

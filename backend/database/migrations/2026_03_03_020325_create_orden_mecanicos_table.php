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
        Schema::create('orden_mecanicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_servicio_id')->constrained('orden_servicios')->cascadeOnDelete();
            $table->foreignId('mecanico_id')->constrained('mecanicos')->cascadeOnDelete();
            $table->string('rol', 20)->default('auxiliar');
            $table->timestamps();

            $table->unique(['orden_servicio_id', 'mecanico_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_mecanicos');
    }
};

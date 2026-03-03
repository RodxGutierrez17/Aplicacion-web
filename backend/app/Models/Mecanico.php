<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mecanico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'cedula',
        'telefono',
        'especialidad',
        'activo',
    ];

    public function ordenesPrincipal(): HasMany
    {
        return $this->hasMany(OrdenServicio::class, 'mecanico_principal_id');
    }

    public function ordenesAsignadas(): BelongsToMany
    {
        return $this->belongsToMany(OrdenServicio::class, 'orden_mecanicos')
            ->withPivot('rol')
            ->withTimestamps();
    }

    public function actualizaciones(): HasMany
    {
        return $this->hasMany(ActualizacionOrden::class);
    }
}

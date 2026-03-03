<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenServicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'cliente_id',
        'vehiculo_id',
        'mecanico_principal_id',
        'tipo_servicio',
        'descripcion',
        'monto',
        'forma_pago',
        'tipo_pago',
        'estado',
        'fecha_ingreso',
        'fecha_estimada_terminacion',
        'fecha_entrega_real',
        'motivo_retraso',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function mecanicoPrincipal(): BelongsTo
    {
        return $this->belongsTo(Mecanico::class, 'mecanico_principal_id');
    }

    public function mecanicos(): BelongsToMany
    {
        return $this->belongsToMany(Mecanico::class, 'orden_mecanicos')
            ->withPivot('rol')
            ->withTimestamps();
    }

    public function actualizaciones(): HasMany
    {
        return $this->hasMany(ActualizacionOrden::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }
}

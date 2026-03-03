<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActualizacionOrden extends Model
{
    use HasFactory;

    protected $table = 'actualizaciones_orden';

    protected $fillable = [
        'orden_servicio_id',
        'mecanico_id',
        'estado',
        'mensaje',
        'imagen_url',
        'notificar_cliente',
    ];

    public function ordenServicio(): BelongsTo
    {
        return $this->belongsTo(OrdenServicio::class);
    }

    public function mecanico(): BelongsTo
    {
        return $this->belongsTo(Mecanico::class);
    }
}

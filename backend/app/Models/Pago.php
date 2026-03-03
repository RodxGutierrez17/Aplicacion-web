<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'orden_servicio_id',
        'monto',
        'metodo',
        'tipo_pago',
        'referencia',
        'pagado_en',
        'recibido_por',
    ];

    protected $casts = [
        'pagado_en' => 'datetime',
        'monto' => 'decimal:2',
    ];

    public function ordenServicio(): BelongsTo
    {
        return $this->belongsTo(OrdenServicio::class);
    }
}

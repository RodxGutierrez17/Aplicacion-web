<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActualizacionOrden;
use App\Models\OrdenServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenServicioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OrdenServicio::query()
            ->with(['cliente', 'vehiculo', 'mecanicoPrincipal', 'mecanicos'])
            ->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->integer('cliente_id'));
        }

        if ($request->filled('vehiculo_id')) {
            $query->where('vehiculo_id', $request->integer('vehiculo_id'));
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'vehiculo_id' => ['required', 'integer', 'exists:vehiculos,id'],
            'mecanico_principal_id' => ['nullable', 'integer', 'exists:mecanicos,id'],
            'tipo_servicio' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'monto' => ['required', 'numeric', 'min:0'],
            'forma_pago' => ['nullable', 'string', 'max:30'],
            'tipo_pago' => ['required', 'in:unico,cuotas'],
            'estado' => ['nullable', 'string', 'max:40'],
            'fecha_ingreso' => ['nullable', 'date'],
            'fecha_estimada_terminacion' => ['nullable', 'date'],
            'mecanicos_auxiliares' => ['nullable', 'array'],
            'mecanicos_auxiliares.*' => ['integer', 'exists:mecanicos,id'],
        ]);

        $orden = DB::transaction(function () use ($validated) {
            $vehiculoClienteId = (int) DB::table('vehiculos')
                ->where('id', $validated['vehiculo_id'])
                ->value('cliente_id');

            if ($vehiculoClienteId !== (int) $validated['cliente_id']) {
                abort(422, 'El vehiculo no pertenece al cliente seleccionado.');
            }

            $orden = OrdenServicio::create([
                'codigo' => $this->generarCodigo(),
                'cliente_id' => $validated['cliente_id'],
                'vehiculo_id' => $validated['vehiculo_id'],
                'mecanico_principal_id' => $validated['mecanico_principal_id'] ?? null,
                'tipo_servicio' => $validated['tipo_servicio'],
                'descripcion' => $validated['descripcion'] ?? null,
                'monto' => $validated['monto'],
                'forma_pago' => $validated['forma_pago'] ?? null,
                'tipo_pago' => $validated['tipo_pago'],
                'estado' => $validated['estado'] ?? 'recibido',
                'fecha_ingreso' => $validated['fecha_ingreso'] ?? now()->toDateString(),
                'fecha_estimada_terminacion' => $validated['fecha_estimada_terminacion'] ?? null,
            ]);

            $syncData = [];

            if (! empty($validated['mecanico_principal_id'])) {
                $syncData[$validated['mecanico_principal_id']] = ['rol' => 'principal'];
            }

            foreach ($validated['mecanicos_auxiliares'] ?? [] as $mecanicoId) {
                $syncData[$mecanicoId] = ['rol' => 'auxiliar'];
            }

            if (! empty($syncData)) {
                $orden->mecanicos()->sync($syncData);
            }

            ActualizacionOrden::create([
                'orden_servicio_id' => $orden->id,
                'mecanico_id' => $validated['mecanico_principal_id'] ?? null,
                'estado' => $orden->estado,
                'mensaje' => 'Orden creada y recibida en taller.',
                'notificar_cliente' => true,
            ]);

            return $orden;
        });

        return response()->json(
            $orden->load(['cliente', 'vehiculo', 'mecanicoPrincipal', 'mecanicos', 'actualizaciones']),
            201
        );
    }

    public function show(OrdenServicio $ordenServicio): JsonResponse
    {
        $ordenServicio->load([
            'cliente',
            'vehiculo',
            'mecanicoPrincipal',
            'mecanicos',
            'actualizaciones' => fn ($q) => $q->latest(),
            'pagos',
        ]);

        return response()->json($ordenServicio);
    }

    public function update(Request $request, OrdenServicio $ordenServicio): JsonResponse
    {
        $validated = $request->validate([
            'mecanico_principal_id' => ['nullable', 'integer', 'exists:mecanicos,id'],
            'tipo_servicio' => ['sometimes', 'required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'monto' => ['sometimes', 'required', 'numeric', 'min:0'],
            'forma_pago' => ['nullable', 'string', 'max:30'],
            'tipo_pago' => ['sometimes', 'required', 'in:unico,cuotas'],
            'fecha_estimada_terminacion' => ['nullable', 'date'],
            'fecha_entrega_real' => ['nullable', 'date'],
            'motivo_retraso' => ['nullable', 'string'],
            'mecanicos_auxiliares' => ['nullable', 'array'],
            'mecanicos_auxiliares.*' => ['integer', 'exists:mecanicos,id'],
        ]);

        DB::transaction(function () use ($validated, $ordenServicio) {
            $ordenServicio->update($validated);

            if (array_key_exists('mecanicos_auxiliares', $validated) || array_key_exists('mecanico_principal_id', $validated)) {
                $syncData = [];

                if (! empty($validated['mecanico_principal_id'])) {
                    $syncData[$validated['mecanico_principal_id']] = ['rol' => 'principal'];
                } elseif (! empty($ordenServicio->mecanico_principal_id)) {
                    $syncData[$ordenServicio->mecanico_principal_id] = ['rol' => 'principal'];
                }

                foreach ($validated['mecanicos_auxiliares'] ?? [] as $mecanicoId) {
                    $syncData[$mecanicoId] = ['rol' => 'auxiliar'];
                }

                $ordenServicio->mecanicos()->sync($syncData);
            }
        });

        return response()->json($ordenServicio->fresh()->load(['mecanicoPrincipal', 'mecanicos']));
    }

    public function destroy(OrdenServicio $ordenServicio): JsonResponse
    {
        $ordenServicio->delete();

        return response()->json([], 204);
    }

    public function actualizarEstado(Request $request, OrdenServicio $ordenServicio): JsonResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'string', 'max:40'],
            'mensaje' => ['nullable', 'string'],
            'mecanico_id' => ['nullable', 'integer', 'exists:mecanicos,id'],
            'imagen_url' => ['nullable', 'url', 'max:255'],
            'notificar_cliente' => ['sometimes', 'boolean'],
            'fecha_estimada_terminacion' => ['nullable', 'date'],
            'motivo_retraso' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $ordenServicio) {
            $ordenServicio->update([
                'estado' => $validated['estado'],
                'fecha_estimada_terminacion' => $validated['fecha_estimada_terminacion'] ?? $ordenServicio->fecha_estimada_terminacion,
                'motivo_retraso' => $validated['motivo_retraso'] ?? $ordenServicio->motivo_retraso,
                'fecha_entrega_real' => $validated['estado'] === 'entregado' ? now()->toDateString() : $ordenServicio->fecha_entrega_real,
            ]);

            ActualizacionOrden::create([
                'orden_servicio_id' => $ordenServicio->id,
                'mecanico_id' => $validated['mecanico_id'] ?? null,
                'estado' => $validated['estado'],
                'mensaje' => $validated['mensaje'] ?? null,
                'imagen_url' => $validated['imagen_url'] ?? null,
                'notificar_cliente' => $validated['notificar_cliente'] ?? true,
            ]);
        });

        return response()->json($ordenServicio->fresh()->load('actualizaciones'));
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = 'OS-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (OrdenServicio::where('codigo', $codigo)->exists());

        return $codigo;
    }
}

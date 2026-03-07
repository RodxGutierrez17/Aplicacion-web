<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActualizacionOrden;
use App\Models\Cliente;
use App\Models\OrdenServicio;
use App\Models\Vehiculo;
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
            'cliente_id' => ['nullable', 'integer'],
            'nuevo_cliente' => ['nullable', 'array'],
            'nuevo_cliente.nombre' => ['required_without:cliente_id', 'string', 'max:255'],
            'nuevo_cliente.cedula' => ['required_without:cliente_id', 'string', 'max:20', 'unique:clientes,cedula'],
            'nuevo_cliente.sexo' => ['nullable', 'string', 'max:20'],
            'nuevo_cliente.direccion' => ['required_without:cliente_id', 'string', 'max:255'],
            'nuevo_cliente.telefono' => ['required_without:cliente_id', 'string', 'max:20'],
            'nuevo_cliente.email' => ['nullable', 'email', 'max:255'],
            'vehiculo_id' => ['nullable', 'integer'],
            'nuevo_vehiculo' => ['nullable', 'array'],
            'nuevo_vehiculo.anio' => ['required_without:vehiculo_id', 'integer', 'between:1900,2100'],
            'nuevo_vehiculo.marca' => ['required_without:vehiculo_id', 'string', 'max:100'],
            'nuevo_vehiculo.modelo' => ['required_without:vehiculo_id', 'string', 'max:100'],
            'nuevo_vehiculo.matricula' => ['required_without:vehiculo_id', 'string', 'max:30', 'unique:vehiculos,matricula'],
            'nuevo_vehiculo.color' => ['required_without:vehiculo_id', 'string', 'max:50'],
            'nuevo_vehiculo.combustible' => ['nullable', 'string', 'max:30'],
            'nuevo_vehiculo.chasis' => ['nullable', 'string', 'max:60'],
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
            $clienteId = null;
            if (! empty($validated['cliente_id'])) {
                $clienteId = Cliente::where('id', (int) $validated['cliente_id'])->value('id');
            }

            if (! $clienteId) {
                if (empty($validated['nuevo_cliente'])) {
                    abort(422, 'El ID de cliente no existe. Completa "nuevo cliente" para crearlo.');
                }
                $cliente = Cliente::create($validated['nuevo_cliente']);
                $clienteId = (int) $cliente->id;
            }

            $vehiculoId = null;
            if (! empty($validated['vehiculo_id'])) {
                $vehiculoId = Vehiculo::where('id', (int) $validated['vehiculo_id'])->value('id');
            }

            if ($vehiculoId) {
                $vehiculoClienteId = (int) DB::table('vehiculos')
                    ->where('id', $vehiculoId)
                    ->value('cliente_id');

                if ($vehiculoClienteId !== (int) $clienteId) {
                    abort(422, 'El vehiculo no pertenece al cliente seleccionado.');
                }
            } else {
                if (empty($validated['nuevo_vehiculo'])) {
                    abort(422, 'El ID de vehiculo no existe. Completa "nuevo vehiculo" para crearlo.');
                }
                $vehiculo = Vehiculo::create([
                    ...$validated['nuevo_vehiculo'],
                    'cliente_id' => $clienteId,
                ]);
                $vehiculoId = $vehiculo->id;
            }

            $orden = OrdenServicio::create([
                'codigo' => $this->generarCodigo(),
                'cliente_id' => $clienteId,
                'vehiculo_id' => $vehiculoId,
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vehiculo::query()->with('cliente')->latest();

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->integer('cliente_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('matricula', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'anio' => ['required', 'integer', 'between:1900,2100'],
            'marca' => ['required', 'string', 'max:100'],
            'modelo' => ['required', 'string', 'max:100'],
            'matricula' => ['required', 'string', 'max:30', 'unique:vehiculos,matricula'],
            'color' => ['required', 'string', 'max:50'],
            'combustible' => ['nullable', 'string', 'max:30'],
            'chasis' => ['nullable', 'string', 'max:60'],
        ]);

        $vehiculo = Vehiculo::create($validated)->load('cliente');

        return response()->json($vehiculo, 201);
    }

    public function show(Vehiculo $vehiculo): JsonResponse
    {
        $vehiculo->load(['cliente', 'ordenesServicio']);

        return response()->json($vehiculo);
    }

    public function update(Request $request, Vehiculo $vehiculo): JsonResponse
    {
        $validated = $request->validate([
            'cliente_id' => ['sometimes', 'required', 'integer', 'exists:clientes,id'],
            'anio' => ['sometimes', 'required', 'integer', 'between:1900,2100'],
            'marca' => ['sometimes', 'required', 'string', 'max:100'],
            'modelo' => ['sometimes', 'required', 'string', 'max:100'],
            'matricula' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('vehiculos', 'matricula')->ignore($vehiculo->id)],
            'color' => ['sometimes', 'required', 'string', 'max:50'],
            'combustible' => ['nullable', 'string', 'max:30'],
            'chasis' => ['nullable', 'string', 'max:60'],
        ]);

        $vehiculo->update($validated);

        return response()->json($vehiculo->fresh()->load('cliente'));
    }

    public function destroy(Vehiculo $vehiculo): JsonResponse
    {
        $vehiculo->delete();

        return response()->json([], 204);
    }
}

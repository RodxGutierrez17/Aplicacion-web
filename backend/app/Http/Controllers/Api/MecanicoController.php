<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mecanico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MecanicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Mecanico::query()->latest();

        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('nombre', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:20', 'unique:mecanicos,cedula'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $mecanico = Mecanico::create($validated);

        return response()->json($mecanico, 201);
    }

    public function show(Mecanico $mecanico): JsonResponse
    {
        $mecanico->load(['ordenesPrincipal', 'ordenesAsignadas']);

        return response()->json($mecanico);
    }

    public function update(Request $request, Mecanico $mecanico): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'cedula' => ['nullable', 'string', 'max:20', Rule::unique('mecanicos', 'cedula')->ignore($mecanico->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $mecanico->update($validated);

        return response()->json($mecanico->fresh());
    }

    public function destroy(Mecanico $mecanico): JsonResponse
    {
        $mecanico->delete();

        return response()->json([], 204);
    }
}

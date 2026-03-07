<?php

namespace Database\Seeders;

use App\Models\ActualizacionOrden;
use App\Models\Cliente;
use App\Models\Mecanico;
use App\Models\OrdenServicio;
use App\Models\Pago;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DemoWorkshopSeeder extends Seeder
{
    /**
     * Seed demo workshop data:
     * - 15 clientes
     * - 5 mecanicos
     * - 1 vehiculo por cliente
     * - 1 orden por cliente
     */
    public function run(): void
    {
        $faker = fake('es_ES');
        $seedTag = now()->format('ymdHis');

        $marcas = ['toyota', 'honda', 'mercedes', 'mazda', 'kia', 'hyundai', 'ford', 'suzuki'];
        $modelos = [
            'toyota' => ['Corolla', 'RAV4', 'Yaris', 'Hilux'],
            'honda' => ['Civic', 'CR-V', 'Accord', 'HR-V'],
            'mercedes' => ['C200', 'GLA', 'E300', 'GLE'],
            'mazda' => ['CX-5', 'Mazda 3', 'CX-30', 'CX-9'],
            'kia' => ['Sportage', 'Sorento', 'Rio', 'Seltos'],
            'hyundai' => ['Tucson', 'Elantra', 'Santa Fe', 'Creta'],
            'ford' => ['Ranger', 'Escape', 'Explorer', 'F-150'],
            'suzuki' => ['Swift', 'Vitara', 'Baleno', 'Jimny'],
        ];
        $tiposServicio = ['reparacion', 'mantenimiento', 'pintura'];
        $estados = ['recibido', 'diagnostico', 'esperando_piezas', 'en_reparacion', 'pruebas', 'listo_para_entrega'];
        $especialidades = ['Motor', 'Aire acondicionado', 'Electricidad', 'Pintura', 'Transmision'];

        $mecanicos = collect();
        for ($i = 1; $i <= 5; $i++) {
            $mecanicos->push(Mecanico::create([
                'nombre' => $faker->name(),
                'cedula' => $seedTag.'9'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'telefono' => '809'.str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'especialidad' => Arr::random($especialidades),
                'activo' => true,
            ]));
        }

        for ($i = 1; $i <= 15; $i++) {
            $cliente = Cliente::create([
                'nombre' => $faker->name(),
                'cedula' => $seedTag.'1'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'sexo' => Arr::random(['masculino', 'femenino']),
                'direccion' => $faker->streetAddress().', '.$faker->city(),
                'telefono' => '829'.str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'email' => 'cliente'.$seedTag.$i.'@demo.com',
            ]);

            $marca = Arr::random($marcas);
            $modelo = Arr::random($modelos[$marca]);

            $vehiculo = Vehiculo::create([
                'cliente_id' => $cliente->id,
                'anio' => random_int(2016, 2026),
                'marca' => $marca,
                'modelo' => $modelo,
                'matricula' => strtoupper(Str::random(2)).$seedTag.$i,
                'color' => Arr::random(['Negro', 'Blanco', 'Gris', 'Rojo', 'Azul']),
                'combustible' => Arr::random(['gasolina', 'diesel', 'hibrido']),
                'chasis' => strtoupper(Str::random(17)),
            ]);

            $mecanicoPrincipal = $mecanicos->random();
            $estado = Arr::random($estados);
            $fechaIngreso = now()->subDays(random_int(1, 15))->toDateString();

            $orden = OrdenServicio::create([
                'codigo' => 'OS-DEMO-'.$seedTag.'-'.$i,
                'cliente_id' => $cliente->id,
                'vehiculo_id' => $vehiculo->id,
                'mecanico_principal_id' => $mecanicoPrincipal->id,
                'tipo_servicio' => Arr::random($tiposServicio),
                'descripcion' => $faker->sentence(10),
                'monto' => random_int(3500, 35000),
                'forma_pago' => Arr::random(['efectivo', 'transferencia']),
                'tipo_pago' => Arr::random(['unico', 'cuotas']),
                'estado' => $estado,
                'fecha_ingreso' => $fechaIngreso,
                'fecha_estimada_terminacion' => now()->addDays(random_int(2, 10))->toDateString(),
                'motivo_retraso' => $estado === 'esperando_piezas' ? 'Pieza pendiente en proveedor.' : null,
            ]);

            $auxiliares = $mecanicos
                ->where('id', '!=', $mecanicoPrincipal->id)
                ->random(random_int(1, min(2, max(1, $mecanicos->count() - 1))));

            $syncData = [$mecanicoPrincipal->id => ['rol' => 'principal']];
            foreach ($auxiliares as $auxiliar) {
                $syncData[$auxiliar->id] = ['rol' => 'auxiliar'];
            }
            $orden->mecanicos()->sync($syncData);

            ActualizacionOrden::create([
                'orden_servicio_id' => $orden->id,
                'mecanico_id' => $mecanicoPrincipal->id,
                'estado' => $estado,
                'mensaje' => 'Orden creada automaticamente para datos demo.',
                'notificar_cliente' => false,
            ]);

            Pago::create([
                'orden_servicio_id' => $orden->id,
                'monto' => round(((float) $orden->monto) * 0.3, 2),
                'metodo' => Arr::random(['efectivo', 'transferencia']),
                'tipo_pago' => 'abono',
                'referencia' => 'ABONO-'.$seedTag.'-'.$i,
                'pagado_en' => now()->subDays(random_int(0, 10)),
                'recibido_por' => 'Sistema Demo',
            ]);
        }
    }
}

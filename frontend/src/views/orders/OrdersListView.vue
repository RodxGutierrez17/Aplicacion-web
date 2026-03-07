<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { http } from '../../api/http'

const ordenes = ref([])
const clientes = ref([])
const vehiculos = ref([])
const mecanicos = ref([])
const mensaje = ref('')
const estadoDraft = ref({})
const marcas = ['Toyota', 'Honda', 'Mercedes', 'Mazda', 'Kia', 'Hyundai', 'Ford', 'Suzuki']

const usarClienteNuevo = ref(false)
const usarVehiculoNuevo = ref(false)

const form = ref({
  cliente_id: '',
  vehiculo_id: '',
  mecanico_principal_id: '',
  mecanicos_auxiliares: [],
  tipo_servicio: 'reparacion',
  descripcion: '',
  monto: '',
  forma_pago: 'efectivo',
  tipo_pago: 'unico',
  fecha_estimada_terminacion: '',
  nuevo_cliente: {
    nombre: '',
    cedula: '',
    sexo: '',
    direccion: '',
    telefono: '',
    email: ''
  },
  nuevo_vehiculo: {
    anio: '',
    marca: '',
    modelo: '',
    matricula: '',
    color: '',
    combustible: '',
    chasis: ''
  }
})

const vehiculosCliente = computed(() =>
  vehiculos.value.filter((v) => String(v.cliente_id) === String(form.value.cliente_id))
)

const vehiculoSeleccionado = computed(() =>
  vehiculos.value.find((v) => String(v.id) === String(form.value.vehiculo_id))
)

watch(
  () => form.value.cliente_id,
  () => {
    if (usarClienteNuevo.value) return
    const primerVehiculo = vehiculosCliente.value[0]
    form.value.vehiculo_id = primerVehiculo ? String(primerVehiculo.id) : ''
    usarVehiculoNuevo.value = !primerVehiculo
  }
)

watch(usarClienteNuevo, (value) => {
  if (value) {
    form.value.cliente_id = ''
    form.value.vehiculo_id = ''
    usarVehiculoNuevo.value = true
  }
})

watch(usarVehiculoNuevo, (value) => {
  if (value) form.value.vehiculo_id = ''
})

const cargarData = async () => {
  const [oRes, cRes, vRes, mRes] = await Promise.all([
    http.get('/ordenes-servicio'),
    http.get('/clientes'),
    http.get('/vehiculos'),
    http.get('/mecanicos?activo=1')
  ])
  ordenes.value = oRes.data.data || []
  clientes.value = cRes.data.data || []
  vehiculos.value = vRes.data.data || []
  mecanicos.value = mRes.data.data || []
}

const resetForm = () => {
  usarClienteNuevo.value = false
  usarVehiculoNuevo.value = false
  form.value = {
    cliente_id: '',
    vehiculo_id: '',
    mecanico_principal_id: '',
    mecanicos_auxiliares: [],
    tipo_servicio: 'reparacion',
    descripcion: '',
    monto: '',
    forma_pago: 'efectivo',
    tipo_pago: 'unico',
    fecha_estimada_terminacion: '',
    nuevo_cliente: {
      nombre: '',
      cedula: '',
      sexo: '',
      direccion: '',
      telefono: '',
      email: ''
    },
    nuevo_vehiculo: {
      anio: '',
      marca: '',
      modelo: '',
      matricula: '',
      color: '',
      combustible: '',
      chasis: ''
    }
  }
}

const crearOrden = async () => {
  try {
    await http.post('/ordenes-servicio', {
      cliente_id: usarClienteNuevo.value ? null : Number(form.value.cliente_id),
      vehiculo_id: usarVehiculoNuevo.value ? null : Number(form.value.vehiculo_id),
      mecanico_principal_id: form.value.mecanico_principal_id ? Number(form.value.mecanico_principal_id) : null,
      mecanicos_auxiliares: form.value.mecanicos_auxiliares.map((id) => Number(id)),
      monto: Number(form.value.monto),
      tipo_servicio: form.value.tipo_servicio,
      descripcion: form.value.descripcion,
      forma_pago: form.value.forma_pago,
      tipo_pago: form.value.tipo_pago,
      fecha_estimada_terminacion: form.value.fecha_estimada_terminacion || null,
      nuevo_cliente: usarClienteNuevo.value ? { ...form.value.nuevo_cliente } : null,
      nuevo_vehiculo: usarVehiculoNuevo.value
        ? {
            ...form.value.nuevo_vehiculo,
            anio: form.value.nuevo_vehiculo.anio ? Number(form.value.nuevo_vehiculo.anio) : null
          }
        : null
    })
    mensaje.value = 'Orden creada correctamente.'
    resetForm()
    await cargarData()
  } catch (error) {
    const backendErrors = error?.response?.data?.errors
    mensaje.value = backendErrors
      ? Object.values(backendErrors).flat().join(' | ')
      : error?.response?.data?.message || 'Error al crear orden.'
  }
}

const actualizarEstado = async (orden) => {
  const estado = estadoDraft.value[orden.id]
  if (!estado) return
  await http.patch(`/ordenes-servicio/${orden.id}/estado`, {
    estado,
    mensaje: `Estado actualizado a: ${estado}`
  })
  await cargarData()
}

const estadoLegible = (estado) =>
  String(estado || '')
    .replaceAll('_', ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())

onMounted(cargarData)
</script>

<template>
  <main class="page">
    <h2 class="title">Ordenes de servicio</h2>
    <p v-if="mensaje">{{ mensaje }}</p>

    <section class="order-card">
      <form class="order-grid" @submit.prevent="crearOrden">
        <div class="toggle-row">
          <label><input v-model="usarClienteNuevo" type="checkbox" /> Cliente nuevo</label>
          <label><input v-model="usarVehiculoNuevo" type="checkbox" /> Vehiculo nuevo</label>
        </div>

        <div v-if="!usarClienteNuevo" class="field-span">
          <label>Cliente</label>
          <select v-model="form.cliente_id" required>
            <option value="">Seleccione cliente</option>
            <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }} (ID: {{ c.id }})</option>
          </select>
        </div>

        <div v-if="!usarVehiculoNuevo" class="field-span">
          <label>Vehiculo (auto al elegir cliente)</label>
          <select v-model="form.vehiculo_id" :disabled="!form.cliente_id || vehiculosCliente.length === 0" required>
            <option value="">Seleccione vehiculo</option>
            <option v-for="v in vehiculosCliente" :key="v.id" :value="v.id">
              {{ v.marca }} {{ v.modelo }} - {{ v.matricula }}
            </option>
          </select>
          <div v-if="vehiculoSeleccionado" class="vehicle-chip">
            {{ vehiculoSeleccionado.anio }} - {{ vehiculoSeleccionado.color }} - {{ vehiculoSeleccionado.combustible || 'N/A' }}
          </div>
        </div>

        <template v-if="usarClienteNuevo">
          <input v-model="form.nuevo_cliente.nombre" placeholder="Nombre cliente" required />
          <input v-model="form.nuevo_cliente.cedula" placeholder="Cedula cliente" required />
          <input v-model="form.nuevo_cliente.telefono" placeholder="Telefono cliente" required />
          <input v-model="form.nuevo_cliente.direccion" placeholder="Direccion cliente" required />
          <select v-model="form.nuevo_cliente.sexo">
            <option value="">Sexo</option>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
          </select>
          <input v-model="form.nuevo_cliente.email" placeholder="Email cliente (opcional)" />
        </template>

        <template v-if="usarVehiculoNuevo">
          <input v-model="form.nuevo_vehiculo.anio" type="number" placeholder="Ano vehiculo" required />
          <select v-model="form.nuevo_vehiculo.marca" required>
            <option value="">Marca vehiculo</option>
            <option v-for="marca in marcas" :key="marca" :value="marca">{{ marca }}</option>
          </select>
          <input v-model="form.nuevo_vehiculo.modelo" placeholder="Modelo vehiculo" required />
          <input v-model="form.nuevo_vehiculo.matricula" placeholder="Matricula vehiculo" required />
          <input v-model="form.nuevo_vehiculo.color" placeholder="Color vehiculo" required />
          <input v-model="form.nuevo_vehiculo.combustible" placeholder="Combustible (opcional)" />
        </template>

        <select v-model="form.tipo_servicio" required>
          <option value="reparacion">Reparacion</option>
          <option value="mantenimiento">Mantenimiento</option>
          <option value="pintura">Pintura</option>
          <option value="articulo">Compra articulo</option>
        </select>
        <input v-model="form.monto" type="number" step="0.01" placeholder="Monto" required />
        <select v-model="form.mecanico_principal_id">
          <option value="">Mecanico principal</option>
          <option v-for="m in mecanicos" :key="m.id" :value="m.id">{{ m.nombre }}</option>
        </select>
        <select v-model="form.forma_pago">
          <option value="efectivo">Efectivo</option>
          <option value="transferencia">Transferencia</option>
        </select>
        <select v-model="form.tipo_pago">
          <option value="unico">Unico</option>
          <option value="cuotas">Cuotas</option>
        </select>
        <input v-model="form.fecha_estimada_terminacion" type="date" />
        <textarea v-model="form.descripcion" placeholder="Descripcion (opcional)" />
        <details class="field-span">
          <summary>Mecanicos auxiliares (opcional)</summary>
          <select v-model="form.mecanicos_auxiliares" multiple>
            <option v-for="m in mecanicos" :key="`aux-${m.id}`" :value="m.id">{{ m.nombre }}</option>
          </select>
        </details>
        <button type="submit">Crear orden</button>
      </form>
    </section>

    <table>
      <thead>
        <tr>
          <th>Codigo</th>
          <th>Cliente</th>
          <th>Vehiculo</th>
          <th>Estado</th>
          <th>Monto</th>
          <th>Cambiar estado</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="orden in ordenes" :key="orden.id">
          <td>{{ orden.codigo }}</td>
          <td>{{ orden.cliente?.nombre }}</td>
          <td>{{ orden.vehiculo?.marca }} {{ orden.vehiculo?.modelo }}</td>
          <td>{{ estadoLegible(orden.estado) }}</td>
          <td>{{ orden.monto }}</td>
          <td>
            <div class="row">
              <select v-model="estadoDraft[orden.id]">
                <option value="">Seleccionar</option>
                <option value="recibido">Recibido</option>
                <option value="diagnostico">Diagnostico</option>
                <option value="esperando_piezas">Esperando Piezas</option>
                <option value="en_reparacion">En Reparacion</option>
                <option value="pruebas">Pruebas</option>
                <option value="listo_para_entrega">Ready</option>
                <option value="entregado">Entregado</option>
              </select>
              <button class="secondary" type="button" @click="actualizarEstado(orden)">Actualizar</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </main>
</template>

<style scoped>
.order-card {
  border: 1px solid #d8e4db;
  background: #fcfffd;
  border-radius: 14px;
  padding: 0.9rem;
  margin-bottom: 1rem;
}

.order-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 0.65rem;
}

.toggle-row {
  grid-column: 1 / -1;
  display: flex;
  gap: 1rem;
  align-items: center;
  color: #1d3a33;
}

.field-span {
  grid-column: 1 / -1;
}

label {
  display: block;
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
  color: #2e5a4f;
}

.vehicle-chip {
  margin-top: 0.35rem;
  font-size: 0.85rem;
  color: #275447;
  background: #e6f3ec;
  padding: 0.3rem 0.55rem;
  border-radius: 8px;
  display: inline-block;
}
</style>

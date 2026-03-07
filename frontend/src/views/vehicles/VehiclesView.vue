<script setup>
import { onMounted, ref } from 'vue'
import { http } from '../../api/http'

const vehiculos = ref([])
const clientes = ref([])
const loading = ref(false)
const marcas = ['toyota', 'honda', 'mercedes', 'mazda', 'kia', 'hyundai', 'ford', 'suzuki']
const form = ref({
  cliente_id: '',
  anio: '',
  marca: '',
  modelo: '',
  matricula: '',
  color: '',
  combustible: '',
  chasis: ''
})

const cargarBase = async () => {
  loading.value = true
  try {
    const [vRes, cRes] = await Promise.all([
      http.get('/vehiculos'),
      http.get('/clientes')
    ])
    vehiculos.value = vRes.data.data || []
    clientes.value = cRes.data.data || []
  } finally {
    loading.value = false
  }
}

const crearVehiculo = async () => {
  await http.post('/vehiculos', {
    ...form.value,
    cliente_id: Number(form.value.cliente_id),
    anio: Number(form.value.anio)
  })
  form.value = {
    cliente_id: '',
    anio: '',
    marca: '',
    modelo: '',
    matricula: '',
    color: '',
    combustible: '',
    chasis: ''
  }
  await cargarBase()
}

onMounted(cargarBase)
</script>

<template>
  <main class="page">
    <h2 class="title">Vehiculos</h2>

    <form class="grid" @submit.prevent="crearVehiculo">
      <select v-model="form.cliente_id" required>
        <option value="">Seleccione cliente</option>
        <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }} ({{ c.id }})</option>
      </select>
      <input v-model="form.anio" type="number" placeholder="Ano" required />
      <select v-model="form.marca" required>
        <option value="">Marca</option>
        <option v-for="marca in marcas" :key="marca" :value="marca">{{ marca }}</option>
      </select>
      <input v-model="form.modelo" placeholder="Modelo" required />
      <input v-model="form.matricula" placeholder="Matricula" required />
      <input v-model="form.color" placeholder="Color" required />
      <input v-model="form.combustible" placeholder="Combustible" />
      <input v-model="form.chasis" placeholder="Chasis (opcional)" />
      <button type="submit">Registrar vehiculo</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Vehiculo</th>
          <th>Matricula</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="4">Cargando...</td>
        </tr>
        <tr v-for="v in vehiculos" :key="v.id">
          <td>{{ v.id }}</td>
          <td>{{ v.cliente?.nombre }}</td>
          <td>{{ v.marca }} {{ v.modelo }} ({{ v.anio }})</td>
          <td>{{ v.matricula }}</td>
        </tr>
      </tbody>
    </table>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { http } from '../../api/http'

const clientes = ref([])
const loading = ref(false)
const form = ref({
  nombre: '',
  cedula: '',
  sexo: '',
  direccion: '',
  telefono: '',
  email: ''
})

const cargarClientes = async () => {
  loading.value = true
  try {
    const { data } = await http.get('/clientes')
    clientes.value = data.data || []
  } finally {
    loading.value = false
  }
}

const crearCliente = async () => {
  await http.post('/clientes', form.value)
  form.value = {
    nombre: '',
    cedula: '',
    sexo: '',
    direccion: '',
    telefono: '',
    email: ''
  }
  await cargarClientes()
}

onMounted(cargarClientes)
</script>

<template>
  <main class="page">
    <h2 class="title">Clientes</h2>

    <form class="grid" @submit.prevent="crearCliente">
      <input v-model="form.nombre" placeholder="Nombre" required />
      <input v-model="form.cedula" placeholder="Cedula" required />
      <select v-model="form.sexo">
        <option value="">Sexo</option>
        <option value="masculino">Masculino</option>
        <option value="femenino">Femenino</option>
      </select>
      <input v-model="form.telefono" placeholder="Telefono" required />
      <input v-model="form.direccion" placeholder="Direccion" required />
      <input v-model="form.email" placeholder="Email (opcional)" />
      <button type="submit">Registrar cliente</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Cedula</th>
          <th>Telefono</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="4">Cargando...</td>
        </tr>
        <tr v-for="cliente in clientes" :key="cliente.id">
          <td>{{ cliente.id }}</td>
          <td>{{ cliente.nombre }}</td>
          <td>{{ cliente.cedula }}</td>
          <td>{{ cliente.telefono }}</td>
        </tr>
      </tbody>
    </table>
  </main>
</template>

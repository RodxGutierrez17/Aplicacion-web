<script setup>
import { onMounted, ref } from 'vue'
import { http } from '../../api/http'

const mecanicos = ref([])
const form = ref({
  nombre: '',
  cedula: '',
  telefono: '',
  especialidad: '',
  activo: true
})

const cargarMecanicos = async () => {
  const { data } = await http.get('/mecanicos')
  mecanicos.value = data.data || []
}

const crearMecanico = async () => {
  await http.post('/mecanicos', form.value)
  form.value = {
    nombre: '',
    cedula: '',
    telefono: '',
    especialidad: '',
    activo: true
  }
  await cargarMecanicos()
}

onMounted(cargarMecanicos)
</script>

<template>
  <main class="page">
    <h2 class="title">Mecanicos</h2>

    <form class="grid" @submit.prevent="crearMecanico">
      <input v-model="form.nombre" placeholder="Nombre" required />
      <input v-model="form.cedula" placeholder="Cedula (opcional)" />
      <input v-model="form.telefono" placeholder="Telefono" />
      <input v-model="form.especialidad" placeholder="Especialidad" />
      <button type="submit">Agregar mecanico</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Especialidad</th>
          <th>Activo</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="m in mecanicos" :key="m.id">
          <td>{{ m.id }}</td>
          <td>{{ m.nombre }}</td>
          <td>{{ m.especialidad || '-' }}</td>
          <td>{{ m.activo ? 'Si' : 'No' }}</td>
        </tr>
      </tbody>
    </table>
  </main>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { http } from './api/http'
import { clearAuth, getUser, isAuthenticated } from './auth'
import sideLogo from '../Images/image.png'

const route = useRoute()
const router = useRouter()

const showShell = computed(() => route.path !== '/login' && isAuthenticated())
const user = computed(() => getUser())

const logout = async () => {
  try {
    await http.post('/logout')
  } catch {
    // noop
  } finally {
    clearAuth()
    router.push('/login')
  }
}
</script>

<template>
  <div v-if="showShell" class="app-shell">
    <aside class="side-nav">
      <img :src="sideLogo" alt="Taller logo" class="side-logo" />
      <h1>Taller AutoMax</h1>
      <p>Gestion de ordenes</p>
      <p v-if="user">{{ user.name }}</p>
      <nav>
        <RouterLink to="/ordenes">Ordenes</RouterLink>
        <RouterLink to="/clientes">Clientes</RouterLink>
        <RouterLink to="/vehiculos">Vehiculos</RouterLink>
        <RouterLink to="/mecanicos">Mecanicos</RouterLink>
      </nav>
      <button class="secondary logout-btn" @click="logout">Cerrar sesion</button>
    </aside>
    <section class="content">
      <RouterView />
    </section>
  </div>
  <RouterView v-else />
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { http } from '../../api/http'
import { setAuth } from '../../auth'
import loginImage from '../../../Images/login_imagen.png'

const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const login = async () => {
  errorMessage.value = ''
  loading.value = true
  try {
    const { data } = await http.post('/login', {
      email: email.value,
      password: password.value
    })
    setAuth(data)
    router.push('/ordenes')
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || 'No se pudo iniciar sesion.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="auth-layout">
    <section class="page auth-page">
      <h2 class="title">Iniciar sesion</h2>
      <p>Accede con tu cuenta para gestionar el taller.</p>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <form class="auth-form" @submit.prevent="login">
        <input v-model="email" type="email" placeholder="Correo" required />
        <input v-model="password" type="password" placeholder="Clave" required />
        <button :disabled="loading" type="submit">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>
    </section>

    <section class="auth-art">
      <img :src="loginImage" alt="Taller login" />
    </section>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import Alert from '@/components/Alert.vue'

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const error = ref('')

const router = useRouter()

const register = async () => {
  try {
    await api.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value
    })
    error.value = ''
    router.push('/login')
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao registrar usuário'
  }
}
</script>

<template>
  <div class="login-container">
    <h1>Registrar</h1>

    <Alert :message="error" />

    <form @submit.prevent="register">
      <input v-model="name" type="text" placeholder="Nome" required />
      <input v-model="email" type="email" placeholder="Email" required />
      <input v-model="password" type="text"  placeholder="Senha" required />
      <input v-model="password_confirmation" type="text" placeholder="Confirme a Senha" required />
      <button type="submit">Registrar</button>
    </form>

    <router-link to="/login">← Voltar para o Login</router-link>
  </div>
</template>

<style scoped>
.login-container {
  max-width: 400px;
  margin: auto;
  padding: 24px;
}
input {
  display: block;
  margin: 8px 0;
  padding: 8px;
  width: 100%;
}
button {
  margin: 12px 0;
  padding: 8px 12px;
}
</style>

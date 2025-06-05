<script>
import api from '../api'
import { useRouter } from 'vue-router'
import Alert from '@/components/Alert.vue'

export default {
  components: { Alert },
  data() {
    return {
      email: '',
      password: '',
      error: ''
    }
  },
  setup() {
    const router = useRouter()
    return { router }
  },
  methods: {
    async login() {
      try {
        const response = await api.post('/login', {
          email: this.email,
          password: this.password
        })
        localStorage.setItem('token', response.data.access_token)
        this.error = ''
        this.router.push('/dashboard')
      } catch (err) {
        this.error = err.response?.data?.message || 'Erro ao fazer login'
      }
    }
  }
}
</script>

<template>
  <div class="login-container">
    <Alert :message="error" />
    <h2>Login</h2>
    <input v-model="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Senha" />
    <button @click="login" class="login-btn">Entrar</button>
    
    <router-link to="/register" class="register-btn">Registrar</router-link>
  </div>
</template>

<style>

.register-btn {
  margin-top: 10px;
  margin-left: 50px;
  background-color: cadetblue;
  color: black;
  cursor: pointer;
  text-decoration: none;
}

</style>
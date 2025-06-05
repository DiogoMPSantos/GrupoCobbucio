<script>
import api from '../api'
import { useRouter } from 'vue-router'
import Alert from '@/components/Alert.vue'
import { ref } from 'vue'

export default {
  components: { Alert },
  data() {
    return {
      balance: 0,
      depositAmount: 0,
      transferAmount: 0,
      receiverId: '',
      error: ''
    }
  },
  setup() {
    const router = useRouter()
    return { router }
  },
  methods: {
    async loadBalance() {
      try {
        const res = await api.get('/wallet')
        this.balance = res.data.balance
        this.error = ''
      } catch (err) {
        this.error = err.response?.data?.message || 'Erro ao carregar saldo'
      }
    },
    async deposit() {
      try {
        await api.post('/deposit', { amount: this.depositAmount })
        this.depositAmount = 0
        this.loadBalance()
        this.error = ''
      } catch (err) {
        this.error = err.response?.data?.message || 'Erro ao depositar'
      }
    },
    async transfer() {
      try {
        await api.post('/transfer', {
          amount: this.transferAmount,
          receiver_id: this.receiverId
        })
        this.transferAmount = 0
        this.receiverId = ''
        this.loadBalance()
        this.error = ''
      } catch (err) {
        this.error = err.response?.data?.error || 'Erro ao transferir'
      }
    },
    logout() {
      localStorage.removeItem('token')
      this.router.push('/login')
    }
  },
  mounted() {
    this.loadBalance()
  }
}
</script>

<template>
  <div class="container">
    <h1>Carteira Digital</h1>

    <button @click="logout">Sair</button>

    <Alert :message="error" />
    
    <div class="balance">
      <p><strong>Saldo:</strong> R$ {{ balance }}</p>
      <button @click="loadBalance">Atualizar</button>
    </div>

    <hr />

    <div class="actions">
      <h2>Depósito</h2>
      <input v-model.number="depositAmount" type="number" placeholder="Valor" />
      <button @click="deposit">Depositar</button>

      <h2>Transferência</h2>
      <input v-model="receiverId" type="text" placeholder="ID do destinatário" />
      <input v-model.number="transferAmount" type="number" placeholder="Valor" />
      <button @click="transfer">Transferir</button>
    </div>

    <hr />

    <div class="history">
      <router-link to="/transactions">📜 Ver Histórico</router-link>
    </div>

  </div>
</template>
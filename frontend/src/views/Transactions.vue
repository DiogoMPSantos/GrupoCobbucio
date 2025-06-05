<script setup>
import { onMounted, ref } from 'vue'
import api from '@/api'
import { useRouter } from 'vue-router'
import Alert from '@/components/Alert.vue'

const transactions = ref([])
const error = ref('')
const router = useRouter()

onMounted(async () => {
  try {
    const response = await api.get('/transactions')
    transactions.value = response.data.data
  } catch (err) {
    error.value = err.response?.data?.data?.message || 'Erro ao carregar transações'
  }
})

const revert = (reference) => {
  router.push({ name: 'Revert', params: { reference } })
}
</script>

<template>
  <div>
    
    <Alert :message="error" />

    <button @click="router.back()">← Voltar</button>

    <h1>Histórico de Transações</h1>
    <ul v-if="transactions.length">
      <li v-for="tx in transactions" :key="tx.reference">
        {{ tx.type }} - {{ tx.amount }} - {{ tx.status }}
        <button @click="revert(tx.reference)" :disabled="tx.status === 'reversed' || tx.type === 'reversal'">Reverter</button>
      </li>
    </ul>
    <p v-else>Nenhuma transação encontrada.</p>
  </div>
</template>

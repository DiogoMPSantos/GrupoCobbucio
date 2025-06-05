<script setup>
import { onMounted, ref } from 'vue'
import api from '@/api'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const result = ref('Revertendo...')

onMounted(async () => {
  try {
    await api.post('/reverse', { reference: route.params.reference })
    result.value = 'Transação revertida com sucesso!'
  } catch (err) {
    result.value = err.response?.data?.data?.message || 'Erro ao reverter transação.'
    console.error(err)
  }

  setTimeout(() => {
    router.push('/')
  }, 2000)
})
</script>

<template>
  <div>
    <h1>{{ result }}</h1>
  </div>
</template>

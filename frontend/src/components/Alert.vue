<template>
  <div v-if="message" class="alert">
    {{ message }}
  </div>
</template>

<script setup>
defineProps({
  message: String
})
</script>

<style>
.alert {
  background-color: #f8d7da;
  color: #721c24;
  padding: 12px;
  margin-bottom: 16px;
  border: 1px solid #f5c6cb;
  border-radius: 5px;
}
</style>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../api/http'

const apiStatus = ref('Checking backend...')

onMounted(async () => {
  try {
    const response = await api.get('/health')
    apiStatus.value = response.data.data.message
  } catch (error) {
    apiStatus.value = 'Backend not connected yet'
  }
})
</script>

<template>
  <main class="container py-5">
    <h1>Campus Marketplace System</h1>
    <p class="text-muted">Vue frontend is running.</p>

    <div class="alert alert-info">
      Backend status: {{ apiStatus }}
    </div>
  </main>
</template>
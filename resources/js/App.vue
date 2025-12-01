<template>
  <div id="app">
    <!-- Login page without layout -->
    <template v-if="!authStore.isAuthenticated">
      <router-view />
    </template>
    
    <!-- Main app with layout -->
    <template v-else>
      <AppLayout>
        <router-view />
      </AppLayout>
    </template>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import AppLayout from './components/layout/AppLayout.vue'

const authStore = useAuthStore()

onMounted(async () => {
  if (authStore.token) {
    try {
      await authStore.getUser()
    } catch (error) {
      console.error('Failed to get user data:', error)
    }
  }
})
</script>
<template>
  <div id="app">
    <!-- Show router view while checking auth -->
    <!-- Router guard will handle authentication and redirects -->
    <template v-if="!isInitializing">
      <!-- Login page doesn't use AppLayout -->
      <router-view v-if="isLoginPage" />
      <!-- All other pages use AppLayout -->
      <AppLayout v-else>
        <router-view />
      </AppLayout>
    </template>
    <div v-else class="flex items-center justify-center min-h-screen">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from './stores/auth'
import AppLayout from './components/layout/AppLayout.vue'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const isInitializing = ref(true)

const isLoginPage = computed(() => {
  return route.path === '/login'
})

onMounted(async () => {
  // Only load user if we have a token and we're not on login page
  if (authStore.token && route.path !== '/login') {
    try {
      // Don't show error if user load fails during initial load
      await authStore.getUser()
    } catch (error) {
      // Silently fail - router guard will handle redirect
      console.error('Failed to get user data:', error)
    }
  }
  isInitializing.value = false
})
</script>
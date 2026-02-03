<template>
  <div class="min-h-screen flex items-center justify-center relative bg-cover bg-center bg-no-repeat" :style="{ backgroundImage: `url(${backgroundImage})` }">
    <!-- Overlay untuk membuat background sedikit lebih gelap agar form lebih readable -->
    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
    
    <!-- Login Form Container -->
    <div class="relative z-10 w-full max-w-md px-4">
      <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
        <!-- Red Banner: SMK PROGRESIA -->
        <div class="bg-red-600 text-white text-center py-2 px-4">
          <h1 class="text-sm font-bold">SMK PROGRESIA CIANJUR</h1>
        </div>
        
        <!-- Logo Section -->
        <div class="flex flex-col items-center py-4 px-6">
          <img :src="logoImage" alt="Logo SMK Progresia" class="h-20 w-20 object-contain mb-2">
          
          <!-- Title: SI Rapor Kurmer -->
          <h2 class="text-xl font-bold text-gray-900">SI Rapor Kurmer</h2>
        </div>
        
        <!-- Form Section -->
        <form class="px-6 pb-6 space-y-4" @submit.prevent="handleLogin">
          <!-- Username Field -->
          <div class="flex items-center space-x-3">
            <label for="email" class="text-sm font-medium text-gray-700 w-20">Email</label>
            <input 
              id="email" 
              v-model="form.email"
              name="email" 
              type="text" 
              autocomplete="email" 
              required 
              class="flex-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
              placeholder="Masukkan email"
              :class="{ 'border-red-300': errors.email }"
            >
          </div>
          
          <!-- Password Field -->
          <div class="flex items-center space-x-3">
            <label for="password" class="text-sm font-medium text-gray-700 w-20">Password</label>
            <div class="flex-1 relative">
              <input 
                id="password" 
                v-model="form.password"
                name="password" 
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password" 
                required 
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                placeholder="Masukkan password"
                :class="{ 'border-red-300': errors.password }"
              >

              <button
                type="button"
                @click="toggleShowPassword"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
              >
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 3C5.5 3 1.7 6.2.9 10c.8 3.8 4.6 7 9.1 7s8.3-3.2 9.1-7c-.8-3.8-4.6-7-9.1-7zM10 14a4 4 0 110-8 4 4 0 010 8z" />
                  <path d="M10 8a2 2 0 100 4 2 2 0 000-4z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.94 10.94 0 0112 20c-4.5 0-8.3-3.2-9.1-7 .5-2.2 1.8-4.1 3.6-5.4" />
                  <path d="M1 1l22 22" />
                  <path d="M9.88 9.88A3 3 0 0014.12 14.12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Error messages -->
          <div v-if="errors.email || errors.password || errors.general" class="rounded-md bg-red-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Terjadi kesalahan saat login
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <ul class="list-disc pl-5 space-y-1">
                    <li v-if="errors.email">{{ errors.email[0] }}</li>
                    <li v-if="errors.password">{{ errors.password[0] }}</li>
                    <li v-if="errors.general">{{ errors.general }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Login Button -->
          <div>
            <button 
              type="submit" 
              :disabled="loading"
              class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
              </span>
              <span v-else>Masuk</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useToast } from 'vue-toastification'

// Image paths from public folder
const logoImage = '/images/logo.jpeg'
const backgroundImage = '/images/background.jpeg'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const errors = ref({})

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const showPassword = ref(false)

const toggleShowPassword = () => {
  showPassword.value = !showPassword.value
}

const handleLogin = async () => {
  loading.value = true
  errors.value = {}

  try {
    const { role } = await authStore.login({
      email: form.email,
      password: form.password
    })

    toast.success('Berhasil login!')
    
    // Redirect based on role
    const route = authStore.getDefaultRoute()
    router.push(route)
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else if (error.response?.status === 401) {
      errors.value = { general: 'Email atau password salah' }
    } else {
      errors.value = { general: 'Terjadi kesalahan pada server' }
      toast.error('Terjadi kesalahan saat login')
    }
  } finally {
    loading.value = false
  }
}

const loginDemo = (role) => {
  const demoAccounts = {
    admin: { email: 'admin@smk.sch.id', password: 'password' },
    guru: { email: 'guru@smk.sch.id', password: 'password' },
    siswa: { email: 'siswa@smk.sch.id', password: 'password' }
  }

  const account = demoAccounts[role]
  if (account) {
    form.email = account.email
    form.password = account.password
    handleLogin()
  }
}
</script>
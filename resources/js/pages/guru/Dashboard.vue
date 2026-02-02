<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            {{ authStore.isWaliKelas ? 'Dashboard Wali Kelas' : 'Dashboard Guru' }}
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Selamat datang, {{ authStore.user?.name }}
          </p>
        </div>
      </div>

      <!-- Teaching Summary -->
      <div class="mt-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <!-- Total Siswa -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Siswa</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.total_siswa || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <router-link to="/guru/nilai" class="font-medium text-green-700 hover:text-green-900">
                  Input nilai
                </router-link>
              </div>
            </div>
          </div>

          <!-- Mata Pelajaran -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Mata Pelajaran</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.total_mapel || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">

        <!-- Classes Taught -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Kelas yang Diampu</h3>
            <div class="mt-6">
              <div v-if="classes.length" class="space-y-3">
                <div v-for="kelas in classes" :key="kelas.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ kelas.nama_kelas }}</p>
                    <p class="text-sm text-gray-500">{{ kelas.jurusan?.nama_jurusan }}</p>
                    <p class="text-sm text-gray-500">{{ kelas.siswa_count || 0 }} siswa</p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-sm text-gray-500">Belum ada kelas yang diampu</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const toast = useToast()

const stats = ref({})
const classes = ref([])
const loading = ref(true)

const fetchDashboardData = async () => {
  // Check if user is still authenticated before fetching
  if (!authStore.isAuthenticated) {
    loading.value = false
    return
  }

  try {
    const response = await axios.get('/dashboard/guru')
    stats.value = response.data.stats || {}
    classes.value = response.data.classes || []
  } catch (error) {
    // Don't show error if user is not authenticated (likely logged out)
    if (error.response?.status === 401 || !authStore.isAuthenticated) {
      // User is logged out, don't show error
      return
    }
    console.error('Error fetching dashboard data:', error)
    toast.error('Gagal mengambil data dashboard')
    stats.value = {}
    classes.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
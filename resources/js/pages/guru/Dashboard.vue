<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Dashboard Guru
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Selamat datang, {{ authStore.user?.name }}
          </p>
        </div>
      </div>

      <!-- Teaching Summary -->
      <div class="mt-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Total Kelas Diampu -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Kelas Diampu</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.total_kelas || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

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
                    <dd class="text-lg font-medium text-gray-900">{{ stats.mata_pelajaran?.nama_mapel || '-' }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Jadwal Hari Ini -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Jadwal Hari Ini</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ todaySchedule.length }} Pertemuan</dd>
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
                  <div class="flex-shrink-0">
                    <router-link 
                      :to="`/guru/kelas/${kelas.id}`"
                      class="text-sm font-medium text-blue-600 hover:text-blue-500"
                    >
                      Lihat →
                    </router-link>
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

      <!-- Recent Grades & CP Progress -->
      <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Grade Entries -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Input Nilai Terbaru</h3>
            <div class="mt-6 flow-root">
              <ul v-if="recentGrades.length" class="-my-5 divide-y divide-gray-200">
                <li v-for="nilai in recentGrades" :key="nilai.id" class="py-4">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">
                        {{ nilai.siswa?.nama_lengkap }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ nilai.kelas?.nama_kelas }} - {{ nilai.jenis_nilai }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ formatDate(nilai.tanggal_input) }}
                      </p>
                    </div>
                    <div class="flex-shrink-0">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium"
                            :class="getGradeColor(nilai.nilai_akhir)">
                        {{ nilai.nilai_akhir }}
                      </span>
                    </div>
                  </div>
                </li>
              </ul>
              <div v-else class="text-center py-4">
                <p class="text-sm text-gray-500">Belum ada nilai yang diinput</p>
              </div>
            </div>
            <div class="mt-6">
              <router-link to="/guru/nilai" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                Input nilai siswa →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Capaian Pembelajaran Progress -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Capaian Pembelajaran</h3>
            <div class="mt-6">
              <div v-if="cpProgress.length" class="space-y-4">
                <div v-for="cp in cpProgress.slice(0, 3)" :key="cp.id" class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center justify-between text-sm">
                      <span class="font-medium text-gray-900">{{ cp.kelas?.nama_kelas }}</span>
                      <span class="text-gray-500">{{ cp.progress }}%</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">{{ cp.description }}</p>
                    <div class="mt-2">
                      <div class="bg-gray-200 rounded-full h-2">
                        <div 
                          class="h-2 rounded-full" 
                          :class="cp.progress >= 80 ? 'bg-green-600' : cp.progress >= 60 ? 'bg-yellow-600' : 'bg-red-600'"
                          :style="{ width: `${Math.min(cp.progress, 100)}%` }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-sm text-gray-500">Belum ada data capaian pembelajaran</p>
              </div>
            </div>
            <div class="mt-6">
              <router-link to="/guru/capaian-pembelajaran" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                Kelola capaian pembelajaran →
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <router-link 
            to="/guru/nilai"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Input Nilai
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Input nilai harian, UTS, UAS siswa
              </p>
            </div>
          </router-link>

          <router-link 
            to="/guru/capaian-pembelajaran"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-green-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Capaian Pembelajaran
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Kelola CP dan TP siswa
              </p>
            </div>
          </router-link>

          <router-link 
            to="/guru/p5"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-yellow-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Projek P5
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Kelola projek penguatan profil pelajar Pancasila
              </p>
            </div>
          </router-link>
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
const recentGrades = ref([])
const cpProgress = ref([])
const loading = ref(true)

const fetchDashboardData = async () => {
  try {
    const response = await axios.get('/dashboard/guru')
    stats.value = response.data.stats || {}
    classes.value = response.data.classes || []
    recentGrades.value = response.data.recent_grades || []
    cpProgress.value = response.data.cp_progress || []
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    toast.error('Gagal mengambil data dashboard')
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID')
}

const getGradeColor = (grade) => {
  if (grade >= 90) return 'bg-green-100 text-green-800'
  if (grade >= 80) return 'bg-blue-100 text-blue-800'
  if (grade >= 70) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

onMounted(() => {
  fetchDashboardData()
})
</script>
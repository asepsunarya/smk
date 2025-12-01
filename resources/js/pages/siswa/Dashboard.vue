<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Dashboard Siswa
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Selamat datang, {{ authStore.user?.name }}
          </p>
        </div>
      </div>

      <!-- Profile Card -->
      <div class="mt-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                  {{ authStore.user?.name?.charAt(0) }}
                </div>
              </div>
              <div class="ml-5">
                <h3 class="text-lg font-medium text-gray-900">{{ profile.nama_lengkap || authStore.user?.name }}</h3>
                <p class="text-sm text-gray-500">NIS: {{ profile.nis }}</p>
                <p class="text-sm text-gray-500">Kelas: {{ profile.kelas?.nama_kelas }}</p>
                <p class="text-sm text-gray-500">Jurusan: {{ profile.kelas?.jurusan?.nama_jurusan }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Academic Summary -->
      <div class="mt-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Rata-rata Nilai -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Rata-rata Nilai</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.rata_rata_nilai || '-' }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Kehadiran -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Kehadiran</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.persentase_kehadiran || 0 }}%</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Tugas Selesai -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Tugas Selesai</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.tugas_selesai || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Mapel -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

      <!-- Recent Grades & Schedule -->
      <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Grades -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Nilai Terbaru</h3>
            <div class="mt-6 flow-root">
              <ul v-if="recentGrades.length" class="-my-5 divide-y divide-gray-200">
                <li v-for="nilai in recentGrades" :key="nilai.id" class="py-4">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">
                        {{ nilai.mata_pelajaran?.nama_mapel }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ nilai.jenis_nilai }} - {{ formatDate(nilai.tanggal_input) }}
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
                <p class="text-sm text-gray-500">Belum ada nilai</p>
              </div>
            </div>
            <div class="mt-6">
              <router-link to="/siswa/nilai" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                Lihat semua nilai →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Today's Schedule -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Jadwal Hari Ini</h3>
            <div class="mt-6">
              <div v-if="todaySchedule.length" class="space-y-4">
                <div v-for="jadwal in todaySchedule" :key="jadwal.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">
                      {{ jadwal.mata_pelajaran?.nama_mapel }}
                    </p>
                    <p class="text-sm text-gray-500">
                      {{ jadwal.guru?.nama_lengkap }}
                    </p>
                  </div>
                  <div class="flex-shrink-0 text-right">
                    <p class="text-sm font-medium text-gray-900">{{ jadwal.jam_mulai }} - {{ jadwal.jam_selesai }}</p>
                    <p class="text-sm text-gray-500">{{ jadwal.ruangan }}</p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-sm text-gray-500">Tidak ada jadwal hari ini</p>
              </div>
            </div>
            <div class="mt-6">
              <router-link to="/siswa/jadwal" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                Lihat jadwal lengkap →
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
            to="/siswa/nilai"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Lihat Nilai
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Cek nilai dan pencapaian akademik
              </p>
            </div>
          </router-link>

          <router-link 
            to="/siswa/rapor"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-green-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Rapor
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Download rapor semester
              </p>
            </div>
          </router-link>

          <router-link 
            to="/siswa/jadwal"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-yellow-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Jadwal Pelajaran
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Lihat jadwal pelajaran minggu ini
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

const profile = ref({})
const stats = ref({})
const recentGrades = ref([])
const todaySchedule = ref([])
const loading = ref(true)

const fetchDashboardData = async () => {
  try {
    const response = await axios.get('/dashboard/siswa')
    profile.value = response.data.profile || {}
    stats.value = response.data.stats || {}
    recentGrades.value = response.data.recent_grades || []
    todaySchedule.value = response.data.today_schedule || []
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

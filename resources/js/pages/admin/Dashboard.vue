<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Dashboard Administrator
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Selamat datang di Pengolahan Nilai Rapor Kurikulum Merdeka
          </p>
        </div>
      </div>

      <!-- Stats -->
      <div class="mt-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Total Siswa -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <router-link to="/admin/siswa" class="font-medium text-blue-700 hover:text-blue-900">
                  Lihat semua siswa
                </router-link>
              </div>
            </div>
          </div>

          <!-- Total Guru -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Guru</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.total_guru || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <router-link to="/admin/guru" class="font-medium text-green-700 hover:text-green-900">
                  Lihat semua guru
                </router-link>
              </div>
            </div>
          </div>

          <!-- Total Kelas -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Kelas</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.total_kelas || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <router-link to="/admin/kelas" class="font-medium text-yellow-700 hover:text-yellow-900">
                  Lihat semua kelas
                </router-link>
              </div>
            </div>
          </div>

          <!-- Total Mata Pelajaran -->
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
            <div class="bg-gray-50 px-5 py-3">
              <div class="text-sm">
                <router-link to="/admin/mata-pelajaran" class="font-medium text-purple-700 hover:text-purple-900">
                  Lihat mata pelajaran
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tahun Ajaran Aktif -->
      <div class="mt-8" v-if="stats.tahun_ajaran_aktif">
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">
                Tahun Ajaran Aktif
              </h3>
              <div class="mt-2 text-sm text-blue-700">
                <p>{{ stats.tahun_ajaran_aktif.full_description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Students -->
      <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Students -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Siswa Baru Terdaftar</h3>
            <div class="mt-6 flow-root">
              <ul v-if="stats.recent_students?.length" class="-my-5 divide-y divide-gray-200">
                <li v-for="siswa in stats.recent_students" :key="siswa.id" class="py-4">
                  <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                      <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium">
                        {{ siswa.nama_lengkap.charAt(0) }}
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">
                        {{ siswa.nama_lengkap }}
                      </p>
                      <p class="text-sm text-gray-500 truncate">
                        {{ siswa.nis }} - {{ siswa.kelas?.nama_kelas }}
                      </p>
                    </div>
                    <div class="flex-shrink-0">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ siswa.status }}
                      </span>
                    </div>
                  </div>
                </li>
              </ul>
              <div v-else class="text-center py-4">
                <p class="text-sm text-gray-500">Belum ada siswa baru</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Class Capacity -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Kapasitas Kelas</h3>
            <div class="mt-6">
              <div v-if="stats.class_capacity?.length" class="space-y-4">
                <div v-for="kelas in stats.class_capacity.slice(0, 5)" :key="kelas.kelas" class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center justify-between text-sm">
                      <span class="font-medium text-gray-900">{{ kelas.kelas }}</span>
                      <span class="text-gray-500">{{ kelas.siswa_count }}/{{ kelas.kapasitas }}</span>
                    </div>
                    <div class="mt-1">
                      <div class="bg-gray-200 rounded-full h-2">
                        <div 
                          class="h-2 rounded-full" 
                          :class="kelas.percentage >= 90 ? 'bg-red-600' : kelas.percentage >= 75 ? 'bg-yellow-600' : 'bg-green-600'"
                          :style="{ width: `${Math.min(kelas.percentage, 100)}%` }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-sm text-gray-500">Belum ada data kelas</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <router-link 
            to="/admin/siswa/create"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Tambah Siswa
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Daftarkan siswa baru ke sistem
              </p>
            </div>
          </router-link>

          <router-link 
            to="/admin/guru/create"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-green-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Tambah Guru
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Daftarkan guru baru ke sistem
              </p>
            </div>
          </router-link>

          <router-link 
            to="/admin/kelas"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-yellow-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Kelola Kelas
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Atur kelas dan wali kelas
              </p>
            </div>
          </router-link>

          <router-link 
            to="/admin/wali-kelas"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-indigo-50 text-indigo-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Wali Kelas
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Kelola penugasan wali kelas
              </p>
            </div>
          </router-link>

          <router-link 
            to="/admin/mata-pelajaran"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-purple-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                Mata Pelajaran
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Kelola data mata pelajaran
              </p>
            </div>
          </router-link>

          <router-link 
            to="/admin/ekstrakurikuler"
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
                Ekstrakurikuler
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Kelola data ekstrakurikuler
              </p>
            </div>
          </router-link>

          <router-link 
            to="/admin/pkl"
            class="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-orange-500 rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-orange-50 text-orange-600 ring-4 ring-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium">
                <span class="absolute inset-0" aria-hidden="true"></span>
                PKL
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Kelola data PKL siswa
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
import { useToast } from 'vue-toastification'

const toast = useToast()
const stats = ref({})
const loading = ref(true)

const fetchStats = async () => {
  try {
    const response = await axios.get('/dashboard/admin')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
    toast.error('Gagal mengambil data statistik')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>
<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Nilai SAS
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Cek penilaian SAS (Sumatif Akhir Semester) siswa berdasarkan mata pelajaran
          </p>
        </div>
      </div>

      <!-- Filter Kelas dan Semester -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Filter Kelas
            </label>
            <select
              v-model="selectedKelasId"
              @change="onKelasChange"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Pilih Kelas</option>
              <option
                v-for="k in kelas"
                :key="k.id"
                :value="k.id"
              >
                {{ k.nama_kelas }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Filter Semester
            </label>
            <select
              v-model="selectedSemester"
              @change="fetchData"
              :disabled="!selectedKelasId"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            >
              <option value="">Pilih Semester</option>
              <option value="1">Semester 1</option>
              <option value="2">Semester 2</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Empty State - No Filter -->
      <div v-else-if="!selectedKelasId || !selectedSemester" class="bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih Kelas dan Semester</h3>
        <p class="mt-1 text-sm text-gray-500">
          Pilih kelas dan semester terlebih dahulu untuk melihat daftar mata pelajaran
        </p>
      </div>

      <!-- Empty State - No Data -->
      <div v-else-if="selectedKelasId && selectedSemester && mataPelajaran.length === 0" class="bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada mata pelajaran</h3>
        <p class="mt-1 text-sm text-gray-500">
          Tidak ada mata pelajaran yang terdaftar untuk kelas dan semester yang dipilih
        </p>
      </div>

      <!-- Mata Pelajaran Table -->
      <div v-if="!loading && !error && selectedKelasId && selectedSemester && mataPelajaran.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
          <h3 class="text-lg font-medium text-gray-900">
            Daftar Mata Pelajaran
          </h3>
          <p class="mt-1 text-sm text-gray-500">
            Mata pelajaran dari kelas yang Anda walikan
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                  No
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nama Mata Pelajaran
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Guru Pengajar
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">
                  Progress Input Nilai
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(mapel, index) in mataPelajaran" :key="mapel.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-center text-gray-500">
                  {{ index + 1 }}
                </td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                  {{ mapel.nama_mapel }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                  {{ mapel.guru?.user?.name || mapel.guru?.nama_lengkap || '-' }}
                </td>
                <td class="px-4 py-3 text-sm">
                  <div v-if="mapel.progress && mapel.progress.progress_data && mapel.progress.progress_data.length > 0" class="flex flex-wrap gap-2">
                    <span
                      v-for="(progressItem, idx) in getSortedProgressData(mapel.progress.progress_data)"
                      :key="idx"
                      :class="progressItem.lengkap 
                        ? 'bg-green-100 text-green-800 border border-green-200' 
                        : 'bg-red-100 text-red-800 border border-red-200'"
                      class="inline-flex items-center justify-center px-2.5 py-1 rounded text-xs font-semibold whitespace-nowrap"
                    >
                      <span class="font-medium mr-1">{{ progressItem.kode_cp }}:</span>
                      {{ progressItem.sudah_input }}/{{ progressItem.total }}
                    </span>
                  </div>
                  <span v-else class="text-gray-400 text-xs">Belum ada CP</span>
                </td>
                <td class="px-4 py-3 text-sm text-center">
                  <button
                    @click="openDetailModal(mapel)"
                    class="text-blue-600 hover:text-blue-900 font-medium"
                  >
                    Lihat Detail
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Detail SAS -->
    <Modal v-model:show="showDetailModal" :title="detailTitle" size="6xl">
      <template #footer>
        <div class="flex justify-end">
          <button
            @click="closeDetailModal"
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
          >
            Tutup
          </button>
        </div>
      </template>

      <!-- Loading State -->
      <div v-if="detailLoading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
      </div>

      <!-- Detail Content -->
      <div v-else-if="detailData">
        <!-- Mata Pelajaran Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-blue-800">
                <span class="font-medium">Mata Pelajaran:</span> {{ detailData.mata_pelajaran?.nama_mapel }}
              </p>
              <p class="text-sm text-blue-800 mt-1">
                <span class="font-medium">Kode:</span> {{ detailData.mata_pelajaran?.kode_mapel || '-' }}
              </p>
            </div>
            <div>
              <p class="text-sm text-blue-800">
                <span class="font-medium">KKM:</span> {{ detailData.mata_pelajaran?.kkm || '-' }}
              </p>
              <p class="text-sm text-blue-800 mt-1">
                <span class="font-medium">Tahun Ajaran:</span> {{ detailData.tahun_ajaran?.tahun }} - Semester {{ detailData.tahun_ajaran?.semester }}
              </p>
            </div>
          </div>
        </div>

        <!-- Siswa Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                  No
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  NIS
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nama Siswa
                </th>
                <th
                  v-for="cp in detailCPList"
                  :key="cp.kode_cp"
                  class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24"
                >
                  {{ cp.kode_cp }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(siswa, index) in detailData.siswa" :key="siswa.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-center text-gray-500">
                  {{ index + 1 }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">
                  {{ siswa.nis }}
                </td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                  {{ siswa.nama_lengkap }}
                </td>
                <td
                  v-for="cp in detailCPList"
                  :key="cp.kode_cp"
                  class="px-4 py-3 text-sm text-center"
                >
                  <span
                    v-if="siswa.nilai_by_cp && siswa.nilai_by_cp[cp.kode_cp]"
                    :class="getNilaiColor(siswa.nilai_by_cp[cp.kode_cp].nilai)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ siswa.nilai_by_cp[cp.kode_cp].nilai || '-' }}
                  </span>
                  <span v-else class="text-gray-400 text-xs">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import Modal from '../../../../components/ui/Modal.vue'

const toast = useToast()

// Data
const mataPelajaran = ref([])
const kelas = ref([])

// State
const loading = ref(false)
const error = ref(null)
const showDetailModal = ref(false)
const detailLoading = ref(false)
const detailData = ref(null)
const selectedMapel = ref(null)
const selectedKelasId = ref('')
const selectedSemester = ref('')

// Computed
const detailTitle = computed(() => {
  if (selectedMapel.value) {
    return `Detail SAS - ${selectedMapel.value.nama_mapel}`
  }
  return 'Detail SAS'
})

const detailCPList = computed(() => {
  if (!detailData.value || !detailData.value.capaian_pembelajaran) {
    return []
  }
  
  // Sort: CP first (alphabetically), then SAS at the end
  return detailData.value.capaian_pembelajaran.sort((a, b) => {
    if (a.kode_cp === 'SAS') return 1
    if (b.kode_cp === 'SAS') return -1
    return a.kode_cp.localeCompare(b.kode_cp)
  })
})

// Get all unique CP headers from all mata pelajaran
const cpHeaders = computed(() => {
  const cpSet = new Set()
  
  mataPelajaran.value.forEach(mapel => {
    if (mapel.progress && mapel.progress.progress_data) {
      mapel.progress.progress_data.forEach(item => {
        // Only include SAS and CP (exclude STS)
        if (item.kode_cp !== 'STS') {
          cpSet.add(item.kode_cp)
        }
      })
    }
  })
  
  // Sort: CP first (CP-1, CP-2, CP-3, etc.), then SAS at the end
  const sorted = Array.from(cpSet).sort((a, b) => {
    if (a === 'SAS') return 1  // SAS goes to end
    if (b === 'SAS') return -1  // SAS goes to end
    return a.localeCompare(b)   // Other CP sorted alphabetically
  })
  
  return sorted.map(kode_cp => ({ kode_cp }))
})

// Methods
const getProgressForCP = (mapel, kodeCP) => {
  if (!mapel.progress || !mapel.progress.progress_data) {
    return null
  }
  
  return mapel.progress.progress_data.find(item => item.kode_cp === kodeCP) || null
}

const getSortedProgressData = (progressData) => {
  if (!progressData || !Array.isArray(progressData)) {
    return []
  }
  
  // Sort: CP first (alphabetically), then SAS at the end
  return [...progressData].sort((a, b) => {
    if (a.kode_cp === 'SAS') return 1  // SAS goes to end
    if (b.kode_cp === 'SAS') return -1  // SAS goes to end
    return a.kode_cp.localeCompare(b.kode_cp)   // Other CP sorted alphabetically
  })
}

// Methods
const fetchKelas = async () => {
  try {
    const response = await axios.get('/wali-kelas/cek-penilaian/sas')
    kelas.value = response.data.kelas || []
    
    if (kelas.value.length === 0) {
      error.value = 'Anda belum ditugaskan sebagai wali kelas'
    }
  } catch (err) {
    console.error('Failed to fetch kelas:', err)
    error.value = err.response?.data?.message || 'Gagal mengambil data kelas'
    toast.error(error.value)
  }
}

const onKelasChange = () => {
  selectedSemester.value = ''
  mataPelajaran.value = []
  fetchData()
}

const fetchData = async () => {
  // Only fetch if kelas and semester are selected
  if (!selectedKelasId.value || !selectedSemester.value) {
    mataPelajaran.value = []
    return
  }
  
  try {
    loading.value = true
    error.value = null
    
    const params = {
      kelas_id: selectedKelasId.value,
      semester: selectedSemester.value
    }
    
    const response = await axios.get('/wali-kelas/cek-penilaian/sas', { params })
    
    mataPelajaran.value = response.data.mata_pelajaran || []
  } catch (err) {
    console.error('Failed to fetch data:', err)
    error.value = err.response?.data?.message || 'Gagal mengambil data mata pelajaran'
    toast.error(error.value)
  } finally {
    loading.value = false
  }
}

const openDetailModal = async (mapel) => {
  if (!selectedSemester.value) {
    toast.warning('Pilih semester terlebih dahulu')
    return
  }
  
  selectedMapel.value = mapel
  showDetailModal.value = true
  detailData.value = null
  
  try {
    detailLoading.value = true
    const response = await axios.get(`/wali-kelas/cek-penilaian/sas/${mapel.id}`, {
      params: {
        semester: selectedSemester.value
      }
    })
    detailData.value = response.data
  } catch (err) {
    console.error('Failed to fetch detail:', err)
    toast.error(err.response?.data?.message || 'Gagal mengambil data detail')
    closeDetailModal()
  } finally {
    detailLoading.value = false
  }
}

const closeDetailModal = () => {
  showDetailModal.value = false
  detailData.value = null
  selectedMapel.value = null
}

const getNilaiColor = (nilai) => {
  if (!nilai || nilai === null || nilai === '') {
    return 'bg-gray-100 text-gray-800'
  }
  
  const kkm = detailData.value?.mata_pelajaran?.kkm || 0
  const nilaiNum = parseFloat(nilai)
  
  if (nilaiNum < kkm) {
    return 'bg-red-100 text-red-800'
  } else {
    return 'bg-green-100 text-green-800'
  }
}

// Lifecycle
onMounted(() => {
  fetchKelas()
})
</script>

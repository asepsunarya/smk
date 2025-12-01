<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Nilai Saya
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Lihat nilai dan prestasi akademik Anda
          </p>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
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
                  <dd class="text-lg font-medium text-gray-900">{{ summary.rata_rata || '-' }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Nilai Tertinggi</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ summary.nilai_tertinggi || '-' }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

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
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Mata Pelajaran</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ summary.total_mapel || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Ranking Kelas</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ summary.ranking || '-' }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <FormField
            v-model="selectedTahunAjaran"
            type="select"
            label="Tahun Ajaran"
            placeholder="Pilih Tahun Ajaran"
            :options="tahunAjaranOptions"
            option-value="id"
            option-label="full_description"
            @update:model-value="fetchNilai"
          />
          <FormField
            v-model="selectedSemester"
            type="select"
            label="Semester"
            placeholder="Pilih Semester"
            :options="semesterOptions"
            @update:model-value="fetchNilai"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data nilai...</p>
      </div>

      <!-- Grades Table -->
      <div v-else class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
          <h3 class="text-lg font-medium text-gray-900">
            Daftar Nilai {{ selectedTahunAjaranText }} - {{ selectedSemesterText }}
          </h3>
        </div>

        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>No</th>
                <th>Mata Pelajaran</th>
                <th>Guru</th>
                <th class="text-center">Nilai Harian</th>
                <th class="text-center">UTS</th>
                <th class="text-center">UAS</th>
                <th class="text-center">Nilai Akhir</th>
                <th class="text-center">Predikat</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(nilai, index) in nilaiData" :key="nilai.id" class="hover:bg-gray-50">
                <td class="text-center">{{ index + 1 }}</td>
                <td>
                  <div class="text-sm font-medium text-gray-900">{{ nilai.mata_pelajaran?.nama_mapel }}</div>
                  <div class="text-sm text-gray-500">{{ nilai.mata_pelajaran?.kode_mapel }}</div>
                </td>
                <td>
                  <div class="text-sm text-gray-900">{{ nilai.guru?.nama_lengkap }}</div>
                </td>
                <td class="text-center">
                  <span class="text-sm font-medium">{{ nilai.nilai_harian || '-' }}</span>
                </td>
                <td class="text-center">
                  <span class="text-sm font-medium">{{ nilai.nilai_uts || '-' }}</span>
                </td>
                <td class="text-center">
                  <span class="text-sm font-medium">{{ nilai.nilai_uas || '-' }}</span>
                </td>
                <td class="text-center">
                  <span class="text-sm font-medium">{{ nilai.nilai_akhir || '-' }}</span>
                </td>
                <td class="text-center">
                  <span :class="getPredicateColor(nilai.nilai_akhir)" 
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getPredicate(nilai.nilai_akhir) }}
                  </span>
                </td>
                <td>
                  <span class="text-sm text-gray-500">{{ nilai.catatan || '-' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="nilaiData.length === 0" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada nilai</h3>
          <p class="mt-1 text-sm text-gray-500">Nilai untuk periode ini belum diinput oleh guru.</p>
        </div>
      </div>

      <!-- Progress Chart -->
      <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Progress Nilai per Mata Pelajaran</h3>
        <div class="space-y-4">
          <div v-for="progress in progressData" :key="progress.mapel" class="flex items-center">
            <div class="w-32 text-sm font-medium text-gray-900">{{ progress.mapel }}</div>
            <div class="flex-1 mx-4">
              <div class="bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all duration-300"
                  :class="getProgressColor(progress.nilai)"
                  :style="{ width: `${Math.min(progress.nilai, 100)}%` }"
                ></div>
              </div>
            </div>
            <div class="w-16 text-sm font-medium text-gray-900 text-right">{{ progress.nilai }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import FormField from '../../../components/ui/FormField.vue'

const toast = useToast()

// Data
const nilaiData = ref([])
const tahunAjaranOptions = ref([])
const summary = ref({})
const loading = ref(true)

// State
const selectedTahunAjaran = ref('')
const selectedSemester = ref('')

// Options
const semesterOptions = [
  { value: '', label: 'Semua Semester' },
  { value: '1', label: 'Semester 1' },
  { value: '2', label: 'Semester 2' }
]

// Computed
const selectedTahunAjaranText = computed(() => {
  const tahun = tahunAjaranOptions.value.find(t => t.id == selectedTahunAjaran.value)
  return tahun?.full_description || 'Semua Tahun'
})

const selectedSemesterText = computed(() => {
  const semester = semesterOptions.find(s => s.value === selectedSemester.value)
  return semester?.label || 'Semua Semester'
})

const progressData = computed(() => {
  return nilaiData.value.map(nilai => ({
    mapel: nilai.mata_pelajaran?.nama_mapel || '',
    nilai: nilai.nilai_akhir || 0
  }))
})

// Methods
const fetchTahunAjaran = async () => {
  try {
    const response = await axios.get('/admin/tahun-ajaran')
    tahunAjaranOptions.value = response.data.data
    
    // Set current active year as default
    const activeYear = tahunAjaranOptions.value.find(t => t.is_active)
    if (activeYear) {
      selectedTahunAjaran.value = activeYear.id
    }
  } catch (error) {
    console.error('Failed to fetch tahun ajaran:', error)
  }
}

const fetchNilai = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (selectedTahunAjaran.value) params.append('tahun_ajaran_id', selectedTahunAjaran.value)
    if (selectedSemester.value) params.append('semester', selectedSemester.value)
    
    const response = await axios.get(`/siswa/nilai?${params}`)
    nilaiData.value = response.data.data
    summary.value = response.data.summary || {}
  } catch (error) {
    toast.error('Gagal mengambil data nilai')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const getPredicate = (nilai) => {
  if (!nilai) return '-'
  if (nilai >= 90) return 'A'
  if (nilai >= 80) return 'B'
  if (nilai >= 70) return 'C'
  if (nilai >= 60) return 'D'
  return 'E'
}

const getPredicateColor = (nilai) => {
  const predicate = getPredicate(nilai)
  const colors = {
    A: 'bg-green-100 text-green-800',
    B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800',
    D: 'bg-orange-100 text-orange-800',
    E: 'bg-red-100 text-red-800'
  }
  return colors[predicate] || 'bg-gray-100 text-gray-800'
}

const getProgressColor = (nilai) => {
  if (nilai >= 90) return 'bg-green-600'
  if (nilai >= 80) return 'bg-blue-600'
  if (nilai >= 70) return 'bg-yellow-600'
  if (nilai >= 60) return 'bg-orange-600'
  return 'bg-red-600'
}

// Lifecycle
onMounted(async () => {
  await fetchTahunAjaran()
  await fetchNilai()
})
</script>

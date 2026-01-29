<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Cetak Rapor Hasil P5
        </h2>
        <p class="mt-1 text-sm text-gray-500">
          Cetak rapor hasil P5 (Projek Penguatan Profil Pelajar Pancasila) siswa
        </p>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <FormField
            v-model="filters.kelas_id"
            type="select"
            label="Kelas"
            placeholder="Pilih Kelas"
            :options="kelasFilterOptions"
            option-value="id"
            option-label="full_name"
            @update:model-value="fetchRapor"
          />
          <FormField
            v-model="filters.tahun_ajaran_id"
            type="select"
            label="Tahun Ajaran"
            placeholder="Pilih Tahun Ajaran"
            :options="tahunAjaranFilterOptions"
            option-value="id"
            option-label="label"
            @update:model-value="fetchRapor"
          />
        </div>
        <div class="mt-4">
          <FormField
            v-model="filters.search"
            type="text"
            label="Cari Siswa"
            placeholder="Cari berdasarkan nama atau NIS"
            @update:model-value="handleSearch"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data rapor...</p>
      </div>

      <!-- Rapor List -->
      <div v-else class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Siswa
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kelas
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Projek P5
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="siswa in (siswaList?.data || [])" :key="siswa.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ siswa.nama_lengkap }}</div>
                <div class="text-sm text-gray-500">NIS: {{ siswa.nis }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ siswa.kelas?.nama_kelas }}</div>
                <div class="text-sm text-gray-500">{{ siswa.kelas?.jurusan?.nama_jurusan }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  <span class="font-medium">{{ siswa.total_p5_projects || 0 }}</span> Projek
                </div>
                <div v-if="siswa.p5_projects && siswa.p5_projects.length > 0" class="text-xs text-gray-500 mt-1">
                  <div v-for="(project, idx) in siswa.p5_projects.slice(0, 2)" :key="project.id">
                    • {{ project.tema }}
                  </div>
                  <div v-if="siswa.p5_projects.length > 2" class="text-gray-400">
                    +{{ siswa.p5_projects.length - 2 }} lainnya
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="previewRapor(siswa)"
                  class="text-blue-600 hover:text-blue-900 mr-4"
                >
                  Preview
                </button>
                <button
                  @click="downloadRapor(siswa)"
                  class="text-green-600 hover:text-green-900"
                >
                  Download
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="siswaList && siswaList.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="changePage(siswaList.current_page - 1)"
                :disabled="siswaList.current_page === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Previous
              </button>
              <button
                @click="changePage(siswaList.current_page + 1)"
                :disabled="siswaList.current_page === siswaList.last_page"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Menampilkan
                  <span class="font-medium">{{ siswaList.from || 0 }}</span>
                  sampai
                  <span class="font-medium">{{ siswaList.to || 0 }}</span>
                  dari
                  <span class="font-medium">{{ siswaList.total || 0 }}</span>
                  hasil
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="changePage(siswaList.current_page - 1)"
                    :disabled="siswaList.current_page === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    Previous
                  </button>
                  <button
                    v-for="page in getPageNumbers()"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                      page === siswaList.current_page
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <button
                    @click="changePage(siswaList.current_page + 1)"
                    :disabled="siswaList.current_page === siswaList.last_page"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                  >
                    Next
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Modal -->
      <Modal v-model:show="showPreviewModal" title="Preview Rapor Hasil P5" size="xl">
        <div v-if="previewData" class="space-y-6">
          <!-- Student Info -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-3">Data Siswa</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium">Nama:</span> {{ previewData.siswa?.nama_lengkap }}
              </div>
              <div>
                <span class="font-medium">NIS:</span> {{ previewData.siswa?.nis }}
              </div>
              <div>
                <span class="font-medium">Kelas:</span> {{ previewData.siswa?.kelas?.nama_kelas }}
              </div>
              <div>
                <span class="font-medium">Jurusan:</span> {{ previewData.siswa?.kelas?.jurusan?.nama_jurusan }}
              </div>
            </div>
          </div>

          <!-- P5 Projects -->
          <div v-if="previewData.p5_projects && previewData.p5_projects.length > 0">
            <h3 class="text-lg font-semibold mb-3">Projek P5</h3>
            <div v-for="project in previewData.p5_projects" :key="project.id" class="mb-6 border rounded-lg p-4">
              <div class="mb-3">
                <h4 class="text-md font-semibold text-gray-900">{{ project.judul || project.tema }}</h4>
                <p v-if="project.deskripsi" class="text-sm text-gray-600 mt-1">{{ project.deskripsi }}</p>
                <div class="text-xs text-gray-500 mt-2">
                  <span v-if="project.dimensi">Dimensi: {{ project.dimensi }}</span>
                  <span v-if="project.tahun_ajaran" class="ml-4">Tahun Ajaran: {{ project.tahun_ajaran.label }}</span>
                  <span v-if="project.koordinator?.nama" class="ml-4">Koordinator: {{ project.koordinator.nama }}</span>
                </div>
              </div>

              <!-- Elemen & Sub Elemen dengan Predikat -->
              <div v-if="project.elemen_sub && project.elemen_sub.length > 0" class="mt-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Predikat per Sub Elemen:</h5>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-12">No</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Elemen</th>
                      <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sub Elemen</th>
                      <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Predikat</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(es, idx) in project.elemen_sub" :key="idx">
                      <td class="px-3 py-2 text-sm text-gray-900">{{ idx + 1 }}</td>
                      <td class="px-3 py-2 text-sm text-gray-900">{{ es.elemen }}</td>
                      <td class="px-3 py-2 text-sm text-gray-900">{{ es.sub_elemen }}</td>
                      <td class="px-3 py-2 text-center">
                        <span :class="getNilaiBadgeClass(es.predikat)" class="px-2 py-1 rounded text-xs font-medium">
                          {{ es.predikat_label || es.predikat || '-' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="text-sm text-gray-500 mt-2">Belum ada nilai sub elemen.</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            Belum ada data projek P5
          </div>
        </div>

        <template #footer>
          <button
            @click="showPreviewModal = false"
            class="btn btn-secondary mr-3"
          >
            Tutup
          </button>
          <button
            v-if="previewData"
            @click="downloadRaporFromPreview"
            class="btn btn-primary"
          >
            Download PDF
          </button>
        </template>
      </Modal>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import Modal from '../../../../components/ui/Modal.vue'
import FormField from '../../../../components/ui/FormField.vue'

const toast = useToast()

// Data
const siswaList = ref(null)
const loading = ref(false)
const showPreviewModal = ref(false)
const previewData = ref(null)
const selectedSiswa = ref(null)

// Filters
const filters = reactive({
  search: '',
  kelas_id: '',
  tahun_ajaran_id: ''
})

// Options
const kelasFilterOptions = ref([])
const tahunAjaranFilterOptions = ref([])

// Methods
const fetchKelas = async () => {
  try {
    const response = await axios.get('/lookup/kelas')
    kelasFilterOptions.value = response.data.map(k => ({
      ...k,
      full_name: `${k.nama_kelas} - ${k.jurusan?.nama_jurusan || ''}`
    }))
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
  }
}

const fetchTahunAjaran = async () => {
  try {
    const response = await axios.get('/lookup/tahun-ajaran')
    tahunAjaranFilterOptions.value = response.data.map(ta => ({
      ...ta,
      label: ta.tahun
    }))
  } catch (error) {
    console.error('Failed to fetch tahun ajaran:', error)
  }
}

const fetchRapor = async (page = 1) => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    
    if (filters.search) params.append('search', filters.search)
    if (filters.kelas_id) params.append('kelas_id', filters.kelas_id)
    if (filters.tahun_ajaran_id) params.append('tahun_ajaran_id', filters.tahun_ajaran_id)
    params.append('page', page)
    params.append('per_page', 15)
    
    const response = await axios.get(`/admin/cetak-rapor/p5?${params.toString()}`)
    siswaList.value = response.data
  } catch (error) {
    toast.error('Gagal mengambil data rapor P5')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  fetchRapor(1)
}

const changePage = (page) => {
  if (page >= 1 && page <= siswaList.value.last_page) {
    fetchRapor(page)
  }
}

const getPageNumbers = () => {
  if (!siswaList.value) return []
  const current = siswaList.value.current_page
  const last = siswaList.value.last_page
  const pages = []
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 3) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 2) {
      pages.push(1)
      pages.push('...')
      for (let i = last - 4; i <= last; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    }
  }
  
  return pages.filter(p => p !== '...' || pages.indexOf(p) !== pages.lastIndexOf(p))
}

const previewRapor = async (siswa) => {
  try {
    selectedSiswa.value = siswa
    const params = new URLSearchParams()
    if (filters.tahun_ajaran_id) {
      params.append('tahun_ajaran_id', filters.tahun_ajaran_id)
    }
    
    const response = await axios.get(`/admin/cetak-rapor/p5/${siswa.id}/preview?${params.toString()}`)
    previewData.value = response.data
    showPreviewModal.value = true
  } catch (error) {
    toast.error('Gagal mengambil detail rapor P5')
    console.error(error)
  }
}

const downloadRapor = async (siswa) => {
  try {
    const params = new URLSearchParams()
    if (filters.tahun_ajaran_id) {
      params.append('tahun_ajaran_id', filters.tahun_ajaran_id)
    }
    const response = await axios.get(`/admin/cetak-rapor/p5/${siswa.id}/download?${params.toString()}`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `rapor-p5-${siswa.nis}-${(siswa.nama_lengkap || 'siswa').replace(/\s+/g, '-')}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    toast.success('Rapor P5 berhasil diunduh')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal mengunduh rapor P5')
    console.error(error)
  }
}

const downloadRaporFromPreview = () => {
  if (selectedSiswa.value) {
    downloadRapor(selectedSiswa.value)
  }
}

const getNilaiBadgeClass = (nilai) => {
  const classes = {
    'MB': 'bg-red-100 text-red-800',
    'SB': 'bg-yellow-100 text-yellow-800',
    'BSH': 'bg-blue-100 text-blue-800',
    'SAB': 'bg-green-100 text-green-800'
  }
  return classes[nilai] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchKelas()
  fetchTahunAjaran()
  fetchRapor()
})
</script>


<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Rapor Pembelajaran
        </h2>
        <p class="mt-1 text-sm text-gray-500">
          Cetak dan Download Nilai Rapor Pembelajaran
        </p>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <FormField
            v-model="filters.kelas_id"
            type="select"
            label="Kelas"
            placeholder="Pilih Kelas"
            :options="kelasOptions"
            option-value="id"
            option-label="full_name"
            @update:model-value="onFiltersChange"
          />
          <FormField
            v-model="filters.semester"
            type="select"
            label="Semester"
            placeholder="Pilih Semester"
            :options="semesterOptions"
            option-value="value"
            option-label="label"
            @update:model-value="onFiltersChange"
          />
          <FormField
            v-model="filters.jenis"
            type="select"
            label="Periode"
            placeholder="Pilih Periode"
            :options="periodeOptions"
            option-value="value"
            option-label="label"
            @update:model-value="onFiltersChange"
          />
          <FormField
            v-model="filters.titimangsa"
            type="date"
            label="Titimangsa Rapor"
            :required="true"
          />
        </div>
        <p class="mt-2 text-xs text-gray-500">
          Pilih periode STS (Tengah Semester) atau SAS (Akhir Semester). Cetak hanya tersedia jika nilai periode tersebut sudah diisi, semua nilai ≥ KKM, dan rapor sudah disetujui KS.
        </p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
      </div>

      <!-- Table -->
      <div v-else-if="filtersReady" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  No
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none"
                  @click="toggleSortNama"
                >
                  <span class="inline-flex items-center">
                    Nama Siswa
                    <svg
                      v-if="sortNama === 'asc'"
                      class="ml-1 w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                    <svg
                      v-else-if="sortNama === 'desc'"
                      class="ml-1 w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <span v-else class="ml-1 opacity-50">↕</span>
                  </span>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  NISN
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  NIS
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cetak Rapor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cetak Transkrip
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, idx) in sortedList" :key="row.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ idx + 1 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ row.nama_lengkap }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ row.nisn }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ row.nis }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    type="button"
                    :disabled="!row.can_cetak_rapor"
                    :class="[
                      'inline-flex items-center px-3 py-1.5 border text-sm font-medium rounded-md',
                      row.can_cetak_rapor
                        ? 'bg-blue-600 text-white border-transparent hover:bg-blue-700'
                        : 'bg-gray-200 text-gray-400 cursor-not-allowed border-gray-200'
                    ]"
                    @click="row.can_cetak_rapor && cetakRapor(row)"
                  >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak
                  </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700"
                    @click="cetakTranskrip(row)"
                  >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty -->
        <div
          v-if="!loading && list.length === 0"
          class="px-6 py-12 text-center text-sm text-gray-500"
        >
          Tidak ada siswa dalam kelas ini.
        </div>
      </div>

      <!-- Prompt: pilih filter -->
      <div
        v-else-if="!loading && !filtersReady"
        class="bg-white shadow rounded-lg p-8 text-center"
      >
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih Kelas, Semester dan Periode</h3>
        <p class="mt-1 text-sm text-gray-500">
          Pilih kelas, semester, dan periode (STS/SAS) untuk menampilkan daftar siswa dan cetak rapor.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import FormField from '../../../../components/ui/FormField.vue'

const toast = useToast()

const loading = ref(false)
const list = ref([])
const kelasOptions = ref([])
const sortNama = ref(null) // null | 'asc' | 'desc'

const filters = ref({
  kelas_id: '',
  semester: '',
  jenis: '',
  titimangsa: new Date().toISOString().split('T')[0]
})

const semesterOptions = [
  { value: '1', label: 'Semester 1' },
  { value: '2', label: 'Semester 2' }
]

const periodeOptions = [
  { value: 'sts', label: 'Tengah Semester (STS)' },
  { value: 'sas', label: 'Akhir Semester (SAS)' }
]

const filtersReady = computed(() => {
  return !!filters.value.kelas_id && !!filters.value.semester && !!filters.value.jenis
})

const sortedList = computed(() => {
  const data = [...list.value]
  if (sortNama.value === 'asc') {
    return data.sort((a, b) => (a.nama_lengkap || '').localeCompare(b.nama_lengkap || ''))
  }
  if (sortNama.value === 'desc') {
    return data.sort((a, b) => (b.nama_lengkap || '').localeCompare(a.nama_lengkap || ''))
  }
  return data
})

function toggleSortNama() {
  if (sortNama.value === null) sortNama.value = 'asc'
  else if (sortNama.value === 'asc') sortNama.value = 'desc'
  else sortNama.value = null
}

function onFiltersChange() {
  if (filtersReady.value) {
    fetchList()
  } else {
    list.value = []
  }
}

async function fetchKelas() {
  try {
    const res = await axios.get('/wali-kelas/cetak-rapor/kelas')
    kelasOptions.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    console.error('fetch kelas:', e)
    toast.error('Gagal memuat data kelas')
  }
}

async function fetchList() {
  if (!filters.value.kelas_id || !filters.value.semester || !filters.value.jenis) return
  loading.value = true
  try {
    const params = new URLSearchParams({
      kelas_id: filters.value.kelas_id,
      semester: filters.value.semester,
      jenis: filters.value.jenis
    })
    const res = await axios.get(`/wali-kelas/cetak-rapor/belajar?${params}`)
    list.value = res.data?.data ?? []
  } catch (e) {
    console.error('fetch list:', e)
    toast.error(e.response?.data?.message || 'Gagal memuat data siswa')
    list.value = []
  } finally {
    loading.value = false
  }
}

async function cetakRapor(row) {
  if (!row.can_cetak_rapor) return
  try {
    const params = new URLSearchParams({
      semester: filters.value.semester,
      jenis: filters.value.jenis
    })
    if (filters.value.titimangsa) params.append('titimangsa', filters.value.titimangsa)
    const res = await axios.get(`/wali-kelas/cetak-rapor/belajar/${row.id}/download?${params}`, {
      responseType: 'blob'
    })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `rapor-${row.nis}-${row.nama_lengkap || 'siswa'}-${filters.value.jenis}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
    toast.success('Rapor berhasil diunduh')
  } catch (e) {
    let msg = 'Gagal mengunduh rapor'
    if (e.response?.status === 403) {
      msg = 'Cetak rapor tidak dapat dilakukan. Terdapat nilai di bawah KKM.'
    } else if (e.response?.data?.message && typeof e.response.data.message === 'string') {
      msg = e.response.data.message
    } else if (e.response?.data instanceof Blob) {
      try {
        const text = await e.response.data.text()
        const j = JSON.parse(text)
        if (j?.message) msg = j.message
      } catch (_) {}
    }
    toast.error(msg)
  }
}

async function cetakTranskrip(row) {
  if (!filters.value.titimangsa) {
    toast.error('Harap pilih titimangsa rapor terlebih dahulu')
    return
  }
  try {
    const params = new URLSearchParams({
      semester: filters.value.semester,
      jenis: filters.value.jenis || 'sas',
      titimangsa: filters.value.titimangsa
    })
    const res = await axios.get(`/wali-kelas/cetak-rapor/belajar/${row.id}/transkrip?${params}`, {
      responseType: 'blob'
    })
    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    window.open(url, '_blank')
    toast.success('Transkrip dibuka di tab baru')
  } catch (e) {
    const msg = e.response?.data?.message || 'Gagal cetak transkrip'
    if (e.response?.data instanceof Blob) {
      try {
        const text = await e.response.data.text()
        const j = JSON.parse(text)
        toast.error(j?.message || msg)
      } catch (_) {
        toast.error(msg)
      }
    } else {
      toast.error(typeof msg === 'string' ? msg : 'Gagal cetak transkrip')
    }
  }
}

onMounted(() => {
  fetchKelas()
})
</script>

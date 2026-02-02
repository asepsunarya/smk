<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Cetak Legger Nilai
        </h2>
        <p class="mt-1 text-sm text-gray-500">
          Cetak legger (buku nilai) per kelas
        </p>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <FormField
            v-model="filters.kelas_id"
            type="select"
            label="Kelas"
            placeholder="Pilih Kelas"
            :options="kelasFilterOptions"
            option-value="id"
            option-label="full_name"
            required
            @update:model-value="loadLegger"
          />
          <FormField
            v-model="filters.tahun_ajaran_id"
            type="select"
            label="Tahun Ajaran"
            placeholder="Pilih Tahun Ajaran"
            :options="tahunAjaranFilterOptions"
            option-value="id"
            option-label="label"
            @update:model-value="loadLegger"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data legger...</p>
      </div>

      <!-- Legger Table -->
      <div v-else-if="leggerData" class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Header Info -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">
                Legger Nilai - {{ leggerData.kelas?.nama_kelas }}
              </h3>
              <p class="text-sm text-gray-600">
                {{ leggerData.kelas?.jurusan?.nama_jurusan }}
                <span v-if="leggerData.tahun_ajaran">
                  • {{ leggerData.tahun_ajaran.label }}
                </span>
              </p>
            </div>
            <button
              @click="downloadLegger"
              class="btn btn-primary"
              :disabled="downloading"
            >
              <svg v-if="downloading" class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              {{ downloading ? 'Mengunduh...' : 'Download' }}
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 border-r border-gray-200">
                  No
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-12 bg-gray-50 z-10 border-r border-gray-200 min-w-[150px]">
                  Nama Siswa
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 z-10 border-r border-gray-200 min-w-[100px]">
                  NISN
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 z-10 border-r border-gray-200 min-w-[100px]">
                  NIS
                </th>
                <th
                  v-for="mapel in leggerData.mata_pelajaran"
                  :key="mapel.id"
                  class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]"
                  :title="mapel.nama_mapel"
                >
                  <div class="transform -rotate-45 origin-center whitespace-nowrap">
                    {{ mapel.nama_mapel }}
                  </div>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in leggerData.legger" :key="item.siswa.id">
                <td class="px-4 py-3 text-center text-sm text-gray-900 sticky left-0 bg-white z-10 border-r border-gray-200">
                  {{ index + 1 }}
                </td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900 sticky left-12 bg-white z-10 border-r border-gray-200">
                  {{ item.siswa.nama_lengkap }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 bg-white z-10 border-r border-gray-200">
                  {{ item.siswa.nisn || '-' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 bg-white z-10 border-r border-gray-200">
                  {{ item.siswa.nis }}
                </td>
                <td
                  v-for="mapel in leggerData.mata_pelajaran"
                  :key="mapel.id"
                  class="px-3 py-3 text-center text-sm"
                >
                  <span
                    v-if="getNilaiLegger(item.nilai, mapel.id)"
                    :class="getNilaiColor(getNilaiLegger(item.nilai, mapel.id))"
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                  >
                    {{ getNilaiLegger(item.nilai, mapel.id) }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Summary -->
        <div v-if="leggerData.legger && leggerData.legger.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div class="text-sm text-gray-600">
            Total Siswa: <span class="font-medium">{{ leggerData.legger.length }}</span>
            <span class="ml-4">
              Total Mata Pelajaran: <span class="font-medium">{{ leggerData.mata_pelajaran?.length || 0 }}</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih Kelas</h3>
        <p class="mt-1 text-sm text-gray-500">Pilih kelas untuk menampilkan legger nilai</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import FormField from '../../../../components/ui/FormField.vue'

const toast = useToast()

// Data
const leggerData = ref(null)
const loading = ref(false)
const downloading = ref(false)

// Filters
const filters = reactive({
  kelas_id: '',
  tahun_ajaran_id: ''
})

// Options (kelas hanya yang diwalikan)
const kelasFilterOptions = ref([])
const tahunAjaranFilterOptions = ref([])

// Methods
const fetchKelas = async () => {
  try {
    const response = await axios.get('/wali-kelas/cetak-rapor/kelas')
    kelasFilterOptions.value = response.data.map(k => ({
      ...k,
      full_name: k.full_name || `${k.nama_kelas} - ${k.jurusan?.nama_jurusan || ''}`
    }))
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
  }
}

const formatTahunAjaranLabel = (ta) => {
  if (!ta) return ''
  const semester = ta.semester != null ? ta.semester : ''
  return semester ? `${ta.tahun} - Semester ${semester}` : ta.tahun
}

const fetchTahunAjaran = async () => {
  try {
    const response = await axios.get('/lookup/tahun-ajaran')
    tahunAjaranFilterOptions.value = response.data.map(ta => ({
      ...ta,
      label: formatTahunAjaranLabel(ta)
    }))
  } catch (error) {
    console.error('Failed to fetch tahun ajaran:', error)
  }
}

const loadLegger = async () => {
  if (!filters.kelas_id) {
    leggerData.value = null
    return
  }

  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.tahun_ajaran_id) {
      params.append('tahun_ajaran_id', filters.tahun_ajaran_id)
    }

    const response = await axios.get(`/wali-kelas/cetak-rapor/legger/${filters.kelas_id}?${params.toString()}`)
    leggerData.value = {
      ...response.data,
      tahun_ajaran: response.data.tahun_ajaran ? {
        ...response.data.tahun_ajaran,
        label: formatTahunAjaranLabel(response.data.tahun_ajaran)
      } : null
    }
  } catch (error) {
    toast.error('Gagal mengambil data legger')
    console.error(error)
    leggerData.value = null
  } finally {
    loading.value = false
  }
}

const getNilaiLegger = (nilaiMap, mapelId) => {
  if (!nilaiMap || !nilaiMap[mapelId]) return null
  const nilai = nilaiMap[mapelId]
  return nilai.nilai_akhir || nilai.nilai_rapor || null
}

const getNilaiColor = (nilai) => {
  if (!nilai) return 'bg-gray-100 text-gray-800'

  const numNilai = parseFloat(nilai)
  if (numNilai >= 85) return 'bg-green-100 text-green-800'
  if (numNilai >= 70) return 'bg-blue-100 text-blue-800'
  if (numNilai >= 60) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const downloadLegger = async () => {
  if (!filters.kelas_id) {
    toast.warning('Pilih kelas terlebih dahulu')
    return
  }

  try {
    downloading.value = true
    const params = new URLSearchParams()
    if (filters.tahun_ajaran_id) {
      params.append('tahun_ajaran_id', filters.tahun_ajaran_id)
    }

    const response = await axios.get(`/wali-kelas/cetak-rapor/legger/${filters.kelas_id}/download?${params.toString()}`, {
      responseType: 'blob'
    })

    const blob = response.data
    const contentType = response.headers['content-type'] || 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    if (contentType.includes('application/json')) {
      const text = await blob.text()
      const json = JSON.parse(text)
      toast.error(json?.message || 'Gagal mengunduh legger')
      return
    }

    const url = window.URL.createObjectURL(new Blob([blob], { type: contentType }))
    const link = document.createElement('a')
    link.href = url
    const disposition = response.headers['content-disposition']
    let filename = `Legger-Nilai-${leggerData.value?.kelas?.nama_kelas || filters.kelas_id}.xlsx`
    if (disposition && disposition.includes('filename=')) {
      const match = disposition.match(/filename\*?=(?:UTF-8'')?"?([^";\n]+)"?/i) || disposition.match(/filename="?([^";\n]+)"?/i)
      if (match) filename = match[1].trim()
    }
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    toast.success('Legger berhasil diunduh')
  } catch (error) {
    toast.error('Gagal mengunduh legger')
    console.error(error)
  } finally {
    downloading.value = false
  }
}

onMounted(() => {
  fetchKelas()
  fetchTahunAjaran()
})
</script>

<style scoped>
.sticky {
  position: sticky;
}

.left-0 {
  left: 0;
}

.left-12 {
  left: 3rem;
}

.left-48 {
  left: 12rem;
}

.transform {
  transform: rotate(-45deg);
}

.origin-center {
  transform-origin: center;
}
</style>
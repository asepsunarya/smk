<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data UKK (Uji Kompetensi Keahlian)"
        description="Kelola data UKK siswa"
        :data="ukk"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data UKK"
        empty-description="Mulai dengan menambahkan data UKK baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah UKK
          </button>
        </template>

        <template #filters>
          <FormField
            v-model="filters.jurusan_id"
            type="select"
            placeholder="Pilih Jurusan"
            :options="jurusanFilterOptions"
            option-value="id"
            option-label="nama_jurusan"
            @update:model-value="fetchUkk"
          />
          <FormField
            v-model="filters.tahun_ajaran_id"
            type="select"
            placeholder="Pilih Tahun Ajaran"
            :options="tahunAjaranFilterOptions"
            option-value="id"
            option-label="label"
            @update:model-value="fetchUkk"
          />
          <FormField
            v-model="filters.predikat"
            type="select"
            placeholder="Pilih Predikat"
            :options="predikatOptions"
            @update:model-value="fetchUkk"
          />
        </template>

        <template #cell-siswa="{ item }">
          <div class="text-sm">
            <div class="font-medium text-gray-900">{{ item.siswa?.nama_lengkap || '-' }}</div>
            <div class="text-gray-500 text-xs">NIS: {{ item.siswa?.nis || '-' }}</div>
          </div>
        </template>

        <template #cell-jurusan="{ item }">
          <div class="text-sm text-gray-900">
            {{ item.jurusan?.nama_jurusan || '-' }}
          </div>
        </template>

        <template #cell-kelas="{ item }">
          <div class="text-sm text-gray-900">
            {{ item.kelas?.nama_kelas || '-' }}
          </div>
        </template>

        <template #cell-nama_du_di="{ item }">
          <div class="text-sm text-gray-900">
            {{ item.nama_du_di || '-' }}
          </div>
        </template>

        <template #cell-tanggal_ujian="{ item }">
          <div class="text-sm text-gray-900">
            {{ formatDate(item.tanggal_ujian) }}
          </div>
        </template>

        <template #cell-nilai="{ item }">
          <div class="text-sm">
            <div class="text-gray-900">
              Teori: {{ item.nilai_teori !== null ? item.nilai_teori : '-' }}
            </div>
            <div class="text-gray-900">
              Praktek: {{ item.nilai_praktek !== null ? item.nilai_praktek : '-' }}
            </div>
            <div class="font-medium text-gray-900 mt-1">
              Akhir: {{ item.nilai_akhir !== null ? item.nilai_akhir : '-' }}
            </div>
          </div>
        </template>

        <template #cell-predikat="{ item }">
          <span :class="getPredikatBadge(item.predikat)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ item.predikat || 'Belum Dinilai' }}
          </span>
        </template>

        <template #cell-penguji_internal="{ item }">
          <div class="text-sm">
            <div class="text-gray-900">{{ item.penguji_internal?.nama_lengkap || '-' }}</div>
            <div class="text-gray-500 text-xs">{{ item.penguji_eksternal ? `Eksternal: ${item.penguji_eksternal}` : '' }}</div>
          </div>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="editUkk(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="deleteUkk(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit UKK' : 'Tambah UKK'" size="lg">
        <form @submit.prevent="submitForm" id="ukk-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.tahun_ajaran_id"
              type="select"
              label="Tahun Ajaran"
              placeholder="Pilih tahun ajaran"
              :options="tahunAjaranOptions.map(ta => ({ value: ta.id, label: `${ta.tahun} - Semester ${ta.semester}` }))"
              required
              :error="errors.tahun_ajaran_id"
            />
            <FormField
              v-model="form.jurusan_id"
              type="select"
              label="Jurusan"
              placeholder="Pilih jurusan"
              :options="jurusanOptions"
              option-value="id"
              option-label="nama_jurusan"
              required
              :error="errors.jurusan_id"
              @update:model-value="onJurusanChange"
            />
          </div>

          <FormField
            v-model="form.kelas_id"
            type="select"
            label="Kelas"
            placeholder="Pilih kelas"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            required
            :error="errors.kelas_id"
            :disabled="!form.jurusan_id"
            @update:model-value="onKelasChange"
          />

          <FormField
            v-model="form.siswa_id"
            type="select"
            label="Siswa"
            placeholder="Pilih siswa"
            :options="siswaOptions"
            option-value="id"
            option-label="label"
            required
            :error="errors.siswa_id"
            :disabled="!form.kelas_id"
          />

          <FormField
            v-model="form.nama_du_di"
            type="text"
            label="Nama DU/DI"
            placeholder="Masukkan nama DU/DI"
            :error="errors.nama_du_di"
          />

          <FormField
            v-model="form.tanggal_ujian"
            type="date"
            label="Tanggal Ujian"
            required
            :error="errors.tanggal_ujian"
          />

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.nilai_teori"
              type="number"
              label="Nilai Teori"
              placeholder="0-100"
              min="0"
              max="100"
              :error="errors.nilai_teori"
            />
            <FormField
              v-model="form.nilai_praktek"
              type="number"
              label="Nilai Praktek"
              placeholder="0-100"
              min="0"
              max="100"
              :error="errors.nilai_praktek"
            />
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.penguji_internal_id"
              type="select"
              label="Penguji Internal"
              placeholder="Pilih penguji internal"
              :options="guruOptions"
              option-value="id"
              option-label="nama_lengkap"
              required
              :error="errors.penguji_internal_id"
            />
            <FormField
              v-model="form.penguji_eksternal"
              type="text"
              label="Penguji Eksternal"
              placeholder="Masukkan nama penguji eksternal (opsional)"
              :error="errors.penguji_eksternal"
            />
          </div>

          <div v-if="form.nilai_teori && form.nilai_praktek" class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-gray-700">
              <div class="font-medium mb-1">Perhitungan Nilai:</div>
              <div>Nilai Akhir: <span class="font-semibold">{{ calculateNilaiAkhir() }}</span> (30% Teori + 70% Praktek)</div>
              <div>Predikat: <span class="font-semibold">{{ calculatePredikat() }}</span></div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeForm" class="btn btn-secondary">
              Batal
            </button>
            <button type="submit" :disabled="submitting" class="btn btn-primary">
              {{ submitting ? 'Menyimpan...' : (isEditing ? 'Perbarui' : 'Simpan') }}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import DataTable from '../../../components/ui/DataTable.vue'
import Modal from '../../../components/ui/Modal.vue'
import FormField from '../../../components/ui/FormField.vue'

const toast = useToast()

const ukk = ref([])
const loading = ref(false)
const showForm = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const errors = ref({})

const filters = ref({
  jurusan_id: '',
  tahun_ajaran_id: '',
  predikat: ''
})

const form = ref({
  id: null,
  siswa_id: '',
  jurusan_id: '',
  kelas_id: '',
  nama_du_di: '',
  tanggal_ujian: '',
  nilai_teori: '',
  nilai_praktek: '',
  penguji_internal_id: '',
  penguji_eksternal: '',
  tahun_ajaran_id: ''
})

const jurusanOptions = ref([])
const kelasOptions = ref([])
const siswaOptions = ref([])
const guruOptions = ref([])
const tahunAjaranOptions = ref([])

const columns = [
  { key: 'siswa', label: 'Siswa' },
  { key: 'jurusan', label: 'Jurusan' },
  { key: 'kelas', label: 'Kelas' },
  { key: 'nama_du_di', label: 'DU/DI' },
  { key: 'tanggal_ujian', label: 'Tanggal Ujian' },
  { key: 'nilai', label: 'Nilai' },
  { key: 'predikat', label: 'Predikat' },
  { key: 'penguji_internal', label: 'Penguji' },
  { key: 'actions', label: 'Aksi' }
]

const predikatOptions = [
  { value: '', label: 'Semua Predikat' },
  { value: 'Kompeten', label: 'Kompeten' },
  { value: 'Belum Kompeten', label: 'Belum Kompeten' }
]

const jurusanFilterOptions = computed(() => [
  { id: '', nama_jurusan: 'Semua Jurusan' },
  ...jurusanOptions.value
])

const tahunAjaranFilterOptions = computed(() => [
  { id: '', label: 'Semua Tahun Ajaran' },
  ...tahunAjaranOptions.value.map(ta => ({
    id: ta.id,
    label: `${ta.tahun} - Semester ${ta.semester}`
  }))
])

const calculateNilaiAkhir = () => {
  if (!form.value.nilai_teori || !form.value.nilai_praktek) return '-'
  const nilai = (parseFloat(form.value.nilai_teori) * 0.3) + (parseFloat(form.value.nilai_praktek) * 0.7)
  return nilai.toFixed(2)
}

const calculatePredikat = () => {
  const nilai = calculateNilaiAkhir()
  if (nilai === '-') return '-'
  return parseFloat(nilai) >= 75 ? 'Kompeten' : 'Belum Kompeten'
}

const getPredikatBadge = (predikat) => {
  if (!predikat) return 'bg-gray-100 text-gray-800'
  return predikat === 'Kompeten' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID')
}

const fetchUkk = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.jurusan_id) params.append('jurusan_id', filters.value.jurusan_id)
    if (filters.value.tahun_ajaran_id) params.append('tahun_ajaran_id', filters.value.tahun_ajaran_id)
    if (filters.value.predikat) params.append('predikat', filters.value.predikat)
    if (filters.value.search) params.append('search', filters.value.search)

    const response = await axios.get(`/admin/ukk?${params.toString()}`)
    // Handle paginated response - extract the data array
    if (response.data.data && Array.isArray(response.data.data)) {
      ukk.value = response.data.data
    } else if (Array.isArray(response.data)) {
      ukk.value = response.data
    } else {
      ukk.value = []
    }
  } catch (error) {
    console.error('Error fetching UKK:', error)
    toast.error('Gagal mengambil data UKK')
    ukk.value = []
  } finally {
    loading.value = false
  }
}

const fetchJurusan = async () => {
  try {
    const response = await axios.get('/lookup/jurusan')
    jurusanOptions.value = response.data
  } catch (error) {
    console.error('Error fetching jurusan:', error)
  }
}

const fetchKelas = async (jurusanId) => {
  if (!jurusanId) {
    kelasOptions.value = []
    return
  }
  try {
    const response = await axios.get('/admin/kelas', {
      params: {
        per_page: 100
      }
    })
    const allKelas = response.data.data || response.data
    // Filter kelas by jurusan and tingkat 12 only
    kelasOptions.value = allKelas.filter(k => k.jurusan_id == jurusanId && k.tingkat == '12')
  } catch (error) {
    console.error('Error fetching kelas:', error)
  }
}

const fetchSiswa = async (kelasId) => {
  if (!kelasId) {
    siswaOptions.value = []
    return
  }
  try {
    const response = await axios.get('/admin/siswa', {
      params: { 
        status: 'aktif',
        per_page: 1000
      }
    })
    const allSiswa = response.data.data || response.data
    // Filter siswa by kelas
    const filteredSiswa = allSiswa.filter(s => s.kelas_id == kelasId)
    siswaOptions.value = filteredSiswa.map(s => ({
      id: s.id,
      label: `${s.nama_lengkap} (${s.nis})`
    }))
  } catch (error) {
    console.error('Error fetching siswa:', error)
  }
}

const fetchGuru = async () => {
  try {
    const response = await axios.get('/lookup/guru')
    guruOptions.value = response.data
  } catch (error) {
    console.error('Error fetching guru:', error)
  }
}

const fetchTahunAjaran = async () => {
  try {
    const response = await axios.get('/admin/tahun-ajaran', {
      params: {
        per_page: 100
      }
    })
    if (response.data.data) {
      tahunAjaranOptions.value = response.data.data
    } else if (Array.isArray(response.data)) {
      tahunAjaranOptions.value = response.data
    }
  } catch (error) {
    console.error('Error fetching tahun ajaran:', error)
  }
}

const onJurusanChange = () => {
  form.value.kelas_id = ''
  form.value.siswa_id = ''
  kelasOptions.value = []
  siswaOptions.value = []
  if (form.value.jurusan_id) {
    fetchKelas(form.value.jurusan_id)
  }
}

const onKelasChange = () => {
  form.value.siswa_id = ''
  if (form.value.kelas_id) {
    fetchSiswa(form.value.kelas_id)
  } else {
    siswaOptions.value = []
  }
}

const handleSearch = (searchTerm) => {
  filters.value.search = searchTerm
  fetchUkk()
}

const editUkk = async (item) => {
  isEditing.value = true
  form.value = {
    id: item.id,
    siswa_id: item.siswa_id,
    jurusan_id: item.jurusan_id,
    kelas_id: item.kelas_id || item.kelas?.id || '',
    nama_du_di: item.nama_du_di || '',
    tanggal_ujian: item.tanggal_ujian ? item.tanggal_ujian.split('T')[0] : '',
    nilai_teori: item.nilai_teori || '',
    nilai_praktek: item.nilai_praktek || '',
    penguji_internal_id: item.penguji_internal_id,
    penguji_eksternal: item.penguji_eksternal || '',
    tahun_ajaran_id: item.tahun_ajaran_id
  }
  if (item.jurusan_id) {
    await fetchKelas(item.jurusan_id)
    if (item.kelas_id || item.kelas?.id) {
      await fetchSiswa(item.kelas_id || item.kelas?.id)
    }
  }
  showForm.value = true
}

const deleteUkk = async (item) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus UKK untuk siswa ${item.siswa?.nama_lengkap}?`)) {
    return
  }

  try {
    await axios.delete(`/admin/ukk/${item.id}`)
    toast.success('UKK berhasil dihapus')
    fetchUkk()
  } catch (error) {
    console.error('Error deleting UKK:', error)
    toast.error(error.response?.data?.message || 'Gagal menghapus UKK')
  }
}

const closeForm = () => {
  showForm.value = false
  isEditing.value = false
  errors.value = {}
  form.value = {
    id: null,
    siswa_id: '',
    jurusan_id: '',
    kelas_id: '',
    nama_du_di: '',
    tanggal_ujian: '',
    nilai_teori: '',
    nilai_praktek: '',
    penguji_internal_id: '',
    penguji_eksternal: '',
    tahun_ajaran_id: ''
  }
  kelasOptions.value = []
  siswaOptions.value = []
}

const submitForm = async () => {
  errors.value = {}
  submitting.value = true

  try {
    const formData = {
      ...form.value,
      nilai_teori: form.value.nilai_teori ? parseInt(form.value.nilai_teori) : null,
      nilai_praktek: form.value.nilai_praktek ? parseInt(form.value.nilai_praktek) : null
    }

    if (isEditing.value) {
      await axios.put(`/admin/ukk/${form.value.id}`, formData)
      toast.success('UKK berhasil diperbarui')
    } else {
      await axios.post('/admin/ukk', formData)
      toast.success('UKK berhasil ditambahkan')
    }

    closeForm()
    fetchUkk()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error(error.response?.data?.message || 'Gagal menyimpan UKK')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchUkk()
  fetchJurusan()
  fetchGuru()
  fetchTahunAjaran()
})
</script>


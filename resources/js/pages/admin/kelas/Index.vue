<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data Kelas"
        description="Kelola data kelas sekolah"
        :data="kelas"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data kelas"
        empty-description="Mulai dengan menambahkan kelas baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Kelas
          </button>
        </template>

        <template #filters>
          <FormField
            v-model="filters.jurusan_id"
            type="select"
            placeholder="Pilih Jurusan"
            :options="jurusanOptions"
            option-value="id"
            option-label="nama_jurusan"
            @update:model-value="fetchKelas"
          />
          <FormField
            v-model="filters.tingkat"
            type="select"
            placeholder="Pilih Tingkat"
            :options="tingkatOptions"
            @update:model-value="fetchKelas"
          />
        </template>

        <template #cell-nama_kelas="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.tingkat }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.nama_kelas }}</div>
              <div class="text-sm text-gray-500">{{ item.jurusan?.nama_jurusan }}</div>
            </div>
          </div>
        </template>

        <template #cell-jurusan="{ item }">
          <span v-if="item.jurusan" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ item.jurusan.nama_jurusan }}
          </span>
          <span v-else class="text-gray-400">-</span>
        </template>

        <template #cell-wali_kelas="{ item }">
          <div v-if="item.wali_kelas" class="text-sm text-gray-900">
            {{ item.wali_kelas.name }}
          </div>
          <span v-else class="text-gray-400">Belum ditetapkan</span>
        </template>

        <template #cell-kapasitas="{ item }">
          <div class="text-sm">
            <div class="font-medium text-gray-900">
              {{ item.active_siswa_count || 0 }} / {{ item.kapasitas }}
            </div>
            <div class="text-xs text-gray-500">
              <span v-if="item.is_full" class="text-red-600">Penuh</span>
              <span v-else class="text-green-600">Tersedia: {{ item.available_capacity || 0 }}</span>
            </div>
          </div>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="viewSiswa(item)" class="text-green-600 hover:text-green-900" title="Lihat Siswa">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </button>
            <button @click="editKelas(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <!-- <button @click="assignWali(item)" class="text-purple-600 hover:text-purple-900" title="Assign Wali Kelas">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </button> -->
            <button @click="deleteKelas(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Kelas' : 'Tambah Kelas'" size="lg">
        <form @submit.prevent="submitForm" id="kelas-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.nama_kelas"
              label="Nama Kelas"
              placeholder="Contoh: X RPL 1"
              required
              :error="errors.nama_kelas"
            />
            <FormField
              v-model="form.tingkat"
              type="select"
              label="Tingkat"
              placeholder="Pilih tingkat"
              :options="tingkatOptions"
              required
              :error="errors.tingkat"
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
            />
            <FormField
              v-model="form.kapasitas"
              type="number"
              label="Kapasitas"
              placeholder="Masukkan kapasitas (1-50)"
              required
              :error="errors.kapasitas"
              :min="1"
              :max="50"
            />
            <!-- <FormField
              v-model="form.wali_kelas_id"
              type="select"
              label="Wali Kelas"
              placeholder="Pilih wali kelas (opsional)"
              :options="waliKelasOptions"
              option-value="id"
              option-label="name"
              :error="errors.wali_kelas_id"
            /> -->
          </div>
        </form>

        <template #footer>
          <button type="submit" form="kelas-form" :disabled="submitting" class="btn btn-primary">
            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button type="button" @click="closeForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Assign Wali Kelas Modal -->
      <Modal v-model:show="showAssignWali" title="Assign Wali Kelas" size="sm">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Tetapkan wali kelas untuk <strong>{{ selectedKelas?.nama_kelas }}</strong>
          </p>
          <!-- <FormField
            v-model="assignWaliForm.wali_kelas_id"
            type="select"
            label="Wali Kelas"
            placeholder="Pilih wali kelas"
            :options="waliKelasOptions"
            option-value="id"
            option-label="name"
            required
            :error="assignWaliErrors.wali_kelas_id"
          /> -->
        </div>

        <template #footer>
          <button @click="submitAssignWali" :disabled="assigningWali" class="btn btn-primary">
            {{ assigningWali ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button @click="showAssignWali = false" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- View Siswa Modal -->
      <Modal v-model:show="showSiswaModal" :title="`Siswa di ${selectedKelas?.nama_kelas}`" size="lg">
        <div v-if="loadingSiswa" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-500">Memuat data siswa...</p>
        </div>
        <div v-else-if="siswaList.length === 0" class="p-8 text-center">
          <p class="text-gray-500">Belum ada siswa di kelas ini</p>
        </div>
        <div v-else class="space-y-2">
          <div v-for="siswa in siswaList" :key="siswa.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-medium">
                {{ siswa.nama_lengkap.charAt(0) }}
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ siswa.nama_lengkap }}</div>
                <div class="text-sm text-gray-500">NIS: {{ siswa.nis }}</div>
              </div>
            </div>
            <span :class="getStatusBadge(siswa.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
              {{ siswa.status }}
            </span>
          </div>
        </div>

        <template #footer>
          <button @click="showSiswaModal = false" class="btn btn-secondary">Tutup</button>
        </template>
      </Modal>

      <!-- Confirmation Dialogs -->
      <ConfirmDialog
        v-model:show="showDeleteConfirm"
        title="Hapus Kelas"
        :message="`Apakah Anda yakin ingin menghapus kelas ${selectedKelas?.nama_kelas}?`"
        confirm-text="Ya, Hapus"
        type="error"
        :loading="deleting"
        @confirm="confirmDelete"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import DataTable from '../../../components/ui/DataTable.vue'
import Modal from '../../../components/ui/Modal.vue'
import FormField from '../../../components/ui/FormField.vue'
import ConfirmDialog from '../../../components/ui/ConfirmDialog.vue'

const toast = useToast()

// Data
const kelas = ref([])
const jurusanOptions = ref([])
const waliKelasOptions = ref([])
const siswaList = ref([])
const loading = ref(true)
const loadingSiswa = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const assigningWali = ref(false)

// Form state
const showForm = ref(false)
const showDeleteConfirm = ref(false)
const showAssignWali = ref(false)
const showSiswaModal = ref(false)
const isEditing = ref(false)
const selectedKelas = ref(null)

// Form data
const form = reactive({
  nama_kelas: '',
  tingkat: '',
  jurusan_id: '',
  wali_kelas_id: '',
  kapasitas: 30
})

const assignWaliForm = reactive({
  wali_kelas_id: ''
})

const errors = ref({})
const assignWaliErrors = ref({})

// Filters
const filters = reactive({
  search: '',
  jurusan_id: '',
  tingkat: ''
})

// Table columns
const columns = [
  { key: 'nama_kelas', label: 'Nama Kelas', sortable: true },
  { key: 'jurusan', label: 'Jurusan', sortable: true },
  { key: 'tingkat', label: 'Tingkat', sortable: true },
  { key: 'wali_kelas', label: 'Wali Kelas' },
  { key: 'kapasitas', label: 'Siswa / Kapasitas' }
]

// Options
const tingkatOptions = [
  { value: '10', label: 'Kelas 10' },
  { value: '11', label: 'Kelas 11' },
  { value: '12', label: 'Kelas 12' }
]

// Methods
const fetchKelas = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.jurusan_id) params.append('jurusan_id', filters.jurusan_id)
    if (filters.tingkat) params.append('tingkat', filters.tingkat)
    params.append('per_page', 100)
    
    const response = await axios.get(`/admin/kelas?${params}`)
    if (response.data.data) {
      kelas.value = response.data.data
    } else if (Array.isArray(response.data)) {
      kelas.value = response.data
    } else {
      kelas.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data kelas')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchJurusan = async () => {
  try {
    const response = await axios.get('/lookup/jurusan')
    jurusanOptions.value = response.data.map(j => ({
      id: j.id,
      nama_jurusan: `${j.kode_jurusan} - ${j.nama_jurusan}`,
      ...j
    }))
  } catch (error) {
    console.error('Failed to fetch jurusan:', error)
  }
}

const fetchWaliKelas = async () => {
  try {
    const response = await axios.get('/admin/user', {
      params: {
        role: 'wali_kelas',
        is_active: 'true',
        per_page: 100
      }
    })
    if (response.data.data) {
      waliKelasOptions.value = response.data.data.map(u => ({
        id: u.id,
        name: u.name,
        ...u
      }))
    }
  } catch (error) {
    console.error('Failed to fetch wali kelas:', error)
  }
}

const fetchSiswa = async (kelasId) => {
  try {
    loadingSiswa.value = true
    const response = await axios.get(`/admin/kelas/${kelasId}/siswa`, {
      params: {
        status: 'aktif',
        per_page: 100
      }
    })
    if (response.data.data) {
      siswaList.value = response.data.data
    } else if (Array.isArray(response.data)) {
      siswaList.value = response.data
    } else {
      siswaList.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data siswa')
    console.error(error)
  } finally {
    loadingSiswa.value = false
  }
}

const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (key === 'kapasitas') {
      form[key] = 30
    } else {
      form[key] = ''
    }
  })
  errors.value = {}
  isEditing.value = false
  selectedKelas.value = null
}

const closeForm = () => {
  showForm.value = false
  resetForm()
}

const editKelas = (kelasItem) => {
  isEditing.value = true
  selectedKelas.value = kelasItem
  form.nama_kelas = kelasItem.nama_kelas || ''
  form.tingkat = kelasItem.tingkat || ''
  form.jurusan_id = kelasItem.jurusan_id || ''
  form.wali_kelas_id = kelasItem.wali_kelas_id || ''
  form.kapasitas = kelasItem.kapasitas || 30
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    const url = isEditing.value ? `/admin/kelas/${selectedKelas.value.id}` : '/admin/kelas'
    const method = isEditing.value ? 'put' : 'post'
    
    const payload = { ...form }
    if (!payload.wali_kelas_id) {
      payload.wali_kelas_id = null
    }
    
    await axios[method](url, payload)
    
    toast.success(`Kelas berhasil ${isEditing.value ? 'diperbarui' : 'ditambahkan'}`)
    closeForm()
    fetchKelas()
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      toast.error('Validasi gagal. Periksa kembali data yang diinput.')
    } else {
      toast.error(error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data')
    }
  } finally {
    submitting.value = false
  }
}

const deleteKelas = (kelasItem) => {
  selectedKelas.value = kelasItem
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/kelas/${selectedKelas.value.id}`)
    toast.success('Kelas berhasil dihapus')
    showDeleteConfirm.value = false
    fetchKelas()
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menghapus kelas'
    toast.error(message)
  } finally {
    deleting.value = false
  }
}

const assignWali = (kelasItem) => {
  selectedKelas.value = kelasItem
  assignWaliForm.wali_kelas_id = kelasItem.wali_kelas_id || ''
  assignWaliErrors.value = {}
  showAssignWali.value = true
}

const submitAssignWali = async () => {
  try {
    assigningWali.value = true
    assignWaliErrors.value = {}
    
    await axios.post(`/admin/kelas/${selectedKelas.value.id}/assign-wali`, assignWaliForm)
    toast.success('Wali kelas berhasil ditetapkan')
    showAssignWali.value = false
    fetchKelas()
  } catch (error) {
    if (error.response?.status === 422) {
      assignWaliErrors.value = error.response.data.errors || {}
    } else {
      toast.error(error.response?.data?.message || 'Gagal menetapkan wali kelas')
    }
  } finally {
    assigningWali.value = false
  }
}

const viewSiswa = async (kelasItem) => {
  selectedKelas.value = kelasItem
  showSiswaModal.value = true
  await fetchSiswa(kelasItem.id)
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchKelas()
}

const getStatusBadge = (status) => {
  const badges = {
    aktif: 'bg-green-100 text-green-800',
    lulus: 'bg-blue-100 text-blue-800',
    pindah: 'bg-yellow-100 text-yellow-800',
    keluar: 'bg-red-100 text-red-800'
  }
  return badges[status] || 'bg-gray-100 text-gray-800'
}

// Lifecycle
onMounted(() => {
  fetchKelas()
  fetchJurusan()
  fetchWaliKelas()
})
</script>

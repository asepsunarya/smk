<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data Jurusan"
        description="Kelola data jurusan/program keahlian sekolah"
        :data="jurusan"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data jurusan"
        empty-description="Mulai dengan menambahkan jurusan baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Jurusan
          </button>
        </template>

        <template #cell-kode_jurusan="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.kode_jurusan.charAt(0) }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.kode_jurusan }}</div>
              <div class="text-sm text-gray-500">{{ item.nama_jurusan }}</div>
            </div>
          </div>
        </template>

        <template #cell-deskripsi="{ item }">
          <div class="text-sm text-gray-900 max-w-md">
            <p class="truncate">{{ item.deskripsi || '-' }}</p>
          </div>
        </template>

        <template #cell-kepala_jurusan="{ item }">
          <div class="text-sm text-gray-900">
            {{ (item.kepalaJurusan || item.kepala_jurusan)?.nama_lengkap || '-' }}
          </div>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <!-- <button @click="viewDetail(item)" class="text-green-600 hover:text-green-900" title="Detail">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button> -->
            <button @click="editJurusan(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="deleteJurusan(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Jurusan' : 'Tambah Jurusan'" size="lg">
        <form @submit.prevent="submitForm" id="jurusan-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.kode_jurusan"
              label="Kode Jurusan"
              placeholder="Contoh: RPL, TKJ, MM"
              required
              :error="errors.kode_jurusan"
              :maxlength="10"
            />
            <FormField
              v-model="form.nama_jurusan"
              label="Nama Jurusan"
              placeholder="Masukkan nama jurusan lengkap"
              required
              :error="errors.nama_jurusan"
            />
          </div>
          <div>
            <FormField
              v-model="form.kepala_jurusan_id"
              type="select"
              label="Kepala Jurusan"
              placeholder="Pilih Kepala Jurusan"
              :options="guruOptions"
              option-value="id"
              option-label="nama_lengkap"
              :error="errors.kepala_jurusan_id"
              required
            />
          </div>
          <div>
            <FormField
              v-model="form.deskripsi"
              type="textarea"
              label="Deskripsi"
              placeholder="Masukkan deskripsi jurusan (opsional)"
              :error="errors.deskripsi"
              :rows="4"
            />
          </div>
        </form>

        <template #footer>
          <button type="submit" form="jurusan-form" :disabled="submitting" class="btn btn-primary">
            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button type="button" @click="closeForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Detail Modal -->
      <Modal v-model:show="showDetailModal" :title="`Detail ${selectedJurusan?.nama_jurusan}`" size="lg">
        <div v-if="loadingDetail" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
        </div>
        <div v-else-if="jurusanDetail" class="space-y-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Jurusan</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Kode Jurusan</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ jurusanDetail.kode_jurusan }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Nama Jurusan</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ jurusanDetail.nama_jurusan }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Kepala Jurusan</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ (jurusanDetail.kepalaJurusan || jurusanDetail.kepala_jurusan)?.nama_lengkap || '-' }}</dd>
              </div>
              <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ jurusanDetail.deskripsi || '-' }}</dd>
              </div>
            </dl>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Statistik</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div class="bg-blue-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-blue-600">Total Kelas</dt>
                <dd class="mt-1 text-2xl font-semibold text-blue-900">{{ jurusanDetail.kelas_count || 0 }}</dd>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-green-600">Siswa Aktif</dt>
                <dd class="mt-1 text-2xl font-semibold text-green-900">{{ jurusanDetail.active_siswa_count || 0 }}</dd>
              </div>
              <div class="bg-purple-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-purple-600">Total Siswa</dt>
                <dd class="mt-1 text-2xl font-semibold text-purple-900">{{ jurusanDetail.siswa?.length || 0 }}</dd>
              </div>
            </dl>
          </div>

          <div v-if="jurusanDetail.kelas && jurusanDetail.kelas.length > 0" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Kelas</h3>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
              <div v-for="kelas in jurusanDetail.kelas" :key="kelas.id" class="bg-gray-50 p-3 rounded-lg">
                <div class="text-sm font-medium text-gray-900">{{ kelas.nama_kelas }}</div>
                <div class="text-xs text-gray-500 mt-1">Tingkat {{ kelas.tingkat }}</div>
              </div>
            </div>
          </div>
        </div>

        <template #footer>
          <button @click="showDetailModal = false" class="btn btn-secondary">Tutup</button>
        </template>
      </Modal>

      <!-- Confirmation Dialogs -->
      <ConfirmDialog
        v-model:show="showDeleteConfirm"
        title="Hapus Jurusan"
        :message="`Apakah Anda yakin ingin menghapus jurusan ${selectedJurusan?.nama_jurusan}?`"
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
const jurusan = ref([])
const jurusanDetail = ref(null)
const loading = ref(true)
const loadingDetail = ref(false)
const submitting = ref(false)
const deleting = ref(false)

// Form state
const showForm = ref(false)
const showDeleteConfirm = ref(false)
const showDetailModal = ref(false)
const isEditing = ref(false)
const selectedJurusan = ref(null)

// Form data
const form = reactive({
  kode_jurusan: '',
  nama_jurusan: '',
  deskripsi: '',
  kepala_jurusan_id: ''
})

// Guru options for Kepala Jurusan
const guruOptions = ref([])

const errors = ref({})

// Filters
const filters = reactive({
  search: ''
})

// Table columns
const columns = [
  { key: 'kode_jurusan', label: 'Kode & Nama Jurusan', sortable: true },
  { key: 'deskripsi', label: 'Deskripsi' },
  { key: 'kepala_jurusan', label: 'Kepala Jurusan' }
]

// Methods
const fetchJurusan = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    params.append('per_page', 100)
    
    const response = await axios.get(`/admin/jurusan?${params}`)
    if (response.data.data) {
      jurusan.value = response.data.data
    } else if (Array.isArray(response.data)) {
      jurusan.value = response.data
    } else {
      jurusan.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data jurusan')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchDetail = async (jurusanId) => {
  try {
    loadingDetail.value = true
    const response = await axios.get(`/admin/jurusan/${jurusanId}`)
    jurusanDetail.value = response.data
  } catch (error) {
    toast.error('Gagal mengambil detail jurusan')
    console.error(error)
  } finally {
    loadingDetail.value = false
  }
}

const fetchGuru = async () => {
  try {
    const response = await axios.get('/lookup/guru')
    guruOptions.value = response.data || []
  } catch (error) {
    console.error('Gagal mengambil data guru', error)
    guruOptions.value = []
  }
}

const resetForm = () => {
  Object.keys(form).forEach(key => {
    form[key] = ''
  })
  errors.value = {}
  isEditing.value = false
  selectedJurusan.value = null
}

const closeForm = () => {
  showForm.value = false
  resetForm()
}

const editJurusan = (jurusanItem) => {
  isEditing.value = true
  selectedJurusan.value = jurusanItem
  form.kode_jurusan = jurusanItem.kode_jurusan || ''
  form.nama_jurusan = jurusanItem.nama_jurusan || ''
  form.deskripsi = jurusanItem.deskripsi || ''
  form.kepala_jurusan_id = jurusanItem.kepala_jurusan_id ?? ''
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    const url = isEditing.value ? `/admin/jurusan/${selectedJurusan.value.id}` : '/admin/jurusan'
    const method = isEditing.value ? 'put' : 'post'
    const payload = {
      ...form,
      kepala_jurusan_id: form.kepala_jurusan_id || null
    }
    await axios[method](url, payload)
    
    toast.success(`Jurusan berhasil ${isEditing.value ? 'diperbarui' : 'ditambahkan'}`)
    closeForm()
    fetchJurusan()
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

const deleteJurusan = (jurusanItem) => {
  selectedJurusan.value = jurusanItem
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/jurusan/${selectedJurusan.value.id}`)
    toast.success('Jurusan berhasil dihapus')
    showDeleteConfirm.value = false
    fetchJurusan()
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menghapus jurusan'
    toast.error(message)
  } finally {
    deleting.value = false
  }
}

const viewDetail = async (jurusanItem) => {
  selectedJurusan.value = jurusanItem
  showDetailModal.value = true
  await fetchDetail(jurusanItem.id)
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchJurusan()
}

// Lifecycle
onMounted(() => {
  fetchJurusan()
  fetchGuru()
})
</script>

<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data PKL (Praktik Kerja Lapangan)"
        description="Kelola data PKL siswa"
        :data="pkl"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data PKL"
        empty-description="Mulai dengan menambahkan data PKL baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah PKL
          </button>
        </template>

        <template #filters>
          <FormField
            v-model="filters.tahun_ajaran_id"
            type="select"
            placeholder="Pilih Tahun Ajaran"
            :options="tahunAjaranFilterOptions"
            option-value="id"
            option-label="label"
            @update:model-value="fetchPkl"
          />
          <FormField
            v-model="filters.status"
            type="select"
            placeholder="Status PKL"
            :options="statusOptions"
            @update:model-value="fetchPkl"
          />
        </template>

        <template #cell-perusahaan="{ item }">
          <div class="text-sm">
            <div class="font-medium text-gray-900">{{ item.nama_perusahaan }}</div>
            <div class="text-gray-500 text-xs line-clamp-1">{{ item.alamat_perusahaan }}</div>
            <div class="text-gray-500 text-xs mt-1">
              Pembimbing: {{ item.pembimbing_perusahaan }}
            </div>
          </div>
        </template>

        <template #cell-pembimbing_sekolah="{ item }">
          <div class="text-sm text-gray-900">
            {{ item.pembimbing_sekolah || '-' }}
          </div>
        </template>

        <template #cell-periode="{ item }">
          <div class="text-sm">
            <div class="text-gray-900">{{ formatDate(item.tanggal_mulai) }} - {{ formatDate(item.tanggal_selesai) }}</div>
            <div class="text-xs text-gray-500">{{ getDuration(item.tanggal_mulai, item.tanggal_selesai) }}</div>
          </div>
        </template>

        <template #cell-status_pkl="{ item }">
          <span :class="getStatusBadge(item.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ item.status }}
          </span>
        </template>


        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="editPkl(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="deletePkl(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit PKL' : 'Tambah PKL'" size="lg">
        <form @submit.prevent="submitForm" id="pkl-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.tahun_ajaran_id"
              type="select"
              label="Tahun Ajaran"
              placeholder="Pilih tahun ajaran"
              :options="tahunAjaranOptions"
              required
              :error="errors.tahun_ajaran_id"
            />
            <div class="sm:col-span-2">
              <FormField
                v-model="form.nama_perusahaan"
                label="Nama Perusahaan"
                placeholder="Masukkan nama perusahaan"
                required
                :error="errors.nama_perusahaan"
              />
            </div>
            <div class="sm:col-span-2">
              <FormField
                v-model="form.alamat_perusahaan"
                type="textarea"
                label="Alamat Perusahaan"
                placeholder="Masukkan alamat perusahaan"
                required
                :error="errors.alamat_perusahaan"
                :rows="2"
              />
            </div>
            <FormField
              v-model="form.pembimbing_perusahaan"
              label="Pembimbing Perusahaan"
              placeholder="Masukkan nama pembimbing perusahaan"
              required
              :error="errors.pembimbing_perusahaan"
            />
            <FormField
              v-model="form.pembimbing_sekolah"
              label="Pembimbing Sekolah"
              placeholder="Masukkan nama pembimbing sekolah"
              required
              :error="errors.pembimbing_sekolah"
            />
            <FormField
              v-model="form.tanggal_mulai"
              type="date"
              label="Tanggal Mulai"
              required
              :error="errors.tanggal_mulai"
            />
            <FormField
              v-model="form.tanggal_selesai"
              type="date"
              label="Tanggal Selesai"
              required
              :error="errors.tanggal_selesai"
            />
          </div>
        </form>

        <template #footer>
          <button type="submit" form="pkl-form" :disabled="submitting" class="btn btn-primary">
            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button type="button" @click="closeForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Confirmation Dialog -->
      <ConfirmDialog
        v-model:show="showDeleteConfirm"
        title="Hapus PKL"
        :message="`Apakah Anda yakin ingin menghapus data PKL untuk ${selectedPkl?.nama_perusahaan}?`"
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
const pkl = ref([])
const loading = ref(true)
const submitting = ref(false)
const deleting = ref(false)

// Form state
const showForm = ref(false)
const isEditing = ref(false)
const selectedPkl = ref(null)
const showDeleteConfirm = ref(false)

// Form data
const form = reactive({
  nama_perusahaan: '',
  alamat_perusahaan: '',
  pembimbing_perusahaan: '',
  pembimbing_sekolah: '',
  tanggal_mulai: '',
  tanggal_selesai: '',
  tahun_ajaran_id: ''
})

const errors = ref({})

// Filters
const filters = reactive({
  search: '',
  tahun_ajaran_id: '',
  status: ''
})

// Table columns
const columns = [
  { key: 'perusahaan', label: 'Perusahaan', sortable: true },
  { key: 'pembimbing_sekolah', label: 'Pembimbing Sekolah' },
  { key: 'periode', label: 'Periode' },
  { key: 'status_pkl', label: 'Status' }
]

// Options
const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'belum_mulai', label: 'Belum Mulai' },
  { value: 'sedang_berlangsung', label: 'Sedang Berlangsung' },
  { value: 'selesai', label: 'Selesai' }
]

const tahunAjaranOptions = ref([])
const tahunAjaranFilterOptions = ref([{ id: '', label: 'Semua Tahun Ajaran' }])

// Methods
const fetchPkl = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.tahun_ajaran_id) params.append('tahun_ajaran_id', filters.tahun_ajaran_id)
    if (filters.status) params.append('status', filters.status)
    params.append('per_page', 100)

    const response = await axios.get(`/admin/pkl?${params}`)
    if (response.data.data) {
      pkl.value = response.data.data
    } else if (Array.isArray(response.data)) {
      pkl.value = response.data
    } else {
      pkl.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data PKL')
    console.error(error)
  } finally {
    loading.value = false
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
      tahunAjaranOptions.value = response.data.data.map(ta => ({
        value: ta.id,
        label: `${ta.tahun} - Semester ${ta.semester}`
      }))
      tahunAjaranFilterOptions.value = [
        { id: '', label: 'Semua Tahun Ajaran' },
        ...response.data.data.map(ta => ({
          id: ta.id,
          label: `${ta.tahun} - Semester ${ta.semester}`
        }))
      ]
    }
  } catch (error) {
    console.error('Failed to fetch tahun ajaran:', error)
  }
}

const resetForm = () => {
  form.nama_perusahaan = ''
  form.alamat_perusahaan = ''
  form.pembimbing_perusahaan = ''
  form.pembimbing_sekolah = ''
  form.tanggal_mulai = ''
  form.tanggal_selesai = ''
  form.tahun_ajaran_id = ''
  errors.value = {}
}

const closeForm = () => {
  showForm.value = false
  isEditing.value = false
  selectedPkl.value = null
  resetForm()
}

const editPkl = async (item) => {
  selectedPkl.value = item
  isEditing.value = true
  form.nama_perusahaan = item.nama_perusahaan
  form.alamat_perusahaan = item.alamat_perusahaan
  form.pembimbing_perusahaan = item.pembimbing_perusahaan
  form.pembimbing_sekolah = item.pembimbing_sekolah || ''
  form.tanggal_mulai = item.tanggal_mulai ? formatDateForInput(item.tanggal_mulai) : ''
  form.tanggal_selesai = item.tanggal_selesai ? formatDateForInput(item.tanggal_selesai) : ''
  form.tahun_ajaran_id = item.tahun_ajaran_id
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    // Validate that selectedPkl exists when editing
    if (isEditing.value && !selectedPkl.value?.id) {
      toast.error('Data PKL tidak ditemukan. Silakan tutup form dan coba lagi.')
      return
    }

    const url = isEditing.value
      ? `/admin/pkl/${selectedPkl.value.id}`
      : '/admin/pkl'

    const method = isEditing.value ? 'put' : 'post'

    // Prepare form data
    const formData = {
      nama_perusahaan: form.nama_perusahaan,
      alamat_perusahaan: form.alamat_perusahaan,
      pembimbing_perusahaan: form.pembimbing_perusahaan,
      pembimbing_sekolah: form.pembimbing_sekolah,
      tanggal_mulai: form.tanggal_mulai,
      tanggal_selesai: form.tanggal_selesai,
      tahun_ajaran_id: Number.parseInt(form.tahun_ajaran_id, 10)
    }

    await axios[method](url, formData)

    toast.success(isEditing.value ? 'PKL berhasil diperbarui' : 'PKL berhasil ditambahkan')
    closeForm()
    fetchPkl()
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      toast.error('Validasi gagal. Periksa kembali data yang diinput.')
    } else {
      console.log(error, 'error response')
      toast.error(error.response?.data?.message || 'Terjadi kesalahan saat menyimpan data')
    }
  } finally {
    submitting.value = false
  }
}

const deletePkl = (item) => {
  selectedPkl.value = item
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/pkl/${selectedPkl.value.id}`)
    toast.success('PKL berhasil dihapus')
    showDeleteConfirm.value = false
    fetchPkl()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal menghapus PKL')
  } finally {
    deleting.value = false
  }
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchPkl()
}

const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatDateForInput = (date) => {
  if (!date) return ''
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const getDuration = (start, end) => {
  if (!start || !end) return '-'
  const startDate = new Date(start)
  const endDate = new Date(end)
  const diffTime = Math.abs(endDate - startDate)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  const months = Math.floor(diffDays / 30)
  const days = diffDays % 30
  if (months > 0) {
    return `${months} bulan ${days} hari`
  }
  return `${diffDays} hari`
}

const getStatusBadge = (status) => {
  const badgeMap = {
    'Belum Mulai': 'bg-yellow-100 text-yellow-800',
    'Sedang Berlangsung': 'bg-blue-100 text-blue-800',
    'Selesai': 'bg-green-100 text-green-800'
  }
  return badgeMap[status] || 'bg-gray-100 text-gray-800'
}

// Lifecycle
onMounted(() => {
  fetchPkl()
  fetchTahunAjaran()
})
</script>


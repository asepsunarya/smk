<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data Ekstrakurikuler"
        description="Kelola data ekstrakurikuler sekolah"
        :data="ekstrakurikuler"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data ekstrakurikuler"
        empty-description="Mulai dengan menambahkan ekstrakurikuler baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Ekstrakurikuler
          </button>
        </template>

        <template #filters>
          <FormField
            v-model="filters.pembina_id"
            type="select"
            placeholder="Pilih Pembina"
            :options="pembinaFilterOptions"
            option-value="id"
            option-label="name"
            @update:model-value="fetchEkstrakurikuler"
          />
          <!-- <FormField
            v-model="filters.is_active"
            type="select"
            placeholder="Status"
            :options="statusOptions"
            @update:model-value="fetchEkstrakurikuler"
          /> -->
        </template>

        <template #cell-nama="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 bg-green-600 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.nama.charAt(0) }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.nama }}</div>
              <div v-if="item.deskripsi" class="text-sm text-gray-500 line-clamp-1">{{ item.deskripsi }}</div>
            </div>
          </div>
        </template>

        <template #cell-pembina="{ item }">
          <div v-if="item.pembina" class="text-sm text-gray-900">
            {{ item.pembina.nama_lengkap || item.pembina.user?.name || '-' }}
          </div>
          <div v-else class="text-sm text-gray-400">Belum ada pembina</div>
        </template>

        <template #cell-status="{ item }">
          <span :class="getStatusBadge(item.is_active)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
          </span>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <!-- <button @click="toggleStatus(item)" class="text-indigo-600 hover:text-indigo-900" :title="item.is_active ? 'Nonaktifkan' : 'Aktifkan'">
              <svg v-if="item.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </button> -->
            <button @click="editEkstrakurikuler(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="deleteEkstrakurikuler(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler'" size="lg">
        <form @submit.prevent="submitForm" id="ekstrakurikuler-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.nama"
              label="Nama Ekstrakurikuler"
              placeholder="Contoh: Pramuka, Paskibra, Futsal"
              required
              :error="errors.nama"
            />
            <FormField
              v-model="form.pembina_id"
              type="select"
              label="Pembina"
              placeholder="Pilih pembina"
              required
              :options="pembinaOptions"
              :error="errors.pembina_id"
            />
            <div class="sm:col-span-2">
              <FormField
                v-model="form.deskripsi"
                type="textarea"
                label="Deskripsi"
                placeholder="Masukkan deskripsi ekstrakurikuler"
                :error="errors.deskripsi"
                :rows="3"
              />
            </div>
            <!-- <div v-if="isEditing" class="sm:col-span-2">
              <div class="flex items-center">
                <input
                  id="is_active"
                  type="checkbox"
                  v-model="form.is_active"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                  Aktif
                </label>
              </div>
            </div> -->
          </div>
        </form>

        <template #footer>
          <button type="submit" form="ekstrakurikuler-form" :disabled="submitting" class="btn btn-primary">
            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button type="button" @click="closeForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Confirmation Dialogs -->
      <ConfirmDialog
        v-model:show="showDeleteConfirm"
        title="Hapus Ekstrakurikuler"
        :message="`Apakah Anda yakin ingin menghapus ekstrakurikuler ${selectedEkstrakurikuler?.nama}? Ini akan menghapus semua data nilai ekstrakurikuler terkait.`"
        confirm-text="Ya, Hapus"
        type="error"
        :loading="deleting"
        @confirm="confirmDelete"
      />

      <ConfirmDialog
        v-model:show="showToggleStatusConfirm"
        :title="selectedEkstrakurikuler?.is_active ? 'Nonaktifkan Ekstrakurikuler' : 'Aktifkan Ekstrakurikuler'"
        :message="`Apakah Anda yakin ingin ${selectedEkstrakurikuler?.is_active ? 'menonaktifkan' : 'mengaktifkan'} ekstrakurikuler ${selectedEkstrakurikuler?.nama}?`"
        :confirm-text="selectedEkstrakurikuler?.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
        type="warning"
        :loading="togglingStatus"
        @confirm="confirmToggleStatus"
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
const ekstrakurikuler = ref([])
const loading = ref(true)
const submitting = ref(false)
const deleting = ref(false)
const togglingStatus = ref(false)

// Form state
const showForm = ref(false)
const isEditing = ref(false)
const selectedEkstrakurikuler = ref(null)
const showDeleteConfirm = ref(false)
const showToggleStatusConfirm = ref(false)

// Form data
const form = reactive({
  nama: '',
  deskripsi: '',
  pembina_id: '',
  is_active: true
})

const errors = ref({})

// Filters
const filters = reactive({
  search: '',
  pembina_id: '',
  is_active: ''
})

// Table columns
const columns = [
  { key: 'nama', label: 'Nama Ekstrakurikuler', sortable: true },
  { key: 'pembina', label: 'Pembina' },
  // { key: 'status', label: 'Status' }
]

// Options
const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'true', label: 'Aktif' },
  { value: 'false', label: 'Nonaktif' }
]

const pembinaOptions = ref([{ value: '', label: 'Pilih Pembina' }])
const pembinaFilterOptions = ref([{ id: '', name: 'Semua Pembina' }])

// Methods
const fetchEkstrakurikuler = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.pembina_id) params.append('pembina_id', filters.pembina_id)
    if (filters.is_active) params.append('is_active', filters.is_active)
    params.append('per_page', 100)

    const response = await axios.get(`/admin/ekstrakurikuler?${params}`)
    if (response.data.data) {
      ekstrakurikuler.value = response.data.data
    } else if (Array.isArray(response.data)) {
      ekstrakurikuler.value = response.data
    } else {
      ekstrakurikuler.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data ekstrakurikuler')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchPembina = async () => {
  try {
    const response = await axios.get('/admin/guru', {
      params: {
        status: 'aktif',
        per_page: 100
      }
    })
    if (response.data.data) {
      pembinaOptions.value = response.data.data.map(g => ({
          value: g.id,
          label: `${g.nama_lengkap}${g.nuptk ? ' - ' + g.nuptk : ''}`
      })) || []
      
      pembinaFilterOptions.value = [
        { id: '', name: 'Semua Pembina' },
        ...response.data.data.map(g => ({
          id: g.id,
          name: `${g.nama_lengkap}${g.nuptk ? ' - ' + g.nuptk : ''}`
        }))
      ]
    }
  } catch (error) {
    console.error('Failed to fetch pembina:', error)
  }
}

const resetForm = () => {
  form.nama = ''
  form.deskripsi = ''
  form.pembina_id = ''
  form.is_active = true
  errors.value = {}
}

const closeForm = () => {
  showForm.value = false
  isEditing.value = false
  selectedEkstrakurikuler.value = null
  resetForm()
}

const editEkstrakurikuler = (item) => {
  selectedEkstrakurikuler.value = item
  isEditing.value = true
  form.nama = item.nama
  form.deskripsi = item.deskripsi || ''
  form.pembina_id = item.pembina?.id || item.pembina_id || ''
  form.is_active = item.is_active
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    // Validate that selectedEkstrakurikuler exists when editing
    if (isEditing.value && !selectedEkstrakurikuler.value?.id) {
      toast.error('Data ekstrakurikuler tidak ditemukan. Silakan tutup form dan coba lagi.')
      return
    }

    const url = isEditing.value
      ? `/admin/ekstrakurikuler/${selectedEkstrakurikuler.value.id}`
      : '/admin/ekstrakurikuler'

    const method = isEditing.value ? 'put' : 'post'

    // Prepare form data
    const formData = {
      nama: form.nama,
      deskripsi: form.deskripsi || null,
      pembina_id: form.pembina_id || null,
      is_active: form.is_active
    }

    await axios[method](url, formData)

    toast.success(isEditing.value ? 'Ekstrakurikuler berhasil diperbarui' : 'Ekstrakurikuler berhasil ditambahkan')
    closeForm()
    fetchEkstrakurikuler()
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

const deleteEkstrakurikuler = (item) => {
  selectedEkstrakurikuler.value = item
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/ekstrakurikuler/${selectedEkstrakurikuler.value.id}`)
    toast.success('Ekstrakurikuler berhasil dihapus')
    showDeleteConfirm.value = false
    fetchEkstrakurikuler()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal menghapus ekstrakurikuler')
  } finally {
    deleting.value = false
  }
}

const toggleStatus = (item) => {
  selectedEkstrakurikuler.value = item
  showToggleStatusConfirm.value = true
}

const confirmToggleStatus = async () => {
  try {
    togglingStatus.value = true
    await axios.post(`/admin/ekstrakurikuler/${selectedEkstrakurikuler.value.id}/toggle-status`)
    toast.success('Status ekstrakurikuler berhasil diubah')
    showToggleStatusConfirm.value = false
    fetchEkstrakurikuler()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal mengubah status ekstrakurikuler')
  } finally {
    togglingStatus.value = false
  }
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchEkstrakurikuler()
}

const getStatusBadge = (isActive) => {
  return isActive
    ? 'bg-green-100 text-green-800'
    : 'bg-gray-100 text-gray-800'
}

// Lifecycle
onMounted(() => {
  fetchEkstrakurikuler()
  fetchPembina()
})
</script>


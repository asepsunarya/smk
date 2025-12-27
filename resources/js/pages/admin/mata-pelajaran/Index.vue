<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data Mata Pelajaran"
        description="Kelola data mata pelajaran sekolah"
        :data="mataPelajaran"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data mata pelajaran"
        empty-description="Mulai dengan menambahkan mata pelajaran baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Mata Pelajaran
          </button>
        </template>

        <template #filters>
          <FormField
            v-model="filters.guru_id"
            type="select"
            placeholder="Pilih Guru"
            :options="guruFilterOptions"
            option-value="id"
            option-label="name"
            @update:model-value="fetchMataPelajaran"
          />
          <FormField
            v-model="filters.kelas_id"
            type="select"
            placeholder="Pilih Kelas"
            :options="kelasFilterOptions"
            option-value="id"
            option-label="nama_kelas"
            @update:model-value="fetchMataPelajaran"
          />
          <FormField
            v-model="filters.is_active"
            type="select"
            placeholder="Status"
            :options="statusOptions"
            @update:model-value="fetchMataPelajaran"
          />
        </template>

        <template #cell-kode_mapel="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.kode_mapel.charAt(0) }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.kode_mapel }}</div>
              <div class="text-sm text-gray-500">{{ item.nama_mapel }}</div>
            </div>
          </div>
        </template>

        <template #cell-guru="{ item }">
          <div class="text-sm text-gray-900">
            {{ item.guru?.nama_lengkap || item.guru?.user?.name || '-' }}
          </div>
          <div v-if="item.guru?.nuptk" class="text-xs text-gray-500">
            {{ item.guru.nuptk }}
          </div>
        </template>

        <template #cell-kelas="{ item }">
          <div class="flex flex-wrap gap-1">
            <span
              v-for="kelas in (item.kelas || [])"
              :key="kelas.id"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
            >
              {{ kelas.nama_kelas }}
              <span v-if="kelas.jurusan" class="ml-1 text-blue-600">
                ({{ kelas.jurusan.nama_jurusan }})
              </span>
            </span>
            <span v-if="!item.kelas || item.kelas.length === 0" class="text-gray-400">-</span>
          </div>
        </template>

        <template #cell-kkm="{ item }">
          <div class="flex items-center">
            <span class="text-sm font-medium text-gray-900">{{ item.kkm }}</span>
            <span class="ml-1 text-xs text-gray-500">/ 100</span>
          </div>
        </template>

        <template #cell-status="{ item }">
          <span :class="getStatusBadge(item.is_active)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
          </span>
        </template>

        <template #cell-statistik="{ item }">
          <div class="text-sm">
            <div class="flex items-center space-x-4">
              <!-- <div>
                <span class="text-gray-500">Nilai:</span>
                <span class="ml-1 font-medium text-gray-900">{{ item.nilai_count || 0 }}</span>
              </div> -->
              <div>
                <span class="text-gray-500">CP:</span>
                <span class="ml-1 font-medium text-gray-900">{{ item.capaian_pembelajaran_count || 0 }}</span>
              </div>
            </div>
          </div>
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
            <button @click="editMataPelajaran(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="deleteMataPelajaran(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'" size="lg">
        <form @submit.prevent="submitForm" id="mata-pelajaran-form" class="space-y-4">
          <!-- Kelas di paling atas -->
          <MultiSelect
            v-model="form.kelas_ids"
            label="Kelas"
            placeholder="Pilih kelas (bisa pilih banyak)"
            :options="kelasOptions.map(k => ({ value: k.id, label: `${k.nama_kelas} - ${k.jurusan?.nama_jurusan}` }))"
            :searchable="true"
            :max-height="500"
            required
            :error="errors.kelas_ids"
            help-text="Pilih satu atau lebih kelas untuk mata pelajaran ini"
          />
          
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.kode_mapel"
              label="Kode Mata Pelajaran"
              placeholder="Contoh: MAT, BIN, RPL"
              required
              :error="errors.kode_mapel"
              :maxlength="20"
            />
            <FormField
              v-model="form.nama_mapel"
              label="Nama Mata Pelajaran"
              placeholder="Masukkan nama mata pelajaran lengkap"
              required
              :error="errors.nama_mapel"
            />
            <FormField
              v-model="form.kkm"
              type="number"
              label="KKM (Kriteria Ketuntasan Minimal)"
              placeholder="Masukkan KKM (0-100)"
              required
              :error="errors.kkm"
              :min="0"
              :max="100"
            />
            <FormField
              v-model="form.guru_id"
              type="select"
              label="Guru"
              placeholder="Pilih guru"
              :options="guruOptions.map(g => ({ value: g.id, label: `${g.nama_lengkap || g.user?.name}${g.nuptk ? ' - ' + g.nuptk : ''}` }))"
              required
              :error="errors.guru_id"
            />
            <FormField
              v-if="isEditing"
              v-model="form.is_active"
              type="checkbox"
              label="Aktif"
              :error="errors.is_active"
            />
          </div>
        </form>

        <template #footer>
          <button type="submit" form="mata-pelajaran-form" :disabled="submitting" class="btn btn-primary">
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
        title="Hapus Mata Pelajaran"
        :message="`Apakah Anda yakin ingin menghapus mata pelajaran ${selectedMataPelajaran?.nama_mapel}?`"
        confirm-text="Ya, Hapus"
        type="error"
        :loading="deleting"
        @confirm="confirmDelete"
      />

      <ConfirmDialog
        v-model:show="showToggleStatusConfirm"
        :title="selectedMataPelajaran?.is_active ? 'Nonaktifkan Mata Pelajaran' : 'Aktifkan Mata Pelajaran'"
        :message="`Apakah Anda yakin ingin ${selectedMataPelajaran?.is_active ? 'menonaktifkan' : 'mengaktifkan'} mata pelajaran ${selectedMataPelajaran?.nama_mapel}?`"
        :confirm-text="selectedMataPelajaran?.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"
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
import MultiSelect from '../../../components/ui/MultiSelect.vue'
import ConfirmDialog from '../../../components/ui/ConfirmDialog.vue'

const toast = useToast()

// Data
const mataPelajaran = ref([])
const loading = ref(true)
const submitting = ref(false)
const deleting = ref(false)
const togglingStatus = ref(false)

// Form state
const showForm = ref(false)
const isEditing = ref(false)
const selectedMataPelajaran = ref(null)
const showDeleteConfirm = ref(false)
const showToggleStatusConfirm = ref(false)

// Form data
const form = reactive({
  kode_mapel: '',
  nama_mapel: '',
  kkm: 75,
  is_active: true,
  guru_id: '',
  kelas_ids: []
})

const errors = ref({})

// Filters
const filters = reactive({
  search: '',
  guru_id: '',
  kelas_id: '',
  is_active: ''
})

// Table columns
const columns = [
  { key: 'kode_mapel', label: 'Kode & Nama', sortable: true },
  { key: 'guru', label: 'Guru' },
  { key: 'kelas', label: 'Kelas' },
  { key: 'kkm', label: 'KKM' },
  // { key: 'status', label: 'Status' },
  { key: 'statistik', label: 'Statistik' }
]

// Options
const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'true', label: 'Aktif' },
  { value: 'false', label: 'Nonaktif' }
]

const guruOptions = ref([])
const kelasOptions = ref([])
const guruFilterOptions = ref([{ id: '', name: 'Semua Guru' }])
const kelasFilterOptions = ref([{ id: '', nama_kelas: 'Semua Kelas' }])

// Methods
const fetchMataPelajaran = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.guru_id) params.append('guru_id', filters.guru_id)
    if (filters.kelas_id) params.append('kelas_id', filters.kelas_id)
    if (filters.is_active) params.append('is_active', filters.is_active)
    params.append('per_page', 100)
    
    const response = await axios.get(`/admin/mata-pelajaran?${params}`)
    if (response.data.data) {
      mataPelajaran.value = response.data.data
    } else if (Array.isArray(response.data)) {
      mataPelajaran.value = response.data
    } else {
      mataPelajaran.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data mata pelajaran')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchGuru = async () => {
  try {
    const response = await axios.get('/admin/guru', {
      params: {
        status: 'aktif',
        per_page: 100
      }
    })
    if (response.data.data) {
      guruOptions.value = response.data.data
      guruFilterOptions.value = [
        { id: '', name: 'Semua Guru' },
        ...response.data.data.map(g => ({
          id: g.id,
          name: `${g.nama_lengkap}${g.nuptk ? ' - ' + g.nuptk : ''}`
        }))
      ]
    }
  } catch (error) {
    console.error('Failed to fetch guru:', error)
  }
}

const fetchKelas = async () => {
  try {
    const response = await axios.get('/admin/kelas', {
      params: {
        per_page: 100
      }
    })
    if (response.data.data) {
      kelasOptions.value = response.data.data
      kelasFilterOptions.value = [
        { id: '', nama_kelas: 'Semua Kelas' },
        ...response.data.data.map(k => ({
          id: k.id,
          nama_kelas: `${k.nama_kelas} - ${k.jurusan?.nama_jurusan}`
        }))
      ]
    }
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
  }
}

const resetForm = () => {
  form.kode_mapel = ''
  form.nama_mapel = ''
  form.kkm = 75
  form.is_active = true
  form.guru_id = ''
  form.kelas_ids = []
  errors.value = {}
}

const closeForm = () => {
  showForm.value = false
  isEditing.value = false
  selectedMataPelajaran.value = null
  resetForm()
}

const editMataPelajaran = async (item) => {
  try {
    // Fetch full data from API to ensure we have all kelas relationships
    const response = await axios.get(`/admin/mata-pelajaran/${item.id}`)
    const fullData = response.data.data || response.data
    
    selectedMataPelajaran.value = fullData
    isEditing.value = true
    form.kode_mapel = fullData.kode_mapel
    form.nama_mapel = fullData.nama_mapel
    form.kkm = fullData.kkm
    form.is_active = fullData.is_active
    form.guru_id = fullData.guru?.id || fullData.guru_id || ''
    
    // Handle kelas - bisa array atau single object
    if (Array.isArray(fullData.kelas)) {
      form.kelas_ids = fullData.kelas.map(k => k.id)
    } else if (fullData.kelas?.id) {
      form.kelas_ids = [fullData.kelas.id]
    } else if (fullData.kelas_id) {
      form.kelas_ids = [fullData.kelas_id]
    } else {
      form.kelas_ids = []
    }
    
    showForm.value = true
  } catch (error) {
    toast.error('Gagal mengambil data mata pelajaran')
    console.error(error)
  }
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    // Validate that selectedMataPelajaran exists when editing
    if (isEditing.value && !selectedMataPelajaran.value?.id) {
      toast.error('Data mata pelajaran tidak ditemukan. Silakan tutup form dan coba lagi.')
      return
    }

    // Validate kelas_ids
    const kelasIdsArray = Array.isArray(form.kelas_ids) 
      ? form.kelas_ids.filter(id => id !== '' && id !== null && id !== undefined)
      : []
    
    if (kelasIdsArray.length === 0) {
      errors.value.kelas_ids = 'Pilih minimal satu kelas'
      toast.error('Pilih minimal satu kelas')
      submitting.value = false
      return
    }

    const url = isEditing.value
      ? `/admin/mata-pelajaran/${selectedMataPelajaran.value.id}`
      : '/admin/mata-pelajaran'
    
    const method = isEditing.value ? 'put' : 'post'
    
    // Prepare form data - ensure kelas_ids are integers
    const formData = {
      kode_mapel: form.kode_mapel,
      nama_mapel: form.nama_mapel,
      kkm: Number.parseInt(form.kkm, 10),
      is_active: form.is_active,
      guru_id: Number.parseInt(form.guru_id, 10),
      kelas_ids: kelasIdsArray.map(id => {
        const numId = typeof id === 'string' ? Number.parseInt(id, 10) : id
        return isNaN(numId) ? null : numId
      }).filter(id => id !== null)
    }
    
    await axios[method](url, formData)
    
    toast.success(isEditing.value ? 'Mata pelajaran berhasil diperbarui' : 'Mata pelajaran berhasil ditambahkan')
    closeForm()
    fetchMataPelajaran()
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

const deleteMataPelajaran = (item) => {
  selectedMataPelajaran.value = item
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/mata-pelajaran/${selectedMataPelajaran.value.id}`)
    toast.success('Mata pelajaran berhasil dihapus')
    showDeleteConfirm.value = false
    fetchMataPelajaran()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal menghapus mata pelajaran')
  } finally {
    deleting.value = false
  }
}

const toggleStatus = (item) => {
  selectedMataPelajaran.value = item
  showToggleStatusConfirm.value = true
}

const confirmToggleStatus = async () => {
  try {
    togglingStatus.value = true
    await axios.post(`/admin/mata-pelajaran/${selectedMataPelajaran.value.id}/toggle-status`)
    toast.success('Status mata pelajaran berhasil diubah')
    showToggleStatusConfirm.value = false
    fetchMataPelajaran()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal mengubah status mata pelajaran')
  } finally {
    togglingStatus.value = false
  }
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchMataPelajaran()
}

const getStatusBadge = (isActive) => {
  return isActive
    ? 'bg-green-100 text-green-800'
    : 'bg-gray-100 text-gray-800'
}

// Lifecycle
onMounted(() => {
  fetchMataPelajaran()
  fetchGuru()
  fetchKelas()
})
</script>

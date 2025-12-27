<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data Wali Kelas"
        description="Kelola penugasan wali kelas untuk setiap kelas"
        :data="waliKelas"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data wali kelas"
        empty-description="Mulai dengan menetapkan wali kelas untuk kelas."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showAssignForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tetapkan Wali Kelas
          </button>
        </template>

        <template #filters>
          <FormField
            v-model="filters.kelas_id"
            type="select"
            placeholder="Pilih Kelas"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            @update:model-value="fetchWaliKelas"
          />
          <FormField
            v-model="filters.jurusan_id"
            type="select"
            placeholder="Pilih Jurusan"
            :options="jurusanOptions"
            option-value="id"
            option-label="nama_jurusan"
            @update:model-value="fetchWaliKelas"
          />
        </template>

        <template #cell-name="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.name.charAt(0) }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
              <div class="text-sm text-gray-500">{{ item.guru?.nuptk || item.email }}</div>
            </div>
          </div>
        </template>

        <template #cell-kelas="{ item }">
          <div class="space-y-1">
            <div v-for="kelas in item.kelas_as_wali" :key="kelas.id" class="flex items-center">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ kelas.nama_kelas }}
              </span>
              <span class="ml-2 text-xs text-gray-500">{{ kelas.jurusan?.nama_jurusan }}</span>
            </div>
            <span v-if="!item.kelas_as_wali || item.kelas_as_wali.length === 0" class="text-gray-400 text-sm">-</span>
          </div>
        </template>

        <template #cell-statistik="{ item }">
          <div class="text-sm">
            <div class="flex items-center space-x-4">
              <div>
                <span class="text-gray-500">Kelas:</span>
                <span class="ml-1 font-medium text-gray-900">{{ item.total_kelas || 0 }}</span>
              </div>
              <div>
                <span class="text-gray-500">Siswa:</span>
                <span class="ml-1 font-medium text-gray-900">{{ item.total_siswa || 0 }}</span>
              </div>
            </div>
          </div>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="viewDetail(item)" class="text-green-600 hover:text-green-900" title="Detail">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button>
            <button @click="editWaliKelas(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="removeWaliKelas(item)" class="text-red-600 hover:text-red-900" title="Hapus dari Kelas">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Assign Form Modal -->
      <Modal v-model:show="showAssignForm" title="Tetapkan Wali Kelas" size="lg">
        <form @submit.prevent="submitAssign" id="assign-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="assignForm.guru_id"
              type="select"
              label="Guru/Wali Kelas"
              placeholder="Pilih guru"
              :options="guruOptions"
              option-value="id"
              option-label="name"
              required
              :error="errors.guru_id"
            />
            <FormField
              v-model="assignForm.kelas_id"
              type="select"
              label="Kelas"
              placeholder="Pilih kelas"
              :options="kelasOptions"
              option-value="id"
              option-label="nama_kelas"
              required
              :error="errors.kelas_id"
            />
          </div>
          <div v-if="selectedKelasInfo" class="p-3 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-600">
              <strong>Info Kelas:</strong> {{ selectedKelasInfo.nama_kelas }} - {{ selectedKelasInfo.jurusan?.nama_jurusan }}
            </p>
            <p v-if="selectedKelasInfo.wali_kelas_id" class="text-sm text-yellow-600 mt-1">
              ⚠️ Kelas ini sudah memiliki wali kelas. Menetapkan wali kelas baru akan menggantikan wali kelas yang ada.
            </p>
          </div>
        </form>

        <template #footer>
          <button type="submit" form="assign-form" :disabled="submitting" class="btn btn-primary">
            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Menyimpan...' : 'Tetapkan' }}
          </button>
          <button type="button" @click="closeAssignForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Detail Modal -->
      <Modal v-model:show="showDetailModal" :title="`Detail Wali Kelas - ${selectedWaliKelas?.name}`" size="lg">
        <div v-if="loadingDetail" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
        </div>
        <div v-else-if="waliKelasDetail" class="space-y-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Wali Kelas</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Nama</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ waliKelasDetail.name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ waliKelasDetail.email }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">NIP</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ waliKelasDetail.guru?.nuptk || '-' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Role</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatRole(waliKelasDetail.role) }}</dd>
              </div>
            </dl>
          </div>

          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Statistik</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div class="bg-blue-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-blue-600">Total Kelas</dt>
                <dd class="mt-1 text-2xl font-semibold text-blue-900">{{ waliKelasDetail.total_kelas || 0 }}</dd>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <dt class="text-sm font-medium text-green-600">Total Siswa</dt>
                <dd class="mt-1 text-2xl font-semibold text-green-900">{{ waliKelasDetail.total_siswa || 0 }}</dd>
              </div>
            </dl>
          </div>

          <div v-if="waliKelasDetail.kelas_as_wali && waliKelasDetail.kelas_as_wali.length > 0" class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Kelas yang Diampu</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div v-for="kelas in waliKelasDetail.kelas_as_wali" :key="kelas.id" class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-900">{{ kelas.nama_kelas }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ kelas.jurusan?.nama_jurusan }}</div>
                <div class="text-xs text-gray-500 mt-1">Tingkat {{ kelas.tingkat }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  Siswa: {{ kelas.active_siswa_count || 0 }} / {{ kelas.kapasitas }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <template #footer>
          <button @click="showDetailModal = false" class="btn btn-secondary">Tutup</button>
        </template>
      </Modal>

      <!-- Edit Form Modal -->
      <Modal v-model:show="showEditForm" :title="`Edit Penetapan Kelas - ${selectedWaliKelas?.name}`" size="lg">
        <form @submit.prevent="submitEdit" id="edit-form" class="space-y-4">
          <!-- Display Guru Info (Read-only) -->
          <div class="p-3 bg-gray-50 rounded-lg">
            <p class="text-sm font-medium text-gray-700">Guru/Wali Kelas</p>
            <p class="text-sm text-gray-900 mt-1">{{ selectedWaliKelas?.name }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ selectedWaliKelas?.guru?.nuptk || selectedWaliKelas?.email }}</p>
          </div>

          <!-- Select Kelas to Edit -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Pilih Kelas yang Akan Diedit</label>
            <FormField
              v-model="editForm.old_kelas_id"
              type="select"
              label="Kelas Saat Ini"
              placeholder="Pilih kelas yang akan diubah"
              :options="selectedWaliKelas?.kelas_as_wali || []"
              option-value="id"
              option-label="nama_kelas"
              required
              :error="editErrors.old_kelas_id"
              @update:model-value="onOldKelasSelect"
            />
          </div>

          <!-- New Kelas Selection -->
          <FormField
            v-model="editForm.kelas_id"
            type="select"
            label="Kelas Baru"
            placeholder="Pilih kelas baru"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            required
            :error="editErrors.kelas_id"
          />
          
          <div v-if="selectedEditKelasInfo" class="p-3 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-600">
              <strong>Info Kelas Baru:</strong> {{ selectedEditKelasInfo.nama_kelas }} - {{ selectedEditKelasInfo.jurusan?.nama_jurusan }}
            </p>
            <p v-if="selectedEditKelasInfo.wali_kelas_id" class="text-sm text-yellow-600 mt-1">
              ⚠️ Kelas ini sudah memiliki wali kelas. Mengubah penetapan akan menggantikan wali kelas yang ada.
            </p>
          </div>
        </form>

        <template #footer>
          <button type="submit" form="edit-form" :disabled="editing" class="btn btn-primary">
            <svg v-if="editing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ editing ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
          <button type="button" @click="closeEditForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Remove Confirmation Modal -->
      <Modal v-model:show="showRemoveModal" title="Hapus Wali Kelas dari Kelas" size="sm">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Pilih kelas untuk menghapus wali kelas <strong>{{ selectedWaliKelas?.name }}</strong>
          </p>
          <FormField
            v-model="removeForm.kelas_id"
            type="select"
            label="Kelas"
            placeholder="Pilih kelas"
            :options="selectedWaliKelas?.kelas_as_wali || []"
            option-value="id"
            option-label="nama_kelas"
            required
            :error="removeErrors.kelas_id"
          />
        </div>

        <template #footer>
          <button @click="confirmRemove" :disabled="removing" class="btn btn-primary">
            {{ removing ? 'Menghapus...' : 'Hapus' }}
          </button>
          <button @click="showRemoveModal = false" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import DataTable from '../../../components/ui/DataTable.vue'
import Modal from '../../../components/ui/Modal.vue'
import FormField from '../../../components/ui/FormField.vue'

const toast = useToast()

// Data
const waliKelas = ref([])
const waliKelasDetail = ref(null)
const guruOptions = ref([])
const kelasOptions = ref([])
const jurusanOptions = ref([])
const loading = ref(true)
const loadingDetail = ref(false)
const submitting = ref(false)
const editing = ref(false)
const removing = ref(false)

// Form state
const showAssignForm = ref(false)
const showEditForm = ref(false)
const showDetailModal = ref(false)
const showRemoveModal = ref(false)
const selectedWaliKelas = ref(null)
const currentWaliKelasId = ref(null)

// Form data
const assignForm = reactive({
  guru_id: '',
  kelas_id: ''
})

const editForm = reactive({
  old_kelas_id: '',
  kelas_id: ''
})

const removeForm = reactive({
  kelas_id: ''
})

const errors = ref({})
const editErrors = ref({})
const removeErrors = ref({})

// Filters
const filters = reactive({
  search: '',
  kelas_id: '',
  jurusan_id: ''
})

// Table columns
const columns = [
  { key: 'name', label: 'Nama & NIP', sortable: true },
  { key: 'kelas', label: 'Kelas yang Diampu' },
  { key: 'statistik', label: 'Statistik' }
]

// Computed
const selectedKelasInfo = computed(() => {
  if (!assignForm.kelas_id) return null
  return kelasOptions.value.find(k => k.id == assignForm.kelas_id)
})

const selectedEditKelasInfo = computed(() => {
  if (!editForm.kelas_id) return null
  return kelasOptions.value.find(k => k.id == editForm.kelas_id)
})

// Methods
const fetchWaliKelas = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.kelas_id) params.append('kelas_id', filters.kelas_id)
    if (filters.jurusan_id) params.append('jurusan_id', filters.jurusan_id)
    params.append('per_page', 100)
    
    const response = await axios.get(`/admin/wali-kelas?${params}`)
    if (response.data.data) {
      waliKelas.value = response.data.data
    } else if (Array.isArray(response.data)) {
      waliKelas.value = response.data
    } else {
      waliKelas.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data wali kelas')
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
        .filter(g => ['wali_kelas', 'guru', 'kepala_sekolah'].includes(g.user?.role))
        .map(g => ({
          id: g.id,
          name: `${g.nama_lengkap}${g.nuptk ? ' - ' + g.nuptk : ''} (${formatRole(g.user?.role)})`,
          ...g
        }))
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
      kelasOptions.value = response.data.data.map(k => ({
        id: k.id,
        nama_kelas: `${k.nama_kelas} - ${k.jurusan?.nama_jurusan}`,
        ...k
      }))
    }
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
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

const fetchDetail = async (guruId) => {
  try {
    loadingDetail.value = true
    // Get all wali kelas assignments for this guru
    const response = await axios.get(`/admin/wali-kelas`, {
      params: {
        guru_id: guruId
      }
    })
    
    if (response.data.data && response.data.data.length > 0) {
      waliKelasDetail.value = response.data.data[0]
    } else {
      waliKelasDetail.value = null
    }
  } catch (error) {
    toast.error('Gagal mengambil detail wali kelas')
    console.error(error)
  } finally {
    loadingDetail.value = false
  }
}

const resetAssignForm = () => {
  assignForm.guru_id = ''
  assignForm.kelas_id = ''
  errors.value = {}
}

const closeAssignForm = () => {
  showAssignForm.value = false
  resetAssignForm()
}

const submitAssign = async () => {
  try {
    submitting.value = true
    errors.value = {}

    await axios.post('/admin/wali-kelas/assign', assignForm)
    
    toast.success('Wali kelas berhasil ditetapkan')
    closeAssignForm()
    fetchWaliKelas()
    fetchKelas() // Refresh kelas options
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      toast.error('Validasi gagal. Periksa kembali data yang diinput.')
    } else {
      toast.error(error.response?.data?.message || 'Terjadi kesalahan saat menetapkan wali kelas')
    }
  } finally {
    submitting.value = false
  }
}

const viewDetail = async (waliKelasItem) => {
  selectedWaliKelas.value = waliKelasItem
  showDetailModal.value = true
  await fetchDetail(waliKelasItem.guru_id)
}

const editWaliKelas = async (waliKelasItem) => {
  selectedWaliKelas.value = waliKelasItem
  
  // Reset form
  editForm.old_kelas_id = ''
  editForm.kelas_id = ''
  currentWaliKelasId.value = null
  
  // If only one kelas, auto-select it
  if (waliKelasItem.kelas_as_wali && waliKelasItem.kelas_as_wali.length === 1) {
    editForm.old_kelas_id = waliKelasItem.kelas_as_wali[0].id
    await onOldKelasSelect(waliKelasItem.kelas_as_wali[0].id)
  }
  
  showEditForm.value = true
}

const onOldKelasSelect = async (kelasId) => {
  if (!kelasId || !selectedWaliKelas.value) return
  
  // First, try to get ID from kelas data if available
  const kelasData = selectedWaliKelas.value.kelas_as_wali?.find(k => k.id == kelasId)
  if (kelasData && kelasData.wali_kelas_id) {
    currentWaliKelasId.value = kelasData.wali_kelas_id
    return
  }
  
  // If not found in data, fetch from API
  try {
    const response = await axios.get('/admin/wali-kelas/find-id', {
      params: {
        guru_id: selectedWaliKelas.value.guru_id,
        kelas_id: kelasId
      }
    })
    
    if (response.data && response.data.id) {
      currentWaliKelasId.value = response.data.id
    } else {
      console.warn('Wali kelas ID not found in response:', response.data)
      currentWaliKelasId.value = null
    }
  } catch (error) {
    console.error('Error fetching wali kelas ID:', error)
    if (error.response?.status === 404) {
      console.error('Response:', error.response?.data)
    }
    currentWaliKelasId.value = null
  }
}

const resetEditForm = () => {
  editForm.old_kelas_id = ''
  editForm.kelas_id = ''
  editErrors.value = {}
  currentWaliKelasId.value = null
}

const closeEditForm = () => {
  showEditForm.value = false
  resetEditForm()
}

const submitEdit = async () => {
  try {
    editing.value = true
    editErrors.value = {}
    
    if (!editForm.old_kelas_id) {
      toast.error('Pilih kelas yang akan diubah terlebih dahulu')
      editing.value = false
      return
    }
    
    if (!editForm.kelas_id) {
      toast.error('Pilih kelas baru terlebih dahulu')
      editing.value = false
      return
    }
    
    if (editForm.old_kelas_id === editForm.kelas_id) {
      toast.error('Kelas baru harus berbeda dengan kelas saat ini')
      editing.value = false
      return
    }
    
    if (!selectedWaliKelas.value || !selectedWaliKelas.value.guru_id) {
      toast.error('Data wali kelas tidak valid')
      editing.value = false
      return
    }
    
    // Get WaliKelas ID - always fetch to ensure we have the correct ID
    let waliKelasId = null
    
    try {
      const response = await axios.get('/admin/wali-kelas/find-id', {
        params: {
          guru_id: selectedWaliKelas.value.guru_id,
          kelas_id: editForm.old_kelas_id
        }
      })
      
      if (response.data && response.data.id) {
        waliKelasId = response.data.id
      } else {
        toast.error('ID wali kelas tidak ditemukan. Silakan hapus dan buat ulang penugasan.')
        editing.value = false
        return
      }
    } catch (error) {
      console.error('Error finding wali kelas ID:', error)
      toast.error('Gagal menemukan data wali kelas. Silakan coba lagi.')
      editing.value = false
      return
    }
    
    if (!waliKelasId) {
      toast.error('ID wali kelas tidak ditemukan. Silakan hapus dan buat ulang penugasan.')
      editing.value = false
      return
    }

    // Update only kelas_id
    await axios.put(`/admin/wali-kelas/${waliKelasId}`, {
      kelas_id: editForm.kelas_id
    })
    
    toast.success('Penetapan kelas berhasil diperbarui')
    closeEditForm()
    fetchWaliKelas()
    fetchKelas() // Refresh kelas options
  } catch (error) {
    if (error.response?.status === 422) {
      editErrors.value = error.response.data.errors || {}
      toast.error('Validasi gagal. Periksa kembali data yang diinput.')
    } else {
      toast.error(error.response?.data?.message || 'Terjadi kesalahan saat memperbarui penetapan kelas')
    }
  } finally {
    editing.value = false
  }
}

const removeWaliKelas = (waliKelasItem) => {
  selectedWaliKelas.value = waliKelasItem
  removeForm.kelas_id = ''
  removeErrors.value = {}
  showRemoveModal.value = true
}

const confirmRemove = async () => {
  try {
    removing.value = true
    removeErrors.value = {}
    
    await axios.post('/admin/wali-kelas/remove', removeForm)
    toast.success('Wali kelas berhasil dihapus dari kelas')
    showRemoveModal.value = false
    fetchWaliKelas()
    fetchKelas() // Refresh kelas options
  } catch (error) {
    if (error.response?.status === 422) {
      removeErrors.value = error.response.data.errors || {}
    } else {
      toast.error(error.response?.data?.message || 'Gagal menghapus wali kelas')
    }
  } finally {
    removing.value = false
  }
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchWaliKelas()
}

const formatRole = (role) => {
  const roleMap = {
    guru: 'Guru',
    wali_kelas: 'Wali Kelas',
    kepala_sekolah: 'Kepala Sekolah'
  }
  return roleMap[role] || role
}

// Lifecycle
onMounted(() => {
  fetchWaliKelas()
  fetchGuru()
  fetchKelas()
  fetchJurusan()
})
</script>


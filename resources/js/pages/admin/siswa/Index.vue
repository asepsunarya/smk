<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data Siswa"
        description="Kelola data siswa sekolah"
        :data="siswa"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data siswa"
        empty-description="Mulai dengan menambahkan siswa baru."
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Siswa
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
            @update:model-value="fetchSiswa"
          />
          <FormField
            v-model="filters.status"
            type="select"
            placeholder="Status"
            :options="statusOptions"
            @update:model-value="fetchSiswa"
          />
        </template>

        <template #cell-nama_lengkap="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.nama_lengkap.charAt(0) }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.nama_lengkap }}</div>
              <div class="text-sm text-gray-500">{{ item.nis }}</div>
            </div>
          </div>
        </template>

        <template #cell-kelas="{ item }">
          <span v-if="item.kelas" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ item.kelas.nama_kelas }}
          </span>
          <span v-else class="text-gray-400">-</span>
        </template>

        <template #cell-status="{ item }">
          <span :class="getStatusBadge(item.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ item.status }}
          </span>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="editSiswa(item)" class="text-blue-600 hover:text-blue-900">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="resetPassword(item)" class="text-yellow-600 hover:text-yellow-900">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-6 6H4a3 3 0 01-3-3V9a3 3 0 013-3h2M8 7a2 2 0 012-2h2m0 0a2 2 0 012 2M7 7a2 2 0 00-2 2m0 0a2 2 0 002 2h4a2 2 0 002-2m-2 0a2 2 0 00-2-2H7z"></path>
              </svg>
            </button>
            <button @click="moveClass(item)" class="text-green-600 hover:text-green-900">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
              </svg>
            </button>
            <button @click="deleteSiswa(item)" class="text-red-600 hover:text-red-900">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Siswa' : 'Tambah Siswa'" size="lg">
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.nis"
              label="NIS"
              placeholder="Masukkan NIS"
              required
              :error="errors.nis"
            />
            <FormField
              v-model="form.nisn"
              label="NISN"
              placeholder="Masukkan NISN"
              :error="errors.nisn"
            />
            <FormField
              v-model="form.nama_lengkap"
              label="Nama Lengkap"
              placeholder="Masukkan nama lengkap"
              required
              :error="errors.nama_lengkap"
            />
            <FormField
              v-model="form.jenis_kelamin"
              type="select"
              label="Jenis Kelamin"
              placeholder="Pilih jenis kelamin"
              :options="[
                { value: 'L', label: 'Laki-laki' },
                { value: 'P', label: 'Perempuan' }
              ]"
              required
              :error="errors.jenis_kelamin"
            />
            <FormField
              v-model="form.tempat_lahir"
              label="Tempat Lahir"
              placeholder="Masukkan tempat lahir"
              :error="errors.tempat_lahir"
            />
            <FormField
              v-model="form.tanggal_lahir"
              type="date"
              label="Tanggal Lahir"
              required
              :error="errors.tanggal_lahir"
            />
            <FormField
              v-model="form.agama"
              type="select"
              label="Agama"
              placeholder="Pilih agama"
              :options="agamaOptions"
              required
              :error="errors.agama"
            />
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
            />
          </div>
          
          <div class="grid grid-cols-1 gap-4">
            <FormField
              v-model="form.alamat"
              type="textarea"
              label="Alamat"
              placeholder="Masukkan alamat lengkap"
              :error="errors.alamat"
            />
            <FormField
              v-model="form.no_hp"
              label="No. HP"
              placeholder="Masukkan nomor HP"
              :error="errors.no_hp"
            />
            <FormField
              v-model="form.email"
              type="email"
              label="Email"
              placeholder="Masukkan email"
              :error="errors.email"
            />
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.nama_ayah"
              label="Nama Ayah"
              placeholder="Masukkan nama ayah"
              :error="errors.nama_ayah"
            />
            <FormField
              v-model="form.nama_ibu"
              label="Nama Ibu"
              placeholder="Masukkan nama ibu"
              :error="errors.nama_ibu"
            />
            <FormField
              v-model="form.no_hp_ortu"
              label="No. HP Orang Tua"
              placeholder="Masukkan nomor HP orang tua"
              :error="errors.no_hp_ortu"
            />
            <FormField
              v-model="form.status"
              type="select"
              label="Status"
              placeholder="Pilih status"
              :options="statusOptions"
              required
              :error="errors.status"
            />
          </div>
        </form>

        <template #footer>
          <button type="submit" form="siswa-form" :disabled="submitting" class="btn btn-primary">
            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button type="button" @click="closeForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Move Class Modal -->
      <Modal v-model:show="showMoveClass" title="Pindah Kelas" size="sm">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Pindahkan <strong>{{ selectedSiswa?.nama_lengkap }}</strong> ke kelas baru
          </p>
          <FormField
            v-model="moveClassForm.kelas_id"
            type="select"
            label="Kelas Baru"
            placeholder="Pilih kelas baru"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            required
            :error="moveClassErrors.kelas_id"
          />
        </div>

        <template #footer>
          <button @click="submitMoveClass" :disabled="movingClass" class="btn btn-primary">
            {{ movingClass ? 'Memproses...' : 'Pindahkan' }}
          </button>
          <button @click="showMoveClass = false" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Confirmation Dialogs -->
      <ConfirmDialog
        v-model:show="showDeleteConfirm"
        title="Hapus Siswa"
        :message="`Apakah Anda yakin ingin menghapus siswa ${selectedSiswa?.nama_lengkap}?`"
        confirm-text="Ya, Hapus"
        type="error"
        :loading="deleting"
        @confirm="confirmDelete"
      />

      <ConfirmDialog
        v-model:show="showResetPasswordConfirm"
        title="Reset Password"
        :message="`Apakah Anda yakin ingin mereset password siswa ${selectedSiswa?.nama_lengkap}?`"
        confirm-text="Ya, Reset"
        type="warning"
        :loading="resettingPassword"
        @confirm="confirmResetPassword"
      />
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
import ConfirmDialog from '../../../components/ui/ConfirmDialog.vue'

const toast = useToast()

// Data
const siswa = ref([])
const kelasOptions = ref([])
const loading = ref(true)
const submitting = ref(false)
const deleting = ref(false)
const resettingPassword = ref(false)
const movingClass = ref(false)

// Form state
const showForm = ref(false)
const showDeleteConfirm = ref(false)
const showResetPasswordConfirm = ref(false)
const showMoveClass = ref(false)
const isEditing = ref(false)
const selectedSiswa = ref(null)

// Form data
const form = reactive({
  nis: '',
  nisn: '',
  nama_lengkap: '',
  jenis_kelamin: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  agama: '',
  alamat: '',
  no_hp: '',
  email: '',
  nama_ayah: '',
  nama_ibu: '',
  no_hp_ortu: '',
  kelas_id: '',
  status: 'aktif'
})

const moveClassForm = reactive({
  kelas_id: ''
})

const errors = ref({})
const moveClassErrors = ref({})

// Filters
const filters = reactive({
  kelas_id: '',
  status: ''
})

// Table columns
const columns = [
  { key: 'nama_lengkap', label: 'Nama & NIS', sortable: true },
  { key: 'jenis_kelamin', label: 'L/P', sortable: true },
  { key: 'kelas', label: 'Kelas' },
  { key: 'tempat_lahir', label: 'Tempat Lahir', sortable: true },
  { key: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date', sortable: true },
  { key: 'status', label: 'Status', sortable: true }
]

// Options
const agamaOptions = [
  'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'
]

const statusOptions = [
  { value: 'aktif', label: 'Aktif' },
  { value: 'lulus', label: 'Lulus' },
  { value: 'pindah', label: 'Pindah' },
  { value: 'keluar', label: 'Keluar' }
]

// Methods
const fetchSiswa = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.kelas_id) params.append('kelas_id', filters.kelas_id)
    if (filters.status) params.append('status', filters.status)
    
    const response = await axios.get(`/admin/siswa?${params}`)
    siswa.value = response.data.data
  } catch (error) {
    toast.error('Gagal mengambil data siswa')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchKelas = async () => {
  try {
    const response = await axios.get('/admin/kelas')
    kelasOptions.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
  }
}

const resetForm = () => {
  Object.keys(form).forEach(key => {
    form[key] = key === 'status' ? 'aktif' : ''
  })
  errors.value = {}
  isEditing.value = false
  selectedSiswa.value = null
}

const closeForm = () => {
  showForm.value = false
  resetForm()
}

const editSiswa = (siswa) => {
  isEditing.value = true
  selectedSiswa.value = siswa
  Object.keys(form).forEach(key => {
    form[key] = siswa[key] || ''
  })
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    const url = isEditing.value ? `/admin/siswa/${selectedSiswa.value.id}` : '/admin/siswa'
    const method = isEditing.value ? 'put' : 'post'
    
    await axios[method](url, form)
    
    toast.success(`Siswa berhasil ${isEditing.value ? 'diperbarui' : 'ditambahkan'}`)
    closeForm()
    fetchSiswa()
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      toast.error('Terjadi kesalahan saat menyimpan data')
    }
  } finally {
    submitting.value = false
  }
}

const deleteSiswa = (siswa) => {
  selectedSiswa.value = siswa
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/siswa/${selectedSiswa.value.id}`)
    toast.success('Siswa berhasil dihapus')
    showDeleteConfirm.value = false
    fetchSiswa()
  } catch (error) {
    toast.error('Gagal menghapus siswa')
  } finally {
    deleting.value = false
  }
}

const resetPassword = (siswa) => {
  selectedSiswa.value = siswa
  showResetPasswordConfirm.value = true
}

const confirmResetPassword = async () => {
  try {
    resettingPassword.value = true
    await axios.post(`/admin/siswa/${selectedSiswa.value.id}/reset-password`)
    toast.success('Password berhasil direset')
    showResetPasswordConfirm.value = false
  } catch (error) {
    toast.error('Gagal mereset password')
  } finally {
    resettingPassword.value = false
  }
}

const moveClass = (siswa) => {
  selectedSiswa.value = siswa
  moveClassForm.kelas_id = ''
  moveClassErrors.value = {}
  showMoveClass.value = true
}

const submitMoveClass = async () => {
  try {
    movingClass.value = true
    moveClassErrors.value = {}
    
    await axios.post(`/admin/siswa/${selectedSiswa.value.id}/move-class`, moveClassForm)
    toast.success('Siswa berhasil dipindahkan kelas')
    showMoveClass.value = false
    fetchSiswa()
  } catch (error) {
    if (error.response?.status === 422) {
      moveClassErrors.value = error.response.data.errors || {}
    } else {
      toast.error('Gagal memindahkan siswa')
    }
  } finally {
    movingClass.value = false
  }
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
  fetchSiswa()
  fetchKelas()
})
</script>
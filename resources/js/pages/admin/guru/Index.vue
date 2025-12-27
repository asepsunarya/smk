<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data Guru"
        description="Kelola data guru dan tenaga pendidik"
        :data="guru"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data guru"
        empty-description="Mulai dengan menambahkan guru baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="openForm" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Guru
          </button>
        </template>

        <template #filters>
          <!-- <FormField
            v-model="filters.status"
            type="select"
            placeholder="Status"
            :options="statusOptions"
            @update:model-value="fetchGuru"
          /> -->
          <FormField
            v-model="filters.bidang_studi"
            type="text"
            placeholder="Bidang Studi"
            @update:model-value="fetchGuru"
          />
        </template>

        <template #cell-nama_lengkap="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.nama_lengkap.charAt(0) }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.nama_lengkap }}</div>
              <div class="text-sm text-gray-500">NUPTK: {{ item.nuptk }}</div>
            </div>
          </div>
        </template>

        <!-- <template #cell-status="{ item }">
          <span :class="getStatusBadge(item.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ formatStatus(item.status) }}
          </span>
        </template> -->

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="editGuru(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <!-- <button @click="resetPassword(item)" class="text-yellow-600 hover:text-yellow-900" title="Reset Password">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-6 6H4a3 3 0 01-3-3V9a3 3 0 013-3h2M8 7a2 2 0 012-2h2m0 0a2 2 0 012 2M7 7a2 2 0 00-2 2m0 0a2 2 0 002 2h4a2 2 0 002-2m-2 0a2 2 0 00-2-2H7z"></path>
              </svg>
            </button> -->
            <!-- <button @click="toggleStatus(item)" class="text-indigo-600 hover:text-indigo-900" :title="item.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan'">
              <svg v-if="item.status === 'aktif'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </button> -->
            <button @click="deleteGuru(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Guru' : 'Tambah Guru'" size="lg">
        <form @submit.prevent="submitForm" id="guru-form" class="space-y-4">
          <!-- Display user info when editing -->
          <div v-if="isEditing && selectedGuru?.user" class="p-3 bg-gray-50 rounded-lg mb-4">
            <p class="text-sm text-gray-700">
              <strong>Nama:</strong> {{ selectedGuru.user.name }}<br>
              <strong>Email:</strong> {{ selectedGuru.user.email }}<br>
              <strong>Role:</strong> {{ getRoleLabel(selectedGuru.user.role) }}
            </p>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.nama_lengkap"
              label="Nama Lengkap"
              placeholder="Masukkan nama lengkap"
              required
              :error="errors.nama_lengkap"
            />
            <FormField
              v-model="form.nuptk"
              label="NUPTK"
              placeholder="Masukkan NUPTK"
              required
              :error="errors.nuptk"
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
              required
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
              v-model="form.no_hp"
              label="No. HP"
              placeholder="Masukkan nomor HP"
              required
              :error="errors.no_hp"
            />
            <FormField
              v-model="form.pendidikan_terakhir"
              label="Pendidikan Terakhir"
              placeholder="Contoh: S1, S2, dll"
              required
              :error="errors.pendidikan_terakhir"
            />
            <FormField
              v-model="form.bidang_studi"
              label="Bidang Studi"
              placeholder="Masukkan bidang studi"
              required
              :error="errors.bidang_studi"
            />
            <FormField
              v-model="form.tanggal_masuk"
              type="date"
              label="Tanggal Masuk"
              required
              :error="errors.tanggal_masuk"
            />
            <!-- <FormField
              v-if="isEditing"
              v-model="form.status"
              type="select"
              label="Status"
              placeholder="Pilih status"
              :options="statusOptions"
              required
              :error="errors.status"
            /> -->
          </div>
          
          <div>
            <FormField
              v-model="form.alamat"
              type="textarea"
              label="Alamat"
              placeholder="Masukkan alamat lengkap"
              required
              :error="errors.alamat"
              :rows="3"
            />
          </div>
        </form>

        <template #footer>
          <button type="submit" form="guru-form" :disabled="submitting" class="btn btn-primary">
            <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button type="button" @click="closeForm" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Reset Password Modal -->
      <Modal v-model:show="showResetPasswordModal" title="Reset Password" size="sm">
        <form @submit.prevent="confirmResetPassword" id="reset-password-form" class="space-y-4">
          <p class="text-sm text-gray-600">
            Reset password untuk guru <strong>{{ selectedGuru?.nama_lengkap }}</strong>
          </p>
          <FormField
            v-model="resetPasswordForm.password"
            type="password"
            label="Password Baru"
            placeholder="Masukkan password baru (min. 8 karakter)"
            required
            :error="resetPasswordErrors.password"
          />
          <FormField
            v-model="resetPasswordForm.password_confirmation"
            type="password"
            label="Konfirmasi Password"
            placeholder="Konfirmasi password baru"
            required
            :error="resetPasswordErrors.password_confirmation"
          />
        </form>

        <template #footer>
          <button type="submit" form="reset-password-form" :disabled="resettingPassword" class="btn btn-primary">
            {{ resettingPassword ? 'Mereset...' : 'Reset Password' }}
          </button>
          <button type="button" @click="showResetPasswordModal = false" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>

      <!-- Confirmation Dialogs -->
      <ConfirmDialog
        v-model:show="showDeleteConfirm"
        title="Hapus Guru"
        :message="`Apakah Anda yakin ingin menghapus guru ${selectedGuru?.nama_lengkap}?`"
        confirm-text="Ya, Hapus"
        type="error"
        :loading="deleting"
        @confirm="confirmDelete"
      />

      <ConfirmDialog
        v-model:show="showToggleStatusConfirm"
        title="Ubah Status Guru"
        :message="`Apakah Anda yakin ingin ${selectedGuru?.status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan'} guru ${selectedGuru?.nama_lengkap}?`"
        confirm-text="Ya, Ubah"
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
const guru = ref([])
const loading = ref(true)
const submitting = ref(false)
const deleting = ref(false)
const resettingPassword = ref(false)
const togglingStatus = ref(false)

// Form state
const showForm = ref(false)
const showDeleteConfirm = ref(false)
const showToggleStatusConfirm = ref(false)
const showResetPasswordModal = ref(false)
const isEditing = ref(false)
const selectedGuru = ref(null)

// Form data
const form = reactive({
  user_id: '',
  nuptk: '',
  jenis_kelamin: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  agama: '',
  alamat: '',
  no_hp: '',
  pendidikan_terakhir: '',
  bidang_studi: '',
  tanggal_masuk: '',
  status: 'aktif'
})

// Available users for selection
const availableUserOptions = ref([])
const selectedUser = ref(null)

const resetPasswordForm = reactive({
  password: '',
  password_confirmation: ''
})

const errors = ref({})
const resetPasswordErrors = ref({})

// Filters
const filters = reactive({
  search: '',
  status: '',
  bidang_studi: ''
})

// Table columns
const columns = [
  { key: 'nama_lengkap', label: 'Nama & NIP', sortable: true },
  { key: 'bidang_studi', label: 'Bidang Studi', sortable: true },
  // { key: 'status', label: 'Status', sortable: true }
]

// Options
const roleOptions = [
  { value: 'guru', label: 'Guru' },
  { value: 'wali_kelas', label: 'Wali Kelas' },
  { value: 'kepala_sekolah', label: 'Kepala Sekolah' }
]

const statusOptions = [
  { value: 'aktif', label: 'Aktif' },
  { value: 'non_aktif', label: 'Non-Aktif' },
  { value: 'pensiun', label: 'Pensiun' }
]

const agamaOptions = [
  'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'
]

// Methods
const fetchGuru = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.bidang_studi) params.append('bidang_studi', filters.bidang_studi)
    params.append('per_page', 100)
    
    const response = await axios.get(`/admin/guru?${params}`)
    if (response.data.data) {
      guru.value = response.data.data
    } else if (Array.isArray(response.data)) {
      guru.value = response.data
    } else {
      guru.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data guru')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (key === 'status') {
      form[key] = 'aktif'
    } else {
      form[key] = ''
    }
  })
  errors.value = {}
  isEditing.value = false
  selectedGuru.value = null
  selectedUser.value = null
}

const fetchAvailableUsers = async () => {
  try {
    const response = await axios.get('/admin/guru/available-users')
    availableUserOptions.value = response.data.map(user => ({
      ...user,
      label: `${user.name} (${user.email}) - ${getRoleLabel(user.role)}`
    }))
  } catch (error) {
    console.error('Failed to fetch available users:', error)
    toast.error('Gagal mengambil data user')
  }
}

const onUserSelect = (userId) => {
  const user = availableUserOptions.value.find(u => u.id == userId)
  if (user) {
    selectedUser.value = user
    // Auto-fill NUPTK if available
    if (user.nuptk) {
      form.nuptk = user.nuptk
    }
  } else {
    selectedUser.value = null
  }
}

const getRoleLabel = (role) => {
  const roleMap = {
    'guru': 'Guru',
    'wali_kelas': 'Wali Kelas',
    'kepala_sekolah': 'Kepala Sekolah'
  }
  return roleMap[role] || role
}

const closeForm = () => {
  showForm.value = false
  resetForm()
}

const openForm = () => {
  showForm.value = true
}

const editGuru = (guruItem) => {
  isEditing.value = true
  selectedGuru.value = guruItem
  form.nama_lengkap = guruItem.nama_lengkap || ''
  form.nuptk = guruItem.nuptk || ''
  form.jenis_kelamin = guruItem.jenis_kelamin || ''
  form.tempat_lahir = guruItem.tempat_lahir || ''
  form.tanggal_lahir = guruItem.tanggal_lahir || ''
  form.agama = guruItem.agama || ''
  form.alamat = guruItem.alamat || ''
  form.no_hp = guruItem.no_hp || ''
  form.pendidikan_terakhir = guruItem.pendidikan_terakhir || ''
  form.bidang_studi = guruItem.bidang_studi || ''
  form.tanggal_masuk = guruItem.tanggal_masuk || ''
  form.status = guruItem.status || 'aktif'
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    const url = isEditing.value ? `/admin/guru/${selectedGuru.value.id}` : '/admin/guru'
    const method = isEditing.value ? 'put' : 'post'
    
    // Only send required fields
    const payload = {
      nama_lengkap: form.nama_lengkap,
      nuptk: form.nuptk,
      jenis_kelamin: form.jenis_kelamin,
      tempat_lahir: form.tempat_lahir,
      tanggal_lahir: form.tanggal_lahir,
      agama: form.agama,
      alamat: form.alamat,
      no_hp: form.no_hp,
      pendidikan_terakhir: form.pendidikan_terakhir,
      bidang_studi: form.bidang_studi,
      tanggal_masuk: form.tanggal_masuk,
    }

    // Add status for editing
    if (isEditing.value) {
      payload.status = form.status
    }
    
    await axios[method](url, payload)
    
    toast.success(`Guru berhasil ${isEditing.value ? 'diperbarui' : 'ditambahkan'}`)
    closeForm()
    fetchGuru()
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

const deleteGuru = (guruItem) => {
  selectedGuru.value = guruItem
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/guru/${selectedGuru.value.id}`)
    toast.success('Guru berhasil dihapus')
    showDeleteConfirm.value = false
    fetchGuru()
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menghapus guru'
    toast.error(message)
  } finally {
    deleting.value = false
  }
}

const resetPassword = (guruItem) => {
  selectedGuru.value = guruItem
  resetPasswordForm.password = ''
  resetPasswordForm.password_confirmation = ''
  resetPasswordErrors.value = {}
  showResetPasswordModal.value = true
}

const confirmResetPassword = async () => {
  try {
    resettingPassword.value = true
    resetPasswordErrors.value = {}
    
    await axios.post(`/admin/guru/${selectedGuru.value.id}/reset-password`, resetPasswordForm)
    toast.success('Password berhasil direset')
    showResetPasswordModal.value = false
  } catch (error) {
    if (error.response?.status === 422) {
      resetPasswordErrors.value = error.response.data.errors || {}
    } else {
      toast.error('Gagal mereset password')
    }
  } finally {
    resettingPassword.value = false
  }
}

const toggleStatus = (guruItem) => {
  selectedGuru.value = guruItem
  showToggleStatusConfirm.value = true
}

const confirmToggleStatus = async () => {
  try {
    togglingStatus.value = true
    await axios.post(`/admin/guru/${selectedGuru.value.id}/toggle-status`)
    toast.success('Status guru berhasil diubah')
    showToggleStatusConfirm.value = false
    fetchGuru()
  } catch (error) {
    toast.error('Gagal mengubah status guru')
  } finally {
    togglingStatus.value = false
  }
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchGuru()
}

const formatRole = (role) => {
  const roleMap = {
    guru: 'Guru',
    wali_kelas: 'Wali Kelas',
    kepala_sekolah: 'Kepala Sekolah'
  }
  return roleMap[role] || role
}

const formatStatus = (status) => {
  const statusMap = {
    aktif: 'Aktif',
    non_aktif: 'Non-Aktif',
    pensiun: 'Pensiun'
  }
  return statusMap[status] || status
}

const getRoleBadge = (role) => {
  const badges = {
    guru: 'bg-blue-100 text-blue-800',
    wali_kelas: 'bg-indigo-100 text-indigo-800',
    kepala_sekolah: 'bg-yellow-100 text-yellow-800'
  }
  return badges[role] || 'bg-gray-100 text-gray-800'
}

const getStatusBadge = (status) => {
  const badges = {
    aktif: 'bg-green-100 text-green-800',
    non_aktif: 'bg-red-100 text-red-800',
    pensiun: 'bg-gray-100 text-gray-800'
  }
  return badges[status] || 'bg-gray-100 text-gray-800'
}

// Lifecycle
onMounted(() => {
  fetchGuru()
})
</script>

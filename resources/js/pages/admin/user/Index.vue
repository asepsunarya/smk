<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Data User"
        description="Kelola data user dan akun sistem"
        :data="users"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada data user"
        empty-description="Mulai dengan menambahkan user baru."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="showForm = true" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah User
          </button>
        </template>

        <template #filters>
          <FormField
            v-model="filters.role"
            type="select"
            placeholder="Pilih Role"
            :options="roleOptions"
            @update:model-value="fetchUsers"
          />
          <FormField
            v-model="filters.is_active"
            type="select"
            placeholder="Status Aktif"
            :options="activeStatusOptions"
            @update:model-value="fetchUsers"
          />
        </template>

        <template #cell-name="{ item }">
          <div class="flex items-center">
            <div :class="getRoleBadgeColor(item.role)" class="h-10 w-10 rounded-full flex items-center justify-center text-white font-medium">
              {{ item.name.charAt(0) }}
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
              <div class="text-sm text-gray-500">{{ item.email }}</div>
            </div>
          </div>
        </template>

        <template #cell-role="{ item }">
          <span :class="getRoleBadge(item.role)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ formatRole(item.role) }}
          </span>
        </template>

        <template #cell-is_active="{ item }">
          <span :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
          </span>
        </template>

        <template #cell-profile="{ item }">
          <div class="text-sm text-gray-900">
            <div v-if="item.guru" class="text-blue-600">Guru: {{ item.guru.nama_lengkap }}</div>
            <div v-else-if="item.siswa" class="text-green-600">Siswa: {{ item.siswa.nama_lengkap }}</div>
            <div v-else class="text-gray-400">-</div>
          </div>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="editUser(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="resetPassword(item)" class="text-yellow-600 hover:text-yellow-900" title="Reset Password">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-6 6H4a3 3 0 01-3-3V9a3 3 0 013-3h2M8 7a2 2 0 012-2h2m0 0a2 2 0 012 2M7 7a2 2 0 00-2 2m0 0a2 2 0 002 2h4a2 2 0 002-2m-2 0a2 2 0 00-2-2H7z"></path>
              </svg>
            </button>
            <button @click="toggleStatus(item)" class="text-indigo-600 hover:text-indigo-900" :title="item.is_active ? 'Nonaktifkan' : 'Aktifkan'">
              <svg v-if="item.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </button>
            <button @click="deleteUser(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <!-- Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit User' : 'Tambah User'" size="lg">
        <form @submit.prevent="submitForm" id="user-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.role"
              type="select"
              label="Role"
              placeholder="Pilih role terlebih dahulu"
              :options="roleOptions"
              required
              :error="errors.role"
              @update:model-value="handleRoleChange"
            />
            <FormField
              v-model="form.email"
              type="email"
              label="Email"
              placeholder="Masukkan email"
              required
              :error="errors.email"
            />
            <FormField
              v-if="!isEditing"
              v-model="form.password"
              type="password"
              label="Password"
              placeholder="Masukkan password (min. 8 karakter)"
              required
              :error="errors.password"
            />
            
            <!-- Nama Lengkap - hanya untuk admin -->
            <FormField
              v-if="form.role === 'admin'"
              v-model="form.name"
              label="Nama Lengkap"
              placeholder="Masukkan nama lengkap"
              required
              :error="errors.name"
            />
            
            <!-- Nama Lengkap - auto-fill dari guru untuk guru, wali_kelas, kepala_sekolah -->
            <div v-if="['guru', 'wali_kelas', 'kepala_sekolah'].includes(form.role)" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
              <input
                type="text"
                :value="form.name"
                disabled
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"
                placeholder="Pilih guru terlebih dahulu"
              />
              <p class="text-xs text-gray-500">Nama akan diambil dari guru yang dipilih</p>
            </div>
            
            <!-- Nama Lengkap - auto-fill dari siswa untuk siswa -->
            <div v-if="form.role === 'siswa'" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
              <input
                type="text"
                :value="form.name"
                disabled
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"
                placeholder="Pilih siswa terlebih dahulu"
              />
              <p class="text-xs text-gray-500">Nama akan diambil dari siswa yang dipilih</p>
            </div>
            
            <!-- Pilih Guru (for guru, wali_kelas, kepala_sekolah) -->
            <FormField
              v-if="['guru', 'wali_kelas', 'kepala_sekolah'].includes(form.role) && !isEditing"
              v-model="form.guru_id"
              type="select"
              label="Pilih Guru"
              placeholder="Pilih guru yang sudah ada"
              :options="availableGuruOptions"
              option-value="id"
              option-label="label"
              required
              :error="errors.guru_id"
              @update:model-value="onGuruSelect"
            />
            <div v-if="['guru', 'wali_kelas', 'kepala_sekolah'].includes(form.role) && selectedGuru && !isEditing" class="p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-gray-700">
                <strong>Nama:</strong> {{ selectedGuru.nama_lengkap }}<br>
                <strong>NUPTK:</strong> {{ selectedGuru.nuptk }}<br>
                <strong>Bidang Studi:</strong> {{ selectedGuru.bidang_studi }}
              </p>
            </div>
            
            <!-- Pilih Siswa (for siswa) -->
            <FormField
              v-if="form.role === 'siswa' && !isEditing"
              v-model="form.siswa_id"
              type="select"
              label="Pilih Siswa"
              placeholder="Pilih siswa yang sudah ada"
              :options="availableSiswaOptions"
              option-value="id"
              option-label="label"
              required
              :error="errors.siswa_id"
              @update:model-value="onSiswaSelect"
            />
            <div v-if="form.role === 'siswa' && selectedSiswa && !isEditing" class="p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-gray-700">
                <strong>Nama:</strong> {{ selectedSiswa.nama_lengkap }}<br>
                <strong>NIS:</strong> {{ selectedSiswa.nis }}<br>
                <strong>Kelas:</strong> {{ selectedSiswa.kelas?.nama_kelas || '-' }}
              </p>
            </div>
            <FormField
              v-if="isEditing"
              v-model="form.is_active"
              type="checkbox"
              label="Status Aktif"
              :error="errors.is_active"
            />
          </div>
        </form>

        <template #footer>
          <button type="submit" form="user-form" :disabled="submitting" class="btn btn-primary">
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
            Reset password untuk user <strong>{{ selectedUser?.name }}</strong>
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
        title="Hapus User"
        :message="`Apakah Anda yakin ingin menghapus user ${selectedUser?.name}?`"
        confirm-text="Ya, Hapus"
        type="error"
        :loading="deleting"
        @confirm="confirmDelete"
      />

      <ConfirmDialog
        v-model:show="showToggleStatusConfirm"
        title="Ubah Status User"
        :message="`Apakah Anda yakin ingin ${selectedUser?.is_active ? 'menonaktifkan' : 'mengaktifkan'} user ${selectedUser?.name}?`"
        confirm-text="Ya, Ubah"
        type="warning"
        :loading="togglingStatus"
        @confirm="confirmToggleStatus"
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
const users = ref([])
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
const selectedUser = ref(null)

// Form data
const form = reactive({
  name: '',
  email: '',
  password: '',
  role: '',
  guru_id: '',
  siswa_id: '',
  nuptk: '',
  nis: '',
  is_active: true
})

// Available guru for selection
const availableGuruOptions = ref([])
const selectedGuru = ref(null)

// Available siswa for selection
const availableSiswaOptions = ref([])
const selectedSiswa = ref(null)

const resetPasswordForm = reactive({
  password: '',
  password_confirmation: ''
})

const errors = ref({})
const resetPasswordErrors = ref({})

// Filters
const filters = reactive({
  search: '',
  role: '',
  is_active: ''
})

// Table columns
const columns = [
  { key: 'name', label: 'Nama & Email', sortable: true },
  { key: 'role', label: 'Role', sortable: true },
  { key: 'profile', label: 'Profile Terkait' },
  { key: 'nuptk', label: 'NUPTK/NIS', sortable: true },
  { key: 'is_active', label: 'Status', sortable: true }
]

// Options
const roleOptions = [
  { value: 'admin', label: 'Admin' },
  { value: 'guru', label: 'Guru' },
  { value: 'wali_kelas', label: 'Wali Kelas' },
  { value: 'kepala_sekolah', label: 'Kepala Sekolah' },
  { value: 'siswa', label: 'Siswa' }
]

const activeStatusOptions = [
  { value: 'true', label: 'Aktif' },
  { value: 'false', label: 'Non-Aktif' }
]

// Computed
const needsNuptk = computed(() => {
  return ['guru', 'wali_kelas', 'kepala_sekolah'].includes(form.role)
})

const needsNis = computed(() => {
  return form.role === 'siswa'
})

// Methods
const fetchUsers = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.role) params.append('role', filters.role)
    if (filters.is_active) params.append('is_active', filters.is_active)
    // Get more items per page for client-side pagination
    params.append('per_page', 100)
    
    const response = await axios.get(`/admin/user?${params}`)
    // Handle paginated response
    if (response.data.data) {
      users.value = response.data.data
    } else if (Array.isArray(response.data)) {
      users.value = response.data
    } else {
      users.value = []
    }
  } catch (error) {
    toast.error('Gagal mengambil data user')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchAvailableGuru = async () => {
  try {
    const response = await axios.get('/admin/guru/available-guru')
    availableGuruOptions.value = response.data.map(guru => ({
      ...guru,
      label: `${guru.nama_lengkap} (${guru.nuptk}) - ${guru.bidang_studi}`
    }))
  } catch (error) {
    console.error('Failed to fetch available guru:', error)
    toast.error('Gagal mengambil data guru')
  }
}

const fetchAvailableSiswa = async () => {
  try {
    const response = await axios.get('/admin/siswa/available-siswa')
    availableSiswaOptions.value = response.data.map(siswa => ({
      ...siswa,
      label: `${siswa.nama_lengkap} (${siswa.nis}) - ${siswa.kelas?.nama_kelas || '-'}`
    }))
  } catch (error) {
    console.error('Failed to fetch available siswa:', error)
    toast.error('Gagal mengambil data siswa')
  }
}

const onGuruSelect = (guruId) => {
  const guru = availableGuruOptions.value.find(g => g.id == guruId)
  if (guru) {
    selectedGuru.value = guru
    // Auto-fill name and NUPTK from guru (nama lengkap wajib dari guru)
    form.name = guru.nama_lengkap
    form.nuptk = guru.nuptk
  } else {
    selectedGuru.value = null
    form.name = ''
    form.nuptk = ''
  }
}

const onSiswaSelect = (siswaId) => {
  const siswa = availableSiswaOptions.value.find(s => s.id == siswaId)
  if (siswa) {
    selectedSiswa.value = siswa
    // Auto-fill name and NIS from siswa
    form.name = siswa.nama_lengkap
    form.nis = siswa.nis
  } else {
    selectedSiswa.value = null
    form.name = ''
    form.nis = ''
  }
}

const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (key === 'is_active') {
      form[key] = true
    } else {
      form[key] = ''
    }
  })
  errors.value = {}
  isEditing.value = false
  selectedUser.value = null
  selectedGuru.value = null
  selectedSiswa.value = null
  availableGuruOptions.value = []
  availableSiswaOptions.value = []
}

const closeForm = () => {
  showForm.value = false
  resetForm()
}

const handleRoleChange = () => {
  // Clear all fields when role changes
  form.name = ''
  form.nuptk = ''
  form.nis = ''
  form.guru_id = ''
  form.siswa_id = ''
  selectedGuru.value = null
  selectedSiswa.value = null
  
  // Fetch available data based on role
  if (['guru', 'wali_kelas', 'kepala_sekolah'].includes(form.role) && !isEditing.value) {
    fetchAvailableGuru()
  } else if (form.role === 'siswa' && !isEditing.value) {
    fetchAvailableSiswa()
  }
}

const openForm = () => {
  resetForm()
  showForm.value = true
  // Will fetch guru when role is selected
}

const editUser = (user) => {
  isEditing.value = true
  selectedUser.value = user
  form.name = user.name || ''
  form.email = user.email || ''
  form.role = user.role || ''
  form.nuptk = user.nuptk || ''
  form.nis = user.nis || ''
  form.is_active = user.is_active ?? true
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    // Validate: if role requires guru, guru_id must be selected
    if (['guru', 'wali_kelas', 'kepala_sekolah'].includes(form.role) && !isEditing.value && !form.guru_id) {
      errors.value.guru_id = [`Guru harus dipilih untuk role ${form.role}`]
      toast.error('Pilih guru terlebih dahulu')
      submitting.value = false
      return
    }

    // Validate: if role is siswa, siswa_id must be selected
    if (form.role === 'siswa' && !isEditing.value && !form.siswa_id) {
      errors.value.siswa_id = ['Siswa harus dipilih untuk role siswa']
      toast.error('Pilih siswa terlebih dahulu')
      submitting.value = false
      return
    }

    // Validate: if role requires data selection, name must be filled
    if (['guru', 'wali_kelas', 'kepala_sekolah', 'siswa'].includes(form.role) && !isEditing.value && !form.name) {
      errors.value.name = ['Nama lengkap harus diambil dari data yang dipilih']
      toast.error('Pilih data terlebih dahulu')
      submitting.value = false
      return
    }

    // Validate: admin requires manual name input
    if (form.role === 'admin' && !form.name) {
      errors.value.name = ['Nama lengkap wajib diisi untuk role admin']
      toast.error('Nama lengkap wajib diisi')
      submitting.value = false
      return
    }

    const url = isEditing.value ? `/admin/user/${selectedUser.value.id}` : '/admin/user'
    const method = isEditing.value ? 'put' : 'post'
    
    const payload = { ...form }
    if (isEditing.value) {
      delete payload.password
      delete payload.guru_id // Don't allow changing guru on edit
    }
    
    // Only include guru_id if role requires it
    if (!['guru', 'wali_kelas', 'kepala_sekolah'].includes(form.role)) {
      delete payload.guru_id
    }
    
    // Only include siswa_id if role is siswa
    if (form.role !== 'siswa') {
      delete payload.siswa_id
    }
    
    // Remove NUPTK and NIS from payload (will be auto-filled from selected data)
    delete payload.nuptk
    delete payload.nis
    
    await axios[method](url, payload)
    
    toast.success(`User berhasil ${isEditing.value ? 'diperbarui' : 'ditambahkan'}`)
    closeForm()
    fetchUsers()
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

const deleteUser = (user) => {
  selectedUser.value = user
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  try {
    deleting.value = true
    await axios.delete(`/admin/user/${selectedUser.value.id}`)
    toast.success('User berhasil dihapus')
    showDeleteConfirm.value = false
    fetchUsers()
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menghapus user'
    toast.error(message)
  } finally {
    deleting.value = false
  }
}

const resetPassword = (user) => {
  selectedUser.value = user
  resetPasswordForm.password = ''
  resetPasswordForm.password_confirmation = ''
  resetPasswordErrors.value = {}
  showResetPasswordModal.value = true
}

const confirmResetPassword = async () => {
  try {
    resettingPassword.value = true
    resetPasswordErrors.value = {}
    
    await axios.post(`/admin/user/${selectedUser.value.id}/reset-password`, resetPasswordForm)
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

const toggleStatus = (user) => {
  selectedUser.value = user
  showToggleStatusConfirm.value = true
}

const confirmToggleStatus = async () => {
  try {
    togglingStatus.value = true
    await axios.post(`/admin/user/${selectedUser.value.id}/toggle-status`)
    toast.success('Status user berhasil diubah')
    showToggleStatusConfirm.value = false
    fetchUsers()
  } catch (error) {
    toast.error('Gagal mengubah status user')
  } finally {
    togglingStatus.value = false
  }
}

const formatRole = (role) => {
  const roleMap = {
    admin: 'Admin',
    guru: 'Guru',
    wali_kelas: 'Wali Kelas',
    kepala_sekolah: 'Kepala Sekolah',
    siswa: 'Siswa'
  }
  return roleMap[role] || role
}

const getRoleBadge = (role) => {
  const badges = {
    admin: 'bg-purple-100 text-purple-800',
    guru: 'bg-blue-100 text-blue-800',
    wali_kelas: 'bg-indigo-100 text-indigo-800',
    kepala_sekolah: 'bg-yellow-100 text-yellow-800',
    siswa: 'bg-green-100 text-green-800'
  }
  return badges[role] || 'bg-gray-100 text-gray-800'
}

const getRoleBadgeColor = (role) => {
  const colors = {
    admin: 'bg-purple-600',
    guru: 'bg-blue-600',
    wali_kelas: 'bg-indigo-600',
    kepala_sekolah: 'bg-yellow-600',
    siswa: 'bg-green-600'
  }
  return colors[role] || 'bg-gray-600'
}

const handleSearch = (searchTerm) => {
  filters.search = searchTerm
  fetchUsers()
}

// Lifecycle
onMounted(() => {
  fetchUsers()
})
</script>


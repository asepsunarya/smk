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

        <template #cell-profile="{ item }">
          <div class="text-sm text-gray-900">
            <div v-if="item.guru" class="text-blue-600">Guru: {{ item.guru.nama_lengkap }}</div>
            <div v-else-if="item.siswa" class="text-green-600">Siswa: {{ item.siswa.nama_lengkap }}</div>
            <div v-else class="text-gray-400">-</div>
          </div>
        </template>

        <template #cell-nuptk="{ item }">
          <span class="text-sm text-gray-900">
            {{ item.guru?.nuptk ?? item.siswa?.nis ?? item.nis ?? item.nuptk ?? '-' }}
          </span>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="editUser(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
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
              v-model="form.password"
              type="password"
              label="Password"
              :placeholder="isEditing ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan password (min. 8 karakter)'"
              :required="!isEditing"
              :error="errors.password"
            />
            
            <!-- Pilih Guru (for guru, kepala_sekolah) -->
            <FormField
              v-if="['guru', 'kepala_sekolah'].includes(form.role)"
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
            <div v-if="['guru', 'kepala_sekolah'].includes(form.role) && selectedGuru" class="p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-gray-700">
                <strong>Nama:</strong> {{ selectedGuru.nama_lengkap }}<br>
                <strong>NUPTK:</strong> {{ selectedGuru.nuptk }}<br>
                <strong>Bidang Studi:</strong> {{ selectedGuru.bidang_studi }}
              </p>
            </div>
            
            <!-- Pilih Siswa (for siswa) -->
            <FormField
              v-if="form.role === 'siswa'"
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
            <div v-if="form.role === 'siswa' && selectedSiswa" class="p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-gray-700">
                <strong>Nama:</strong> {{ selectedSiswa.nama_lengkap }}<br>
                <strong>NIS:</strong> {{ selectedSiswa.nis }}<br>
                <strong>Kelas:</strong> {{ selectedSiswa.kelas?.nama_kelas || '-' }}
              </p>
            </div>
            
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

// Form state
const showForm = ref(false)
const showDeleteConfirm = ref(false)
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
  nis: ''
})

// Available guru for selection
const availableGuruOptions = ref([])
const selectedGuru = ref(null)

// Available siswa for selection
const availableSiswaOptions = ref([])
const selectedSiswa = ref(null)

const errors = ref({})

// Filters
const filters = reactive({
  search: '',
  role: ''
})

// Table columns
const columns = [
  { key: 'name', label: 'Nama & Email', sortable: true },
  { key: 'role', label: 'Role', sortable: true },
  { key: 'profile', label: 'Profile Terkait' },
  { key: 'nuptk', label: 'NUPTK/NIS', sortable: true }
]

// Options
const roleOptions = [
  { value: 'admin', label: 'Admin' },
  { value: 'guru', label: 'Guru' },
  { value: 'kepala_sekolah', label: 'Kepala Sekolah' },
  { value: 'siswa', label: 'Siswa' }
]

// Computed
const needsNuptk = computed(() => {
  return ['guru', 'kepala_sekolah'].includes(form.role)
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
    const params = new URLSearchParams()
    if (isEditing.value && selectedUser.value) {
      params.append('user_id', selectedUser.value.id)
    }
    const response = await axios.get(`/admin/guru/available-guru?${params}`)
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
    const params = new URLSearchParams()
    if (isEditing.value && selectedUser.value) {
      params.append('user_id', selectedUser.value.id)
    }
    const response = await axios.get(`/admin/siswa/available-siswa?${params}`)
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
    // Auto-fill name from guru
    form.name = guru.nama_lengkap
  } else {
    selectedGuru.value = null
    form.name = ''
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
      form[key] = ''
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
  form.nis = ''
  form.guru_id = ''
  form.siswa_id = ''
  selectedGuru.value = null
  selectedSiswa.value = null
  
  // Fetch available data based on role
  if (['guru', 'kepala_sekolah'].includes(form.role)) {
    fetchAvailableGuru()
  } else if (form.role === 'siswa') {
    fetchAvailableSiswa()
  }
}

const openForm = () => {
  resetForm()
  showForm.value = true
  // Will fetch guru when role is selected
}

const editUser = async (user) => {
  isEditing.value = true
  selectedUser.value = user
  form.name = user.name || ''
  form.email = user.email || ''
  form.role = user.role || ''
  form.nis = user.nis || ''
  form.password = '' // Always empty for edit, user can fill if they want to change password
  
  // Load related guru/siswa if exists
  if (user.guru) {
    form.guru_id = user.guru.id
    selectedGuru.value = {
      id: user.guru.id,
      nama_lengkap: user.guru.nama_lengkap,
      nuptk: user.guru.nuptk,
      bidang_studi: user.guru.bidang_studi
    }
    // Fetch available guru options
    await fetchAvailableGuru()
  } else if (user.siswa) {
    form.siswa_id = user.siswa.id
    selectedSiswa.value = {
      id: user.siswa.id,
      nama_lengkap: user.siswa.nama_lengkap,
      nis: user.siswa.nis,
      kelas: user.siswa.kelas
    }
    // Fetch available siswa options
    await fetchAvailableSiswa()
  } else {
    // Fetch available options based on role
    if (['guru', 'kepala_sekolah'].includes(form.role)) {
      await fetchAvailableGuru()
    } else if (form.role === 'siswa') {
      await fetchAvailableSiswa()
    }
  }
  
  showForm.value = true
}

const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    // Validate: if role requires guru, guru_id must be selected
    if (['guru', 'kepala_sekolah'].includes(form.role) && !isEditing.value && !form.guru_id) {
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
    if (['guru', 'kepala_sekolah', 'siswa'].includes(form.role) && !isEditing.value && !form.name) {
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
    // For edit, only include password if provided (not empty)
    if (isEditing.value && !payload.password) {
      delete payload.password
    }
    
    // Only include guru_id if role requires it
    if (!['guru', 'kepala_sekolah'].includes(form.role)) {
      delete payload.guru_id
    }
    
    if (form.role !== 'siswa') {
      delete payload.siswa_id
    }

    // NIS untuk siswa selalu diambil dari data siswa (backend), jangan kirim dari form
    if (form.role === 'siswa') {
      delete payload.nis
    }
    
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


const formatRole = (role) => {
  const roleMap = {
    admin: 'Admin',
    guru: 'Guru',
    kepala_sekolah: 'Kepala Sekolah',
    siswa: 'Siswa'
  }
  return roleMap[role] || role
}

const getRoleBadge = (role) => {
  const badges = {
    admin: 'bg-purple-100 text-purple-800',
    guru: 'bg-blue-100 text-blue-800',
    kepala_sekolah: 'bg-yellow-100 text-yellow-800',
    siswa: 'bg-green-100 text-green-800'
  }
  return badges[role] || 'bg-gray-100 text-gray-800'
}

const getRoleBadgeColor = (role) => {
  const colors = {
    admin: 'bg-purple-600',
    guru: 'bg-blue-600',
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


<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Persetujuan Rapor
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Tinjau dan setujui rapor siswa yang telah diselesaikan
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <button 
            @click="bulkApprove" 
            :disabled="selectedRapor.length === 0 || bulkProcessing"
            class="btn btn-primary"
          >
            <svg v-if="bulkProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ bulkProcessing ? 'Memproses...' : `Setujui ${selectedRapor.length} Rapor` }}
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Menunggu Persetujuan</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ summary.pending || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Disetujui</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ summary.approved || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Ditolak</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ summary.rejected || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Rapor</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ summary.total || 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <FormField
            v-model="filters.tahun_ajaran_id"
            type="select"
            label="Tahun Ajaran"
            placeholder="Pilih Tahun Ajaran"
            :options="tahunAjaranOptions"
            option-value="id"
            option-label="full_description"
            @update:model-value="fetchRapor"
          />
          <FormField
            v-model="filters.semester"
            type="select"
            label="Semester"
            placeholder="Pilih Semester"
            :options="semesterOptions"
            @update:model-value="fetchRapor"
          />
          <FormField
            v-model="filters.kelas_id"
            type="select"
            label="Kelas"
            placeholder="Pilih Kelas"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            @update:model-value="fetchRapor"
          />
          <FormField
            v-model="filters.status"
            type="select"
            label="Status"
            placeholder="Semua Status"
            :options="statusOptions"
            @update:model-value="fetchRapor"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data rapor...</p>
      </div>

      <!-- Rapor Table -->
      <div v-else class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">
              Daftar Rapor
            </h3>
            <div class="flex items-center space-x-3">
              <label class="flex items-center text-sm text-gray-600">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleSelectAll"
                  class="mr-2 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                Pilih Semua
              </label>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th class="w-12">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleSelectAll"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                </th>
                <th>No</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th>Tahun/Semester</th>
                <th>Rata-rata</th>
                <th>Status</th>
                <th>Tanggal Submit</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(rapor, index) in raporData" :key="rapor.id" class="hover:bg-gray-50">
                <td>
                  <input
                    type="checkbox"
                    :value="rapor.id"
                    v-model="selectedRapor"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  />
                </td>
                <td class="text-center">{{ index + 1 }}</td>
                <td>
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                      {{ rapor.siswa?.nama_lengkap?.charAt(0) }}
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">{{ rapor.siswa?.nama_lengkap }}</div>
                      <div class="text-sm text-gray-500">{{ rapor.siswa?.nis }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ rapor.kelas?.nama_kelas }}
                  </span>
                </td>
                <td>
                  <div class="text-sm text-gray-900">{{ rapor.tahun_ajaran?.nama }}</div>
                  <div class="text-sm text-gray-500">Semester {{ rapor.semester }}</div>
                </td>
                <td class="text-center">
                  <span class="text-sm font-medium">{{ rapor.rata_rata || '-' }}</span>
                </td>
                <td>
                  <span :class="getStatusBadge(rapor.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getStatusText(rapor.status) }}
                  </span>
                </td>
                <td class="text-sm text-gray-500">
                  {{ formatDate(rapor.tanggal_submit) }}
                </td>
                <td>
                  <div class="flex items-center space-x-2">
                    <button @click="viewRapor(rapor)" class="text-blue-600 hover:text-blue-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button 
                      v-if="rapor.status === 'pending'" 
                      @click="approveRapor(rapor)" 
                      class="text-green-600 hover:text-green-900"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </button>
                    <button 
                      v-if="rapor.status === 'pending'" 
                      @click="rejectRapor(rapor)" 
                      class="text-red-600 hover:text-red-900"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="raporData.length === 0" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada rapor</h3>
          <p class="mt-1 text-sm text-gray-500">Belum ada rapor yang tersedia untuk ditinjau.</p>
        </div>
      </div>

      <!-- Rapor Detail Modal -->
      <Modal v-model:show="showRaporDetail" title="Detail Rapor" size="xl">
        <div v-if="selectedRaporDetail" class="space-y-6">
          <!-- Student Info -->
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Nama Siswa</label>
                <p class="text-sm text-gray-900">{{ selectedRaporDetail.siswa?.nama_lengkap }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">NIS</label>
                <p class="text-sm text-gray-900">{{ selectedRaporDetail.siswa?.nis }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Kelas</label>
                <p class="text-sm text-gray-900">{{ selectedRaporDetail.kelas?.nama_kelas }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Tahun Ajaran</label>
                <p class="text-sm text-gray-900">{{ selectedRaporDetail.tahun_ajaran?.nama }} - Semester {{ selectedRaporDetail.semester }}</p>
              </div>
            </div>
          </div>

          <!-- Grades Table -->
          <div>
            <h4 class="text-lg font-medium text-gray-900 mb-4">Nilai Akademik</h4>
            <div class="overflow-x-auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Mata Pelajaran</th>
                    <th class="text-center">Harian</th>
                    <th class="text-center">UTS</th>
                    <th class="text-center">UAS</th>
                    <th class="text-center">Akhir</th>
                    <th class="text-center">Predikat</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="nilai in selectedRaporDetail.nilai" :key="nilai.id">
                    <td>{{ nilai.mata_pelajaran?.nama_mapel }}</td>
                    <td class="text-center">{{ nilai.nilai_harian || '-' }}</td>
                    <td class="text-center">{{ nilai.nilai_uts || '-' }}</td>
                    <td class="text-center">{{ nilai.nilai_uas || '-' }}</td>
                    <td class="text-center">{{ nilai.nilai_akhir || '-' }}</td>
                    <td class="text-center">
                      <span :class="getPredicateColor(nilai.nilai_akhir)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                        {{ getPredicate(nilai.nilai_akhir) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Summary -->
          <div class="bg-blue-50 rounded-lg p-4">
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <label class="text-sm font-medium text-gray-500">Rata-rata</label>
                <p class="text-lg font-bold text-blue-600">{{ selectedRaporDetail.rata_rata }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Ranking</label>
                <p class="text-lg font-bold text-blue-600">{{ selectedRaporDetail.ranking || '-' }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Status</label>
                <p class="text-lg font-bold text-blue-600">{{ getStatusText(selectedRaporDetail.status) }}</p>
              </div>
            </div>
          </div>
        </div>

        <template #footer>
          <div class="flex justify-between">
            <div>
              <button 
                v-if="selectedRaporDetail?.status === 'pending'" 
                @click="approveRapor(selectedRaporDetail)" 
                class="btn btn-success mr-3"
              >
                Setujui
              </button>
              <button 
                v-if="selectedRaporDetail?.status === 'pending'" 
                @click="rejectRapor(selectedRaporDetail)" 
                class="btn btn-danger"
              >
                Tolak
              </button>
            </div>
            <button @click="showRaporDetail = false" class="btn btn-secondary">Tutup</button>
          </div>
        </template>
      </Modal>

      <!-- Approval Confirmation -->
      <ConfirmDialog
        v-model:show="showApprovalConfirm"
        title="Setujui Rapor"
        :message="approvalMessage"
        confirm-text="Ya, Setujui"
        type="success"
        :loading="processing"
        @confirm="confirmApproval"
      />

      <!-- Rejection Modal -->
      <Modal v-model:show="showRejectionModal" title="Tolak Rapor" size="md">
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Berikan alasan penolakan untuk rapor <strong>{{ selectedRaporForAction?.siswa?.nama_lengkap }}</strong>
          </p>
          <FormField
            v-model="rejectionReason"
            type="textarea"
            label="Alasan Penolakan"
            placeholder="Masukkan alasan penolakan..."
            required
            rows="3"
          />
        </div>

        <template #footer>
          <button @click="confirmRejection" :disabled="!rejectionReason || processing" class="btn btn-danger">
            {{ processing ? 'Memproses...' : 'Tolak Rapor' }}
          </button>
          <button @click="showRejectionModal = false" class="btn btn-secondary mr-3">Batal</button>
        </template>
      </Modal>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import FormField from '../../../components/ui/FormField.vue'
import Modal from '../../../components/ui/Modal.vue'
import ConfirmDialog from '../../../components/ui/ConfirmDialog.vue'

const toast = useToast()

// Data
const raporData = ref([])
const tahunAjaranOptions = ref([])
const kelasOptions = ref([])
const summary = ref({})
const selectedRapor = ref([])
const selectedRaporDetail = ref(null)
const selectedRaporForAction = ref(null)
const rejectionReason = ref('')

// State
const loading = ref(true)
const processing = ref(false)
const bulkProcessing = ref(false)
const showRaporDetail = ref(false)
const showApprovalConfirm = ref(false)
const showRejectionModal = ref(false)

// Filters
const filters = reactive({
  tahun_ajaran_id: '',
  semester: '',
  kelas_id: '',
  status: ''
})

// Options
const semesterOptions = [
  { value: '', label: 'Semua Semester' },
  { value: '1', label: 'Semester 1' },
  { value: '2', label: 'Semester 2' }
]

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'pending', label: 'Menunggu Persetujuan' },
  { value: 'approved', label: 'Disetujui' },
  { value: 'rejected', label: 'Ditolak' }
]

// Computed
const allSelected = computed(() => {
  const pendingRapor = raporData.value.filter(r => r.status === 'pending')
  return pendingRapor.length > 0 && selectedRapor.value.length === pendingRapor.length
})

const approvalMessage = computed(() => {
  if (selectedRapor.value.length > 1) {
    return `Apakah Anda yakin ingin menyetujui ${selectedRapor.value.length} rapor yang dipilih?`
  } else if (selectedRaporForAction.value) {
    return `Apakah Anda yakin ingin menyetujui rapor ${selectedRaporForAction.value.siswa?.nama_lengkap}?`
  }
  return 'Apakah Anda yakin ingin menyetujui rapor ini?'
})

// Methods
const fetchTahunAjaran = async () => {
  try {
    const response = await axios.get('/admin/tahun-ajaran')
    tahunAjaranOptions.value = response.data.data
    
    // Set current active year as default
    const activeYear = tahunAjaranOptions.value.find(t => t.is_active)
    if (activeYear) {
      filters.tahun_ajaran_id = activeYear.id
    }
  } catch (error) {
    console.error('Failed to fetch tahun ajaran:', error)
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

const fetchRapor = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    if (filters.tahun_ajaran_id) params.append('tahun_ajaran_id', filters.tahun_ajaran_id)
    if (filters.semester) params.append('semester', filters.semester)
    if (filters.kelas_id) params.append('kelas_id', filters.kelas_id)
    if (filters.status) params.append('status', filters.status)
    
    const response = await axios.get(`/kepala-sekolah/rapor-approval?${params}`)
    raporData.value = response.data.data
    summary.value = response.data.summary || {}
    selectedRapor.value = []
  } catch (error) {
    toast.error('Gagal mengambil data rapor')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const toggleSelectAll = () => {
  const pendingRapor = raporData.value.filter(r => r.status === 'pending')
  if (allSelected.value) {
    selectedRapor.value = []
  } else {
    selectedRapor.value = pendingRapor.map(r => r.id)
  }
}

const viewRapor = async (rapor) => {
  try {
    const response = await axios.get(`/kepala-sekolah/rapor-approval/${rapor.id}`)
    selectedRaporDetail.value = response.data.data
    showRaporDetail.value = true
  } catch (error) {
    toast.error('Gagal mengambil detail rapor')
    console.error(error)
  }
}

const approveRapor = (rapor) => {
  selectedRaporForAction.value = rapor
  showApprovalConfirm.value = true
}

const confirmApproval = async () => {
  try {
    processing.value = true
    
    if (selectedRapor.value.length > 1) {
      // Bulk approval
      await axios.post('/kepala-sekolah/rapor-approval/bulk-approve', {
        rapor_ids: selectedRapor.value
      })
      toast.success(`${selectedRapor.value.length} rapor berhasil disetujui`)
      selectedRapor.value = []
    } else {
      // Single approval
      await axios.post(`/kepala-sekolah/rapor-approval/${selectedRaporForAction.value.id}/approve`)
      toast.success('Rapor berhasil disetujui')
    }
    
    showApprovalConfirm.value = false
    showRaporDetail.value = false
    await fetchRapor()
  } catch (error) {
    toast.error('Gagal menyetujui rapor')
    console.error(error)
  } finally {
    processing.value = false
  }
}

const rejectRapor = (rapor) => {
  selectedRaporForAction.value = rapor
  rejectionReason.value = ''
  showRejectionModal.value = true
}

const confirmRejection = async () => {
  try {
    processing.value = true
    
    await axios.post(`/kepala-sekolah/rapor-approval/${selectedRaporForAction.value.id}/reject`, {
      reason: rejectionReason.value
    })
    
    toast.success('Rapor berhasil ditolak')
    showRejectionModal.value = false
    showRaporDetail.value = false
    await fetchRapor()
  } catch (error) {
    toast.error('Gagal menolak rapor')
    console.error(error)
  } finally {
    processing.value = false
  }
}

const bulkApprove = () => {
  if (selectedRapor.value.length === 0) return
  showApprovalConfirm.value = true
}

const getStatusBadge = (status) => {
  const badges = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return badges[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak'
  }
  return texts[status] || status
}

const getPredicate = (nilai) => {
  if (!nilai) return '-'
  if (nilai >= 90) return 'A'
  if (nilai >= 80) return 'B'
  if (nilai >= 70) return 'C'
  if (nilai >= 60) return 'D'
  return 'E'
}

const getPredicateColor = (nilai) => {
  const predicate = getPredicate(nilai)
  const colors = {
    A: 'bg-green-100 text-green-800',
    B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800',
    D: 'bg-orange-100 text-orange-800',
    E: 'bg-red-100 text-red-800'
  }
  return colors[predicate] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(async () => {
  await fetchTahunAjaran()
  await fetchKelas()
  await fetchRapor()
})
</script>

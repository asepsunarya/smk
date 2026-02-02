<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <FormField
            v-model="selectedMapel"
            type="select"
            label="Mata Pelajaran"
            placeholder="Pilih Mata Pelajaran"
            :options="mapelOptions"
            option-value="id"
            option-label="nama_mapel"
            @update:model-value="loadCP"
          />
        </div>
      </div>

      <!-- Header with Add Button -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex-1">
              <h3 class="text-lg font-medium text-gray-900">Capaian Pembelajaran</h3>
              <p class="mt-1 text-sm text-gray-500">Kelola CP (Capaian Pembelajaran)</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-4">
              <button 
                @click="handleAddCP" 
                class="btn btn-primary"
              >
                + Tambah CP
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- No Selection State -->
      <div v-if="!selectedMapel" class="bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih Mata Pelajaran</h3>
        <p class="mt-1 text-sm text-gray-500">Pilih mata pelajaran untuk melihat capaian pembelajaran.</p>
      </div>

      <!-- CP List -->
      <div v-else>
        <!-- Search -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:px-6">
            <div class="relative">
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Cari CP..."
                class="block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredCPList.length === 0" class="bg-white shadow rounded-lg p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada Capaian Pembelajaran</h3>
          <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan CP baru.</p>
          </div>

        <!-- CP Cards -->
        <div v-else class="space-y-4">
              <div 
            v-for="cp in filteredCPList" 
            :key="cp.id" 
            class="bg-white shadow rounded-lg overflow-hidden"
          >
            <div class="px-6 py-4">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-2 mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ cp.kode_cp }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      Tingkat {{ cp.fase }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                      Semester {{ cp.semester || '1' }}
                    </span>
                    <span 
                      v-if="cp.target"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="cp.target === 'tengah_semester' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800'"
                    >
                      {{ cp.target === 'tengah_semester' ? 'STS' : 'SAS' }}
                    </span>
                    <span 
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="cp.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                      {{ cp.is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-700">{{ cp.deskripsi }}</p>
                </div>
                <div class="flex items-center space-x-2 ml-4">
                  <button 
                    @click="editCP(cp)" 
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="deleteCP(cp)" 
                    class="text-red-600 hover:text-red-900"
                    title="Hapus"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Batch CP Input Form Modal (Wireframe - Form Input) -->
      <Modal v-model:show="showBatchInputForm" title="Tambah Capaian Pembelajaran" size="7xl">
        <template #footer>
          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="closeBatchInputForm" 
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Batal
            </button>
            <button 
              type="button" 
              @click="simpanBatchCP" 
              :disabled="submitting"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="submitting" class="inline-block animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </template>
        <div class="flex flex-col w-full" style="max-height: 70vh;">
          <div class="flex-1 overflow-y-auto overflow-x-auto w-full min-h-0 -mx-6">
            <table class="w-full divide-y divide-gray-200">
              <colgroup>
                <col style="width: 15%;">
                <col style="width: 15%;">
                <col style="width: 15%;">
                <col style="width: auto;">
                <col style="width: 15%;">
              </colgroup>
              <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tingkat
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Semester
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Target
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Capaian Pembelajaran
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status Aktif
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(cp, index) in batchCPList" :key="index">
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cp.tingkat"
                      type="select"
                      placeholder="Pilih Tingkat"
                      :options="tingkatOptions"
                      option-value="value"
                      option-label="label"
                      :error="errors[`cp_${index}_tingkat`]"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cp.semester"
                      type="select"
                      placeholder="Pilih Semester"
                      :options="semesterOptions"
                      option-value="value"
                      option-label="label"
                      :error="errors[`cp_${index}_semester`]"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cp.target"
                      type="select"
                      placeholder="Pilih Target"
                      :options="targetOptions"
                      option-value="value"
                      option-label="label"
                      required
                      :error="errors[`cp_${index}_target`]"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <div class="w-full">
                      <FormField
                        v-model="cp.deskripsi"
                        type="textarea"
                        placeholder="Masukkan Capaian Pembelajaran"
                        :rows="4"
                        :maxlength="200"
                        :error="errors[`cp_${index}_deskripsi`]"
                      />
                      <p class="mt-1 text-xs text-gray-500 text-right">
                        {{ (cp.deskripsi?.length || 0) }}/200 karakter
                      </p>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cp.is_active"
                      type="checkbox"
                      checkbox-label="Aktif"
                      :error="errors[`cp_${index}_is_active`]"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Modal>

      <!-- Batch CP Form Modal (Wireframe) -->
      <Modal v-model:show="showBatchForm" title="Tambah Capaian Pembelajaran" size="lg">
        <div class="space-y-6">
          <div class="space-y-4">
            <div class="flex items-center gap-4">
              <label class="block text-sm font-medium text-gray-700 w-1/3 text-left">
                Pilih Mapel
              </label>
              <div class="flex-1">
          <FormField
                  v-model="batchForm.mata_pelajaran_id"
                  type="select"
                  placeholder="Pilih Mapel"
                  :options="allMapelOptions"
                  option-value="id"
                  option-label="nama_mapel"
                  :error="errors.mata_pelajaran_id"
          />
              </div>
            </div>
            <div class="flex items-center gap-4">
              <label class="block text-sm font-medium text-gray-700 w-1/3 text-left">
                Jumlah CP yang Ditambahkan
              </label>
              <div class="flex-1">
          <FormField
                  v-model="batchForm.jumlah_cp"
                  type="select"
                  placeholder="Pilih Jumlah Target CP"
                  :options="jumlahCPOptions"
                  option-value="value"
                  option-label="label"
                  :error="errors.jumlah_cp"
                />
              </div>
            </div>
          </div>
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button 
              type="button" 
              @click="closeBatchForm" 
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Close
            </button>
            <button 
              type="button" 
              @click="prosesBatchCP" 
              :disabled="submitting || !batchForm.mata_pelajaran_id || !batchForm.jumlah_cp"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="submitting" class="inline-block animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? 'Memproses...' : 'Proses' }}
            </button>
          </div>
        </div>
      </Modal>

      <!-- CP Form Modal -->
      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Capaian Pembelajaran' : 'Tambah Capaian Pembelajaran'" size="7xl">
        <template #footer>
          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="closeCPForm" 
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Batal
            </button>
            <button 
              type="button" 
              @click="submitCPForm" 
              :disabled="submitting"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="submitting" class="inline-block animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </template>
        <div class="flex flex-col w-full" style="max-height: 70vh;">
          <div class="flex-1 overflow-y-auto overflow-x-auto w-full min-h-0 -mx-6 px-6">
            <table class="w-full divide-y divide-gray-200">
              <colgroup>
                <col style="width: 15%;">
                <col style="width: 15%;">
                <col style="width: 15%;">
                <col style="width: auto;">
                <col style="width: 15%;">
              </colgroup>
              <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tingkat
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Semester
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Target
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Capaian Pembelajaran
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status Aktif
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cpForm.fase"
                      type="select"
                      placeholder="Pilih Tingkat"
                      :options="tingkatOptions"
                      option-value="value"
                      option-label="label"
                      required
                      :error="errors.fase"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cpForm.semester"
                      type="select"
                      placeholder="Pilih Semester"
                      :options="semesterOptions"
                      option-value="value"
                      option-label="label"
                      :error="errors.semester"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cpForm.target"
                      type="select"
                      placeholder="Pilih Target"
                      :options="targetOptions"
                      option-value="value"
                      option-label="label"
                      required
                      :error="errors.target"
                    />
                  </td>
                  <td class="px-4 py-3">
                    <div class="w-full">
                      <FormField
                        v-model="cpForm.deskripsi"
                        type="textarea"
                        placeholder="Masukkan Capaian Pembelajaran"
                        :rows="4"
                        :maxlength="200"
                        required
                        :error="errors.deskripsi"
                      />
                      <p class="mt-1 text-xs text-gray-500 text-right">
                        {{ (cpForm.deskripsi?.length || 0) }}/200 karakter
                      </p>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <FormField
                      v-model="cpForm.is_active"
                      type="checkbox"
                      checkbox-label="Aktif"
                      :error="errors.is_active"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </Modal>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import FormField from '../../../components/ui/FormField.vue'
import Modal from '../../../components/ui/Modal.vue'

const toast = useToast()

// Data
const mapelOptions = ref([])
const allMapelOptions = ref([])
const kelasOptions = ref([])
const cpList = ref([])

// State
const loading = ref(false)
const submitting = ref(false)
const selectedMapel = ref('')
const showForm = ref(false)
const showBatchForm = ref(false)
const showBatchInputForm = ref(false)
const isEditing = ref(false)
const editingCP = ref(null)
const searchTerm = ref('')

// Batch Form
const batchForm = ref({
  mata_pelajaran_id: '',
  jumlah_cp: ''
})

// Batch CP List for input form
const batchCPList = ref([])
const batchMataPelajaranId = ref('')

// Forms
const cpForm = ref({
  mata_pelajaran_id: '',
  deskripsi: '',
  fase: '',
  semester: '1', // Default to semester 1
  tingkat: '', // Will be set from fase
  target: '', // tengah_semester, akhir_semester, or null (CP biasa)
  is_active: true
})

const errors = ref({})

// Computed
const filteredCPList = computed(() => {
  if (!searchTerm.value) {
    return cpList.value
  }
  
  const term = searchTerm.value.toLowerCase()
  return cpList.value.filter(cp => {
    return (
      cp.kode_cp?.toLowerCase().includes(term) ||
      cp.deskripsi?.toLowerCase().includes(term) ||
      cp.fase?.toString().includes(term) ||
      (cp.semester || '1')?.toString().includes(term)
    )
  })
})

// Options
const faseOptions = [
  { value: '10', label: '10' },
  { value: '11', label: '11' },
  { value: '12', label: '12' }
]

const elemenOptions = [
  { value: 'pemahaman', label: 'Pemahaman' },
  { value: 'keterampilan', label: 'Keterampilan' },
  { value: 'sikap', label: 'Sikap' }
]

const jumlahCPOptions = Array.from({ length: 20 }, (_, i) => ({
  value: i + 1,
  label: `${i + 1}`
}))

const tingkatOptions = [
  { value: '10', label: 'Tingkat 10' },
  { value: '11', label: 'Tingkat 11' },
  { value: '12', label: 'Tingkat 12' }
]

const semesterOptions = [
  { value: '1', label: 'Semester 1' },
  { value: '2', label: 'Semester 2' }
]

const targetOptions = [
  { value: 'tengah_semester', label: 'Tengah Semester (STS)' },
  { value: 'akhir_semester', label: 'Akhir Semester (SAS)' }
]

// Methods
const fetchAllMapel = async () => {
  try {
    const response = await axios.get('/lookup/mata-pelajaran')
    allMapelOptions.value = response.data
    mapelOptions.value = response.data
  } catch (error) {
    console.error('Failed to fetch all mata pelajaran:', error)
  }
}

const loadCP = async () => {
  if (!selectedMapel.value) {
    cpList.value = []
    return
  }

  try {
    loading.value = true
    const response = await axios.get(`/guru/capaian-pembelajaran/mapel/${selectedMapel.value}`)
    cpList.value = response.data.capaian_pembelajaran || response.data.data || []
  } catch (error) {
    toast.error('Gagal mengambil data CP')
    console.error('Error loading CP:', error)
    cpList.value = []
  } finally {
    loading.value = false
  }
}

const handleAddCP = () => {
  // Show batch form modal (wireframe) - pilih mapel dan jumlah CP
  batchForm.value = {
    mata_pelajaran_id: selectedMapel.value || '', // Use selected mapel if available, otherwise empty
    jumlah_cp: ''
  }
  errors.value = {}
  showBatchForm.value = true
}

const editCP = (cp) => {
  editingCP.value = cp
  cpForm.value = {
    mata_pelajaran_id: cp.mata_pelajaran_id,
    deskripsi: cp.deskripsi,
    fase: cp.fase,
    semester: cp.semester || '1', // Default to semester 1 if not set
    tingkat: cp.tingkat || cp.fase, // Use tingkat if available, otherwise use fase
    target: cp.target || '', // Target: tengah_semester, akhir_semester, or empty (CP biasa)
    is_active: cp.is_active !== undefined ? cp.is_active : true
  }
  isEditing.value = true
  showForm.value = true
}

const deleteCP = async (cp) => {
  if (!confirm('Apakah Anda yakin ingin menghapus CP ini?')) return

  try {
    await axios.delete(`/guru/capaian-pembelajaran/${cp.id}`)
    toast.success('CP berhasil dihapus')
    await loadCP()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal menghapus CP')
  }
}

const submitCPForm = async () => {
  errors.value = {}
  
  // Get mata_pelajaran_id from form, batch form, or selected mapel
  const mataPelajaranId = cpForm.value.mata_pelajaran_id || batchMataPelajaranId.value || selectedMapel.value
  
  // Validate mata pelajaran is selected
  if (!mataPelajaranId) {
    errors.value.mata_pelajaran_id = 'Pilih mata pelajaran terlebih dahulu'
    toast.error('Pilih mata pelajaran terlebih dahulu')
    return
  }
  
  // Validate deskripsi max length
  if (cpForm.value.deskripsi && cpForm.value.deskripsi.length > 200) {
    errors.value.deskripsi = 'Maksimal 200 karakter'
    toast.error('Deskripsi maksimal 200 karakter')
    return
  }
  
  // Validate tingkat
  if (!cpForm.value.fase) {
    errors.value.fase = 'Pilih tingkat'
    toast.error('Pilih tingkat terlebih dahulu')
    return
  }
  
  // Validate target
  if (!cpForm.value.target) {
    errors.value.target = 'Pilih target terlebih dahulu'
    toast.error('Pilih target terlebih dahulu')
    return
  }
  
  try {
    submitting.value = true
    // Set tingkat from fase if not explicitly set
    const formData = {
      ...cpForm.value,
      mata_pelajaran_id: mataPelajaranId, // Use mata_pelajaran_id from validation above
      tingkat: cpForm.value.tingkat || cpForm.value.fase // Use tingkat if set, otherwise use fase
    }
    
    if (isEditing.value && editingCP.value) {
      // Keep existing kode_cp and elemen when editing
      formData.kode_cp = editingCP.value.kode_cp
      formData.elemen = editingCP.value.elemen
      await axios.put(`/guru/capaian-pembelajaran/${editingCP.value.id}`, formData)
      toast.success('CP berhasil diperbarui')
    } else {
      // For new CP, we need to generate kode_cp and set default elemen
      // Get all existing CPs (including inactive) to find available kode_cp
      const existingCPs = await axios.get(`/guru/capaian-pembelajaran`, {
        params: {
          mata_pelajaran_id: mataPelajaranId,
          per_page: 1000 // Get all CPs
        }
      })
      const existingCPList = existingCPs.data.data || existingCPs.data || []
      
      // Extract existing kode_cp numbers
      const existingCodes = existingCPList
        .map(cp => {
          const match = cp.kode_cp?.match(/^CP-(\d+)$/i)
          return match ? parseInt(match[1]) : null
        })
        .filter(num => num !== null)
        .sort((a, b) => a - b)
      
      // Find the first available number
      let nextNumber = 1
      for (const code of existingCodes) {
        if (code === nextNumber) {
          nextNumber++
        } else {
          break
        }
      }
      
      formData.kode_cp = `CP-${nextNumber}`
      formData.elemen = 'pemahaman' // Default elemen
      
      await axios.post('/guru/capaian-pembelajaran', formData)
      toast.success('CP berhasil ditambahkan')
    }
    closeCPForm()
    // Update selectedMapel if it was changed
    if (mataPelajaranId && mataPelajaranId !== selectedMapel.value) {
      selectedMapel.value = mataPelajaranId
    }
    await loadCP()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error(error.response?.data?.message || 'Gagal menyimpan CP')
    }
  } finally {
    submitting.value = false
  }
}

const closeCPForm = () => {
  showForm.value = false
  isEditing.value = false
  editingCP.value = null
  cpForm.value = {
    mata_pelajaran_id: '',
    deskripsi: '',
    fase: '',
    semester: '1', // Default to semester 1
    tingkat: '', // Will be set from fase
    target: '', // Target: tengah_semester, akhir_semester, or empty (CP biasa)
    is_active: true
  }
  errors.value = {}
}

const closeBatchForm = () => {
  showBatchForm.value = false
  batchForm.value = {
    mata_pelajaran_id: '',
    jumlah_cp: ''
  }
  errors.value = {}
}

const prosesBatchCP = async () => {
  errors.value = {}
  
  if (!batchForm.value.mata_pelajaran_id) {
    errors.value.mata_pelajaran_id = 'Pilih mata pelajaran terlebih dahulu'
    return
  }
  
  if (!batchForm.value.jumlah_cp) {
    errors.value.jumlah_cp = 'Pilih jumlah CP terlebih dahulu'
    return
  }
  
  const jumlah = parseInt(batchForm.value.jumlah_cp)
  
  // Get existing CPs to determine next kode_cp
  try {
    const existingCPs = await axios.get(`/guru/capaian-pembelajaran/mapel/${batchForm.value.mata_pelajaran_id}`)
    const existingCPList = existingCPs.data.capaian_pembelajaran || []
    const existingCPCount = existingCPList.length
    
    // Initialize batch CP list for input form
    batchCPList.value = []
    for (let i = 1; i <= jumlah; i++) {
      const cpNumber = existingCPCount + i
      batchCPList.value.push({
        kode_cp: `CP-${cpNumber}`,
        tingkat: '',
        semester: '1', // Default to semester 1
        target: '', // Target: tengah_semester, akhir_semester, or empty (CP biasa)
        deskripsi: '',
        elemen: 'pemahaman', // Default elemen
        is_active: true // Default aktif
      })
    }
    
    batchMataPelajaranId.value = batchForm.value.mata_pelajaran_id
    
    // Close first modal and open input form modal
    showBatchForm.value = false
    showBatchInputForm.value = true
  } catch (error) {
    toast.error('Gagal memuat data CP')
    console.error(error)
  }
}

const simpanBatchCP = async () => {
  errors.value = {}
  
  // Validate all CP entries
  let hasError = false
  batchCPList.value.forEach((cp, index) => {
    if (!cp.tingkat) {
      errors.value[`cp_${index}_tingkat`] = 'Pilih tingkat'
      hasError = true
    }
    if (!cp.semester) {
      errors.value[`cp_${index}_semester`] = 'Pilih semester'
      hasError = true
    }
    if (!cp.target) {
      errors.value[`cp_${index}_target`] = 'Pilih target'
      hasError = true
    }
    if (!cp.deskripsi || cp.deskripsi.trim() === '') {
      errors.value[`cp_${index}_deskripsi`] = 'Masukkan capaian pembelajaran'
      hasError = true
    } else if (cp.deskripsi.length > 200) {
      errors.value[`cp_${index}_deskripsi`] = 'Maksimal 200 karakter'
      hasError = true
    }
  })
  
  if (hasError) {
    toast.error('Lengkapi semua data CP')
    return
  }
  
  try {
    submitting.value = true
    
    // Create all CPs
    const promises = batchCPList.value.map(cp => {
      return axios.post('/guru/capaian-pembelajaran', {
        mata_pelajaran_id: batchMataPelajaranId.value,
        kode_cp: cp.kode_cp,
        deskripsi: cp.deskripsi,
        fase: cp.tingkat, // tingkat = fase
        semester: cp.semester,
        tingkat: cp.tingkat,
        target: cp.target || null, // Target: tengah_semester, akhir_semester, or null (CP biasa)
        elemen: cp.elemen,
        is_active: cp.is_active !== undefined ? cp.is_active : true
      })
    })
    
    await Promise.all(promises)
    toast.success(`${batchCPList.value.length} CP berhasil ditambahkan`)
    closeBatchInputForm()
    
    // Reload CP list if the selected mapel matches
    if (selectedMapel.value == batchMataPelajaranId.value) {
      await loadCP()
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error(error.response?.data?.message || 'Gagal menyimpan CP')
    }
  } finally {
    submitting.value = false
  }
}

const closeBatchInputForm = () => {
  showBatchInputForm.value = false
  batchCPList.value = []
  batchMataPelajaranId.value = ''
  errors.value = {}
}

// Lifecycle
onMounted(() => {
  fetchAllMapel()
})
</script>

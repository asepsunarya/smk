<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Input Nilai
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Kelola nilai siswa per mata pelajaran dan kelas
          </p>
        </div>
        <div class="mt-4 md:mt-0 md:ml-4">
          <button
            @click="showAddForm = true"
            class="btn btn-primary"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Nilai
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <FormField
            v-model="selectedMapel"
            type="select"
            label="Mata Pelajaran"
            placeholder="Pilih Mata Pelajaran"
            :options="mapelOptions"
            option-value="id"
            option-label="nama_mapel"
            @update:model-value="onMapelChange"
          />
          <FormField
            v-model="selectedKelas"
            type="select"
            label="Kelas"
            placeholder="Pilih Kelas"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            :disabled="!selectedMapel"
            @update:model-value="onKelasChange"
          />
          <FormField
            v-model="selectedSemester"
            type="select"
            label="Semester"
            placeholder="Pilih Semester"
            :options="semesterOptions"
            option-value="value"
            option-label="label"
            :disabled="!selectedMapel"
            @update:model-value="onSemesterChange"
          />
          <FormField
            v-model="selectedCP"
            type="select"
            label="Capaian Pembelajaran"
            placeholder="Pilih Capaian Pembelajaran"
            :options="cpOptions"
            option-value="id"
            option-label="label"
            :disabled="!selectedSemester || !selectedKelas"
            @update:model-value="loadNilai"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data nilai...</p>
      </div>

      <!-- No Selection State -->
      <div v-else-if="!selectedKelas || !selectedMapel || !selectedSemester || !selectedCP" class="bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih Semua Filter</h3>
        <p class="mt-1 text-sm text-gray-500">Pilih Kelas, Mata Pelajaran, Semester, dan Capaian Pembelajaran untuk mulai input nilai.</p>
      </div>

      <!-- Grade Display Table (Read Only) -->
      <div v-else class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                Nilai {{ selectedKelasName }} - {{ selectedMapelName }}
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                Semester {{ selectedSemester }} - {{ selectedCPName }}
              </p>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">NIS</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Nilai</th>
                <th v-if="!isSelectedSTSOrSAS" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(nilai, index) in nilaiData" :key="nilai.id || nilai.siswa_id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-center text-gray-500">{{ index + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                      {{ nilai.siswa.nama_lengkap.charAt(0) }}
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">{{ nilai.siswa.nama_lengkap }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ nilai.siswa.nis }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="text-sm font-medium text-gray-900">{{ nilai.nilai_akhir || '-' }}</span>
                </td>
                <td v-if="!isSelectedSTSOrSAS" class="px-4 py-3">
                  <p class="text-sm text-gray-700 whitespace-normal break-words max-w-md">{{ nilai.deskripsi || '-' }}</p>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center space-x-2">
                    <button
                      @click="editNilai(nilai)"
                      class="text-blue-600 hover:text-blue-900 transition-colors"
                      title="Edit"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button
                      @click="confirmDeleteNilai(nilai)"
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="Hapus"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="nilaiData.length === 0" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data siswa</h3>
          <p class="mt-1 text-sm text-gray-500">Pastikan kelas memiliki siswa aktif.</p>
        </div>
      </div>
    </div>

    <!-- Modal Tambah/Edit Nilai -->
    <Modal v-model:show="showAddForm" :title="formStep === 1 ? 'Pilih Filter' : (isEditingNilai ? 'Edit Nilai' : 'Input Nilai')" size="6xl">
      <template #footer>
        <div class="flex justify-end space-x-3">
          <button
            v-if="formStep === 2"
            @click="formStep = 1"
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
          >
            Kembali
          </button>
          <button
            v-if="formStep === 1"
            @click="handleStep1Next"
            type="button"
            :disabled="!formMapel || !formKelas || !formSemester || !formCP"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Lanjut
          </button>
          <button
            v-if="formStep === 2"
            @click="handleSubmitNilai"
            type="button"
            :disabled="formSubmitting"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="formSubmitting" class="inline-block animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ formSubmitting ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button
            @click="closeAddForm"
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
          >
            Batal
          </button>
        </div>
      </template>

      <!-- Step 1: Pilih Filter -->
      <div v-if="formStep === 1" class="space-y-4">
        <FormField
          v-model="formMapel"
          type="select"
          label="Mata Pelajaran"
          placeholder="Pilih Mata Pelajaran"
          :options="mapelOptions"
          option-value="id"
          option-label="nama_mapel"
          @update:model-value="onFormMapelChange"
        />
        <FormField
          v-model="formKelas"
          type="select"
          label="Kelas"
          placeholder="Pilih Kelas"
          :options="formKelasOptions"
          option-value="id"
          option-label="nama_kelas"
          :disabled="!formMapel"
          @update:model-value="onFormKelasChange"
        />
        <FormField
          v-model="formSemester"
          type="select"
          label="Semester"
          placeholder="Pilih Semester"
          :options="semesterOptions"
          option-value="value"
          option-label="label"
          :disabled="!formKelas"
          @update:model-value="onFormSemesterChange"
        />
        <FormField
          v-model="formCP"
          type="select"
          label="Capaian Pembelajaran"
          placeholder="Pilih Capaian Pembelajaran"
          :options="formCPOptions"
          option-value="id"
          option-label="label"
          :disabled="!formSemester || !formKelas"
        />
      </div>

      <!-- Step 2: Input Nilai -->
      <div v-else class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
          <h4 class="text-sm font-medium text-blue-900 mb-2">Informasi Capaian Pembelajaran</h4>
          <p class="text-sm text-blue-700 whitespace-normal break-words">
            {{ getFormCPName() }}
          </p>
          <p v-if="!isSTSOrSAS" class="text-sm text-blue-700 mt-1">
            KKM: {{ formKkm }}
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Nilai</th>
                <th v-if="!isSTSOrSAS" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in formNilaiList" :key="item.siswa_id">
                <td class="px-4 py-3 text-sm text-center">{{ index + 1 }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ item.siswa.nama_lengkap }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ item.siswa.nis }}</td>
                <td class="px-4 py-3">
                  <input
                    v-model.number="item.nilai"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    @input="updateDeskripsi(item)"
                  />
                </td>
                <td v-if="!isSTSOrSAS" class="px-4 py-3">
                  <textarea
                    v-model="item.deskripsi"
                    rows="2"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 resize-none"
                    placeholder="Deskripsi akan di-generate otomatis"
                  ></textarea>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Modal>

    <!-- Modal Konfirmasi Hapus -->
    <Modal v-model:show="showDeleteConfirm" title="Konfirmasi Hapus" size="md">
      <template #footer>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteConfirm = false; deletingNilaiId = null"
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
          >
            Batal
          </button>
          <button
            @click="deleteNilai"
            type="button"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
          >
            Hapus
          </button>
        </div>
      </template>
      <div class="py-4">
        <p class="text-sm text-gray-700">Apakah Anda yakin ingin menghapus nilai ini?</p>
        <p class="mt-2 text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import FormField from '../../../components/ui/FormField.vue'
import Modal from '../../../components/ui/Modal.vue'

const toast = useToast()

// Data
const kelasOptions = ref([])
const mapelOptions = ref([])
const cpOptions = ref([])
const nilaiData = ref([])
const changedItems = ref(new Set())

// State
const loading = ref(false)
const updating = ref(false)
const selectedKelas = ref('')
const selectedMapel = ref('')
const selectedSemester = ref('')
const selectedCP = ref('')

// Form State
const showAddForm = ref(false)
const formStep = ref(1) // 1: Select filters, 2: Input nilai
const formMapel = ref('')
const formKelas = ref('')
const formSemester = ref('')
const formCP = ref('')
const formKelasOptions = ref([])
const formCPOptions = ref([])
const formSiswaList = ref([])
const formNilaiList = ref([])
const formSubmitting = ref(false)
const formKkm = ref(0)
const isEditingNilai = ref(false)
const editingNilaiId = ref(null)
const editingNilaiData = ref(null)
const showDeleteConfirm = ref(false)
const deletingNilaiId = ref(null)

// Options
const semesterOptions = [
  { value: '1', label: 'Semester 1' },
  { value: '2', label: 'Semester 2' }
]

// Computed
const selectedKelasName = computed(() => {
  const kelas = kelasOptions.value.find(k => k.id == selectedKelas.value)
  return kelas?.nama_kelas || ''
})

const selectedMapelName = computed(() => {
  const mapel = mapelOptions.value.find(m => m.id == selectedMapel.value)
  return mapel?.nama_mapel || ''
})

const selectedCPName = computed(() => {
  const cp = cpOptions.value.find(cp => cp.id == selectedCP.value)
  if (!cp) return ''
  
  // If STS or SAS, return label directly
  if (cp.id === 'sts' || cp.id === 'sas') {
    return cp.label
  }
  
  return `${cp.kode_cp} - ${cp.deskripsi?.substring(0, 50)}${cp.deskripsi?.length > 50 ? '...' : ''}`
})

const hasChanges = computed(() => changedItems.value.size > 0)

const isSTSOrSAS = computed(() => {
  return formCP.value === 'sts' || formCP.value === 'sas'
})

const isSelectedSTSOrSAS = computed(() => {
  return selectedCP.value === 'sts' || selectedCP.value === 'sas'
})

// Methods
const fetchMapel = async () => {
  try {
    // Fetch all mata pelajaran for the current guru (no kelas filter)
    const response = await axios.get('/lookup/mata-pelajaran')
    mapelOptions.value = response.data
  } catch (error) {
    console.error('Failed to fetch mata pelajaran:', error)
    mapelOptions.value = []
  }
}

const fetchKelas = async () => {
  if (!selectedMapel.value) {
    kelasOptions.value = []
    return
  }
  
  try {
    // Fetch kelas based on selected mata pelajaran
    const response = await axios.get('/lookup/kelas-by-mapel', {
      params: {
        mata_pelajaran_id: selectedMapel.value
      }
    })
    kelasOptions.value = response.data
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
    kelasOptions.value = []
    if (error.response?.status === 403) {
      toast.error('Anda tidak memiliki akses untuk mata pelajaran ini')
    } else if (error.response?.status !== 404) {
      toast.error('Gagal mengambil data kelas')
    }
  }
}

const onMapelChange = () => {
  // Reset selected kelas, semester, and CP when mapel changes
  selectedKelas.value = ''
  selectedSemester.value = ''
  selectedCP.value = ''
  kelasOptions.value = []
  cpOptions.value = []
  // Fetch kelas for the selected mapel
  fetchKelas()
  // Clear nilai data
  nilaiData.value = []
}

const onKelasChange = () => {
  // Reset selected semester and CP when kelas changes
  selectedSemester.value = ''
  selectedCP.value = ''
  cpOptions.value = []
  // Don't fetch CP yet, need semester too
  // Clear nilai data
  nilaiData.value = []
}

const onSemesterChange = () => {
  // Reset selected CP when semester changes
  selectedCP.value = ''
  // Fetch CP when semester is selected (need semester for filtering)
  if (selectedKelas.value && selectedMapel.value && selectedSemester.value) {
    fetchCP()
  }
  // Clear nilai data
  nilaiData.value = []
}

const fetchCP = async () => {
  if (!selectedMapel.value || !selectedKelas.value || !selectedSemester.value) {
    // Even if filters are not complete, still show STS and SAS
    const stsOption = {
      id: 'sts',
      kode_cp: 'STS',
      deskripsi: '',
      fase: '',
      label: 'Nilai STS (Sumatif Tengah Semester)',
      isSpecial: true
    }
    
    const sasOption = {
      id: 'sas',
      kode_cp: 'SAS',
      deskripsi: '',
      fase: '',
      label: 'Nilai SAS (Sumatif Akhir Semester)',
      isSpecial: true
    }
    
    cpOptions.value = [stsOption, sasOption]
    return
  }
  
  try {
    // Get tingkat from selected kelas
    const kelas = kelasOptions.value.find(k => k.id == selectedKelas.value)
    if (!kelas || !kelas.tingkat) {
      // Even if kelas has no tingkat, still show STS and SAS
      const stsOption = {
        id: 'sts',
        kode_cp: 'STS',
        deskripsi: '',
        fase: '',
        label: 'Nilai STS (Sumatif Tengah Semester)',
        isSpecial: true
      }
      
      const sasOption = {
        id: 'sas',
        kode_cp: 'SAS',
        deskripsi: '',
        fase: '',
        label: 'Nilai SAS (Sumatif Akhir Semester)',
        isSpecial: true
      }
      
      cpOptions.value = [stsOption, sasOption]
      toast.warning('Kelas tidak memiliki tingkat')
      return
    }
    
    const tingkat = kelas.tingkat.toString() // Ensure it's a string
    
    // Always create STS and SAS options first
    const stsOption = {
      id: 'sts',
      kode_cp: 'STS',
      deskripsi: '',
      fase: tingkat,
      label: 'Nilai STS (Sumatif Tengah Semester)',
      isSpecial: true
    }
    
    const sasOption = {
      id: 'sas',
      kode_cp: 'SAS',
      deskripsi: '',
      fase: tingkat,
      label: 'Nilai SAS (Sumatif Akhir Semester)',
      isSpecial: true
    }
    
    // Fetch all CP for the mata pelajaran
      const response = await axios.get(`/guru/capaian-pembelajaran/mapel/${selectedMapel.value}`)
      const allCP = response.data.capaian_pembelajaran || []
      
      // Filter CP by:
      // 1. fase (tingkat) must match kelas tingkat
      // 2. is_active must be true
      // 3. Exclude STS and SAS from database (we add them manually)
      // 4. Filter by target based on semester:
      //    - Semester 1: target = 'tengah_semester' or NULL (CP biasa)
      //    - Semester 2: target = 'akhir_semester' or NULL (CP biasa)
      const filteredCP = allCP.filter(cp => {
        // Filter by tingkat (fase) - must match kelas tingkat
        // Only include active CP
        // Also exclude STS and SAS if they exist in database
        if (cp.fase !== tingkat || cp.is_active === false || cp.kode_cp === 'STS' || cp.kode_cp === 'SAS') {
          return false
        }
        
        // Filter by target based on semester
        if (selectedSemester.value === '1') {
          // Semester 1: only show CP with target 'tengah_semester' or NULL
          return cp.target === 'tengah_semester' || !cp.target
        } else if (selectedSemester.value === '2') {
          // Semester 2: only show CP with target 'akhir_semester' or NULL
          return cp.target === 'akhir_semester' || !cp.target
        }
        
        // If semester not selected, show all (shouldn't happen)
        return true
      })
    
    // Map to options with label
    const cpOptionsMapped = filteredCP.map(cp => ({
      id: cp.id,
      kode_cp: cp.kode_cp,
      deskripsi: cp.deskripsi,
      fase: cp.fase,
      label: `${cp.kode_cp} - ${cp.deskripsi?.substring(0, 50)}${cp.deskripsi?.length > 50 ? '...' : ''}`
    }))
    
    // Combine: STS, SAS, then other CP
    cpOptions.value = [stsOption, sasOption, ...cpOptionsMapped]
    
    // Only show warning if there are no CP at all (including STS/SAS)
    if (cpOptionsMapped.length === 0 && cpOptions.value.length === 2) {
      // Only STS and SAS available, no other CP - this is fine, no warning needed
    } else if (cpOptions.value.length === 0) {
      toast.warning('Tidak ada Capaian Pembelajaran untuk tingkat dan semester yang dipilih')
    }
  } catch (error) {
    console.error('Failed to fetch capaian pembelajaran:', error)
    // Even on error, still show STS and SAS
    const stsOption = {
      id: 'sts',
      kode_cp: 'STS',
      deskripsi: '',
      fase: '',
      label: 'Nilai STS (Sumatif Tengah Semester)',
      isSpecial: true
    }
    
    const sasOption = {
      id: 'sas',
      kode_cp: 'SAS',
      deskripsi: '',
      fase: '',
      label: 'Nilai SAS (Sumatif Akhir Semester)',
      isSpecial: true
    }
    
    cpOptions.value = [stsOption, sasOption]
    toast.error('Gagal mengambil data Capaian Pembelajaran')
  }
}

const loadNilai = async () => {
  if (!selectedKelas.value || !selectedMapel.value || !selectedSemester.value || !selectedCP.value) {
    nilaiData.value = []
    return
  }

  try {
    loading.value = true
    
    // For STS and SAS, get or create special CP first
    let capaianPembelajaranId = selectedCP.value
    
    if (selectedCP.value === 'sts' || selectedCP.value === 'sas') {
      try {
        const cpResponse = await axios.post('/guru/nilai/get-or-create-special-cp', {
          mata_pelajaran_id: selectedMapel.value,
          kode_cp: selectedCP.value.toUpperCase(),
          semester: selectedSemester.value
        })
        capaianPembelajaranId = cpResponse.data.capaian_pembelajaran_id
      } catch (error) {
        console.error('Failed to get/create special CP:', error)
        toast.error('Gagal mengambil capaian pembelajaran khusus')
        nilaiData.value = []
        return
      }
    }
    
    const response = await axios.get(`/guru/nilai/kelas/${selectedKelas.value}/mapel/${selectedMapel.value}`, {
      params: {
        semester: selectedSemester.value,
        capaian_pembelajaran_id: capaianPembelajaranId
      }
    })
    // Handle different response formats
    if (response.data.data) {
      nilaiData.value = response.data.data
    } else if (response.data.nilai) {
      nilaiData.value = Array.isArray(response.data.nilai) ? response.data.nilai : []
    } else {
      nilaiData.value = []
    }
    changedItems.value.clear()
  } catch (error) {
    toast.error('Gagal mengambil data nilai')
    console.error(error)
    nilaiData.value = []
  } finally {
    loading.value = false
  }
}

const markAsChanged = (nilai) => {
  changedItems.value.add(nilai.siswa_id)
}

const calculateFinalGrade = (nilai) => {
  const harian = nilai.nilai_harian || 0
  const uts = nilai.nilai_uts || 0
  const uas = nilai.nilai_uas || 0
  
  // Formula: (Harian * 40%) + (UTS * 30%) + (UAS * 30%)
  const final = (harian * 0.4) + (uts * 0.3) + (uas * 0.3)
  return Math.round(final * 100) / 100
}

const getPredicate = (nilai) => {
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

const batchUpdate = async () => {
  if (!hasChanges.value) return

  try {
    updating.value = true
    
    const changedData = nilaiData.value
      .filter(item => changedItems.value.has(item.siswa_id))
      .map(item => ({
        siswa_id: item.siswa_id,
        mata_pelajaran_id: selectedMapel.value,
        kelas_id: selectedKelas.value,
        nilai_harian: item.nilai_harian || 0,
        nilai_uts: item.nilai_uts || 0,
        nilai_uas: item.nilai_uas || 0,
        nilai_akhir: calculateFinalGrade(item),
        catatan: item.catatan || ''
      }))

    await axios.post('/guru/nilai/batch-update', {
      nilai: changedData
    })

    toast.success('Nilai berhasil disimpan')
    changedItems.value.clear()
    
    // Reload data to get updated values
    await loadNilai()
  } catch (error) {
    toast.error('Gagal menyimpan nilai')
    console.error(error)
  } finally {
    updating.value = false
  }
}

// Form Methods
const onFormMapelChange = async () => {
  formKelas.value = ''
  formSemester.value = ''
  formCP.value = ''
  formKelasOptions.value = []
  formCPOptions.value = []
  
  if (formMapel.value) {
    try {
      const response = await axios.get('/lookup/kelas-by-mapel', {
        params: {
          mata_pelajaran_id: formMapel.value
        }
      })
      formKelasOptions.value = response.data
      
      // Get KKM from mata pelajaran
      const mapel = mapelOptions.value.find(m => m.id == formMapel.value)
      if (mapel && mapel.kkm !== undefined && mapel.kkm !== null) {
        formKkm.value = mapel.kkm
      } else {
        // If KKM not in mapelOptions, fetch mata pelajaran detail
        try {
          const mapelResponse = await axios.get(`/guru/capaian-pembelajaran/mapel/${formMapel.value}`)
          if (mapelResponse.data.mata_pelajaran && mapelResponse.data.mata_pelajaran.kkm !== undefined && mapelResponse.data.mata_pelajaran.kkm !== null) {
            formKkm.value = mapelResponse.data.mata_pelajaran.kkm
          } else {
            formKkm.value = 0
          }
        } catch (error) {
          console.error('Failed to fetch mata pelajaran KKM:', error)
          formKkm.value = 0
        }
      }
    } catch (error) {
      console.error('Failed to fetch kelas:', error)
      formKelasOptions.value = []
    }
  }
}

const onFormKelasChange = () => {
  if (!isEditingNilai.value) {
    formSemester.value = ''
    formCP.value = ''
  }
  formCPOptions.value = []
}

const onFormSemesterChange = async () => {
  formCP.value = ''
  
  // Always create STS and SAS options first
  const stsOption = {
    id: 'sts',
    kode_cp: 'STS',
    deskripsi: '',
    fase: '',
    label: 'Nilai STS (Sumatif Tengah Semester)',
    isSpecial: true
  }
  
  const sasOption = {
    id: 'sas',
    kode_cp: 'SAS',
    deskripsi: '',
    fase: '',
    label: 'Nilai SAS (Sumatif Akhir Semester)',
    isSpecial: true
  }
  
  if (formKelas.value && formMapel.value && formSemester.value) {
    try {
      const kelas = formKelasOptions.value.find(k => k.id == formKelas.value)
      if (!kelas || !kelas.tingkat) {
        // Even if kelas has no tingkat, still show STS and SAS
        formCPOptions.value = [stsOption, sasOption]
        return
      }
      
      const tingkat = kelas.tingkat.toString()
      
      // Update STS and SAS with tingkat
      stsOption.fase = tingkat
      sasOption.fase = tingkat
      
      const response = await axios.get(`/guru/capaian-pembelajaran/mapel/${formMapel.value}`)
      const allCP = response.data.capaian_pembelajaran || []
      
      // Filter CP by fase, is_active, and exclude STS/SAS if they exist in database
      const filteredCP = allCP.filter(cp => {
        return cp.fase === tingkat && cp.is_active !== false && cp.kode_cp !== 'STS' && cp.kode_cp !== 'SAS'
      })
      
      const cpOptionsMapped = filteredCP.map(cp => ({
        id: cp.id,
        kode_cp: cp.kode_cp,
        deskripsi: cp.deskripsi,
        fase: cp.fase,
        label: `${cp.kode_cp} - ${cp.deskripsi?.substring(0, 50)}${cp.deskripsi?.length > 50 ? '...' : ''}`
      }))
      
      // Combine: STS, SAS, then other CP
      formCPOptions.value = [stsOption, sasOption, ...cpOptionsMapped]
    } catch (error) {
      console.error('Failed to fetch CP:', error)
      // Even on error, still show STS and SAS
      formCPOptions.value = [stsOption, sasOption]
    }
  } else {
    // If filters are not complete, still show STS and SAS
    formCPOptions.value = [stsOption, sasOption]
  }
}

const handleStep1Next = async () => {
  if (!formMapel.value || !formKelas.value || !formSemester.value || !formCP.value) {
    toast.error('Harap lengkapi semua filter')
    return
  }
  
  try {
    // Fetch siswa dari kelas menggunakan endpoint guru
    const response = await axios.get(`/guru/nilai/kelas/${formKelas.value}/siswa`)
    const siswa = response.data.siswa || []
    
    // Initialize form nilai list
    formNilaiList.value = siswa.map(s => ({
      siswa_id: s.id,
      siswa: {
        id: s.id,
        nama_lengkap: s.nama_lengkap,
        nis: s.nis
      },
      nilai: null,
      deskripsi: ''
    }))
    
    formStep.value = 2
  } catch (error) {
    console.error('Failed to fetch siswa:', error)
    toast.error('Gagal mengambil data siswa')
  }
}

const updateDeskripsi = (item) => {
  if (item.nilai === null || item.nilai === '') {
    item.deskripsi = ''
    return
  }
  
  const cp = formCPOptions.value.find(cp => cp.id == formCP.value)
  
  // Jika STS atau SAS, tidak perlu generate deskripsi
  if (cp && (cp.id === 'sts' || cp.id === 'sas')) {
    item.deskripsi = ''
    return
  }
  
  const cpDeskripsi = cp ? cp.deskripsi : 'Capaian Pembelajaran'
  
  if (item.nilai < formKkm.value) {
    item.deskripsi = `Perlu penguatan dalam ${cpDeskripsi}`
  } else {
    item.deskripsi = `Sudah menguasai dalam ${cpDeskripsi}`
  }
}

const getFormCPName = () => {
  const cp = formCPOptions.value.find(cp => cp.id == formCP.value)
  if (cp) {
    // Jika STS atau SAS, hanya tampilkan label tanpa deskripsi
    if (cp.id === 'sts' || cp.id === 'sas') {
      return cp.label
    }
    return `${cp.kode_cp} - ${cp.deskripsi}`
  }
  // Fallback: try to get from editing nilai data
  if (editingNilaiData.value && editingNilaiData.value.capaian_pembelajaran) {
    const cpData = editingNilaiData.value.capaian_pembelajaran
    return `${cpData.kode_cp} - ${cpData.deskripsi}`
  }
  // Fallback: try to get from selected CP in main filter
  const selectedCPData = cpOptions.value.find(cp => cp.id == selectedCP.value)
  if (selectedCPData) {
    return `${selectedCPData.kode_cp} - ${selectedCPData.deskripsi}`
  }
  return 'Capaian Pembelajaran tidak ditemukan'
}

const handleSubmitNilai = async () => {
  // Validate
  const hasNilai = formNilaiList.value.some(item => item.nilai !== null && item.nilai !== '')
  if (!hasNilai) {
    toast.error('Harap isi minimal satu nilai')
    return
  }
  
  try {
    formSubmitting.value = true
    
    // Get tahun ajaran aktif
    const tahunAjaranResponse = await axios.get('/lookup/tahun-ajaran-aktif')
    const tahunAjaranId = tahunAjaranResponse.data?.id
    
    if (!tahunAjaranId) {
      toast.error('Tahun ajaran aktif tidak ditemukan')
      return
    }
    
    // Get guru from current user
    const guruResponse = await axios.get('/lookup/guru')
    const guruId = guruResponse.data?.id
    
    if (!guruId) {
      toast.error('Guru tidak ditemukan')
      return
    }
    
    if (isEditingNilai.value && editingNilaiId.value) {
      // Update existing nilai
      const item = formNilaiList.value[0]
      await axios.put(`/guru/nilai/${editingNilaiId.value}`, {
        nilai_sumatif_1: item.nilai,
        nilai_akhir: item.nilai,
        deskripsi: item.deskripsi
      })
      
      toast.success('Nilai berhasil diperbarui')
    } else {
      // Create new nilai
      // For STS and SAS, we need to get or create special CP
      let capaianPembelajaranId = formCP.value
      
      if (formCP.value === 'sts' || formCP.value === 'sas') {
        // Get or create special CP for STS/SAS
        try {
          const cpResponse = await axios.post('/guru/nilai/get-or-create-special-cp', {
            mata_pelajaran_id: formMapel.value,
            kode_cp: formCP.value.toUpperCase(),
            semester: formSemester.value
          })
          capaianPembelajaranId = cpResponse.data.capaian_pembelajaran_id
        } catch (error) {
          console.error('Failed to get/create special CP:', error)
          toast.error('Gagal membuat capaian pembelajaran khusus')
          return
        }
      }
      
      const nilaiData = formNilaiList.value
        .filter(item => item.nilai !== null && item.nilai !== '')
        .map(item => ({
          siswa_id: item.siswa_id,
          mata_pelajaran_id: formMapel.value,
          tahun_ajaran_id: tahunAjaranId,
          guru_id: guruId,
          capaian_pembelajaran_id: capaianPembelajaranId,
          semester: formSemester.value,
          nilai: item.nilai,
          nilai_akhir: item.nilai, // Set nilai_akhir sama dengan nilai
          deskripsi: item.deskripsi || '' // Empty for STS/SAS
        }))
      
      await axios.post('/guru/nilai/store', {
        nilai: nilaiData
      })
      
      toast.success('Nilai berhasil disimpan')
    }
    
    closeAddForm()
    
    // Always reload nilai list if all filters are selected
    if (selectedMapel.value && selectedKelas.value && selectedSemester.value && selectedCP.value) {
      await loadNilai()
    }
  } catch (error) {
    console.error('Failed to save nilai:', error)
    toast.error(error.response?.data?.message || 'Gagal menyimpan nilai')
  } finally {
    formSubmitting.value = false
  }
}

const closeAddForm = () => {
  showAddForm.value = false
  formStep.value = 1
  formMapel.value = ''
  formKelas.value = ''
  formSemester.value = ''
  formCP.value = ''
  formKelasOptions.value = []
  formCPOptions.value = []
  formNilaiList.value = []
  formKkm.value = 0
  isEditingNilai.value = false
  editingNilaiId.value = null
  editingNilaiData.value = null
}

const editNilai = async (nilai) => {
  // Store editing nilai data for reference
  editingNilaiData.value = nilai
  
  // Set form values for editing
  formMapel.value = selectedMapel.value
  formKelas.value = selectedKelas.value
  formSemester.value = nilai.semester || selectedSemester.value
  isEditingNilai.value = true
  editingNilaiId.value = nilai.id
  
  // Get KKM
  const mapel = mapelOptions.value.find(m => m.id == formMapel.value)
  if (mapel && mapel.kkm !== undefined && mapel.kkm !== null) {
    formKkm.value = mapel.kkm
  }
  
  // Load kelas options first (without resetting values)
  try {
    const kelasResponse = await axios.get('/lookup/kelas-by-mapel', {
      params: {
        mata_pelajaran_id: formMapel.value
      }
    })
    formKelasOptions.value = kelasResponse.data
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
    formKelasOptions.value = []
  }
  
  // Load CP options
  // Always create STS and SAS options first
  const stsOption = {
    id: 'sts',
    kode_cp: 'STS',
    deskripsi: '',
    fase: '',
    label: 'Nilai STS (Sumatif Tengah Semester)',
    isSpecial: true
  }
  
  const sasOption = {
    id: 'sas',
    kode_cp: 'SAS',
    deskripsi: '',
    fase: '',
    label: 'Nilai SAS (Sumatif Akhir Semester)',
    isSpecial: true
  }
  
  try {
    const kelas = formKelasOptions.value.find(k => k.id == formKelas.value)
    if (kelas && kelas.tingkat) {
      const tingkat = kelas.tingkat.toString()
      
      // Update STS and SAS with tingkat
      stsOption.fase = tingkat
      sasOption.fase = tingkat
      
      const cpResponse = await axios.get(`/guru/capaian-pembelajaran/mapel/${formMapel.value}`)
      const allCP = cpResponse.data.capaian_pembelajaran || []
      
      // Filter CP by fase, is_active, and exclude STS/SAS if they exist in database
      const filteredCP = allCP.filter(cp => {
        return cp.fase === tingkat && cp.is_active !== false && cp.kode_cp !== 'STS' && cp.kode_cp !== 'SAS'
      })
      
      const cpOptionsMapped = filteredCP.map(cp => ({
        id: cp.id,
        kode_cp: cp.kode_cp,
        deskripsi: cp.deskripsi,
        fase: cp.fase,
        label: `${cp.kode_cp} - ${cp.deskripsi?.substring(0, 50)}${cp.deskripsi?.length > 50 ? '...' : ''}`
      }))
      
      formCPOptions.value = [stsOption, sasOption, ...cpOptionsMapped]
      
      // Set CP value from nilai data after options are loaded
      // Check if nilai has STS or SAS CP
      if (nilai.capaian_pembelajaran) {
        const cpData = nilai.capaian_pembelajaran
        if (cpData.kode_cp === 'STS') {
          formCP.value = 'sts'
        } else if (cpData.kode_cp === 'SAS') {
          formCP.value = 'sas'
        } else {
          formCP.value = nilai.capaian_pembelajaran_id || selectedCP.value
        }
      } else {
        formCP.value = nilai.capaian_pembelajaran_id || selectedCP.value
      }
    } else {
      // Even if kelas has no tingkat, still show STS and SAS
      formCPOptions.value = [stsOption, sasOption]
      
      // Set CP value from nilai data
      if (nilai.capaian_pembelajaran) {
        const cpData = nilai.capaian_pembelajaran
        if (cpData.kode_cp === 'STS') {
          formCP.value = 'sts'
        } else if (cpData.kode_cp === 'SAS') {
          formCP.value = 'sas'
        } else {
          formCP.value = nilai.capaian_pembelajaran_id || selectedCP.value
        }
      } else {
        formCP.value = nilai.capaian_pembelajaran_id || selectedCP.value
      }
    }
  } catch (error) {
    console.error('Failed to fetch CP:', error)
    // Even on error, still show STS and SAS
    formCPOptions.value = [stsOption, sasOption]
    
    // Set CP value from nilai data
    if (nilai.capaian_pembelajaran) {
      const cpData = nilai.capaian_pembelajaran
      if (cpData.kode_cp === 'STS') {
        formCP.value = 'sts'
      } else if (cpData.kode_cp === 'SAS') {
        formCP.value = 'sas'
      } else {
        formCP.value = nilai.capaian_pembelajaran_id || selectedCP.value
      }
    } else {
      formCP.value = nilai.capaian_pembelajaran_id || selectedCP.value
    }
  }
  
  // Set form nilai list with single item
  formNilaiList.value = [{
    siswa_id: nilai.siswa_id,
    siswa: {
      id: nilai.siswa.id,
      nama_lengkap: nilai.siswa.nama_lengkap,
      nis: nilai.siswa.nis
    },
    nilai: nilai.nilai_sumatif_1 || nilai.nilai_akhir || null,
    deskripsi: nilai.deskripsi || ''
  }]
  
  // Go directly to step 2
  formStep.value = 2
  showAddForm.value = true
}

const confirmDeleteNilai = (nilai) => {
  deletingNilaiId.value = nilai.id
  showDeleteConfirm.value = true
}

const deleteNilai = async () => {
  if (!deletingNilaiId.value) return
  
  try {
    await axios.delete(`/guru/nilai/${deletingNilaiId.value}`)
    toast.success('Nilai berhasil dihapus')
    showDeleteConfirm.value = false
    deletingNilaiId.value = null
    await loadNilai()
  } catch (error) {
    console.error('Failed to delete nilai:', error)
    toast.error(error.response?.data?.message || 'Gagal menghapus nilai')
  }
}

// Lifecycle
onMounted(() => {
  fetchMapel()
})
</script>

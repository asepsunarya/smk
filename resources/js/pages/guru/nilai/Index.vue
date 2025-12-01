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
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <FormField
            v-model="selectedKelas"
            type="select"
            label="Pilih Kelas"
            placeholder="Pilih Kelas"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            @update:model-value="loadNilai"
          />
          <FormField
            v-model="selectedMapel"
            type="select"
            label="Mata Pelajaran"
            placeholder="Pilih Mata Pelajaran"
            :options="mapelOptions"
            option-value="id"
            option-label="nama_mapel"
            @update:model-value="loadNilai"
          />
          <FormField
            v-model="jenisNilai"
            type="select"
            label="Jenis Nilai"
            placeholder="Pilih Jenis Nilai"
            :options="jenisNilaiOptions"
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
      <div v-else-if="!selectedKelas || !selectedMapel" class="bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih Kelas dan Mata Pelajaran</h3>
        <p class="mt-1 text-sm text-gray-500">Pilih kelas dan mata pelajaran untuk mulai input nilai.</p>
      </div>

      <!-- Grade Input Table -->
      <div v-else class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">
                Nilai {{ selectedKelasName }} - {{ selectedMapelName }}
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                Jenis: {{ jenisNilai || 'Semua' }}
              </p>
            </div>
            <div class="flex space-x-3">
              <button 
                @click="batchUpdate" 
                :disabled="!hasChanges || updating"
                class="btn btn-primary"
              >
                <svg v-if="updating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ updating ? 'Menyimpan...' : 'Simpan Semua' }}
              </button>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th class="w-16">No</th>
                <th>Nama Siswa</th>
                <th>NIS</th>
                <th class="w-24">Nilai Harian</th>
                <th class="w-24">UTS</th>
                <th class="w-24">UAS</th>
                <th class="w-24">Nilai Akhir</th>
                <th class="w-32">Predikat</th>
                <th class="w-40">Catatan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(nilai, index) in nilaiData" :key="nilai.siswa_id" class="hover:bg-gray-50">
                <td class="text-center">{{ index + 1 }}</td>
                <td>
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                      {{ nilai.siswa.nama_lengkap.charAt(0) }}
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">{{ nilai.siswa.nama_lengkap }}</div>
                    </div>
                  </div>
                </td>
                <td class="text-sm text-gray-500">{{ nilai.siswa.nis }}</td>
                <td>
                  <input
                    v-model.number="nilai.nilai_harian"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    @input="markAsChanged(nilai)"
                  />
                </td>
                <td>
                  <input
                    v-model.number="nilai.nilai_uts"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    @input="markAsChanged(nilai)"
                  />
                </td>
                <td>
                  <input
                    v-model.number="nilai.nilai_uas"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    @input="markAsChanged(nilai)"
                  />
                </td>
                <td class="text-center">
                  <span class="text-sm font-medium">{{ calculateFinalGrade(nilai) }}</span>
                </td>
                <td class="text-center">
                  <span :class="getPredicateColor(calculateFinalGrade(nilai))" 
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getPredicate(calculateFinalGrade(nilai)) }}
                  </span>
                </td>
                <td>
                  <textarea
                    v-model="nilai.catatan"
                    rows="1"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 resize-none"
                    placeholder="Catatan..."
                    @input="markAsChanged(nilai)"
                  ></textarea>
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import FormField from '../../../components/ui/FormField.vue'

const toast = useToast()

// Data
const kelasOptions = ref([])
const mapelOptions = ref([])
const nilaiData = ref([])
const changedItems = ref(new Set())

// State
const loading = ref(false)
const updating = ref(false)
const selectedKelas = ref('')
const selectedMapel = ref('')
const jenisNilai = ref('')

// Options
const jenisNilaiOptions = [
  { value: '', label: 'Semua' },
  { value: 'harian', label: 'Nilai Harian' },
  { value: 'uts', label: 'UTS' },
  { value: 'uas', label: 'UAS' }
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

const hasChanges = computed(() => changedItems.value.size > 0)

// Methods
const fetchKelas = async () => {
  try {
    const response = await axios.get('/admin/kelas')
    kelasOptions.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch kelas:', error)
  }
}

const fetchMapel = async () => {
  try {
    const response = await axios.get('/admin/mata-pelajaran')
    mapelOptions.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch mata pelajaran:', error)
  }
}

const loadNilai = async () => {
  if (!selectedKelas.value || !selectedMapel.value) {
    nilaiData.value = []
    return
  }

  try {
    loading.value = true
    const response = await axios.get(`/guru/nilai/kelas/${selectedKelas.value}/mapel/${selectedMapel.value}`)
    nilaiData.value = response.data.data
    changedItems.value.clear()
  } catch (error) {
    toast.error('Gagal mengambil data nilai')
    console.error(error)
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

// Lifecycle
onMounted(() => {
  fetchKelas()
  fetchMapel()
})
</script>

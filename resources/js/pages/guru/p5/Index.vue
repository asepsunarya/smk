<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Nilai P5
        </h2>
        <p class="mt-1 text-sm text-gray-500">
          Isi nilai projek P5 yang ditugaskan kepada Anda (projek dibuat oleh admin)
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
      </div>

      <!-- P5 List -->
      <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div 
          v-for="p5 in p5List" 
          :key="p5.id" 
          class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900">{{ p5.judul || p5.tema }}</h3>
                <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ p5.deskripsi }}</p>
                <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500">
                  <span>{{ (p5.peserta || []).length }} peserta</span>
                  <span v-if="p5.tahun_ajaran">TA: {{ p5.tahun_ajaran.tahun }}</span>
                </div>
              </div>
            </div>
            <div class="mt-6 flex justify-end">
              <button 
                @click="viewNilai(p5)" 
                class="btn btn-sm btn-primary"
              >
                Input Nilai
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && p5List.length === 0" class="bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada projek P5</h3>
        <p class="mt-1 text-sm text-gray-500">Projek P5 yang Anda koordinatori akan muncul di sini. Silakan hubungi admin untuk penetapan projek.</p>
      </div>

      <!-- Input Nilai Modal -->
      <Modal v-model:show="showNilaiModal" title="Input Nilai P5" size="6xl">
        <div v-if="selectedP5" class="space-y-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900">{{ selectedP5.judul || selectedP5.tema }}</h3>
            <p class="text-sm text-gray-500">{{ selectedP5.deskripsi }}</p>
          </div>

          <!-- Detail: Dimensi & Elemen (semua pasangan) -->
          <div v-if="elemenSubList.length > 0" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Detail dimensi dan elemen</h4>
            <dl class="space-y-2">
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dimensi</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">{{ selectedP5.dimensi || '-' }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-2">Elemen & Sub elemen</dt>
                <dd class="mt-1 space-y-2">
                  <div
                    v-for="(es, idx) in elemenSubList"
                    :key="idx"
                    class="text-sm text-gray-900 border-l-2 border-blue-300 pl-3 py-1.5 bg-white rounded-r"
                  >
                    <span class="font-medium">{{ idx + 1 }}. {{ es.elemen || '-' }}</span>
                    <span class="block text-gray-600 mt-0.5">{{ es.sub_elemen || '-' }}</span>
                  </div>
                </dd>
              </div>
            </dl>
          </div>

          <p class="text-sm text-gray-500">Isi predikat per sub elemen untuk setiap peserta. Predikat: Mulai berkembang, Sedang berkembang, Berkembang sesuai harapan, Sangat berkembang.</p>

          <!-- Tabel: baris = peserta, kolom = sub elemen (predikat) + Catatan Proses -->
          <div v-if="elemenSubList.length > 0 && siswaList.length > 0" class="overflow-x-auto">
            <table class="table">
              <thead>
                <tr>
                  <th class="w-16 sticky left-0 bg-gray-50">No</th>
                  <th class="min-w-[200px] sticky left-12 bg-gray-50">Nama Siswa</th>
                  <th v-for="(es, idx) in elemenSubList" :key="idx" class="min-w-[220px] whitespace-normal text-left px-3 py-2">
                    <span class="text-xs font-medium text-gray-500 block">Sub elemen {{ idx + 1 }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ es.sub_elemen }}</span>
                  </th>
                  <th class="min-w-[240px] whitespace-normal text-left px-3 py-2 bg-gray-50">
                    <span class="text-xs font-medium text-gray-500 block">Catatan Proses</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(siswa, index) in siswaList" :key="siswa.id">
                  <td class="text-center sticky left-0 bg-white">{{ index + 1 }}</td>
                  <td class="sticky left-12 bg-white min-w-[200px]">
                    <div class="flex items-center">
                      <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                        {{ siswa.nama_lengkap?.charAt(0) }}
                      </div>
                      <div class="ml-3 min-w-0">
                        <div class="text-sm font-medium text-gray-900 truncate">{{ siswa.nama_lengkap }}</div>
                        <div class="text-sm text-gray-500">{{ siswa.nis }}</div>
                      </div>
                    </div>
                  </td>
                  <td v-for="(es, idx) in elemenSubList" :key="idx" class="px-3 py-2">
                    <FormField
                      :model-value="getNilaiForm(siswa.id, es.sub_elemen)"
                      @update:model-value="(value) => setNilaiForm(siswa.id, es.sub_elemen, value)"
                      type="select"
                      :options="predikatOptions"
                      option-value="value"
                      option-label="label"
                      placeholder="Pilih predikat"
                    />
                  </td>
                  <td class="px-3 py-2">
                    <textarea
                      :value="getCatatanProses(siswa.id)"
                      @input="setCatatanProses(siswa.id, $event.target.value)"
                      class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm min-h-[60px]"
                      placeholder="Catatan proses untuk siswa ini..."
                      rows="2"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else-if="elemenSubList.length === 0" class="text-sm text-amber-600 py-4">
            Projek ini belum memiliki sub elemen. Hubungi admin untuk mengatur elemen & sub elemen pada projek.
          </p>
          <p v-else-if="siswaList.length === 0" class="text-sm text-gray-500 py-4">
            Projek ini belum memiliki peserta. Hubungi admin untuk menambahkan kelompok.
          </p>

          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeNilaiModal" class="btn btn-secondary">
              Batal
            </button>
            <button 
              @click="saveNilai" 
              :disabled="!hasNilaiChanges || savingNilai"
              class="btn btn-primary"
            >
              <svg v-if="savingNilai" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ savingNilai ? 'Menyimpan...' : 'Simpan Nilai' }}
            </button>
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
const p5List = ref([])
const siswaList = ref([])

// State
const loading = ref(false)
const savingNilai = ref(false)
const showNilaiModal = ref(false)
const selectedP5 = ref(null)
const nilaiForm = ref({}) // { [siswa_id]: { [sub_elemen]: nilai } }
const catatanProsesForm = ref({}) // { [siswa_id]: string }
const changedNilai = ref(new Set()) // keys 'siswaId_subElemen' or 'catatan_siswaId'

// Predikat (4 opsi)
const predikatOptions = [
  { value: 'MB', label: 'Mulai berkembang' },
  { value: 'SB', label: 'Sedang berkembang' },
  { value: 'BSH', label: 'Berkembang sesuai harapan' },
  { value: 'SAB', label: 'Sangat berkembang' }
]

const elemenSubList = computed(() => {
  const p5 = selectedP5.value
  if (!p5 || !p5.elemen_sub || !Array.isArray(p5.elemen_sub)) return []
  return p5.elemen_sub.filter((es) => (es.sub_elemen || '').trim())
})

// Computed
const hasNilaiChanges = computed(() => changedNilai.value.size > 0)

function getCatatanProses(siswaId) {
  return catatanProsesForm.value[siswaId] ?? ''
}

function setCatatanProses(siswaId, value) {
  catatanProsesForm.value[siswaId] = value
  changedNilai.value.add(`catatan_${siswaId}`)
}

// Methods
function getNilaiForm(siswaId, subElemen) {
  return nilaiForm.value[siswaId]?.[subElemen] ?? ''
}

function setNilaiForm(siswaId, subElemen, value) {
  if (!nilaiForm.value[siswaId]) nilaiForm.value[siswaId] = {}
  nilaiForm.value[siswaId][subElemen] = value
  changedNilai.value.add(`${siswaId}_${subElemen}`)
}

const fetchP5 = async () => {
  try {
    loading.value = true
    const response = await axios.get('/guru/p5')
    const data = response.data
    p5List.value = (data.data && Array.isArray(data.data)) ? data.data : (Array.isArray(data) ? data : [])
  } catch (error) {
    toast.error('Gagal mengambil data projek P5')
    console.error(error)
  } finally {
    loading.value = false
  }
}

async function viewNilai(p5) {
  selectedP5.value = p5
  nilaiForm.value = {}
  catatanProsesForm.value = {}
  changedNilai.value.clear()
  showNilaiModal.value = true
  siswaList.value = (p5.peserta && Array.isArray(p5.peserta)) ? [...p5.peserta] : []
  const elemenSub = p5.elemen_sub && Array.isArray(p5.elemen_sub) ? p5.elemen_sub : []
  siswaList.value.forEach((siswa) => {
    nilaiForm.value[siswa.id] = {}
    catatanProsesForm.value[siswa.id] = ''
    elemenSub.forEach((es) => {
      const sub = (es.sub_elemen || '').trim()
      if (sub) nilaiForm.value[siswa.id][sub] = ''
    })
  })
  try {
    const res = await axios.get(`/guru/p5/${p5.id}/nilai`)
    const existing = res.data?.nilai || {}
    Object.keys(existing).forEach((siswaId) => {
      if (!nilaiForm.value[siswaId]) nilaiForm.value[siswaId] = {}
      Object.entries(existing[siswaId]).forEach(([sub, nilai]) => {
        nilaiForm.value[siswaId][sub] = nilai
      })
    })
    const catatan = res.data?.catatan_proses || {}
    Object.entries(catatan).forEach(([siswaId, text]) => {
      catatanProsesForm.value[siswaId] = text || ''
    })
  } catch (e) {
    console.error('Failed to load existing nilai:', e)
  }
}

const saveNilai = async () => {
  if (!selectedP5.value) return
  const elemenSub = elemenSubList.value
  if (elemenSub.length === 0) {
    toast.error('Projek belum memiliki sub elemen')
    return
  }
  try {
    savingNilai.value = true
    const nilaiData = []
    Object.entries(nilaiForm.value).forEach(([siswaId, bySub]) => {
      elemenSub.forEach((es) => {
        const sub = (es.sub_elemen || '').trim()
        if (!sub) return
        const nilai = bySub?.[sub]
        if (nilai) {
          nilaiData.push({
            siswa_id: Number(siswaId),
            sub_elemen: sub,
            nilai
          })
        }
      })
    })

    const catatanData = siswaList.value.map((s) => ({
      siswa_id: s.id,
      catatan_proses: (catatanProsesForm.value[s.id] || '').trim() || null
    }))

    await axios.post(`/guru/p5/${selectedP5.value.id}/nilai`, {
      nilai: nilaiData,
      catatan_proses: catatanData
    })

    toast.success('Predikat berhasil disimpan')
    changedNilai.value.clear()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Gagal menyimpan predikat')
  } finally {
    savingNilai.value = false
  }
}

const closeNilaiModal = () => {
  showNilaiModal.value = false
  selectedP5.value = null
  nilaiForm.value = {}
  catatanProsesForm.value = {}
  changedNilai.value.clear()
}

// Lifecycle
onMounted(() => {
  fetchP5()
})
</script>


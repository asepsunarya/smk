<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
        Input Nilai UKK
      </h2>
      <p class="mt-1 text-sm text-gray-500">
        Input nilai UKK (Uji Kompetensi Keahlian) siswa tingkat 12. Pilih kelas lalu isi nilai teori dan praktek; predikat dan nilai akhir dihitung otomatis.
      </p>

      <!-- Access Check -->
      <div v-if="accessDenied" class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">Informasi</h3>
            <p class="mt-2 text-sm text-yellow-700">{{ accessMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Filters: Jurusan + Kelas -->
      <div v-if="canAccess" class="mt-6 bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <FormField
            v-model="selectedJurusan"
            type="select"
            label="Jurusan"
            placeholder="Pilih Jurusan"
            :options="jurusanOptions"
            option-value="id"
            option-label="nama_jurusan"
            @update:model-value="onJurusanChange"
          />
          <FormField
            v-model="selectedKelas"
            type="select"
            label="Kelas"
            placeholder="Pilih Kelas"
            :options="kelasOptions"
            option-value="id"
            option-label="nama_kelas"
            :disabled="!selectedJurusan"
            @update:model-value="onKelasChange"
          />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading && canAccess" class="mt-6 bg-white shadow rounded-lg p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
      </div>

      <!-- No event / empty state -->
      <div v-else-if="canAccess && selectedKelas && !loading && eventMessage" class="mt-6 bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Data UKK belum ada</h3>
        <p class="mt-1 text-sm text-gray-500">{{ eventMessage }}</p>
      </div>

      <!-- Table -->
      <div v-else-if="canAccess && selectedKelas && siswaList.length > 0" class="mt-6 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 overflow-x-auto">
          <table class="w-full divide-y divide-gray-200 min-w-[800px]">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-14">No</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DU/DI</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penguji</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Teori</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Praktek</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Predikat</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(row, index) in siswaList" :key="row.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-center text-gray-500">{{ index + 1 }}</td>
                <td class="px-4 py-3 text-sm">
                  <div class="font-medium text-gray-900">{{ row.nama_lengkap }}</div>
                  <div class="text-xs text-gray-500">NIS: {{ row.nis || '-' }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ namaDuDi }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ pengujiLabel }}</td>
                <td class="px-4 py-3">
                  <input
                    v-model.number="row.form.nilai_teori"
                    type="number"
                    min="0"
                    max="100"
                    placeholder="0-100"
                    class="block w-20 mx-auto text-center rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-2 py-1.5"
                  />
                </td>
                <td class="px-4 py-3">
                  <input
                    v-model.number="row.form.nilai_praktek"
                    type="number"
                    min="0"
                    max="100"
                    placeholder="0-100"
                    class="block w-20 mx-auto text-center rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-2 py-1.5"
                  />
                </td>
                <td class="px-4 py-3 text-sm text-center text-gray-900 font-medium">
                  {{ hitungNilaiAkhir(row.form) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span :class="getPredikatBadge(hitungPredikat(row.form))" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ hitungPredikat(row.form) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="mt-6 flex justify-end">
            <button
              type="button"
              :disabled="saving"
              class="btn btn-primary"
              @click="simpanSemua"
            >
              <svg v-if="saving" class="inline-block animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Pilih kelas dulu -->
      <div v-else-if="canAccess && !selectedKelas" class="mt-6 bg-white shadow rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Pilih Filter</h3>
        <p class="mt-1 text-sm text-gray-500">Pilih Jurusan dan Kelas untuk menampilkan daftar siswa dan input nilai UKK.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '../../../stores/auth'
import FormField from '../../../components/ui/FormField.vue'

const API = '/guru/ukk'
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const canAccess = ref(false)
const accessDenied = ref(false)
const accessMessage = ref('')
const loading = ref(false)
const saving = ref(false)
const selectedJurusan = ref('')
const selectedKelas = ref('')
const jurusanOptions = ref([])
const kelasOptions = ref([])
const siswaList = ref([])
const namaDuDi = ref('')
const pengujiLabel = ref('')
const eventMessage = ref('')

function hitungNilaiAkhir (form) {
  const t = form.nilai_teori
  const p = form.nilai_praktek
  if (t === '' || t == null || p === '' || p == null) return '-'
  const n = (parseFloat(t) * 0.3) + (parseFloat(p) * 0.7)
  return n.toFixed(2)
}

function hitungPredikat (form) {
  const n = hitungNilaiAkhir(form)
  if (n === '-') return '-'
  return parseFloat(n) >= 75 ? 'Kompeten' : 'Belum Kompeten'
}

function getPredikatBadge (predikat) {
  if (!predikat || predikat === '-') return 'bg-gray-100 text-gray-800'
  return predikat === 'Kompeten' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}

async function checkAccess () {
  try {
    const r = await axios.get(`${API}/jurusan-options`)
    jurusanOptions.value = r.data ?? []
    canAccess.value = true
    accessDenied.value = false
  } catch (e) {
    if (e.response?.status === 403) {
      canAccess.value = false
      accessDenied.value = true
      accessMessage.value = e.response?.data?.message ?? 'Akses Nilai UKK hanya untuk Kepala Jurusan.'
      toast.warning(accessMessage.value)
      router.replace('/guru')
    } else {
      toast.error('Gagal memuat data')
    }
  }
}

function onJurusanChange () {
  selectedKelas.value = ''
  kelasOptions.value = []
  siswaList.value = []
  eventMessage.value = ''
  if (!selectedJurusan.value) return
  fetchKelas()
}

function onKelasChange () {
  siswaList.value = []
  eventMessage.value = ''
  if (!selectedKelas.value) return
  loadByKelas()
}

async function fetchKelas () {
  if (!selectedJurusan.value) return
  try {
    const r = await axios.get(`${API}/kelas`, { params: { jurusan_id: selectedJurusan.value } })
    kelasOptions.value = r.data ?? []
  } catch (_) {
    kelasOptions.value = []
  }
}

async function loadByKelas () {
  if (!selectedKelas.value) return
  loading.value = true
  try {
    const r = await axios.get(`${API}/by-kelas`, { params: { kelas_id: selectedKelas.value } })
    if (!r.data.can_access) {
      accessDenied.value = true
      accessMessage.value = r.data.message ?? 'Akses ditolak.'
      return
    }
    if (r.data.message && !r.data.siswa?.length) {
      eventMessage.value = r.data.message
      namaDuDi.value = ''
      pengujiLabel.value = ''
      siswaList.value = []
      return
    }
    namaDuDi.value = r.data.nama_du_di ?? '-'
    const internal = r.data.penguji_internal ?? ''
    const eksternal = r.data.penguji_eksternal ?? ''
    pengujiLabel.value = internal + (eksternal ? (internal ? ` / ${eksternal}` : eksternal) : '') || '-'
    siswaList.value = (r.data.siswa ?? []).map(s => ({
      ...s,
      form: {
        nilai_teori: s.ukk?.nilai_teori ?? '',
        nilai_praktek: s.ukk?.nilai_praktek ?? '',
      },
    }))
    eventMessage.value = ''
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Gagal mengambil data')
    siswaList.value = []
  } finally {
    loading.value = false
  }
}

async function simpanSemua () {
  if (!selectedKelas.value) {
    toast.error('Pilih kelas terlebih dahulu')
    return
  }
  saving.value = true
  try {
    const nilai = siswaList.value.map(row => ({
      siswa_id: row.id,
      nilai_teori: row.form.nilai_teori !== '' && row.form.nilai_teori != null ? Number(row.form.nilai_teori) : null,
      nilai_praktek: row.form.nilai_praktek !== '' && row.form.nilai_praktek != null ? Number(row.form.nilai_praktek) : null,
    }))
    const r = await axios.post(`${API}/simpan-kelas`, {
      kelas_id: selectedKelas.value,
      nilai,
    })
    toast.success(r.data?.message ?? 'Nilai UKK berhasil disimpan.')
    await loadByKelas()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Gagal menyimpan nilai UKK')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  if (authStore.user?.role === 'guru' && !authStore.isKepalaJurusan) {
    toast.warning('Akses Nilai UKK hanya untuk Kepala Jurusan.')
    router.replace('/guru')
    return
  }
  checkAccess()
})
</script>

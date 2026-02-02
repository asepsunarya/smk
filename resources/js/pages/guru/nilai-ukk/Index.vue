<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <DataTable
        title="Nilai UKK"
        description="Kelola nilai UKK siswa jurusan Anda (pastikan Data UKK sudah dibuat oleh admin)"
        :data="ukk"
        :columns="columns"
        :loading="loading"
        empty-message="Belum ada nilai UKK"
        empty-description="Data UKK dibuat oleh admin. Lalu tambah nilai per siswa."
        :searchable="true"
        @search="handleSearch"
      >
        <template #actions>
          <button @click="openForm" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Nilai UKK
          </button>
        </template>

        <template #cell-siswa="{ item }">
          <div class="text-sm">
            <div class="font-medium text-gray-900">{{ item.siswa?.nama_lengkap || '-' }}</div>
            <div class="text-gray-500 text-xs">NIS: {{ item.siswa?.nis || '-' }}</div>
          </div>
        </template>

        <template #cell-jurusan="{ item }">
          <div class="text-sm text-gray-900">{{ item.jurusan?.nama_jurusan || '-' }}</div>
        </template>

        <template #cell-kelas="{ item }">
          <div class="text-sm text-gray-900">{{ item.kelas?.nama_kelas || '-' }}</div>
        </template>

        <template #cell-nama_du_di="{ item }">
          <div class="text-sm text-gray-900">{{ item.nama_du_di || '-' }}</div>
        </template>

        <template #cell-tanggal_ujian="{ item }">
          <div class="text-sm text-gray-900">{{ formatDate(item.tanggal_ujian) }}</div>
        </template>

        <template #cell-nilai="{ item }">
          <div class="text-sm">
            <div class="text-gray-900">Teori: {{ item.nilai_teori != null ? item.nilai_teori : '-' }}</div>
            <div class="text-gray-900">Praktek: {{ item.nilai_praktek != null ? item.nilai_praktek : '-' }}</div>
            <div class="font-medium text-gray-900 mt-1">Akhir: {{ item.nilai_akhir != null ? item.nilai_akhir : '-' }}</div>
          </div>
        </template>

        <template #cell-predikat="{ item }">
          <span :class="getPredikatBadge(item.predikat)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ item.predikat || 'Belum Dinilai' }}
          </span>
        </template>

        <template #cell-penguji_internal="{ item }">
          <div class="text-sm">
            <div class="text-gray-900">{{ item.penguji_internal?.nama_lengkap || '-' }}</div>
            <div v-if="item.penguji_eksternal" class="text-xs text-gray-500">Eksternal: {{ item.penguji_eksternal }}</div>
          </div>
        </template>

        <template #row-actions="{ item }">
          <div class="flex items-center space-x-2">
            <button @click="editUkk(item)" class="text-blue-600 hover:text-blue-900" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
            <button @click="confirmDelete(item)" class="text-red-600 hover:text-red-900" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </div>
        </template>
      </DataTable>

      <Modal v-model:show="showForm" :title="isEditing ? 'Edit Nilai UKK' : 'Tambah Nilai UKK'" size="lg">
        <form @submit.prevent="submitForm" id="nilai-ukk-form" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.jurusan_id"
              type="select"
              label="Jurusan"
              placeholder="Pilih jurusan"
              :options="jurusanOptions"
              option-value="id"
              option-label="nama_jurusan"
              required
              :error="errors.jurusan_id"
              :disabled="isEditing"
              @update:model-value="onJurusanChange"
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
              :disabled="!form.jurusan_id || isEditing"
              @update:model-value="onKelasChange"
            />
          </div>

          <FormField
            v-model="form.siswa_id"
            type="select"
            label="Siswa"
            placeholder="Pilih siswa"
            :options="siswaOptions"
            option-value="id"
            option-label="label"
            required
            :error="errors.siswa_id"
            :disabled="!form.kelas_id || isEditing"
          />

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField
              v-model="form.nilai_teori"
              type="number"
              label="Nilai Teori"
              placeholder="0-100"
              min="0"
              max="100"
              :error="errors.nilai_teori"
            />
            <FormField
              v-model="form.nilai_praktek"
              type="number"
              label="Nilai Praktek"
              placeholder="0-100"
              min="0"
              max="100"
              :error="errors.nilai_praktek"
            />
          </div>

          <div v-if="form.nilai_teori != null && form.nilai_teori !== '' && form.nilai_praktek != null && form.nilai_praktek !== ''" class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-gray-700">
              <div class="font-medium mb-1">Perhitungan:</div>
              <div>Nilai Akhir: <span class="font-semibold">{{ calculateNilaiAkhir() }}</span> (30% Teori + 70% Praktek)</div>
              <div>Predikat: <span class="font-semibold">{{ calculatePredikat() }}</span></div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeForm" class="btn btn-secondary">Batal</button>
            <button type="submit" :disabled="submitting" class="btn btn-primary">
              {{ submitting ? 'Menyimpan...' : (isEditing ? 'Perbarui' : 'Simpan') }}
            </button>
          </div>
        </form>
      </Modal>

      <ConfirmDialog
        v-model:show="showDeleteConfirm"
        title="Hapus Nilai UKK"
        :message="`Hapus nilai UKK untuk ${selectedUkk?.siswa?.nama_lengkap}?`"
        confirm-text="Ya, Hapus"
        type="error"
        :loading="deleting"
        @confirm="doDelete"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '../../../stores/auth'
import DataTable from '../../../components/ui/DataTable.vue'
import Modal from '../../../components/ui/Modal.vue'
import FormField from '../../../components/ui/FormField.vue'
import ConfirmDialog from '../../../components/ui/ConfirmDialog.vue'

const API = '/guru/ukk'
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()
const ukk = ref([])
const loading = ref(false)
const showForm = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const showDeleteConfirm = ref(false)
const selectedUkk = ref(null)
const errors = ref({})

const filters = reactive({ search: '' })

const form = reactive({
  id: null,
  jurusan_id: '',
  kelas_id: '',
  siswa_id: '',
  nilai_teori: '',
  nilai_praktek: ''
})

const jurusanOptions = ref([])
const kelasOptions = ref([])
const siswaOptions = ref([])

const columns = [
  { key: 'siswa', label: 'Siswa' },
  { key: 'jurusan', label: 'Jurusan' },
  { key: 'kelas', label: 'Kelas' },
  { key: 'nama_du_di', label: 'DU/DI' },
  { key: 'tanggal_ujian', label: 'Tanggal Ujian' },
  { key: 'nilai', label: 'Nilai' },
  { key: 'predikat', label: 'Predikat' },
  { key: 'penguji_internal', label: 'Penguji' }
]

function calculateNilaiAkhir () {
  const t = form.nilai_teori
  const p = form.nilai_praktek
  if (t == null || t === '' || p == null || p === '') return '-'
  const n = (parseFloat(t) * 0.3) + (parseFloat(p) * 0.7)
  return n.toFixed(2)
}

function calculatePredikat () {
  const n = calculateNilaiAkhir()
  if (n === '-') return '-'
  return parseFloat(n) >= 75 ? 'Kompeten' : 'Belum Kompeten'
}

function getPredikatBadge (predikat) {
  if (!predikat) return 'bg-gray-100 text-gray-800'
  return predikat === 'Kompeten' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}

function formatDate (d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID')
}

async function fetchUkk () {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    params.append('per_page', 100)
    const res = await axios.get(`${API}?${params}`)
    ukk.value = res.data?.data ?? res.data ?? []
  } catch (e) {
    toast.error(e.response?.status === 403 ? 'Akses ditolak. Hanya Kepala Jurusan.' : 'Gagal mengambil nilai UKK')
    ukk.value = []
  } finally {
    loading.value = false
  }
}

function handleSearch (q) {
  filters.search = q
  fetchUkk()
}

async function fetchJurusan () {
  try {
    const r = await axios.get(`${API}/jurusan-options`)
    jurusanOptions.value = r.data ?? []
  } catch (_) {
    jurusanOptions.value = []
  }
}

async function fetchKelas (jurusanId) {
  if (!jurusanId) { kelasOptions.value = []; return }
  try {
    const r = await axios.get(`${API}/kelas`, { params: { jurusan_id: jurusanId } })
    kelasOptions.value = r.data ?? []
  } catch (_) {
    kelasOptions.value = []
  }
}

async function fetchSiswa (kelasId) {
  if (!kelasId) { siswaOptions.value = []; return }
  try {
    const r = await axios.get(`${API}/siswa`, { params: { kelas_id: kelasId } })
    const list = r.data ?? []
    siswaOptions.value = list.map(s => ({ id: s.id, label: `${s.nama_lengkap} (${s.nis || '-'})` }))
  } catch (_) {
    siswaOptions.value = []
  }
}

function onJurusanChange () {
  form.kelas_id = ''
  form.siswa_id = ''
  kelasOptions.value = []
  siswaOptions.value = []
  fetchKelas(form.jurusan_id)
}

function onKelasChange () {
  form.siswa_id = ''
  fetchSiswa(form.kelas_id)
}

function openForm () {
  isEditing.value = false
  selectedUkk.value = null
  Object.assign(form, { id: null, jurusan_id: '', kelas_id: '', siswa_id: '', nilai_teori: '', nilai_praktek: '' })
  errors.value = {}
  kelasOptions.value = []
  siswaOptions.value = []
  showForm.value = true
}

function closeForm () {
  showForm.value = false
  fetchUkk()
}

function editUkk (item) {
  isEditing.value = true
  selectedUkk.value = item
  form.id = item.id
  form.jurusan_id = item.jurusan_id
  form.kelas_id = item.kelas_id ?? item.kelas?.id ?? ''
  form.siswa_id = item.siswa_id
  form.nilai_teori = item.nilai_teori ?? ''
  form.nilai_praktek = item.nilai_praktek ?? ''
  errors.value = {}
  fetchKelas(item.jurusan_id)
  fetchSiswa(item.kelas_id ?? item.kelas?.id)
  showForm.value = true
}

function confirmDelete (item) {
  selectedUkk.value = item
  showDeleteConfirm.value = true
}

async function doDelete () {
  if (!selectedUkk.value?.id) return
  deleting.value = true
  try {
    await axios.delete(`${API}/${selectedUkk.value.id}`)
    toast.success('Nilai UKK berhasil dihapus')
    showDeleteConfirm.value = false
    fetchUkk()
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Gagal menghapus nilai UKK')
  } finally {
    deleting.value = false
  }
}

async function submitForm () {
  errors.value = {}
  submitting.value = true
  try {
    if (isEditing.value) {
      await axios.put(`${API}/${form.id}`, {
        nilai_teori: form.nilai_teori !== '' && form.nilai_teori != null ? parseInt(form.nilai_teori, 10) : null,
        nilai_praktek: form.nilai_praktek !== '' && form.nilai_praktek != null ? parseInt(form.nilai_praktek, 10) : null
      })
      toast.success('Nilai UKK berhasil diperbarui')
    } else {
      await axios.post(API, {
        jurusan_id: form.jurusan_id,
        kelas_id: form.kelas_id,
        siswa_id: form.siswa_id,
        nilai_teori: form.nilai_teori !== '' && form.nilai_teori != null ? parseInt(form.nilai_teori, 10) : null,
        nilai_praktek: form.nilai_praktek !== '' && form.nilai_praktek != null ? parseInt(form.nilai_praktek, 10) : null
      })
      toast.success('Nilai UKK berhasil ditambahkan')
    }
    closeForm()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data?.errors ?? {}
    }
    toast.error(e.response?.data?.message ?? 'Gagal menyimpan nilai UKK')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  if (authStore.user?.role === 'guru' && !authStore.isKepalaJurusan) {
    toast.warning('Akses Nilai UKK hanya untuk Kepala Jurusan.')
    router.replace('/guru')
    return
  }
  fetchUkk()
  fetchJurusan()
})
</script>

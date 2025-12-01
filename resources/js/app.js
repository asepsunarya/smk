import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import axios from 'axios'
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import '../css/app.css'
import './bootstrap'

// Import main layout
import App from './App.vue'

// Import pages
import Login from './pages/Login.vue'
import Dashboard from './pages/Dashboard.vue'

// Admin pages
import AdminDashboard from './pages/admin/Dashboard.vue'
import SiswaIndex from './pages/admin/siswa/Index.vue'
import SiswaForm from './pages/admin/siswa/Form.vue'
import GuruIndex from './pages/admin/guru/Index.vue'
import GuruForm from './pages/admin/guru/Form.vue'
import KelasIndex from './pages/admin/kelas/Index.vue'
import JurusanIndex from './pages/admin/jurusan/Index.vue'
import MataPelajaranIndex from './pages/admin/mata-pelajaran/Index.vue'
import UserIndex from './pages/admin/user/Index.vue'

// Guru pages
import GuruDashboard from './pages/guru/Dashboard.vue'
import NilaiIndex from './pages/guru/nilai/Index.vue'
import CapaianPembelajaranIndex from './pages/guru/capaian-pembelajaran/Index.vue'

// Wali Kelas pages
import WaliKelasDashboard from './pages/wali-kelas/Dashboard.vue'
import NilaiKelasIndex from './pages/wali-kelas/nilai-kelas/Index.vue'
import KehadiranIndex from './pages/wali-kelas/kehadiran/Index.vue'
import RaporIndex from './pages/wali-kelas/rapor/Index.vue'

// Kepala Sekolah pages
import KepalaSekolahDashboard from './pages/kepala-sekolah/Dashboard.vue'
import RaporApprovalIndex from './pages/kepala-sekolah/rapor-approval/Index.vue'
import RekapIndex from './pages/kepala-sekolah/rekap/Index.vue'

// Siswa pages
import SiswaDashboard from './pages/siswa/Dashboard.vue'
import RaporSiswaIndex from './pages/siswa/rapor/Index.vue'
import NilaiSiswaIndex from './pages/siswa/nilai/Index.vue'

// Store
import { useAuthStore } from './stores/auth'

// Configure axios
axios.defaults.baseURL = '/api'
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

// Routes
const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true }
  },
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  
  // Admin routes
  {
    path: '/admin',
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: AdminDashboard
      },
      {
        path: 'siswa',
        name: 'admin.siswa.index',
        component: SiswaIndex
      },
      {
        path: 'siswa/create',
        name: 'admin.siswa.create',
        component: SiswaForm
      },
      {
        path: 'siswa/:id/edit',
        name: 'admin.siswa.edit',
        component: SiswaForm,
        props: true
      },
      {
        path: 'guru',
        name: 'admin.guru.index',
        component: GuruIndex
      },
      {
        path: 'guru/create',
        name: 'admin.guru.create',
        component: GuruForm
      },
      {
        path: 'guru/:id/edit',
        name: 'admin.guru.edit',
        component: GuruForm,
        props: true
      },
      {
        path: 'kelas',
        name: 'admin.kelas.index',
        component: KelasIndex
      },
      {
        path: 'jurusan',
        name: 'admin.jurusan.index',
        component: JurusanIndex
      },
      {
        path: 'mata-pelajaran',
        name: 'admin.mata-pelajaran.index',
        component: MataPelajaranIndex
      },
      {
        path: 'user',
        name: 'admin.user.index',
        component: UserIndex
      }
    ]
  },

  // Guru routes
  {
    path: '/guru',
    meta: { requiresAuth: true, role: 'guru' },
    children: [
      {
        path: '',
        name: 'guru.dashboard',
        component: GuruDashboard
      },
      {
        path: 'nilai',
        name: 'guru.nilai.index',
        component: NilaiIndex
      },
      {
        path: 'capaian-pembelajaran',
        name: 'guru.capaian-pembelajaran.index',
        component: CapaianPembelajaranIndex
      }
    ]
  },

  // Wali Kelas routes
  {
    path: '/wali-kelas',
    meta: { requiresAuth: true, role: 'wali_kelas' },
    children: [
      {
        path: '',
        name: 'wali-kelas.dashboard',
        component: WaliKelasDashboard
      },
      {
        path: 'nilai-kelas',
        name: 'wali-kelas.nilai-kelas.index',
        component: NilaiKelasIndex
      },
      {
        path: 'kehadiran',
        name: 'wali-kelas.kehadiran.index',
        component: KehadiranIndex
      },
      {
        path: 'rapor',
        name: 'wali-kelas.rapor.index',
        component: RaporIndex
      }
    ]
  },

  // Kepala Sekolah routes
  {
    path: '/kepala-sekolah',
    meta: { requiresAuth: true, role: 'kepala_sekolah' },
    children: [
      {
        path: '',
        name: 'kepala-sekolah.dashboard',
        component: KepalaSekolahDashboard
      },
      {
        path: 'rapor-approval',
        name: 'kepala-sekolah.rapor-approval.index',
        component: RaporApprovalIndex
      },
      {
        path: 'rekap',
        name: 'kepala-sekolah.rekap.index',
        component: RekapIndex
      }
    ]
  },

  // Siswa routes
  {
    path: '/siswa',
    meta: { requiresAuth: true, role: 'siswa' },
    children: [
      {
        path: '',
        name: 'siswa.dashboard',
        component: SiswaDashboard
      },
      {
        path: 'rapor',
        name: 'siswa.rapor.index',
        component: RaporSiswaIndex
      },
      {
        path: 'nilai',
        name: 'siswa.nilai.index',
        component: NilaiSiswaIndex
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Route guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
    return
  }
  
  if (to.meta.guest && authStore.isAuthenticated) {
    next('/')
    return
  }
  
  if (to.meta.role && authStore.user?.role !== to.meta.role) {
    next('/')
    return
  }
  
  next()
})

// Create app
const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(Toast, {
  position: 'top-right',
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false
})

app.mount('#app')
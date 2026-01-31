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
import EkstrakurikulerIndex from './pages/admin/ekstrakurikuler/Index.vue'
import PklIndex from './pages/admin/pkl/Index.vue'
import UkkEventsIndex from './pages/admin/ukk-events/Index.vue'
import GuruNilaiUkkIndex from './pages/guru/nilai-ukk/Index.vue'
import AdminP5Index from './pages/admin/p5/Index.vue'
import AdminP5KelompokIndex from './pages/admin/p5/Kelompok.vue'
import CetakRaporHasilBelajarIndex from './pages/admin/cetak-rapor/hasil-belajar/Index.vue'
import CetakRaporP5Index from './pages/admin/cetak-rapor/p5/Index.vue'
import CetakRaporLeggerIndex from './pages/admin/cetak-rapor/legger/Index.vue'
import TahunAjaranIndex from './pages/admin/tahun-ajaran/Index.vue'
import UserIndex from './pages/admin/user/Index.vue'
import WaliKelasIndex from './pages/admin/wali-kelas/Index.vue'

// Guru pages
import GuruDashboard from './pages/guru/Dashboard.vue'
import NilaiIndex from './pages/guru/nilai/Index.vue'
import CapaianPembelajaranIndex from './pages/guru/capaian-pembelajaran/Index.vue'
import NilaiEkstrakurikulerIndex from './pages/guru/nilai-ekstrakurikuler/Index.vue'
import P5Index from './pages/guru/p5/Index.vue'

// Wali Kelas pages
import WaliKelasDashboard from './pages/wali-kelas/Dashboard.vue'
import NilaiKelasIndex from './pages/wali-kelas/nilai-kelas/Index.vue'
import KehadiranIndex from './pages/wali-kelas/kehadiran/Index.vue'
import RaporIndex from './pages/wali-kelas/rapor/Index.vue'
import CekPenilaianIndex from './pages/wali-kelas/cek-penilaian/Index.vue'
import CapaianPembelajaranWaliKelasIndex from './pages/wali-kelas/capaian-pembelajaran/Index.vue'
import NilaiSumatifWaliKelasIndex from './pages/wali-kelas/nilai-sumatif/Index.vue'
import NilaiEkstrakurikulerWaliKelasIndex from './pages/wali-kelas/nilai-ekstrakurikuler/Index.vue'
import NilaiPklWaliKelasIndex from './pages/wali-kelas/nilai-pkl/Index.vue'
import KetidakhadiranWaliKelasIndex from './pages/wali-kelas/ketidakhadiran/Index.vue'
import CekPenilaianStsIndex from './pages/wali-kelas/cek-penilaian/sts/Index.vue'
import CekPenilaianSasIndex from './pages/wali-kelas/cek-penilaian/sas/Index.vue'
import CekPenilaianP5Index from './pages/wali-kelas/cek-penilaian/p5/Index.vue'
import CetakRaporBelajarIndex from './pages/wali-kelas/cetak-rapor/belajar/Index.vue'
import CetakRaporP5WaliKelasIndex from './pages/wali-kelas/cetak-rapor/p5/Index.vue'
import CetakRaporLeggerWaliKelasIndex from './pages/wali-kelas/cetak-rapor/legger/Index.vue'
import CetakRaporProfilSiswaIndex from './pages/wali-kelas/cetak-rapor/profil-siswa/Index.vue'

// Kepala Sekolah pages
import KepalaSekolahDashboard from './pages/kepala-sekolah/Dashboard.vue'
import RaporApprovalIndex from './pages/kepala-sekolah/rapor-approval/Index.vue'
import RaporApprovalBelajar from './pages/kepala-sekolah/rapor-approval/Belajar.vue'
import RaporApprovalP5 from './pages/kepala-sekolah/rapor-approval/P5.vue'
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
    meta: { requiresAuth: true },
    beforeEnter: async (to, from, next) => {
      const authStore = useAuthStore()
      // Load user if not loaded
      if (authStore.token && !authStore.user) {
        try {
          await authStore.getUser()
        } catch (error) {
          console.error('Failed to load user:', error)
        }
      }
      // Redirect to role-specific dashboard
      if (authStore.user) {
        next(authStore.getDefaultRoute())
      } else {
        next('/login')
      }
    }
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
        path: 'wali-kelas',
        name: 'admin.wali-kelas.index',
        component: WaliKelasIndex
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
        path: 'ekstrakurikuler',
        name: 'admin.ekstrakurikuler.index',
        component: EkstrakurikulerIndex
      },
      {
        path: 'pkl',
        name: 'admin.pkl.index',
        component: PklIndex
      },
      {
        path: 'ukk-events',
        name: 'admin.ukk-events.index',
        component: UkkEventsIndex
      },
      {
        path: 'p5',
        name: 'admin.p5.index',
        component: AdminP5Index
      },
      {
        path: 'p5/kelompok',
        name: 'admin.p5.kelompok.index',
        component: AdminP5KelompokIndex
      },
        {
          path: 'cetak-rapor/hasil-belajar',
          name: 'admin.cetak-rapor.hasil-belajar',
          component: CetakRaporHasilBelajarIndex
        },
        {
          path: 'cetak-rapor/p5',
          name: 'admin.cetak-rapor.p5',
          component: CetakRaporP5Index
        },
        {
          path: 'cetak-rapor/legger',
          name: 'admin.cetak-rapor.legger',
          component: CetakRaporLeggerIndex
      },
      {
        path: 'tahun-ajaran',
        name: 'admin.tahun-ajaran.index',
        component: TahunAjaranIndex
      },
      {
        path: 'users',
        name: 'admin.users.index',
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
      },
      {
        path: 'nilai-ekstrakurikuler',
        name: 'guru.nilai-ekstrakurikuler.index',
        component: NilaiEkstrakurikulerIndex
      },
      {
        path: 'nilai-ukk',
        name: 'guru.nilai-ukk.index',
        component: GuruNilaiUkkIndex
      },
      {
        path: 'p5',
        name: 'guru.p5.index',
        component: P5Index
      }
    ]
  },

  // Wali Kelas routes (guru with active wali kelas assignment only)
  {
    path: '/wali-kelas',
    meta: { requiresAuth: true, waliKelasOnly: true },
    children: [
      {
        path: '',
        name: 'wali-kelas.dashboard',
        component: WaliKelasDashboard
      },
      {
        path: 'capaian-pembelajaran',
        name: 'wali-kelas.capaian-pembelajaran.index',
        component: CapaianPembelajaranWaliKelasIndex
      },
      {
        path: 'nilai-sumatif',
        name: 'wali-kelas.nilai-sumatif.index',
        component: NilaiSumatifWaliKelasIndex
      },
      {
        path: 'nilai-ekstrakurikuler',
        name: 'wali-kelas.nilai-ekstrakurikuler.index',
        component: NilaiEkstrakurikulerWaliKelasIndex
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
        path: 'ketidakhadiran',
        name: 'wali-kelas.ketidakhadiran.index',
        component: KetidakhadiranWaliKelasIndex
      },
      {
        path: 'nilai-pkl',
        name: 'wali-kelas.nilai-pkl.index',
        component: NilaiPklWaliKelasIndex
      },
      {
        path: 'rapor',
        name: 'wali-kelas.rapor.index',
        component: RaporIndex
      },
      {
        path: 'cek-penilaian',
        name: 'wali-kelas.cek-penilaian.index',
        component: CekPenilaianIndex
      },
      {
        path: 'cek-penilaian/sts',
        name: 'wali-kelas.cek-penilaian.sts',
        component: CekPenilaianStsIndex
      },
      {
        path: 'cek-penilaian/sas',
        name: 'wali-kelas.cek-penilaian.sas',
        component: CekPenilaianSasIndex
      },
      {
        path: 'cek-penilaian/p5',
        name: 'wali-kelas.cek-penilaian.p5',
        component: CekPenilaianP5Index
      },
      {
        path: 'cetak-rapor/belajar',
        name: 'wali-kelas.cetak-rapor.belajar',
        component: CetakRaporBelajarIndex
      },
      {
        path: 'cetak-rapor/p5',
        name: 'wali-kelas.cetak-rapor.p5',
        component: CetakRaporP5WaliKelasIndex
      },
      {
        path: 'cetak-rapor/legger',
        name: 'wali-kelas.cetak-rapor.legger',
        component: CetakRaporLeggerWaliKelasIndex
      },
      {
        path: 'cetak-rapor/profil-siswa',
        name: 'wali-kelas.cetak-rapor.profil-siswa',
        component: CetakRaporProfilSiswaIndex
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
        path: 'rapor-approval/belajar',
        name: 'kepala-sekolah.rapor-approval.belajar',
        component: RaporApprovalBelajar
      },
      {
        path: 'rapor-approval/p5',
        name: 'kepala-sekolah.rapor-approval.p5',
        component: RaporApprovalP5
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
        path: 'rapor/belajar',
        name: 'siswa.rapor.belajar',
        component: RaporSiswaIndex
      },
      {
        path: 'rapor/p5',
        name: 'siswa.rapor.p5',
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

// Configure axios response interceptor after router is created
let routerInstance = router
let errorRetryCount = new Map() // Track retry count per URL to prevent infinite loops
let lastErrorTime = new Map() // Track last error time per URL

axios.interceptors.response.use(
  (response) => {
    // Reset retry count on successful response
    if (response.config?.url) {
      errorRetryCount.delete(response.config.url)
      lastErrorTime.delete(response.config.url)
    }
    return response
  },
  (error) => {
    const url = error.config?.url || ''
    const status = error.response?.status
    const now = Date.now()
    
    // Prevent infinite loop for 500 errors
    if (status === 500) {
      const retryCount = errorRetryCount.get(url) || 0
      const lastTime = lastErrorTime.get(url) || 0
      
      // If same error within 2 seconds, increment retry count
      if (now - lastTime < 2000) {
        if (retryCount >= 2) {
          // Already retried multiple times, don't retry again
          errorRetryCount.delete(url)
          lastErrorTime.delete(url)
          console.error('Server error (500) - preventing infinite loop:', url)
          // Don't reject here, let the error propagate but prevent further retries
          return Promise.reject(error)
        }
        errorRetryCount.set(url, retryCount + 1)
      } else {
        // Reset if error happened more than 2 seconds ago
        errorRetryCount.set(url, 1)
      }
      lastErrorTime.set(url, now)
    }
    
    if (status === 401) {
      const authStore = useAuthStore()
      
      // Don't logout if we're currently loading user (e.g., on refresh)
      // This prevents logout when user is being loaded from token
      if (authStore.userLoading) {
        return Promise.reject(error)
      }
      
      // Only logout if user was previously authenticated and we got 401
      // This means token is invalid or expired
      if (authStore.isAuthenticated && authStore.user) {
        authStore.logout()
        // Use router.push with error handling to avoid timing issues
        if (routerInstance) {
          routerInstance.push('/login').catch(() => {
            // Fallback to window.location if router push fails
            window.location.href = '/login'
          })
        } else {
          window.location.href = '/login'
        }
      }
    }
    
    return Promise.reject(error)
  }
)

// Route guards
router.beforeEach(async (to, from, next) => {
  try {
  const authStore = useAuthStore()
  
    // Validate route component exists
    if (to.matched.length === 0) {
      console.warn('Route not found:', to.path)
      // If authenticated, redirect to role dashboard, otherwise to login
      if (authStore.isAuthenticated && authStore.user) {
        next(authStore.getDefaultRoute())
      } else {
        next('/login')
      }
      return
    }
    
    // If route requires auth but user is not authenticated
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      // If we have a token, try to load user first
      if (authStore.token && !authStore.user) {
        try {
          await authStore.getUser()
          // After loading user, check again
          if (authStore.isAuthenticated && authStore.user) {
            // User loaded successfully, continue navigation
            next()
            return
          }
        } catch (error) {
          // Failed to load user, redirect to login
          console.error('Failed to load user on auth check:', error)
          next('/login')
          return
        }
      }
      // No token or failed to load user, redirect to login
    next('/login')
    return
  }
  
    // If user is authenticated but trying to access guest route
  if (to.meta.guest && authStore.isAuthenticated) {
      // Redirect to role-specific dashboard
      if (authStore.user) {
        next(authStore.getDefaultRoute())
      } else {
    next('/')
      }
      return
    }
    
    // If route requires specific role, ensure user is loaded first
    if (to.meta.role) {
      // Wait if user is currently being loaded (e.g., from App.vue onMounted)
      if (authStore.userLoading) {
        while (authStore.userLoading) {
          await new Promise(resolve => setTimeout(resolve, 50))
        }
      }
      
      // If user is not loaded yet but has token, load user first
      if (authStore.isAuthenticated && !authStore.user) {
        try {
          await authStore.getUser()
        } catch (error) {
          console.error('Failed to load user:', error)
          // Only redirect to login if we're not already on a route that requires auth
          // This prevents redirect during refresh
          if (!to.path.startsWith('/login')) {
            next('/login')
          } else {
            next()
          }
          return
        }
      }
      
      // Check if user role matches required role
      if (!authStore.user) {
        // User not loaded, redirect to login
        next('/login')
        return
      }
      
      // IMPORTANT: Check role match FIRST - if it matches, always allow navigation
      // This ensures refresh works correctly - user stays on current route
      if (authStore.user.role === to.meta.role) {
        // Role matches perfectly, allow navigation to the requested path
        // This is the key fix - don't redirect if role matches!
        next()
    return
  }
  
      const defaultRoute = authStore.getDefaultRoute()
      if (to.path.startsWith(defaultRoute)) {
        next()
      } else {
        next(defaultRoute)
      }
      return
    }

    // Wali-kelas routes: guru with active wali kelas assignment only
    if (to.meta.waliKelasOnly) {
      if (authStore.userLoading) {
        while (authStore.userLoading) {
          await new Promise(resolve => setTimeout(resolve, 50))
        }
      }
      if (authStore.isAuthenticated && !authStore.user) {
        try {
          await authStore.getUser()
        } catch (e) {
          console.error('Get user error:', e)
          if (!to.path.startsWith('/login')) next('/login')
          else next()
          return
        }
      }
      if (!authStore.user) {
        next('/login')
        return
      }
      if (authStore.user.role === 'guru' && authStore.isWaliKelas) {
        next()
        return
      }
      next(authStore.getDefaultRoute())
      return
    }

    next()
  } catch (error) {
    console.error('Router guard error:', error)
    const authStore = useAuthStore()
    // On error, redirect to appropriate page based on auth state
    if (authStore.isAuthenticated && authStore.user) {
      next(authStore.getDefaultRoute())
    } else {
      next('/login')
    }
  }
})

// Handle router errors
router.onError((error) => {
  console.error('Router error:', error)
  // Prevent infinite loops
  if (error.message && !error.message.includes('NavigationDuplicated')) {
    console.error('Navigation error:', error)
  }
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
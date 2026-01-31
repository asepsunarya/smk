import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    profile: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    userLoading: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isGuru: (state) => state.user?.role === 'guru',
    isWaliKelas: (state) => {
      if (state.user?.role !== 'guru') return false
      const w = state.profile?.waliKelasAktif ?? state.profile?.wali_kelas_aktif
      if (!w) return false
      const n = Array.isArray(w) ? w.length : (typeof w === 'object' && w !== null ? Object.keys(w).length : 0)
      return n > 0
    },
    isKepalaSekolah: (state) => state.user?.role === 'kepala_sekolah',
    isSiswa: (state) => state.user?.role === 'siswa',
    isKepalaJurusan: (state) => !!(state.profile?.is_kepala_jurusan ?? state.profile?.isKepalaJurusan)
  },

  actions: {
    async login(credentials) {
      this.loading = true
      try {
        const response = await axios.post('/login', credentials)
        const { user, profile, token, role } = response.data

        this.user = user
        this.profile = profile
        this.token = token
        
        localStorage.setItem('auth_token', token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

        return { user, profile, role }
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      try {
        const response = await axios.post('/register', userData)
        const { user, token } = response.data

        this.user = user
        this.token = token
        
        localStorage.setItem('auth_token', token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

        return user
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post('/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.profile = null
        this.token = null
        this.userLoading = false
        
        localStorage.removeItem('auth_token')
        delete axios.defaults.headers.common['Authorization']
      }
    },

    async getUser() {
      if (!this.token) return null
      
      // Prevent multiple simultaneous requests
      if (this.userLoading) {
        // Wait for existing request to complete
        while (this.userLoading) {
          await new Promise(resolve => setTimeout(resolve, 50))
        }
        return this.user
      }

      this.userLoading = true
      try {
        const response = await axios.get('/user')
        const { user, profile } = response.data
        
        this.user = user
        this.profile = profile
        
        return user
      } catch (error) {
        console.error('Get user error:', error)
        // Only logout if we got 401 and user was previously loaded
        // This prevents logout during initial load on refresh
        if (error.response?.status === 401 && this.user) {
          this.logout()
        } else if (error.response?.status === 401) {
          // Token is invalid, clear it but don't trigger full logout
          this.token = null
          localStorage.removeItem('auth_token')
          delete axios.defaults.headers.common['Authorization']
        }
        throw error
      } finally {
        this.userLoading = false
      }
    },

    async updatePassword(passwords) {
      try {
        await axios.put('/update-password', passwords)
      } catch (error) {
        throw error
      }
    },

    getDefaultRoute() {
      switch (this.user?.role) {
        case 'admin':
          return '/admin'
        case 'guru':
          return '/guru'
        case 'kepala_sekolah':
          return '/kepala-sekolah'
        case 'siswa':
          return '/siswa'
        default:
          return '/'
      }
    }
  }
})
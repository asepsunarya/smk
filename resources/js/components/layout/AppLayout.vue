<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col" 
         :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
      
      <!-- Logo -->
      <div class="flex items-center justify-center h-16 px-4 bg-blue-600 flex-shrink-0">
        <div class="flex items-center">
          <img :src="logoImage" alt="Logo SMK Progresia" class="w-10 h-10 object-contain mr-3 bg-white rounded-lg p-1">
          <div class="text-white">
            <div class="text-lg font-bold">SI Rapor Kurmer</div>
            <div class="text-xs opacity-75">Pengolahan Nilai Rapor</div>
          </div>
        </div>
      </div>

      <!-- Navigation (Scrollable) -->
      <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
        <!-- Dashboard -->
        <router-link 
          :to="getDashboardRoute()" 
          class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors group"
          :class="{ 'bg-blue-50 text-blue-600 border-r-2 border-blue-600': $route.path === getDashboardRoute() }"
        >
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
          </svg>
          Dashboard
        </router-link>

        <!-- Role-specific navigation -->
        <template v-for="(menuGroup, menuIdx) in currentUserMenus" :key="'menu-' + menuIdx">
          <div class="pt-4">
            <!-- Section Menu -->
            <template v-if="menuGroup.type === 'section'">
              <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                {{ menuGroup.title }}
              </h3>
              <div class="space-y-1">
                <SidebarLink 
                  v-for="item in menuGroup.items" 
                  :key="item.to"
                  :to="item.to" 
                  :icon="item.icon" 
                  :label="item.label" 
                />
              </div>
            </template>

            <!-- Dropdown Menu -->
            <template v-if="menuGroup.type === 'dropdown'">
              <SidebarDropdown 
                :label="menuGroup.title"
                :icon="menuGroup.icon"
                :routes="menuGroup.routes"
              >
                <SidebarLink 
                  v-for="item in menuGroup.items" 
                  :key="item.to"
                  :to="item.to" 
                  :icon="item.icon" 
                  :label="item.label" 
                />
              </SidebarDropdown>
            </template>
          </div>
        </template>
      </nav>

      <!-- User Info -->
      <div class="flex-shrink-0 p-4 border-t border-gray-200 bg-white">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-medium">
            {{ userInitials }}
          </div>
          <div class="ml-3 flex-1">
            <div class="text-sm font-medium text-gray-900 truncate">{{ authStore.user?.name }}</div>
            <div class="text-xs text-gray-500">{{ roleText }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="lg:pl-64">
      <!-- Top Header -->
      <div class="sticky top-0 z-40 lg:mx-auto lg:max-w-full">
        <div class="flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200 lg:px-6">
          <!-- Mobile menu button -->
          <button
            @click="toggleSidebar"
            type="button"
            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Page title -->
          <div class="flex-1 lg:flex-none">
            <h1 class="text-xl font-semibold text-gray-900">{{ pageTitle }}</h1>
          </div>

          <!-- Right side actions -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button type="button" class="p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-full">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.97 4.97a.235.235 0 0 0-.02 0 5.013 5.013 0 0 0-7.125 7.125c0 .007.007.014.01.021a6.107 6.107 0 0 1 7.135-7.146Z"></path>
              </svg>
            </button>

            <!-- Profile dropdown -->
            <div class="relative">
              <button
                @click="showProfileMenu = !showProfileMenu"
                type="button"
                class="flex items-center p-2 text-sm bg-white rounded-full hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                  {{ userInitials }}
                </div>
              </button>

              <!-- Dropdown menu -->
              <div v-show="showProfileMenu" 
                   class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
                <hr class="my-1">
                <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Keluar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Page Content -->
      <main class="flex-1">
        <slot />
      </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div v-if="sidebarOpen" 
         class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
         @click="closeSidebar">
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useToast } from 'vue-toastification'
import SidebarLink from './SidebarLink.vue'
import SidebarDropdown from './SidebarDropdown.vue'
import { menuConfig } from '../../config/menus.js'

// Logo image path
const logoImage = '/images/logo.jpeg'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()

const sidebarOpen = ref(false)
const showProfileMenu = ref(false)

const userInitials = computed(() => {
  if (!authStore.user?.name) return 'U'
  return authStore.user.name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const roleText = computed(() => {
  const roles = {
    admin: 'Administrator',
    guru: 'Guru',
    kepala_sekolah: 'Kepala Sekolah',
    siswa: 'Siswa'
  }
  return roles[authStore.user?.role] || ''
})

const currentUserMenus = computed(() => {
  const role = authStore.user?.role
  let base = menuConfig[role] || []
  // For guru: hide "Nilai UKK" unless user is kepala jurusan
  if (role === 'guru') {
    base = base.map((section) => {
      if (section.items && !authStore.isKepalaJurusan) {
        return { ...section, items: section.items.filter((it) => !it.kepalaJurusanOnly) }
      }
      return section
    })
  }
  if (role === 'guru' && authStore.isWaliKelas) {
    const wali = (menuConfig.wali_kelas || []).filter((g) => g.title !== 'Pembelajaran')
    return [...base, ...wali]
  }
  return base
})

const pageTitle = computed(() => {
  const titles = {
    '/admin': 'Dashboard Admin',
    '/admin/siswa': 'Data Siswa',
    '/admin/guru': 'Data Guru',
    '/admin/kelas': 'Data Kelas',
    '/guru': 'Dashboard Guru',
    '/guru/nilai': 'Input Nilai',
    '/wali-kelas': 'Dashboard Wali Kelas',
    '/wali-kelas/rapor': 'Rapor Kelas',
    '/kepala-sekolah': 'Dashboard Kepala Sekolah',
    '/siswa': 'Dashboard Siswa',
    '/siswa/nilai': 'Nilai Saya',
  }
  return titles[route.path] || 'Dashboard'
})

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const getDashboardRoute = () => {
  return authStore.getDefaultRoute()
}

const logout = async () => {
  try {
    await authStore.logout()
    toast.success('Berhasil logout')
    router.push('/login')
  } catch (error) {
    toast.error('Gagal logout')
  }
}

const handleClickOutside = (event) => {
  if (showProfileMenu.value && !event.target.closest('.relative')) {
    showProfileMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
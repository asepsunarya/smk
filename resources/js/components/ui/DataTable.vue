<template>
  <div class="bg-white shadow rounded-lg">
    <!-- Header -->
    <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
          <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
          <p v-if="description" class="mt-1 text-sm text-gray-500">{{ description }}</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-4 flex space-x-3">
          <slot name="actions"></slot>
        </div>
      </div>
      
      <!-- Search and Filters -->
      <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div v-if="searchable" class="relative">
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Cari data..."
            class="form-input w-full"
          />
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>
        <slot name="filters"></slot>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="p-8 text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!data.length" class="p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">{{ emptyMessage || 'Tidak ada data' }}</h3>
      <p class="mt-1 text-sm text-gray-500">{{ emptyDescription || 'Mulai dengan menambahkan data baru.' }}</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th v-for="column in columns" :key="column.key" @click="sort(column.key)" 
                  :class="{ 'cursor-pointer hover:bg-gray-100': column.sortable }">
                <div class="flex items-center">
                  {{ column.label }}
                  <svg v-if="column.sortable && sortKey === column.key" 
                       class="ml-1 h-4 w-4 text-gray-400"
                       :class="{ 'transform rotate-180': sortOrder === 'desc' }"
                       fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                  </svg>
                </div>
              </th>
              <th v-if="hasActions" class="relative px-6 py-3 text-center w-32">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in paginatedData" :key="getItemKey(item, index)" 
                class="hover:bg-gray-50">
              <td v-for="column in columns" :key="column.key">
                <slot :name="'cell-' + column.key" :item="item" :value="getNestedValue(item, column.key)">
                  {{ formatValue(getNestedValue(item, column.key), column) }}
                </slot>
              </td>
              <td v-if="hasActions" class="relative whitespace-nowrap py-4 px-6 text-center text-sm font-medium w-32">
                <slot name="row-actions" :item="item" :index="index"></slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button @click="previousPage" :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
              Sebelumnya
            </button>
            <button @click="nextPage" :disabled="currentPage === totalPages"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
              Selanjutnya
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Menampilkan {{ startIndex + 1 }} sampai {{ endIndex }} dari {{ filteredData.length }} data
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button @click="previousPage" :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                </button>
                
                <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                        :class="[
                          'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                          page === currentPage 
                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                        ]">
                  {{ page }}
                </button>
                
                <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, useSlots } from 'vue'

const slots = useSlots()

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: true
  },
  itemsPerPage: {
    type: Number,
    default: 10
  },
  emptyMessage: {
    type: String,
    default: ''
  },
  emptyDescription: {
    type: String,
    default: ''
  },
  itemKey: {
    type: String,
    default: 'id'
  }
})

const emit = defineEmits(['search', 'sort'])

const searchTerm = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')
const currentPage = ref(1)

const hasActions = computed(() => props.columns.some(col => col.key === 'actions') || !!slots['row-actions'])

const filteredData = computed(() => {
  let result = [...props.data]
  
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    result = result.filter(item => {
      return props.columns.some(column => {
        const value = getNestedValue(item, column.key)
        return String(value).toLowerCase().includes(term)
      })
    })
  }
  
  if (sortKey.value) {
    result.sort((a, b) => {
      const aValue = getNestedValue(a, sortKey.value)
      const bValue = getNestedValue(b, sortKey.value)
      
      if (aValue < bValue) return sortOrder.value === 'asc' ? -1 : 1
      if (aValue > bValue) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  }
  
  return result
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / props.itemsPerPage))

const startIndex = computed(() => (currentPage.value - 1) * props.itemsPerPage)
const endIndex = computed(() => Math.min(startIndex.value + props.itemsPerPage, filteredData.value.length))

const paginatedData = computed(() => {
  return filteredData.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((o, p) => o?.[p], obj)
}

const getItemKey = (item, index) => {
  return getNestedValue(item, props.itemKey) || index
}

const formatValue = (value, column) => {
  if (value === null || value === undefined) return '-'
  
  if (column.type === 'date') {
    return new Date(value).toLocaleDateString('id-ID')
  }
  
  if (column.type === 'currency') {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR'
    }).format(value)
  }
  
  if (column.type === 'number') {
    return new Intl.NumberFormat('id-ID').format(value)
  }
  
  return value
}

const sort = (key) => {
  const column = props.columns.find(col => col.key === key)
  if (!column?.sortable) return
  
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
  
  emit('sort', { key, order: sortOrder.value })
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const goToPage = (page) => {
  currentPage.value = page
}

// Reset to first page when search term changes
watch(searchTerm, () => {
  currentPage.value = 1
  emit('search', searchTerm.value)
})

// Reset to first page when data changes
watch(() => props.data, () => {
  currentPage.value = 1
})
</script>
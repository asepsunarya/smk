<template>
  <div class="space-y-1">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative z-[60]" ref="dropdownRef">
      <!-- Selected Items Display -->
      <div
        :id="inputId"
        :class="containerClasses"
        @click="toggleDropdown"
        @keydown.enter.prevent="toggleDropdown"
        @keydown.space.prevent="toggleDropdown"
        :tabindex="disabled ? -1 : 0"
        role="combobox"
        :aria-expanded="isOpen"
        :aria-haspopup="true"
      >
        <div class="flex flex-wrap gap-2 items-center min-h-[42px] py-2 px-3 max-h-32 overflow-y-auto">
          <!-- Selected Tags -->
          <template v-if="selectedItems.length > 0">
            <span
              v-for="item in selectedItems"
              :key="getOptionValue(item)"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200 whitespace-nowrap"
            >
              <span class="max-w-[200px] truncate">{{ getOptionLabel(item) }}</span>
              <button
                type="button"
                @click.stop="removeItem(getOptionValue(item))"
                class="text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded flex-shrink-0"
                :disabled="disabled"
                title="Hapus"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </span>
          </template>
          
          <!-- Placeholder -->
          <span v-else class="text-gray-400 text-sm">
            {{ placeholder || 'Pilih opsi...' }}
          </span>
        </div>
        
        <!-- Dropdown Icon -->
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
          <svg
            :class="['w-5 h-5 text-gray-400 transition-transform duration-200', { 'rotate-180': isOpen }]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </div>
      </div>
      
      <!-- Dropdown Menu -->
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="absolute z-[100] mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 overflow-hidden"
          :style="{ maxHeight: `${typeof maxHeight === 'number' ? maxHeight : parseInt(maxHeight)}px` }"
        >
          <!-- Search Input -->
          <div v-if="searchable" class="p-2 border-b border-gray-200 sticky top-0 bg-white z-10">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari..."
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2"
              @click.stop
              @keydown.esc="closeDropdown"
            />
          </div>
          
          <!-- Options List -->
          <div class="overflow-y-auto" :style="{ maxHeight: `${(typeof maxHeight === 'number' ? maxHeight : parseInt(maxHeight)) - 80}px` }">
            <div v-if="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
              {{ searchQuery ? 'Tidak ada hasil' : 'Tidak ada opsi tersedia' }}
            </div>
            
            <label
              v-for="option in filteredOptions"
              :key="getOptionValue(option)"
              class="flex items-center px-4 py-2.5 hover:bg-gray-50 cursor-pointer transition-colors min-h-[44px]"
              :class="{ 'bg-blue-50': isSelected(getOptionValue(option)) }"
            >
              <input
                type="checkbox"
                :checked="isSelected(getOptionValue(option))"
                @change="toggleOption(getOptionValue(option))"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded flex-shrink-0"
                @click.stop
              />
              <span class="ml-3 text-sm text-gray-900 flex-1 break-words">
                {{ getOptionLabel(option) }}
              </span>
              <svg
                v-if="isSelected(getOptionValue(option))"
                class="w-5 h-5 text-blue-600 flex-shrink-0 ml-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </label>
          </div>
          
          <!-- Select All / Clear All -->
          <div v-if="filteredOptions.length > 0" class="border-t border-gray-200 px-4 py-2 flex gap-2">
            <button
              type="button"
              @click.stop="selectAll"
              class="text-xs text-blue-600 hover:text-blue-800 font-medium"
            >
              Pilih Semua
            </button>
            <span class="text-gray-300">|</span>
            <button
              type="button"
              @click.stop="clearAll"
              class="text-xs text-red-600 hover:text-red-800 font-medium"
            >
              Hapus Semua
            </button>
            <span class="ml-auto text-xs text-gray-500">
              {{ selectedItems.length }} dipilih
            </span>
          </div>
        </div>
      </Transition>
    </div>
    
    <!-- Help text -->
    <p v-if="helpText" class="text-sm text-gray-500">{{ helpText }}</p>
    
    <!-- Error message -->
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Pilih opsi...'
  },
  helpText: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  options: {
    type: Array,
    default: () => []
  },
  optionValue: {
    type: String,
    default: 'value'
  },
  optionLabel: {
    type: String,
    default: 'label'
  },
  searchable: {
    type: Boolean,
    default: true
  },
  maxHeight: {
    type: [String, Number],
    default: 120 // 30rem = 480px
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const inputId = ref(`multiselect-${Math.random().toString(36).substr(2, 9)}`)
const isOpen = ref(false)
const searchQuery = ref('')
const dropdownRef = ref(null)

const containerClasses = computed(() => {
  const baseClasses = [
    'block w-full rounded-md border shadow-sm',
    'focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
    'bg-white cursor-pointer',
    'transition-colors duration-150'
  ]
  
  if (props.error) {
    baseClasses.push('border-red-300 focus:border-red-500 focus:ring-red-500')
  } else {
    baseClasses.push('border-gray-300')
  }
  
  if (props.disabled) {
    baseClasses.push('bg-gray-50 text-gray-500 cursor-not-allowed')
  }
  
  if (isOpen.value) {
    baseClasses.push('ring-2 ring-blue-500 border-blue-500')
  }
  
  return baseClasses.join(' ')
})

const selectedItems = computed(() => {
  if (!Array.isArray(props.modelValue)) return []
  
  return props.options.filter(option => {
    const value = getOptionValue(option)
    // Normalize for comparison
    const normalizedValue = typeof value === 'number' ? value : (typeof value === 'string' && !isNaN(Number(value)) ? Number(value) : value)
    
    return props.modelValue.some(v => {
      const normalizedV = typeof v === 'number' ? v : (typeof v === 'string' && !isNaN(Number(v)) ? Number(v) : v)
      return normalizedV === normalizedValue
    })
  })
})

const filteredOptions = computed(() => {
  if (!searchQuery.value) {
    return props.options
  }
  
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option => {
    const label = getOptionLabel(option).toLowerCase()
    return label.includes(query)
  })
})

const getOptionValue = (option) => {
  if (typeof option === 'object' && option !== null) {
    return option[props.optionValue]
  }
  return option
}

const getOptionLabel = (option) => {
  if (typeof option === 'object' && option !== null) {
    return option[props.optionLabel]
  }
  return option
}

const isSelected = (value) => {
  if (!Array.isArray(props.modelValue)) return false
  
  // Normalize for comparison
  const normalizedValue = typeof value === 'number' ? value : (typeof value === 'string' && !isNaN(Number(value)) ? Number(value) : value)
  
  return props.modelValue.some(v => {
    const normalizedV = typeof v === 'number' ? v : (typeof v === 'string' && !isNaN(Number(v)) ? Number(v) : v)
    return normalizedV === normalizedValue
  })
}

const toggleOption = (value) => {
  if (props.disabled) return
  
  const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : []
  // Normalize values for comparison
  const normalizedValue = typeof value === 'number' ? value : (typeof value === 'string' && !isNaN(Number(value)) ? Number(value) : value)
  
  const index = currentValue.findIndex(v => {
    const normalizedV = typeof v === 'number' ? v : (typeof v === 'string' && !isNaN(Number(v)) ? Number(v) : v)
    return normalizedV === normalizedValue
  })
  
  if (index > -1) {
    currentValue.splice(index, 1)
  } else {
    // Keep original value type
    currentValue.push(value)
  }
  
  emit('update:modelValue', currentValue)
}

const removeItem = (value) => {
  if (props.disabled) return
  toggleOption(value)
}

const selectAll = () => {
  if (props.disabled) return
  
  const allValues = filteredOptions.value.map(option => getOptionValue(option))
  const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : []
  
  // Add only values that are not already selected
  allValues.forEach(value => {
    const normalizedValue = typeof value === 'number' ? value : (typeof value === 'string' && !isNaN(Number(value)) ? Number(value) : value)
    const isAlreadySelected = currentValue.some(v => {
      const normalizedV = typeof v === 'number' ? v : (typeof v === 'string' && !isNaN(Number(v)) ? Number(v) : v)
      return normalizedV === normalizedValue
    })
    
    if (!isAlreadySelected) {
      currentValue.push(value)
    }
  })
  
  emit('update:modelValue', currentValue)
}

const clearAll = () => {
  if (props.disabled) return
  
  const filteredValues = filteredOptions.value.map(option => {
    const value = getOptionValue(option)
    return typeof value === 'number' ? value : (typeof value === 'string' && !isNaN(Number(value)) ? Number(value) : value)
  })
  
  const currentValue = Array.isArray(props.modelValue) 
    ? props.modelValue.filter(v => {
        const normalizedV = typeof v === 'number' ? v : (typeof v === 'string' && !isNaN(Number(v)) ? Number(v) : v)
        return !filteredValues.some(fv => fv === normalizedV)
      })
    : []
  
  emit('update:modelValue', currentValue)
}

const toggleDropdown = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  
  if (isOpen.value) {
    emit('focus')
    searchQuery.value = ''
  } else {
    emit('blur')
  }
}

const closeDropdown = () => {
  isOpen.value = false
  emit('blur')
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeDropdown()
  }
}

watch(isOpen, (newValue) => {
  if (newValue) {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('keydown', handleEscape)
  } else {
    document.removeEventListener('click', handleClickOutside)
    document.removeEventListener('keydown', handleEscape)
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscape)
})
</script>


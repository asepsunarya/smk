<template>
  <Teleport to="body">
    <Transition
      enter-active-class="duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:p-0">
          <!-- Backdrop -->
          <Transition
            enter-active-class="duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div
              v-if="show"
              class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
              @click="closeModal"
            ></div>
          </Transition>

          <!-- Modal panel -->
          <Transition
            enter-active-class="duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              class="relative transform overflow-visible rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full"
              :class="[sizeClasses, props.size === 'full' ? 'flex flex-col' : '']"
            >
              <!-- Header -->
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 flex-shrink-0" :class="props.size === 'full' ? 'border-b' : ''">
                <div class="sm:flex sm:items-start" :class="props.size === 'full' ? 'flex flex-col' : ''">
                  <!-- Icon -->
                  <div v-if="icon" class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                       :class="iconClasses">
                    <!-- Success Icon -->
                    <svg v-if="icon === 'success'" class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <!-- Warning Icon -->
                    <svg v-else-if="icon === 'warning'" class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <!-- Error Icon -->
                    <svg v-else-if="icon === 'error'" class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <!-- Info Icon -->
                    <svg v-else-if="icon === 'info'" class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  
                  <!-- Content -->
                  <div class="mt-3 text-center sm:mt-0 sm:text-left flex-1 flex flex-col min-w-0" :class="{ 'sm:ml-4': icon }">
                    <h3 v-if="title" class="text-lg font-medium leading-6 text-gray-900 flex-shrink-0">
                      {{ title }}
                    </h3>
                    <div class="mt-2 flex-1 min-h-0 overflow-visible" :class="props.size === 'full' ? 'overflow-y-auto' : ''">
                      <slot></slot>
                    </div>
                  </div>

                  <!-- Close button -->
                  <div v-if="closable" class="absolute top-0 right-0 pt-4 pr-4">
                    <button
                      type="button"
                      class="rounded-md bg-white text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      @click="closeModal"
                    >
                      <span class="sr-only">Tutup</span>
                      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div v-if="$slots.footer" class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 flex-shrink-0 border-t">
                <slot name="footer"></slot>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', 'full'].includes(value)
  },
  icon: {
    type: String,
    default: '',
    validator: (value) => ['', 'success', 'warning', 'error', 'info'].includes(value)
  },
  closable: {
    type: Boolean,
    default: true
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'update:show'])

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
    '5xl': 'sm:max-w-5xl',
    '6xl': 'sm:max-w-6xl',
    '7xl': 'sm:max-w-7xl',
    'full': 'sm:max-w-[98vw] sm:w-[98vw] sm:h-[95vh]'
  }
  return sizes[props.size]
})

const iconClasses = computed(() => {
  const classes = {
    success: 'bg-green-100',
    warning: 'bg-yellow-100',
    error: 'bg-red-100',
    info: 'bg-blue-100'
  }
  return classes[props.icon] || 'bg-gray-100'
})


const closeModal = () => {
  if (!props.persistent) {
    emit('close')
    emit('update:show', false)
  }
}

// Handle escape key
watch(() => props.show, (newValue) => {
  if (newValue) {
    const handleEscape = (e) => {
      if (e.key === 'Escape' && props.closable) {
        closeModal()
      }
    }
    document.addEventListener('keydown', handleEscape)
    
    // Cleanup when modal closes
    return () => {
      document.removeEventListener('keydown', handleEscape)
    }
  }
})
</script>
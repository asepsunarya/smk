<template>
  <Modal
    v-model:show="show"
    :title="title"
    :icon="type"
    size="sm"
    :closable="!loading"
    :persistent="loading"
  >
    <p class="text-sm text-gray-500">{{ message }}</p>
    
    <template #footer>
      <button
        type="button"
        :disabled="loading"
        class="btn btn-danger"
        @click="handleConfirm"
      >
        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ loading ? 'Memproses...' : confirmText }}
      </button>
      <button
        type="button"
        :disabled="loading"
        class="btn btn-secondary mr-3"
        @click="handleCancel"
      >
        {{ cancelText }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import Modal from './Modal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Konfirmasi'
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin melanjutkan?'
  },
  confirmText: {
    type: String,
    default: 'Ya, Lanjutkan'
  },
  cancelText: {
    type: String,
    default: 'Batal'
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['success', 'warning', 'error', 'info'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel', 'update:show'])

const show = ref(props.show)

const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  show.value = false
  emit('cancel')
  emit('update:show', false)
}

watch(() => props.show, (newValue) => {
  show.value = newValue
})

watch(show, (newValue) => {
  emit('update:show', newValue)
})
</script>
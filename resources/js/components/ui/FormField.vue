<template>
  <div class="space-y-1">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <!-- Text Input -->
      <input
        v-if="type === 'text' || type === 'email' || type === 'password' || type === 'number'"
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
      />
      
      <!-- Textarea -->
      <textarea
        v-else-if="type === 'textarea'"
        :id="inputId"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :rows="rows"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
      ></textarea>
      
      <!-- Select -->
      <select
        v-else-if="type === 'select'"
        :id="inputId"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :class="inputClasses"
        @change="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
      >
        <option v-if="placeholder" value="">{{ placeholder }}</option>
        <option
          v-for="option in options"
          :key="getOptionValue(option)"
          :value="getOptionValue(option)"
        >
          {{ getOptionLabel(option) }}
        </option>
      </select>
      
      <!-- Checkbox -->
      <div v-else-if="type === 'checkbox'" class="flex items-center">
        <input
          :id="inputId"
          type="checkbox"
          :checked="modelValue"
          :disabled="disabled"
          :required="required"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          @change="$emit('update:modelValue', $event.target.checked)"
          @blur="$emit('blur')"
          @focus="$emit('focus')"
        />
        <label v-if="checkboxLabel" :for="inputId" class="ml-2 block text-sm text-gray-900">
          {{ checkboxLabel }}
        </label>
      </div>
      
      <!-- Date -->
      <input
        v-else-if="type === 'date'"
        :id="inputId"
        type="date"
        :value="modelValue"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
      />
      
      <!-- File -->
      <input
        v-else-if="type === 'file'"
        :id="inputId"
        type="file"
        :disabled="disabled"
        :required="required"
        :accept="accept"
        :multiple="multiple"
        :class="inputClasses"
        @change="handleFileChange"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
      />
    </div>
    
    <!-- Help text -->
    <p v-if="helpText" class="text-sm text-gray-500">{{ helpText }}</p>
    
    <!-- Error message -->
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean, Array, File],
    default: ''
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'number', 'textarea', 
      'select', 'checkbox', 'date', 'file'
    ].includes(value)
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
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
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  rows: {
    type: Number,
    default: 3
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
  checkboxLabel: {
    type: String,
    default: ''
  },
  accept: {
    type: String,
    default: ''
  },
  multiple: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'file-change'])

const inputId = ref(`input-${Math.random().toString(36).substr(2, 9)}`)

const inputClasses = computed(() => {
  const baseClasses = [
    'block w-full rounded-md border-gray-300 shadow-sm',
    'focus:border-blue-500 focus:ring-blue-500 sm:text-sm'
  ]
  
  if (props.error) {
    baseClasses.push('border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500')
  }
  
  if (props.disabled) {
    baseClasses.push('bg-gray-50 text-gray-500 cursor-not-allowed')
  }
  
  if (props.readonly) {
    baseClasses.push('bg-gray-50')
  }
  
  return baseClasses.join(' ')
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

const handleFileChange = (event) => {
  const files = event.target.files
  if (props.multiple) {
    emit('update:modelValue', Array.from(files))
  } else {
    emit('update:modelValue', files[0] || null)
  }
  emit('file-change', files)
}
</script>
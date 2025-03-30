<!-- resources/js/components/shared/AlertMessage.vue -->

<template>
    <div
      v-show="visible"
      :class="[
        'mb-6 px-4 py-3 rounded-md border flex items-center justify-between shadow-sm',
        alertStyles[type].container
      ]"
      role="alert"
    >
      <!-- Alert icon based on type -->
      <div class="flex items-center">
        <span 
          class="material-icons text-lg mr-2" 
          :class="alertStyles[type].icon"
        >{{ alertIcon }}</span>
        <span v-html="message" class="text-sm"></span>
      </div>
      
      <!-- Close button -->
      <button
        @click="dismissAlert"
        class="text-gray-400 hover:text-gray-600 focus:outline-none"
        aria-label="Close"
      >
        <span class="material-icons text-lg">close</span>
      </button>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  
  // Props definition
  const props = defineProps({
    /**
     * Alert type (determines styling)
     * Accepted values: success, error, warning, info
     */
    type: {
      type: String,
      default: 'info',
      validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    
    /**
     * Alert message (can contain HTML)
     */
    message: {
      type: String,
      required: true
    },
    
    /**
     * Auto-dismiss timeout in milliseconds (0 to disable)
     */
    timeout: {
      type: Number,
      default: 5000
    }
  });
  
  // Emit events to parent
  const emit = defineEmits(['close']);
  
  // Local state
  const visible = ref(true);
  let dismissTimer = null;
  
  /**
   * Styling map for different alert types
   */
  const alertStyles = {
    success: {
      container: 'bg-green-50 border-green-200 text-green-800',
      icon: 'text-green-500'
    },
    error: {
      container: 'bg-red-50 border-red-200 text-red-800',
      icon: 'text-red-500'
    },
    warning: {
      container: 'bg-yellow-50 border-yellow-200 text-yellow-800',
      icon: 'text-yellow-500'
    },
    info: {
      container: 'bg-blue-50 border-blue-200 text-blue-800',
      icon: 'text-blue-500'
    }
  };
  
  /**
   * Determine appropriate icon based on alert type
   */
  const alertIcon = computed(() => {
    switch(props.type) {
      case 'success':
        return 'check_circle';
      case 'error':
        return 'error';
      case 'warning':
        return 'warning';
      case 'info':
      default:
        return 'info';
    }
  });
  
  /**
   * Dismiss the alert and emit close event
   */
  function dismissAlert() {
    visible.value = false;
    emit('close');
    
    // Clear any existing dismiss timer
    if (dismissTimer) {
      clearTimeout(dismissTimer);
      dismissTimer = null;
    }
  }
  
  /**
   * Setup auto-dismiss timer if timeout is set
   */
  function setupAutoDismiss() {
    // Clear any existing timer
    if (dismissTimer) {
      clearTimeout(dismissTimer);
      dismissTimer = null;
    }
    
    // Set up new timer if timeout is positive
    if (props.timeout > 0) {
      dismissTimer = setTimeout(() => {
        dismissAlert();
      }, props.timeout);
    }
  }
  
  // Watch for message changes to reset auto-dismiss
  watch(() => props.message, () => {
    if (props.message) {
      visible.value = true;
      setupAutoDismiss();
    }
  });
  
  // Initialize auto-dismiss timer
  onMounted(() => {
    setupAutoDismiss();
  });
  </script>
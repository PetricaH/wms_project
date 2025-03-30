<!-- resources/js/views/auth/ForgotPasswordView.vue -->

<template>
    <div>
      <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Reset your password</h2>
      
      <!-- Email sent confirmation -->
      <div v-if="emailSent" class="rounded-md bg-green-50 p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <span class="material-icons text-green-400">check_circle</span>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">Reset link sent</h3>
            <div class="mt-2 text-sm text-green-700">
              <p>
                We've sent a password reset link to {{ form.email }}. Please check your inbox and follow the instructions to reset your password.
              </p>
            </div>
            <div class="mt-4">
              <div class="-mx-2 -my-1.5 flex">
                <button
                  @click="handleResend"
                  :disabled="isResending"
                  class="px-2 py-1.5 rounded-md text-sm font-medium text-green-800 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <span v-if="isResending" class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-green-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Resending...
                  </span>
                  <span v-else>Resend email</span>
                </button>
                <router-link
                  to="/"
                  class="ml-3 px-2 py-1.5 rounded-md text-sm font-medium text-green-800 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  Back to login
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Forgot password form -->
      <div v-else>
        <p class="mb-6 text-sm text-gray-600">
          Enter your email address below and we'll send you a link to reset your password.
        </p>
        
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Email field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                name="email"
                type="email"
                autocomplete="email"
                required
                :disabled="isLoading"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-300': formError }"
              />
              <!-- Error message -->
              <p v-if="formError" class="mt-1 text-sm text-red-600">{{ formError }}</p>
            </div>
          </div>
  
          <!-- Submit button -->
          <div>
            <button
              type="submit"
              :disabled="isLoading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <span v-if="isLoading" class="mr-2">
                <!-- Loading spinner -->
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ isLoading ? 'Sending reset link...' : 'Send reset link' }}
            </button>
          </div>
        </form>
  
        <!-- Back to login link -->
        <div class="mt-6">
          <p class="text-center text-sm text-gray-600">
            Remember your password?
            <router-link to="/" class="font-medium text-blue-600 hover:text-blue-500">
              Back to login
            </router-link>
          </p>
        </div>
      </div>
      
      <!-- Reset password form (will be shown when user clicks the reset link from their email) -->
      <div v-if="showResetForm" class="mt-8 pt-8 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Reset your password</h3>
        
        <form @submit.prevent="handleResetPassword" class="space-y-6">
          <!-- New password field -->
          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-700">New password</label>
            <div class="mt-1 relative">
              <input
                id="new_password"
                v-model="resetForm.password"
                name="new_password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :disabled="isResetting"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-300': resetErrors.password }"
              />
              <!-- Toggle password visibility -->
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <span class="material-icons text-gray-400 text-sm">
                  {{ showPassword ? 'visibility_off' : 'visibility' }}
                </span>
              </button>
              <!-- Error message -->
              <p v-if="resetErrors.password" class="mt-1 text-sm text-red-600">{{ resetErrors.password }}</p>
              
              <!-- Password requirements -->
              <div class="mt-1 text-xs text-gray-500">
                Password must be at least 8 characters and include a number and a special character.
              </div>
            </div>
          </div>
  
          <!-- Confirm password field -->
          <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm new password</label>
            <div class="mt-1">
              <input
                id="confirm_password"
                v-model="resetForm.password_confirmation"
                name="confirm_password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                :disabled="isResetting"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-300': resetErrors.password_confirmation }"
              />
              <!-- Error message -->
              <p v-if="resetErrors.password_confirmation" class="mt-1 text-sm text-red-600">{{ resetErrors.password_confirmation }}</p>
            </div>
          </div>
  
          <!-- Submit button -->
          <div>
            <button
              type="submit"
              :disabled="isResetting"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <span v-if="isResetting" class="mr-2">
                <!-- Loading spinner -->
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ isResetting ? 'Resetting password...' : 'Reset password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Store and router
  const route = useRoute();
  const router = useRouter();
  const alertStore = useAlertStore();
  
  // Form state for password reset request
  const form = reactive({
    email: ''
  });
  const formError = ref('');
  const isLoading = ref(false);
  const emailSent = ref(false);
  const isResending = ref(false);
  
  // Password visibility
  const showPassword = ref(false);
  
  // Reset password form state
  const showResetForm = ref(false);
  const resetForm = reactive({
    email: '',
    token: '',
    password: '',
    password_confirmation: ''
  });
  const resetErrors = reactive({
    password: '',
    password_confirmation: '',
    general: ''
  });
  const isResetting = ref(false);
  
  /**
   * Handle forgot password form submission
   */
  async function handleSubmit() {
    // Validate email
    if (!form.email) {
      formError.value = 'Email is required';
      return;
    } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
      formError.value = 'Please enter a valid email address';
      return;
    }
    
    // Reset error
    formError.value = '';
    
    // Set loading state
    isLoading.value = true;
    
    try {
      // Send password reset request
      await axios.post('/api/auth/forgot-password', {
        email: form.email
      });
      
      // Show success message
      emailSent.value = true;
      isLoading.value = false;
    } catch (error) {
      console.error('Error sending reset link:', error);
      isLoading.value = false;
      
      if (error.response) {
        if (error.response.status === 422) {
          // Validation errors
          const validationErrors = error.response.data.errors;
          
          if (validationErrors.email) {
            formError.value = validationErrors.email[0];
          } else {
            formError.value = 'Failed to send reset link. Please try again.';
          }
        } else {
          // Other server errors
          formError.value = error.response.data.message || 'Failed to send reset link. Please try again.';
        }
      } else {
        // Network or other errors
        formError.value = 'Failed to send reset link. Please check your connection and try again.';
      }
    }
  }
  
  /**
   * Handle resend password reset link
   */
  async function handleResend() {
    isResending.value = true;
    
    try {
      // Resend password reset request
      await axios.post('/api/auth/forgot-password', {
        email: form.email
      });
      
      // Show success notification
      alertStore.setSuccessAlert('Password reset link has been resent to your email.');
      isResending.value = false;
    } catch (error) {
      console.error('Error resending reset link:', error);
      isResending.value = false;
      
      // Show error notification
      alertStore.setErrorAlert('Failed to resend reset link. Please try again.');
    }
  }
  
  /**
   * Validate reset password form
   * @returns {boolean} True if form is valid
   */
  function validateResetForm() {
    // Reset errors
    Object.keys(resetErrors).forEach(key => {
      resetErrors[key] = '';
    });
    
    let isValid = true;
    
    // Validate password
    if (!resetForm.password) {
      resetErrors.password = 'Password is required';
      isValid = false;
    } else if (resetForm.password.length < 8) {
      resetErrors.password = 'Password must be at least 8 characters';
      isValid = false;
    } else if (!/(?=.*[0-9])/.test(resetForm.password)) {
      resetErrors.password = 'Password must include at least one number';
      isValid = false;
    } else if (!/(?=.*[!@#$%^&*])/.test(resetForm.password)) {
      resetErrors.password = 'Password must include at least one special character';
      isValid = false;
    }
    
    // Validate password confirmation
    if (!resetForm.password_confirmation) {
      resetErrors.password_confirmation = 'Please confirm your password';
      isValid = false;
    } else if (resetForm.password !== resetForm.password_confirmation) {
      resetErrors.password_confirmation = 'Passwords do not match';
      isValid = false;
    }
    
    return isValid;
  }
  
  /**
   * Handle reset password form submission
   */
  async function handleResetPassword() {
    // Validate form
    if (!validateResetForm()) {
      return;
    }
    
    // Set loading state
    isResetting.value = true;
    
    try {
      // Submit password reset
      await axios.post('/api/auth/reset-password', resetForm);
      
      // Show success notification
      alertStore.setSuccessAlert('Your password has been reset successfully. Please log in with your new password.');
      
      // Navigate to login page
      router.push('/');
    } catch (error) {
      console.error('Error resetting password:', error);
      isResetting.value = false;
      
      if (error.response) {
        if (error.response.status === 422) {
          // Validation errors
          const validationErrors = error.response.data.errors;
          
          // Map backend validation errors to form fields
          Object.keys(validationErrors).forEach(key => {
            if (resetErrors.hasOwnProperty(key)) {
              resetErrors[key] = validationErrors[key][0];
            } else {
              resetErrors.general = validationErrors[key][0];
            }
          });
        } else {
          // Other server errors
          resetErrors.general = error.response.data.message || 'Failed to reset password. Please try again.';
          alertStore.setErrorAlert(resetErrors.general);
        }
      } else {
        // Network or other errors
        resetErrors.general = 'Failed to reset password. Please check your connection and try again.';
        alertStore.setErrorAlert(resetErrors.general);
      }
    }
  }
  
  // Check if we're on the reset password page
  onMounted(() => {
    // Check for token and email in query params
    const token = route.query.token;
    const email = route.query.email;
    
    if (token && email) {
      // Show reset password form
      showResetForm.value = true;
      
      // Populate form data
      resetForm.token = token;
      resetForm.email = email;
    }
    
    // Auto-focus email field
    if (typeof document !== 'undefined') {
      setTimeout(() => {
        document.getElementById('email')?.focus();
      }, 100);
    }
  });
  </script>
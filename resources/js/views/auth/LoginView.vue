<!-- resources/js/views/auth/LoginView.vue -->

<template>
    <div>
      <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Sign in to your account</h2>
      
      <!-- Login form -->
      <form @submit.prevent="handleLogin" class="space-y-6">
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
              :class="{ 'border-red-300': formErrors.email }"
            />
            <!-- Error message -->
            <p v-if="formErrors.email" class="mt-1 text-sm text-red-600">{{ formErrors.email }}</p>
          </div>
        </div>
  
        <!-- Password field -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="mt-1">
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              :disabled="isLoading"
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': formErrors.password }"
            />
            <!-- Error message -->
            <p v-if="formErrors.password" class="mt-1 text-sm text-red-600">{{ formErrors.password }}</p>
          </div>
        </div>
  
        <!-- Remember me and forgot password -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="form.remember"
              name="remember-me"
              type="checkbox"
              :disabled="isLoading"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember me</label>
          </div>
  
          <div class="text-sm">
            <router-link
              to="/forgot-password"
              class="font-medium text-blue-600 hover:text-blue-500"
            >
              Forgot your password?
            </router-link>
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
            {{ isLoading ? 'Signing in...' : 'Sign in' }}
          </button>
        </div>
      </form>
  
      <!-- Register link -->
      <div class="mt-6">
        <p class="text-center text-sm text-gray-600">
          Don't have an account?
          <router-link to="/register" class="font-medium text-blue-600 hover:text-blue-500">
            Register now
          </router-link>
        </p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  
  // Form state
  const form = reactive({
    email: '',
    password: '',
    remember: false
  });
  
  // Form errors
  const formErrors = reactive({
    email: '',
    password: '',
    general: ''
  });
  
  // Loading state
  const isLoading = ref(false);
  
  // Store and router
  const router = useRouter();
  const route = useRoute();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  /**
   * Handle login form submission
   */
  async function handleLogin() {
    // Reset errors
    formErrors.email = '';
    formErrors.password = '';
    formErrors.general = '';
    
    // Basic form validation
    if (!form.email) {
      formErrors.email = 'Email is required';
      return;
    }
    
    if (!form.password) {
      formErrors.password = 'Password is required';
      return;
    }
    
    // Set loading state
    isLoading.value = true;
    
    try {
      // Attempt login
      await authStore.login({
        email: form.email,
        password: form.password,
        remember: form.remember
      });
      
      // Success notification
      alertStore.setSuccessAlert('Welcome back! You have been successfully logged in.');
      
      // Navigate to dashboard or redirect URL if available
      const redirectPath = route.query.redirect || '/dashboard';
      router.push(redirectPath);
    } catch (error) {
      // Handle authentication errors
      isLoading.value = false;
      
      if (error.response) {
        if (error.response.status === 422) {
          // Validation errors
          const validationErrors = error.response.data.errors;
          
          if (validationErrors.email) {
            formErrors.email = validationErrors.email[0];
          }
          
          if (validationErrors.password) {
            formErrors.password = validationErrors.password[0];
          }
        } else if (error.response.status === 401) {
          // Invalid credentials
          formErrors.general = 'Invalid email or password';
          alertStore.setErrorAlert('Invalid email or password. Please try again.');
        } else {
          // Other server errors
          formErrors.general = error.response.data.message || 'Login failed. Please try again.';
          alertStore.setErrorAlert(formErrors.general);
        }
      } else {
        // Network or other errors
        formErrors.general = 'Login failed. Please check your connection and try again.';
        alertStore.setErrorAlert(formErrors.general);
      }
    }
  }
  
  // Auto-focus email field on component mount
  if (typeof document !== 'undefined') {
    setTimeout(() => {
      document.getElementById('email')?.focus();
    }, 100);
  }
  </script>
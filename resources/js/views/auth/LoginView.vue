<template>
    <div class="max-w-md mx-auto mt-8 p-6 bg-white rounded-lg shadow-md">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Sign in to your account</h2>
  
      <form @submit.prevent="handleLogin" class="space-y-6">
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
              :class="{ 'border-red-500': formErrors.email }"
              @input="clearError('email')"
            />
            <p v-if="formErrors.email" class="mt-1 text-sm text-red-600">{{ formErrors.email }}</p>
          </div>
        </div>
  
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
              :class="{ 'border-red-500': formErrors.password }"
              @input="clearError('password')"
            />
            <p v-if="formErrors.password" class="mt-1 text-sm text-red-600">{{ formErrors.password }}</p>
          </div>
        </div>
  
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
              to="/forgot-password" class="font-medium text-blue-600 hover:text-blue-500"
            >
              Forgot your password?
            </router-link>
          </div>
        </div>
  
        <div v-if="formErrors.general" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 102 0V7zm-1 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                {{ formErrors.general }}
              </h3>
            </div>
          </div>
        </div>
  
        <div>
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
          >
            <span v-if="isLoading" class="mr-2">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ isLoading ? 'Signing in...' : 'Sign in' }}
          </button>
        </div>
      </form>
  
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
  import { ref, reactive, onMounted } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useAuthStore } from '../../stores/auth'; // Ensure path is correct
  import { useAlertStore } from '../../stores/alert'; // Ensure path is correct
  
  // --- Component State ---
  
  // Reactive object for form fields
  const form = reactive({
    email: '',
    password: '',
    remember: false // Note: 'remember me' functionality often requires backend logic
  });
  
  // Reactive object for form validation errors
  const formErrors = reactive({
    email: '',
    password: '',
    general: '' // For errors not specific to a field (e.g., auth failure)
  });
  
  // Ref for tracking loading state during submission
  const isLoading = ref(false);
  
  // --- Hooks and Stores ---
  
  const router = useRouter();
  const route = useRoute(); // To get query params like redirect URL
  const authStore = useAuthStore();
  const alertStore = useAlertStore(); // For displaying success/error messages globally
  
  // --- Methods ---
  
  /**
   * Clears the error message for a specific form field.
   * Also clears the general error when the user starts typing.
   * @param {string} field - The name of the field ('email' or 'password')
   */
  function clearError(field) {
    if (formErrors[field]) {
      formErrors[field] = '';
    }
    // Clear general error on any input change after an error occurred
    if (formErrors.general) {
      formErrors.general = '';
    }
  }
  
  /**
   * Handles the login form submission.
   * Performs basic validation, calls the auth store's login action,
   * handles success and error scenarios.
   */
  async function handleLogin() {
    // 1. Reset previous errors
    formErrors.email = '';
    formErrors.password = '';
    formErrors.general = '';
  
    // 2. Basic frontend validation (optional but good UX)
    let isValid = true;
    // Simple check for presence and basic email format
    if (!form.email || !/\S+@\S+\.\S+/.test(form.email)) {
      formErrors.email = 'Please enter a valid email address';
      isValid = false;
    }
    if (!form.password) {
      formErrors.password = 'Password is required';
      isValid = false;
    }
    // If frontend validation fails, stop here
    if (!isValid) {
      return;
    }
  
    // 3. Set loading state
    isLoading.value = true;
  
    try {
      // 4. Call the auth store's login action
      await authStore.login({
        email: form.email,
        password: form.password,
        // remember: form.remember // Include if your backend/store uses this
      });
  
      // 5. Handle Success
      // isLoading state might be reset within the store action, or reset it here if needed
      alertStore.setSuccessAlert('Welcome back! You have been successfully logged in.');

      isLoading.value = false;
  
      // Redirect user: Check for redirect query param or default to dashboard/root
      const redirectPath = route.query.redirect || '/'; // Adjust '/' if your dashboard is elsewhere
      router.push(redirectPath);
  
    } catch (error) {
      // 6. Handle Errors thrown by the store action
      isLoading.value = false; // Ensure loading indicator stops on error
  
      // Log the error for debugging
      console.error("Login Error in Component:", error);
  
      // Display errors to the user based on the error structure
      if (error.response && error.response.data) {
        // Error has response data (likely from Axios)
        const data = error.response.data;
        const status = error.response.status;
  
        if (status === 422 && data.errors) {
          // Handle Laravel validation errors (HTTP 422)
          if (data.errors.email) {
            formErrors.email = data.errors.email[0];
          }
          if (data.errors.password) {
            formErrors.password = data.errors.password[0];
          }
          // Add a general message if specific fields aren't highlighted but validation failed
          if (!formErrors.email && !formErrors.password) {
             formErrors.general = 'Please check the form for errors.';
          }
        } else if (status === 401) {
          // Handle incorrect credentials (HTTP 401 Unauthorized)
          // Use error message from store/backend if available, or a default
          formErrors.general = authStore.error || data.message || data.error || 'Invalid email or password.';
          alertStore.setErrorAlert(formErrors.general); // Show global alert for this type of error
        } else {
          // Handle other server errors (e.g., 500 Internal Server Error)
          formErrors.general = authStore.error || data.message || 'An unexpected server error occurred. Please try again later.';
          alertStore.setErrorAlert(formErrors.general);
        }
      } else {
        // Handle network errors or other issues where error.response is missing
        formErrors.general = authStore.error || 'Login failed. Please check your network connection and try again.';
        alertStore.setErrorAlert(formErrors.general);
      }
    } finally {
      // Optional: Ensure loading is always false after attempt, though should be handled in try/catch
      isLoading.value = false;
      // isLoading.value = false;
    }
  }
  
  // --- Lifecycle Hooks ---
  
  // Auto-focus the email field when the component mounts
  onMounted(() => {
    // Ensure this code runs only in the browser environment
    if (typeof document !== 'undefined') {
      const emailInput = document.getElementById('email');
      if (emailInput) {
        // Use setTimeout to ensure the element is rendered and focusable
        setTimeout(() => {
          emailInput.focus();
        }, 100); // Small delay often helps
      }
    }
  });
  </script>
  
  <style scoped>
  /* Add component-specific styles here if needed */
  
  /* Example: Slightly enhance focus visibility for accessibility */
  input:focus-visible {
    outline: 2px solid theme('colors.blue.500');
    outline-offset: 1px;
    border-color: theme('colors.blue.500'); /* Optional: match border color */
  }
  
  /* Style disabled button */
  button:disabled {
    background-color: theme('colors.blue.400'); /* Lighter blue when disabled */
  }
  </style>
  
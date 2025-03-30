<!-- resources/js/views/auth/RegisterView.vue -->

<template>
    <div>
      <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create an account</h2>
      
      <!-- Registration form -->
      <form @submit.prevent="handleRegister" class="space-y-6">
        <!-- Name field -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
          <div class="mt-1">
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              autocomplete="name"
              required
              :disabled="isLoading"
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': formErrors.name }"
            />
            <!-- Error message -->
            <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
          </div>
        </div>
  
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
          <div class="mt-1 relative">
            <input
              id="password"
              v-model="form.password"
              name="password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="new-password"
              required
              :disabled="isLoading"
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': formErrors.password }"
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
            <p v-if="formErrors.password" class="mt-1 text-sm text-red-600">{{ formErrors.password }}</p>
            
            <!-- Password requirements -->
            <div class="mt-1 text-xs text-gray-500">
              Password must be at least 8 characters and include a number and a special character.
            </div>
          </div>
        </div>
  
        <!-- Password confirmation field -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
          <div class="mt-1">
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="new-password"
              required
              :disabled="isLoading"
              class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': formErrors.password_confirmation }"
            />
            <!-- Error message -->
            <p v-if="formErrors.password_confirmation" class="mt-1 text-sm text-red-600">{{ formErrors.password_confirmation }}</p>
          </div>
        </div>
  
        <!-- Terms agreement checkbox -->
        <div class="flex items-center">
          <input
            id="terms"
            v-model="form.terms"
            name="terms"
            type="checkbox"
            required
            :disabled="isLoading"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            :class="{ 'border-red-300': formErrors.terms }"
          />
          <label for="terms" class="ml-2 block text-sm text-gray-700">
            I agree to the 
            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Terms and Conditions</a>
            and
            <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Privacy Policy</a>
          </label>
        </div>
        <!-- Terms error message -->
        <p v-if="formErrors.terms" class="mt-1 text-sm text-red-600">{{ formErrors.terms }}</p>
  
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
            {{ isLoading ? 'Creating account...' : 'Create account' }}
          </button>
        </div>
      </form>
  
      <!-- Login link -->
      <div class="mt-6">
        <p class="text-center text-sm text-gray-600">
          Already have an account?
          <router-link to="/" class="font-medium text-blue-600 hover:text-blue-500">
            Sign in
          </router-link>
        </p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  
  // Form state
  const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false
  });
  
  // Form errors
  const formErrors = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: '',
    general: ''
  });
  
  // UI state
  const isLoading = ref(false);
  const showPassword = ref(false);
  
  // Store and router
  const router = useRouter();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  /**
   * Validate the registration form
   * @returns {boolean} True if form is valid
   */
  function validateForm() {
    // Reset errors
    Object.keys(formErrors).forEach(key => {
      formErrors[key] = '';
    });
    
    let isValid = true;
    
    // Validate name
    if (!form.name) {
      formErrors.name = 'Name is required';
      isValid = false;
    }
    
    // Validate email
    if (!form.email) {
      formErrors.email = 'Email is required';
      isValid = false;
    } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
      formErrors.email = 'Please enter a valid email address';
      isValid = false;
    }
    
    // Validate password
    if (!form.password) {
      formErrors.password = 'Password is required';
      isValid = false;
    } else if (form.password.length < 8) {
      formErrors.password = 'Password must be at least 8 characters';
      isValid = false;
    } else if (!/(?=.*[0-9])/.test(form.password)) {
      formErrors.password = 'Password must include at least one number';
      isValid = false;
    } else if (!/(?=.*[!@#$%^&*])/.test(form.password)) {
      formErrors.password = 'Password must include at least one special character';
      isValid = false;
    }
    
    // Validate password confirmation
    if (!form.password_confirmation) {
      formErrors.password_confirmation = 'Please confirm your password';
      isValid = false;
    } else if (form.password !== form.password_confirmation) {
      formErrors.password_confirmation = 'Passwords do not match';
      isValid = false;
    }
    
    // Validate terms agreement
    if (!form.terms) {
      formErrors.terms = 'You must agree to the terms and conditions';
      isValid = false;
    }
    
    return isValid;
  }
  
  /**
   * Handle registration form submission
   */
  async function handleRegister() {
    // Validate form
    if (!validateForm()) {
      return;
    }
    
    // Set loading state
    isLoading.value = true;
    
    try {
      // Attempt registration
      await authStore.register({
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation
      });
      
      // Success notification
      alertStore.setSuccessAlert('Your account has been created successfully. Welcome to WMS!');
      
      // Navigate to dashboard
      router.push('/dashboard');
    } catch (error) {
      // Handle registration errors
      isLoading.value = false;
      
      if (error.response) {
        if (error.response.status === 422) {
          // Validation errors
          const validationErrors = error.response.data.errors;
          
          // Map backend validation errors to form fields
          Object.keys(validationErrors).forEach(key => {
            if (formErrors.hasOwnProperty(key)) {
              formErrors[key] = validationErrors[key][0];
            } else {
              formErrors.general = validationErrors[key][0];
            }
          });
        } else {
          // Other server errors
          formErrors.general = error.response.data.message || 'Registration failed. Please try again.';
          alertStore.setErrorAlert(formErrors.general);
        }
      } else {
        // Network or other errors
        formErrors.general = 'Registration failed. Please check your connection and try again.';
        alertStore.setErrorAlert(formErrors.general);
      }
    }
  }
  
  // Auto-focus name field on component mount
  if (typeof document !== 'undefined') {
    setTimeout(() => {
      document.getElementById('name')?.focus();
    }, 100);
  }
  </script>
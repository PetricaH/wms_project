<template>
  
    <div class="max-w-lg mx-auto mt-8 p-8 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create Your Account</h2>
    
        <form @submit.prevent="handleRegister" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
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
                :class="{ 'border-red-500': formErrors.name }"
                @input="clearError('name')"
              />
              <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
            </div>
          </div>
    
          <div>
            <label for="business_name" class="block text-sm font-medium text-gray-700">Business Name</label>
            <div class="mt-1">
              <input
                id="business_name"
                v-model="form.business_name"
                name="business_name"
                type="text"
                required
                :disabled="isLoading"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-500': formErrors.business_name }"
                @input="clearError('business_name')"
              />
              <p v-if="formErrors.business_name" class="mt-1 text-sm text-red-600">{{ formErrors.business_name }}</p>
            </div>
          </div>
    
          <div>
            <label for="subdomain" class="block text-sm font-medium text-gray-700">Choose Your Subdomain</label>
            <div class="mt-1 flex rounded-md shadow-sm">
               <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                 https://
               </span>
              <input
                id="subdomain"
                v-model="form.subdomain"
                name="subdomain"
                type="text"
                required
                :disabled="isLoading"
                class="appearance-none flex-1 block w-full min-w-0 px-3 py-2 border border-gray-300 rounded-none focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-500': formErrors.subdomain }"
                @input="clearError('subdomain')"
                placeholder="your-company"
              />
               <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                 .yourapp.com
               </span>
            </div>
             <p class="mt-1 text-xs text-gray-500">This will be your unique web address. Use only letters, numbers, and hyphens.</p>
             <p v-if="formErrors.subdomain" class="mt-1 text-sm text-red-600">{{ formErrors.subdomain }}</p>
          </div>
    
    
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
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
                :class="{ 'border-red-500': formErrors.password }"
                @input="clearError('password')"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                aria-label="Toggle password visibility"
              >
                 <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.274 1.016-.68 1.96-1.188 2.807M12 15a3 3 0 100-6 3 3 0 000 6z" />
                 </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .274-1.016.68-1.96 1.188-2.807M14.25 14.25A3 3 0 1012 12m2.25 2.25l-4.5-4.5" />
                  </svg>
              </button>
            </div>
             <p v-if="formErrors.password" class="mt-1 text-sm text-red-600">{{ formErrors.password }}</p>
             <div class="mt-1 text-xs text-gray-500">
               Use 8 or more characters with a mix of letters, numbers & symbols.
             </div>
          </div>
    
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
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
                :class="{ 'border-red-500': formErrors.password_confirmation }"
                @input="clearError('password_confirmation')"
              />
              <p v-if="formErrors.password_confirmation" class="mt-1 text-sm text-red-600">{{ formErrors.password_confirmation }}</p>
            </div>
          </div>
    
          <div class="pt-2">
              <div class="flex items-start">
                  <div class="flex items-center h-5">
                      <input
                          id="terms"
                          v-model="form.terms"
                          name="terms"
                          type="checkbox"
                          required
                          :disabled="isLoading"
                          class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                          :class="{ 'border-red-500': formErrors.terms }"
                          @change="clearError('terms')"
                      />
                  </div>
                  <div class="ml-3 text-sm">
                      <label for="terms" class="font-medium text-gray-700">
                          I agree to the
                          <a href="/terms" target="_blank" class="text-blue-600 hover:text-blue-500">Terms and Conditions</a>
                          and
                          <a href="/privacy" target="_blank" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>.
                      </label>
                       <p v-if="formErrors.terms" class="mt-1 text-sm text-red-600">{{ formErrors.terms }}</p>
                  </div>
              </div>
          </div>
    
    
           <div v-if="formErrors.general" class="rounded-md bg-red-50 p-4 mt-4">
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
    
          <div class="pt-2">
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
              {{ isLoading ? 'Creating Account...' : 'Create Account' }}
            </button>
          </div>
        </form>
    
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
    import { ref, reactive, onMounted } from 'vue';
    import { useRouter } from 'vue-router';
    import { useAuthStore } from '../../stores/auth'; // Adjust path if needed
    import { useAlertStore } from '../../stores/alert'; // Adjust path if needed
    
    // --- Component State ---
    
    // Reactive object for form fields, including new ones
    const form = reactive({
      name: '',
      business_name: '', // Added
      subdomain: '',     // Added
      email: '',
      password: '',
      password_confirmation: '',
      terms: false
    });
    
    // Reactive object for form validation errors, including new ones
    const formErrors = reactive({
      name: '',
      business_name: '', // Added
      subdomain: '',     // Added
      email: '',
      password: '',
      password_confirmation: '',
      terms: '',
      general: '' // For non-field specific errors
    });
    
    // UI state refs
    const isLoading = ref(false);
    const showPassword = ref(false);
    
    // --- Hooks and Stores ---
    
    const router = useRouter();
    const authStore = useAuthStore();
    const alertStore = useAlertStore();
    
    // --- Methods ---
    
    /**
     * Clears the error message for a specific form field.
     * Also clears the general error message.
     * @param {string} field - The name of the field (e.g., 'name', 'business_name')
     */
    function clearError(field) {
      if (formErrors[field]) {
        formErrors[field] = '';
      }
      if (formErrors.general) {
        formErrors.general = '';
      }
    }
    
    /**
     * Performs frontend validation for the registration form.
     * @returns {boolean} True if the form passes validation, false otherwise.
     */
    function validateForm() {
      // Reset all errors before re-validating
      Object.keys(formErrors).forEach(key => {
        formErrors[key] = '';
      });
    
      let isValid = true;
    
      // Validate name
      if (!form.name.trim()) {
        formErrors.name = 'Full name is required';
        isValid = false;
      }
    
      // Validate business name
      if (!form.business_name.trim()) {
        formErrors.business_name = 'Business name is required';
        isValid = false;
      }
    
      // Validate subdomain
      if (!form.subdomain.trim()) {
          formErrors.subdomain = 'Subdomain is required';
          isValid = false;
      } else if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(form.subdomain)) {
          // Basic check: lowercase letters, numbers, and hyphens (not at start/end)
          formErrors.subdomain = 'Subdomain can only contain lowercase letters, numbers, and hyphens.';
          isValid = false;
      }
    
    
      // Validate email
      if (!form.email.trim()) {
        formErrors.email = 'Email address is required';
        isValid = false;
      } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
        formErrors.email = 'Please enter a valid email address';
        isValid = false;
      }
    
      // Validate password (matching backend rules if possible)
      if (!form.password) {
        formErrors.password = 'Password is required';
        isValid = false;
      } else if (form.password.length < 8) {
        formErrors.password = 'Password must be at least 8 characters';
        isValid = false;
      }
      // Add more frontend password checks if needed (e.g., number, special char)
      // Example:
      // else if (!/\d/.test(form.password)) {
      //   formErrors.password = 'Password must include a number';
      //   isValid = false;
      // } else if (!/[!@#$%^&*]/.test(form.password)) {
      //   formErrors.password = 'Password must include a special character (!@#$%^&*)';
      //   isValid = false;
      // }
    
    
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
        formErrors.terms = 'You must agree to the terms and conditions to register';
        isValid = false;
      }
    
      return isValid;
    }
    
    /**
     * Handles the registration form submission.
     * Validates the form, calls the auth store's register action,
     * and manages loading state and error/success feedback.
     */
    async function handleRegister() {
      // 1. Validate form on the frontend first
      if (!validateForm()) {
        // If validation fails, scroll to the first error (optional UX improvement)
        const firstErrorField = Object.keys(formErrors).find(key => formErrors[key]);
        if (firstErrorField && document.getElementById(firstErrorField)) {
            document.getElementById(firstErrorField).focus();
        }
        return; // Stop submission
      }
    
      // 2. Set loading state
      isLoading.value = true;
      formErrors.general = ''; // Clear previous general errors
    
      try {
        // 3. Call the auth store's register action, passing all form data
        await authStore.register({
          name: form.name,
          business_name: form.business_name, // Include new field
          subdomain: form.subdomain,         // Include new field
          email: form.email,
          password: form.password,
          password_confirmation: form.password_confirmation
          // 'terms' is usually validated on frontend/backend but not always sent
        });
    
        // 4. Handle Success
        // isLoading state might be reset within the store action, or reset it here
        alertStore.setSuccessAlert('Account created successfully! Welcome.');
    
        // Redirect to dashboard or login page after successful registration
        router.push('/dashboard'); // Or maybe '/' to login? Adjust as needed.
    
      } catch (error) {
        // 5. Handle Errors from the store action
        isLoading.value = false; // Stop loading indicator
    
        console.error("Registration Error in Component:", error); // Log for debugging
    
        if (error.response && error.response.data) {
          // Error has response data (likely from Axios)
          const data = error.response.data;
          const status = error.response.status;
    
          if (status === 422 && data.errors) {
            // Handle Laravel validation errors (HTTP 422)
            Object.keys(data.errors).forEach(key => {
              // Map backend error key to frontend formErrors key
              if (formErrors.hasOwnProperty(key)) {
                formErrors[key] = data.errors[key][0]; // Take the first error message
              } else {
                // If the error key doesn't match a specific field, show it as a general error
                formErrors.general = `${formErrors.general} ${data.errors[key][0]}`.trim();
              }
            });
             // If there were validation errors but no general message set, add one
             if (!formErrors.general && Object.values(formErrors).some(err => err)) {
                 formErrors.general = "Please correct the errors highlighted above.";
             }
    
          } else {
            // Handle other server errors (e.g., 500, 409 Conflict)
            formErrors.general = data.message || authStore.error || 'Registration failed due to a server error. Please try again.';
            alertStore.setErrorAlert(formErrors.general);
          }
        } else {
          // Handle network errors or other issues where error.response is missing
          formErrors.general = authStore.error || 'Registration failed. Please check your network connection and try again.';
          alertStore.setErrorAlert(formErrors.general);
        }
         // Optional: Scroll to general error message if shown
         if (formErrors.general) {
             // Find the general error display element and scroll to it
         }
    
      }
    }
    
    // --- Lifecycle Hooks ---
    
    // Auto-focus the first form field (name) when the component mounts
    onMounted(() => {
      if (typeof document !== 'undefined') {
        const nameInput = document.getElementById('name');
        if (nameInput) {
          setTimeout(() => nameInput.focus(), 100); // Delay ensures element is ready
        }
      }
    });
    </script>
    
    <style scoped>
    /* Add component-specific styles here if needed */
    input:focus-visible {
      outline: 2px solid theme('colors.blue.500');
      outline-offset: 1px;
    }
    button:disabled {
      background-color: theme('colors.blue.400');
    }
    </style>
    
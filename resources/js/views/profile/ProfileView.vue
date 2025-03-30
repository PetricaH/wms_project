<!-- resources/js/views/profile/ProfileView.vue -->

<template>
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Profile</h1>
  
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
  
      <div v-else class="space-y-8">
        <!-- Profile information section -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
          <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
            <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
              <span class="material-icons mr-2 text-gray-500">person</span>
              Profile Information
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Personal details and preferences
            </p>
          </div>
  
          <div class="px-4 py-5 sm:p-6">
            <form @submit.prevent="updateProfile">
              <!-- Profile form fields in a grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name field -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                  <div class="mt-1">
                    <input
                      id="name"
                      v-model="profileForm.name"
                      type="text"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': formErrors.name }"
                      required
                    />
                    <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
                  </div>
                </div>
  
                <!-- Email field (read-only) -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                  <div class="mt-1">
                    <input
                      id="email"
                      v-model="profileForm.email"
                      type="email"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md bg-gray-50"
                      readonly
                    />
                    <p class="mt-1 text-xs text-gray-500">Email address cannot be changed</p>
                  </div>
                </div>
  
                <!-- Phone field -->
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                  <div class="mt-1">
                    <input
                      id="phone"
                      v-model="profileForm.phone"
                      type="tel"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': formErrors.phone }"
                    />
                    <p v-if="formErrors.phone" class="mt-1 text-sm text-red-600">{{ formErrors.phone }}</p>
                  </div>
                </div>
  
                <!-- Position/title field -->
                <div>
                  <label for="position" class="block text-sm font-medium text-gray-700">Position/Title</label>
                  <div class="mt-1">
                    <input
                      id="position"
                      v-model="profileForm.position"
                      type="text"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                </div>
  
                <!-- Timezone field -->
                <div>
                  <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                  <div class="mt-1">
                    <select
                      id="timezone"
                      v-model="profileForm.timezone"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    >
                      <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                        {{ tz.label }}
                      </option>
                    </select>
                  </div>
                </div>
  
                <!-- Date format preference -->
                <div>
                  <label for="date_format" class="block text-sm font-medium text-gray-700">Date format</label>
                  <div class="mt-1">
                    <select
                      id="date_format"
                      v-model="profileForm.date_format"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    >
                      <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                      <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                      <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                    </select>
                  </div>
                </div>
              </div>
  
              <!-- Profile form actions -->
              <div class="flex justify-end mt-6">
                <button
                  type="submit"
                  :disabled="isSaving"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  <span v-if="isSaving" class="mr-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  {{ isSaving ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>
  
        <!-- Change password section -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
          <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
            <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
              <span class="material-icons mr-2 text-gray-500">lock</span>
              Change Password
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Update your password for security
            </p>
          </div>
  
          <div class="px-4 py-5 sm:p-6">
            <form @submit.prevent="changePassword">
              <div class="space-y-4">
                <!-- Current password field -->
                <div>
                  <label for="current_password" class="block text-sm font-medium text-gray-700">Current password</label>
                  <div class="mt-1 relative">
                    <input
                      id="current_password"
                      v-model="passwordForm.current_password"
                      :type="showCurrentPassword ? 'text' : 'password'"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': passwordErrors.current_password }"
                      required
                    />
                    <button 
                      type="button" 
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500"
                    >
                      <span class="material-icons text-sm">
                        {{ showCurrentPassword ? 'visibility_off' : 'visibility' }}
                      </span>
                    </button>
                    <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.current_password }}</p>
                  </div>
                </div>
  
                <!-- New password field -->
                <div>
                  <label for="new_password" class="block text-sm font-medium text-gray-700">New password</label>
                  <div class="mt-1 relative">
                    <input
                      id="new_password"
                      v-model="passwordForm.new_password"
                      :type="showNewPassword ? 'text' : 'password'"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': passwordErrors.new_password }"
                      required
                    />
                    <button 
                      type="button" 
                      @click="showNewPassword = !showNewPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500"
                    >
                      <span class="material-icons text-sm">
                        {{ showNewPassword ? 'visibility_off' : 'visibility' }}
                      </span>
                    </button>
                    <p v-if="passwordErrors.new_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password }}</p>
                  </div>
                  <!-- Password requirements -->
                  <div class="mt-1 text-xs text-gray-500">
                    Password must be at least 8 characters and include a number and a special character.
                  </div>
                </div>
  
                <!-- Confirm new password field -->
                <div>
                  <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm new password</label>
                  <div class="mt-1 relative">
                    <input
                      id="new_password_confirmation"
                      v-model="passwordForm.new_password_confirmation"
                      :type="showNewPassword ? 'text' : 'password'"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': passwordErrors.new_password_confirmation }"
                      required
                    />
                    <p v-if="passwordErrors.new_password_confirmation" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password_confirmation }}</p>
                  </div>
                </div>
              </div>
  
              <!-- Password form actions -->
              <div class="flex justify-end mt-6">
                <button
                  type="submit"
                  :disabled="isChangingPassword"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  <span v-if="isChangingPassword" class="mr-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  {{ isChangingPassword ? 'Updating...' : 'Update Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
  
        <!-- API access tokens section (if enabled) -->
        <div v-if="hasApiAccess" class="bg-white shadow overflow-hidden rounded-lg">
          <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b">
            <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
              <span class="material-icons mr-2 text-gray-500">vpn_key</span>
              API Access Tokens
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Manage your API access tokens
            </p>
          </div>
  
          <div class="px-4 py-5 sm:p-6">
            <!-- Token list -->
            <div v-if="tokens.length > 0" class="mb-6">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Your Active Tokens</h4>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Created
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Last Used
                      </th>
                      <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="token in tokens" :key="token.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ token.name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatDate(token.created_at) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ token.last_used_at ? formatDate(token.last_used_at) : 'Never' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button
                          @click="revokeToken(token.id)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Revoke
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
  
            <div v-else class="p-4 bg-gray-50 text-center rounded-md mb-6">
              <p class="text-sm text-gray-500">You don't have any active API tokens</p>
            </div>
  
            <!-- Create new token form -->
            <div>
              <h4 class="text-sm font-medium text-gray-700 mb-3">Create New Token</h4>
              <form @submit.prevent="createToken" class="space-y-4">
                <div>
                  <label for="token_name" class="block text-sm font-medium text-gray-700">Token Name</label>
                  <div class="mt-1">
                    <input
                      id="token_name"
                      v-model="newTokenName"
                      type="text"
                      placeholder="My Application"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      required
                    />
                  </div>
                </div>
  
                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="isCreatingToken"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                  >
                    <span v-if="isCreatingToken" class="mr-2">
                      <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    </span>
                    {{ isCreatingToken ? 'Creating...' : 'Create Token' }}
                  </button>
                </div>
              </form>
            </div>
  
            <!-- New token display -->
            <div v-if="newToken" class="mt-6 p-4 bg-blue-50 rounded-md border border-blue-200">
              <h4 class="text-sm font-medium text-blue-700 mb-2 flex items-center">
                <span class="material-icons mr-1 text-sm">info</span>
                Your New API Token
              </h4>
              <p class="text-xs text-blue-600 mb-3">
                Please copy your new API token. For security reasons, it won't be shown again.
              </p>
              <div class="bg-white p-3 rounded border border-blue-300 mb-3 overflow-x-auto">
                <code class="text-sm whitespace-nowrap">{{ newToken }}</code>
              </div>
              <div class="flex justify-end">
                <button
                  @click="copyTokenToClipboard"
                  class="inline-flex items-center py-1 px-3 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="material-icons mr-1 text-sm">content_copy</span>
                  Copy
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, onMounted } from 'vue';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Stores
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // State variables
  const loading = ref(true);
  const isSaving = ref(false);
  const isChangingPassword = ref(false);
  const isCreatingToken = ref(false);
  const hasApiAccess = ref(false);
  const tokens = ref([]);
  const newTokenName = ref('');
  const newToken = ref('');
  
  // Form visibility toggles
  const showCurrentPassword = ref(false);
  const showNewPassword = ref(false);
  
  // Profile form state
  const profileForm = reactive({
    name: '',
    email: '',
    phone: '',
    position: '',
    timezone: 'UTC',
    date_format: 'MM/DD/YYYY'
  });
  
  // Password form state
  const passwordForm = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  });
  
  // Form errors
  const formErrors = reactive({
    name: '',
    phone: '',
    timezone: '',
    date_format: ''
  });
  
  const passwordErrors = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  });
  
  /**
   * List of common timezones for the dropdown
   * In a real app, this would be fetched from the backend or a more complete list
   */
  const timezones = [
    { value: 'UTC', label: 'UTC' },
    { value: 'America/New_York', label: 'Eastern Time (US & Canada)' },
    { value: 'America/Chicago', label: 'Central Time (US & Canada)' },
    { value: 'America/Denver', label: 'Mountain Time (US & Canada)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (US & Canada)' },
    { value: 'America/Anchorage', label: 'Alaska' },
    { value: 'Pacific/Honolulu', label: 'Hawaii' },
    { value: 'Europe/London', label: 'London' },
    { value: 'Europe/Paris', label: 'Paris' },
    { value: 'Europe/Berlin', label: 'Berlin' },
    { value: 'Europe/Athens', label: 'Athens' },
    { value: 'Asia/Dubai', label: 'Dubai' },
    { value: 'Asia/Kolkata', label: 'Mumbai, New Delhi' },
    { value: 'Asia/Singapore', label: 'Singapore' },
    { value: 'Asia/Tokyo', label: 'Tokyo' },
    { value: 'Australia/Sydney', label: 'Sydney' },
    { value: 'Pacific/Auckland', label: 'Auckland' }
  ];
  
  /**
   * Format date to readable string
   * @param {string} dateString - Date string to format
   * @returns {string} Formatted date
   */
  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    // Convert to JS Date object
    const date = new Date(dateString);
    
    // Format with browser's locale date format
    return date.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }
  
  /**
   * Fetch user profile data from API
   */
  async function fetchProfile() {
    loading.value = true;
    
    try {
      // Get user profile from auth store or API
      const response = await axios.get('/api/auth/me');
      
      // Populate form with user data
      if (response.data.user) {
        const user = response.data.user;
        profileForm.name = user.name;
        profileForm.email = user.email;
        profileForm.phone = user.phone || '';
        profileForm.position = user.position || '';
        profileForm.timezone = user.timezone || 'UTC';
        profileForm.date_format = user.date_format || 'MM/DD/YYYY';
        
        // Check if user has API access permission
        hasApiAccess.value = authStore.hasPermission('api.access');
        
        // If has API access, fetch tokens
        if (hasApiAccess.value) {
          await fetchTokens();
        }
      }
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching user profile:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load your profile data.');
      loading.value = false;
    }
  }
  
  /**
   * Fetch user API tokens
   */
  async function fetchTokens() {
    try {
      const response = await axios.get('/api/auth/tokens');
      tokens.value = response.data;
    } catch (error) {
      console.error('Error fetching tokens:', error);
      // Don't show an alert for this as it's not critical
      tokens.value = [];
    }
  }
  
  /**
   * Update user profile
   */
  async function updateProfile() {
    // Reset form errors
    Object.keys(formErrors).forEach(key => {
      formErrors[key] = '';
    });
    
    // Basic validation
    if (!profileForm.name) {
      formErrors.name = 'Name is required';
      return;
    }
    
    isSaving.value = true;
    
    try {
      // Submit updated profile
      await axios.put('/api/auth/profile', profileForm);
      
      // Update user in auth store if needed
      await authStore.fetchUserProfile();
      
      // Show success notification
      alertStore.setSuccessAlert('Your profile has been updated successfully.');
      
      isSaving.value = false;
    } catch (error) {
      console.error('Error updating profile:', error);
      
      // Handle validation errors
      if (error.response?.status === 422 && error.response.data.errors) {
        const validationErrors = error.response.data.errors;
        
        // Map backend validation errors to form fields
        Object.keys(validationErrors).forEach(key => {
          if (formErrors.hasOwnProperty(key)) {
            formErrors[key] = validationErrors[key][0];
          }
        });
      } else {
        // Show general error alert
        alertStore.setApiErrorAlert(error, 'Failed to update your profile.');
      }
      
      isSaving.value = false;
    }
  }
  
  /**
   * Change user password
   */
  async function changePassword() {
    // Reset password errors
    Object.keys(passwordErrors).forEach(key => {
      passwordErrors[key] = '';
    });
    
    // Basic validation
    if (!passwordForm.current_password) {
      passwordErrors.current_password = 'Current password is required';
      return;
    }
    
    if (!passwordForm.new_password) {
      passwordErrors.new_password = 'New password is required';
      return;
    }
    
    if (passwordForm.new_password.length < 8) {
      passwordErrors.new_password = 'Password must be at least 8 characters';
      return;
    }
    
    if (!/(?=.*[0-9])/.test(passwordForm.new_password)) {
      passwordErrors.new_password = 'Password must include at least one number';
      return;
    }
    
    if (!/(?=.*[!@#$%^&*])/.test(passwordForm.new_password)) {
      passwordErrors.new_password = 'Password must include at least one special character';
      return;
    }
    
    if (!passwordForm.new_password_confirmation) {
      passwordErrors.new_password_confirmation = 'Please confirm your new password';
      return;
    }
    
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
      passwordErrors.new_password_confirmation = 'Passwords do not match';
      return;
    }
    
    isChangingPassword.value = true;
    
    try {
      // Submit password change request
      await axios.put('/api/auth/password', passwordForm);
      
      // Show success notification
      alertStore.setSuccessAlert('Your password has been changed successfully.');
      
      // Reset form
      passwordForm.current_password = '';
      passwordForm.new_password = '';
      passwordForm.new_password_confirmation = '';
      
      isChangingPassword.value = false;
    } catch (error) {
      console.error('Error changing password:', error);
      
      // Handle validation errors
      if (error.response?.status === 422 && error.response.data.errors) {
        const validationErrors = error.response.data.errors;
        
        // Map backend validation errors to form fields
        Object.keys(validationErrors).forEach(key => {
          if (passwordErrors.hasOwnProperty(key)) {
            passwordErrors[key] = validationErrors[key][0];
          }
        });
      } else if (error.response?.status === 401) {
        // Current password is incorrect
        passwordErrors.current_password = 'Current password is incorrect';
      } else {
        // Show general error alert
        alertStore.setApiErrorAlert(error, 'Failed to change your password.');
      }
      
      isChangingPassword.value = false;
    }
  }
  
  /**
   * Create a new API token
   */
  async function createToken() {
    if (!newTokenName.value) return;
    
    isCreatingToken.value = true;
    
    try {
      // Create token
      const response = await axios.post('/api/auth/tokens', {
        name: newTokenName.value
      });
      
      // Set new token and update token list
      newToken.value = response.data.plainTextToken;
      await fetchTokens();
      
      // Reset form
      newTokenName.value = '';
      
      isCreatingToken.value = false;
    } catch (error) {
      console.error('Error creating token:', error);
      alertStore.setApiErrorAlert(error, 'Failed to create API token.');
      
      isCreatingToken.value = false;
    }
  }
  
  /**
   * Revoke an API token
   * @param {number} tokenId - Token ID to revoke
   */
  async function revokeToken(tokenId) {
    try {
      // Revoke token
      await axios.delete(`/api/auth/tokens/${tokenId}`);
      
      // Update token list
      await fetchTokens();
      
      // Show success notification
      alertStore.setSuccessAlert('API token has been revoked.');
    } catch (error) {
      console.error('Error revoking token:', error);
      alertStore.setApiErrorAlert(error, 'Failed to revoke API token.');
    }
  }
  
  /**
   * Copy token to clipboard
   */
  async function copyTokenToClipboard() {
    if (!newToken.value) return;
    
    try {
      await navigator.clipboard.writeText(newToken.value);
      alertStore.setSuccessAlert('Token copied to clipboard.');
    } catch (error) {
      console.error('Error copying to clipboard:', error);
      alertStore.setErrorAlert('Failed to copy token to clipboard.');
    }
  }
  
  // Initialize component
  onMounted(() => {
    fetchProfile();
  });
  </script>
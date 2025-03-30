<!-- resources/js/components/navigation/UserMenu.vue -->

<template>
    <div class="relative">
      <!-- User menu button -->
      <button
        @click="toggleMenu"
        class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        id="user-menu-button"
        aria-expanded="false"
        aria-haspopup="true"
      >
        <span class="sr-only">Open user menu</span>
        
        <!-- User avatar -->
        <div class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-300 text-gray-600">
          <!-- If user has an avatar, display it, otherwise use initials -->
          <img 
            v-if="user && user.avatar_url" 
            :src="user.avatar_url" 
            :alt="user.name" 
            class="w-8 h-8 rounded-full"
          />
          <span v-else class="text-sm font-medium">{{ userInitials }}</span>
        </div>
        
        <!-- User name and dropdown indicator -->
        <div class="ml-2 flex items-center">
          <div class="flex flex-col text-left">
            <span class="text-sm font-medium text-gray-700">{{ userName }}</span>
            <span class="text-xs font-normal text-gray-500">{{ userRole }}</span>
          </div>
          <svg 
            class="ml-1 h-5 w-5 text-gray-400" 
            xmlns="http://www.w3.org/2000/svg" 
            viewBox="0 0 20 20" 
            fill="currentColor" 
            aria-hidden="true"
          >
            <path 
              fill-rule="evenodd" 
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" 
              clip-rule="evenodd" 
            />
          </svg>
        </div>
      </button>
  
      <!-- User dropdown menu -->
      <div
        v-if="isMenuOpen"
        ref="menuRef"
        class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu-button"
        tabindex="-1"
      >
        <!-- User profile link -->
        <router-link
          to="/dashboard/profile"
          class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          role="menuitem"
          tabindex="-1"
          @click="closeMenu"
        >
          <span class="material-icons text-gray-400 mr-2 text-sm">person</span>
          Your Profile
        </router-link>
        
        <!-- Settings link -->
        <router-link
          v-if="authStore.hasPermission('settings.view')"
          to="/dashboard/settings"
          class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          role="menuitem"
          tabindex="-1"
          @click="closeMenu"
        >
          <span class="material-icons text-gray-400 mr-2 text-sm">settings</span>
          Settings
        </router-link>
        
        <!-- Divider -->
        <div class="border-t border-gray-100 my-1"></div>
        
        <!-- Logout button -->
        <button
          @click="handleLogout"
          class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          role="menuitem"
          tabindex="-1"
        >
          <span class="material-icons text-gray-400 mr-2 text-sm">logout</span>
          Sign out
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  
  const router = useRouter();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // Reactive state for menu open/close
  const isMenuOpen = ref(false);
  const menuRef = ref(null);
  
  /**
   * Toggle the dropdown menu
   */
  function toggleMenu() {
    isMenuOpen.value = !isMenuOpen.value;
  }
  
  /**
   * Close the dropdown menu
   */
  function closeMenu() {
    isMenuOpen.value = false;
  }
  
  /**
   * Get user data from auth store
   */
  const user = computed(() => authStore.user);
  
  /**
   * Get user's display name
   */
  const userName = computed(() => {
    return user.value ? user.value.name : 'Loading...';
  });
  
  /**
   * Get user's primary role for display
   */
  const userRole = computed(() => {
    if (!authStore.roles || authStore.roles.length === 0) return 'User';
    return authStore.roles[0]?.name || 'User';
  });
  
  /**
   * Generate user's initials for avatar fallback
   */
  const userInitials = computed(() => {
    if (!user.value || !user.value.name) return '';
    
    return user.value.name
      .split(' ')
      .map(part => part.charAt(0).toUpperCase())
      .slice(0, 2)
      .join('');
  });
  
  /**
   * Handle user logout
   */
  async function handleLogout() {
    try {
      await authStore.logout();
      closeMenu();
      router.push({ name: 'login' });
      alertStore.setAlert({
        type: 'success',
        message: 'You have been successfully logged out.'
      });
    } catch (error) {
      console.error('Logout error:', error);
      alertStore.setAlert({
        type: 'error',
        message: 'There was a problem logging out.'
      });
    }
  }
  
  /**
   * Handle clicks outside the menu to close it
   */
  function handleOutsideClick(event) {
    if (menuRef.value && !menuRef.value.contains(event.target) && isMenuOpen.value) {
      closeMenu();
    }
  }
  
  // Add and remove event listeners for outside clicks
  onMounted(() => {
    document.addEventListener('click', handleOutsideClick);
  });
  
  onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick);
  });
  </script>
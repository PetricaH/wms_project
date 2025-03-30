<!-- resources/js/layouts/DefaultLayout.vue -->

<template>
    <div class="min-h-screen bg-gray-100">
      <!-- Sidebar component -->
      <aside 
        :class="[
          'fixed inset-y-0 left-0 z-10 w-64 bg-gray-800 text-white shadow-lg transition-transform duration-300 transform',
          { '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }
        ]"
      >
        <!-- Logo and brand -->
        <div class="flex items-center justify-center h-16 bg-gray-900">
          <h1 class="text-xl font-bold">WMS System</h1>
        </div>
        
        <!-- Navigation links -->
        <nav class="mt-5 px-2">
          <SidebarNavItem 
            v-for="item in navigationItems" 
            :key="item.name"
            :item="item"
            @click="itemClicked"
          />
        </nav>
      </aside>
  
      <!-- Main content area -->
      <div :class="['flex flex-col min-h-screen', sidebarOpen ? 'md:pl-64' : '']">
        <!-- Top navigation bar -->
        <header class="sticky top-0 z-10 bg-white shadow-sm">
          <div class="flex items-center justify-between h-16 px-4">
            <!-- Sidebar toggle button -->
            <button 
              @click="toggleSidebar" 
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500"
              aria-controls="mobile-menu"
              aria-expanded="false"
            >
              <span class="sr-only">Open main menu</span>
              <svg 
                class="h-6 w-6" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path 
                  v-if="sidebarOpen"
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M6 18L18 6M6 6l12 12" 
                />
                <path 
                  v-else
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M4 6h16M4 12h16M4 18h16" 
                />
              </svg>
            </button>
  
            <!-- Breadcrumbs navigation -->
            <div class="flex-1 flex justify-start ml-4">
              <BreadcrumbNav />
            </div>
  
            <!-- User menu -->
            <div class="flex items-center ml-4">
              <UserMenu />
            </div>
          </div>
        </header>
  
        <!-- Main content -->
        <main class="flex-1 p-6">
          <!-- Display any global alerts/notifications -->
          <AlertMessage 
            v-if="alert.show" 
            :type="alert.type" 
            :message="alert.message"
            @close="closeAlert" 
          />
          
          <!-- Router view for page content -->
          <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </main>
  
        <!-- Footer -->
        <footer class="bg-white py-4 px-6 border-t">
          <div class="text-center text-gray-500 text-sm">
            WMS System &copy; {{ currentYear }}
          </div>
        </footer>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import { useRoute } from 'vue-router';
  import { useAuthStore } from '../stores/auth';
  import { useAlertStore } from '../stores/alert';
  import SidebarNavItem from '../components/navigation/SidebarNavItem.vue';
  import BreadcrumbNav from '../components/navigation/BreadcrumbNav.vue';
  import UserMenu from '../components/navigation/UserMenu.vue';
  import AlertMessage from '../components/shared/AlertMessage.vue';
  
  // Get the current year for the footer copyright
  const currentYear = new Date().getFullYear();
  
  // Responsive sidebar state
  const sidebarOpen = ref(window.innerWidth >= 768); // Open by default on desktop
  const route = useRoute();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // Alert state from store for displaying notifications
  const alert = computed(() => alertStore.alert);
  
  /**
   * Toggle sidebar visibility
   */
  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
  }
  
  /**
   * Close sidebar on mobile after navigation (for better UX)
   */
  function itemClicked() {
    if (window.innerWidth < 768) {
      sidebarOpen.value = false;
    }
  }
  
  /**
   * Close the alert notification
   */
  function closeAlert() {
    alertStore.clearAlert();
  }
  
  /**
   * Handle window resize to auto-show sidebar on desktop
   */
  function handleResize() {
    if (window.innerWidth >= 768) {
      sidebarOpen.value = true;
    } else {
      sidebarOpen.value = false;
    }
  }
  
  // Define navigation items with permission checks
  const navigationItems = computed(() => [
    {
      name: 'Dashboard',
      icon: 'dashboard',
      route: { name: 'dashboard' },
      visible: true // Always visible
    },
    {
      name: 'Inventory',
      icon: 'inventory',
      visible: authStore.hasPermission('inventory.view'),
      children: [
        {
          name: 'Products',
          route: { name: 'products' },
          visible: authStore.hasPermission('products.view')
        },
        {
          name: 'Stock Levels',
          route: { name: 'inventory' },
          visible: authStore.hasPermission('inventory.view')
        },
        {
          name: 'Stock Adjustments',
          route: { name: 'stock-adjustments' },
          visible: authStore.hasPermission('inventory.adjust')
        }
      ]
    },
    {
      name: 'Warehouse',
      icon: 'warehouse',
      visible: authStore.hasPermission('warehouses.view'),
      children: [
        {
          name: 'Warehouses',
          route: { name: 'warehouses' },
          visible: authStore.hasPermission('warehouses.view')
        },
        {
          name: 'Zones',
          route: { name: 'zones' },
          visible: authStore.hasPermission('zones.view')
        },
        {
          name: 'Bin Locations',
          route: { name: 'bin-locations' },
          visible: authStore.hasPermission('bin-locations.view')
        }
      ]
    },
    {
      name: 'Purchasing',
      icon: 'shopping_cart',
      visible: authStore.hasPermission('purchase-orders.view'),
      children: [
        {
          name: 'Purchase Orders',
          route: { name: 'purchase-orders' },
          visible: authStore.hasPermission('purchase-orders.view')
        },
        {
          name: 'Receiving',
          route: { name: 'receiving' },
          visible: authStore.hasPermission('receiving.view')
        },
        {
          name: 'Suppliers',
          route: { name: 'suppliers' },
          visible: authStore.hasPermission('suppliers.view')
        }
      ]
    },
    {
      name: 'Orders',
      icon: 'receipt',
      visible: authStore.hasPermission('orders.view'),
      children: [
        {
          name: 'All Orders',
          route: { name: 'orders' },
          visible: authStore.hasPermission('orders.view')
        },
        {
          name: 'Picking',
          route: { name: 'picking' },
          visible: authStore.hasPermission('picking.view')
        },
        {
          name: 'Packing',
          route: { name: 'packing' },
          visible: authStore.hasPermission('packing.view')
        },
        {
          name: 'Shipping',
          route: { name: 'shipping' },
          visible: authStore.hasPermission('shipping.view')
        }
      ]
    },
    {
      name: 'Settings',
      icon: 'settings',
      route: { name: 'settings' },
      visible: authStore.hasPermission('settings.view')
    }
  ]);
  
  // Add event listeners for window resize
  onMounted(() => {
    window.addEventListener('resize', handleResize);
  });
  
  onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
  });
  </script>
  
  <style scoped>
  /* Fade transition for page content */
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.2s;
  }
  
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  </style>
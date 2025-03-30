<!-- resources/js/views/binLocations/BinLocationsView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Bin Locations</h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Create button -->
          <router-link 
            v-if="hasPermission('bin-locations.create')"
            to="/dashboard/bin-locations/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add Location
          </router-link>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Warehouse filter -->
          <div>
            <label for="warehouse" class="block text-sm font-medium text-gray-700">Warehouse</label>
            <select
              id="warehouse"
              v-model="filters.warehouse_id"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              @change="warehouseChanged"
            >
              <option value="">All Warehouses</option>
              <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                {{ warehouse.name }}
              </option>
            </select>
          </div>
          
          <!-- Zone filter -->
          <div>
            <label for="zone" class="block text-sm font-medium text-gray-700">Zone</label>
            <select
              id="zone"
              v-model="filters.zone_id"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              :disabled="!filters.warehouse_id"
            >
              <option value="">All Zones</option>
              <option v-for="zone in filteredZones" :key="zone.id" :value="zone.id">
                {{ zone.name }}
              </option>
            </select>
          </div>
          
          <!-- Status filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select
              id="status"
              v-model="filters.is_active"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Statuses</option>
              <option :value="1">Active</option>
              <option :value="0">Inactive</option>
            </select>
          </div>
        </div>
        
        <!-- Second row of filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <!-- Search input -->
          <div class="md:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons text-gray-400 text-sm">search</span>
              </div>
              <input
                id="search"
                v-model="filters.search"
                type="text"
                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                placeholder="Search by name, code, or position"
                @keyup.enter="fetchLocations"
              />
              <div v-if="filters.search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button @click="clearSearch" class="text-gray-400 hover:text-gray-500">
                  <span class="material-icons text-sm">close</span>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Inventory status filter -->
          <div>
            <label for="inventory_status" class="block text-sm font-medium text-gray-700">Inventory Status</label>
            <select
              id="inventory_status"
              v-model="filters.inventory_status"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Locations</option>
              <option value="has_inventory">Has Inventory</option>
              <option value="empty">Empty</option>
              <option value="full">Full</option>
            </select>
          </div>
        </div>
        
        <!-- Filter actions -->
        <div class="mt-4 flex justify-end">
          <button
            @click="resetFilters"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Reset Filters
          </button>
          <button
            @click="fetchLocations"
            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Apply Filters
          </button>
        </div>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <!-- Bin Locations table -->
      <div v-else-if="locations.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Location
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Zone / Warehouse
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Position
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Capacity
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Inventory
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="location in locations" :key="location.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-medium text-gray-900">{{ location.name }}</div>
                  <div class="text-sm text-gray-500">{{ location.code }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ location.zone?.name || 'Unknown Zone' }}</div>
                  <div class="text-sm text-gray-500">{{ location.zone?.warehouse?.name || 'Unknown Warehouse' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatPosition(location.position) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ location.capacity ? `${location.capacity} units` : 'Unlimited' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <!-- Utilization bar -->
                    <div class="w-24 bg-gray-200 rounded-full h-2.5 mr-2">
                      <div 
                        class="h-2.5 rounded-full" 
                        :class="getUtilizationColorClass(location)"
                        :style="{ width: `${getUtilizationPercentage(location)}%` }"
                      ></div>
                    </div>
                    <span class="text-sm text-gray-700">
                      {{ location.inventory_count || 0 }} items
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  >
                    {{ location.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-3">
                    <!-- View inventory button -->
                    <router-link 
                      :to="{ name: 'inventory', query: { location_id: location.id } }"
                      class="text-blue-600 hover:text-blue-900"
                      title="View Inventory"
                    >
                      <span class="material-icons text-sm">inventory_2</span>
                    </router-link>
                    
                    <!-- Edit button -->
                    <router-link 
                      v-if="hasPermission('bin-locations.edit')"
                      :to="{ name: 'bin-location-edit', params: { id: location.id } }"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Edit"
                    >
                      <span class="material-icons text-sm">edit</span>
                    </router-link>
                    
                    <!-- Delete button -->
                    <button 
                      v-if="hasPermission('bin-locations.delete')"
                      @click="confirmDelete(location)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete"
                    >
                      <span class="material-icons text-sm">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <!-- Results info -->
            <div>
              <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ pagination.from }}</span>
                to
                <span class="font-medium">{{ pagination.to }}</span>
                of
                <span class="font-medium">{{ pagination.total }}</span>
                results
              </p>
            </div>
            
            <!-- Page navigation -->
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Previous page button -->
                <button
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
                >
                  <span class="sr-only">Previous</span>
                  <span class="material-icons text-sm">chevron_left</span>
                </button>
                
                <!-- Page numbers -->
                <template v-for="page in paginationPages" :key="page">
                  <!-- Ellipsis -->
                  <span
                    v-if="page === '...'"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                  >
                    ...
                  </span>
                  
                  <!-- Page button -->
                  <button
                    v-else
                    @click="changePage(page)"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium"
                    :class="[
                      page === pagination.current_page
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>
                </template>
                
                <!-- Next page button -->
                <button
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                  :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
                >
                  <span class="sr-only">Next</span>
                  <span class="material-icons text-sm">chevron_right</span>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty state -->
      <div v-else class="bg-white p-8 rounded-lg shadow text-center">
        <div class="text-gray-500 mb-4">
          <span class="material-icons text-6xl">location_on</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No bin locations found</h3>
        <p class="text-gray-500 mb-6">
          {{ filters.warehouse_id || filters.zone_id || filters.search || filters.is_active 
             ? 'Try adjusting your filters' 
             : 'Add a bin location to get started' }}
        </p>
        <div v-if="hasPermission('bin-locations.create') && !filters.warehouse_id && !filters.zone_id && !filters.search && !filters.is_active">
          <router-link 
            to="/dashboard/bin-locations/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add your first bin location
          </router-link>
        </div>
      </div>
      
      <!-- Delete confirmation dialog -->
      <div 
        v-if="showDeleteDialog" 
        class="fixed z-10 inset-0 overflow-y-auto"
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div 
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
            aria-hidden="true"
            @click="showDeleteDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <span class="material-icons text-red-600">warning</span>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Delete Bin Location
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete "{{ locationToDelete?.name }}"? This action cannot be undone.
                    </p>
                    <p v-if="locationToDelete?.inventory_count > 0" class="text-sm text-red-500 mt-2 font-medium">
                      Warning: This location contains {{ locationToDelete.inventory_count }} inventory items. Deleting it will also remove all inventory records.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="deleteLocation"
                :disabled="isSubmitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSubmitting ? 'Deleting...' : 'Delete' }}
              </button>
              <button
                @click="showDeleteDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted, watch } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Router and stores
  const route = useRoute();
  const router = useRouter();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // State variables
  const loading = ref(true);
  const locations = ref([]);
  const warehouses = ref([]);
  const zones = ref([]);
  const isSubmitting = ref(false);
  
  // Dialog state
  const showDeleteDialog = ref(false);
  const locationToDelete = ref(null);
  
  // Filter state
  const filters = reactive({
    search: '',
    warehouse_id: '',
    zone_id: '',
    is_active: '',
    inventory_status: '',
    page: 1
  });
  
  // Pagination state
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0
  });
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Get filtered zones based on selected warehouse
   */
  const filteredZones = computed(() => {
    if (!filters.warehouse_id) return zones.value;
    
    return zones.value.filter(zone => zone.warehouse_id === parseInt(filters.warehouse_id));
  });
  
  /**
   * Fetch bin locations from API
   */
  async function fetchLocations() {
    loading.value = true;
    
    try {
      // Build query params for API request
      const params = { ...filters };
      
      // Make API request
      const response = await axios.get('/api/bin-locations', { params });
      
      // Update component state with response data
      locations.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total
      };
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching bin locations:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load bin locations.');
      
      // Set empty data state
      locations.value = [];
      pagination.value = {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0
      };
      
      loading.value = false;
    }
  }
  
  /**
   * Fetch warehouses from API
   */
  async function fetchWarehouses() {
    try {
      const response = await axios.get('/api/warehouses', {
        params: { is_active: 1 }
      });
      
      warehouses.value = response.data;
    } catch (error) {
      console.error('Error fetching warehouses:', error);
      alertStore.setErrorAlert('Failed to load warehouses.');
      warehouses.value = [];
    }
  }
  
  /**
   * Fetch zones from API
   */
  async function fetchZones() {
    try {
      const params = {};
      if (filters.warehouse_id) {
        params.warehouse_id = filters.warehouse_id;
      }
      
      const response = await axios.get('/api/zones', {
        params: { ...params, is_active: 1 }
      });
      
      zones.value = response.data;
    } catch (error) {
      console.error('Error fetching zones:', error);
      alertStore.setErrorAlert('Failed to load zones.');
      zones.value = [];
    }
  }
  
  /**
   * Format position data to a readable string
   * @param {Object|string} position - Position data (can be a JSON string or object)
   * @returns {string} Formatted position string
   */
  function formatPosition(position) {
    if (!position) return 'No position data';
    
    try {
      // Parse position if it's a string
      const posData = typeof position === 'string' ? JSON.parse(position) : position;
      
      // Format position components
      const components = [];
      if (posData.aisle) components.push(`Aisle: ${posData.aisle}`);
      if (posData.bay) components.push(`Bay: ${posData.bay}`);
      if (posData.level) components.push(`Level: ${posData.level}`);
      
      return components.length > 0 ? components.join(', ') : 'No position data';
    } catch (error) {
      console.error('Error parsing position data:', error);
      return 'Invalid position data';
    }
  }
  
  /**
   * Calculate utilization percentage
   * @param {Object} location - Bin location object
   * @returns {number} Utilization percentage (0-100)
   */
  function getUtilizationPercentage(location) {
    if (!location.capacity || location.capacity <= 0) return 0;
    
    const inventory = location.inventory_count || 0;
    const percentage = (inventory / location.capacity) * 100;
    
    return Math.min(percentage, 100); // Cap at 100%
  }
  
  /**
   * Get CSS color class for utilization bar
   * @param {Object} location - Bin location object
   * @returns {string} CSS class for color
   */
  function getUtilizationColorClass(location) {
    const percentage = getUtilizationPercentage(location);
    
    if (percentage === 0) return 'bg-gray-300';
    if (percentage < 50) return 'bg-green-500';
    if (percentage < 80) return 'bg-yellow-500';
    return 'bg-red-500';
  }
  
  /**
   * Change current page and fetch updated data
   * @param {number} page - Page number to navigate to
   */
  function changePage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    
    filters.page = page;
    fetchLocations();
  }
  
  /**
   * Clear search field
   */
  function clearSearch() {
    filters.search = '';
    fetchLocations();
  }
  
  /**
   * Reset all filters to default values
   */
  function resetFilters() {
    Object.assign(filters, {
      search: '',
      warehouse_id: '',
      zone_id: '',
      is_active: '',
      inventory_status: '',
      page: 1
    });
    
    fetchLocations();
  }
  
  /**
   * Handle warehouse selection change
   * Reset zone filter when warehouse changes
   */
  function warehouseChanged() {
    // Reset zone selection when warehouse changes
    filters.zone_id = '';
    
    // Fetch zones for the selected warehouse
    fetchZones();
  }
  
  /**
   * Open delete confirmation dialog
   * @param {Object} location - Bin location to delete
   */
  function confirmDelete(location) {
    locationToDelete.value = location;
    showDeleteDialog.value = true;
  }
  
  /**
   * Delete bin location
   */
  async function deleteLocation() {
    if (!locationToDelete.value) return;
    
    isSubmitting.value = true;
    
    try {
      await axios.delete(`/api/bin-locations/${locationToDelete.value.id}`);
      
      // Show success notification
      alertStore.setSuccessAlert(`Bin location "${locationToDelete.value.name}" has been deleted.`);
      
      // Close dialog and refresh data
      showDeleteDialog.value = false;
      locationToDelete.value = null;
      await fetchLocations();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error deleting bin location:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete bin location.');
      
      isSubmitting.value = false;
    }
  }
  
  /**
   * Calculate pagination pages to display
   * Displays current page, first and last pages, and pages around current page
   */
  const paginationPages = computed(() => {
    const currentPage = pagination.value.current_page;
    const lastPage = pagination.value.last_page;
    
    // If only a few pages, show all
    if (lastPage <= 7) {
      return Array.from({ length: lastPage }, (_, i) => i + 1);
    }
    
    // Otherwise show a subset with ellipses
    const pages = [];
    
    // Always include first page
    pages.push(1);
    
    // Add ellipsis if needed
    if (currentPage > 3) {
      pages.push('...');
    }
    
    // Add pages around current page
    const start = Math.max(2, currentPage - 1);
    const end = Math.min(lastPage - 1, currentPage + 1);
    
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    
    // Add ellipsis if needed
    if (currentPage < lastPage - 2) {
      pages.push('...');
    }
    
    // Always include last page
    if (lastPage > 1) {
      pages.push(lastPage);
    }
    
    return pages;
  });
  
  // Watch for warehouse filter changes to update zones
  watch(() => filters.warehouse_id, (newValue) => {
    if (newValue) {
      fetchZones();
    }
  });
  
  // Initialize component
  onMounted(async () => {
    // Fetch warehouses first for filter dropdowns
    await fetchWarehouses();
    
    // Apply query parameters from URL
    const queryWarehouseId = parseInt(route.query.warehouse_id);
    const queryZoneId = parseInt(route.query.zone_id);
    
    if (queryWarehouseId) {
      filters.warehouse_id = queryWarehouseId;
      
      // Fetch zones for the selected warehouse
      await fetchZones();
      
      if (queryZoneId) {
        filters.zone_id = queryZoneId;
      }
    } else {
      // Fetch zones with default params
      await fetchZones();
    }
    
    // Then fetch bin locations with params from URL
    await fetchLocations();
  });
  </script>
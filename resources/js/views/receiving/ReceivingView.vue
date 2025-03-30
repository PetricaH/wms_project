<!-- resources/js/views/receiving/ReceivingView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Receiving</h1>
      </div>
      
      <!-- Filters and search -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Search input -->
          <div>
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
                placeholder="Search by PO #, supplier"
                @keyup.enter="fetchPurchaseOrders"
              />
            </div>
          </div>
          
          <!-- Warehouse filter -->
          <div>
            <label for="warehouse" class="block text-sm font-medium text-gray-700">Warehouse</label>
            <select
              id="warehouse"
              v-model="filters.warehouse_id"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Warehouses</option>
              <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                {{ warehouse.name }}
              </option>
            </select>
          </div>
          
          <!-- Supplier filter -->
          <div>
            <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
            <select
              id="supplier"
              v-model="filters.supplier_id"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Suppliers</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.name }}
              </option>
            </select>
          </div>
          
          <!-- Status filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select
              id="status"
              v-model="filters.status"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Statuses</option>
              <option value="awaiting_approval,approved,sent,confirmed">Open POs</option>
              <option value="awaiting_approval">Awaiting Approval</option>
              <option value="approved">Approved</option>
              <option value="sent">Sent to Supplier</option>
              <option value="confirmed">Confirmed</option>
              <option value="partially_received">Partially Received</option>
              <option value="fully_received">Fully Received</option>
              <option value="closed">Closed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        
        <!-- Date filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <!-- Date range type -->
          <div>
            <label for="date_range_type" class="block text-sm font-medium text-gray-700">Date Range</label>
            <select
              id="date_range_type"
              v-model="dateRangeType"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="order_date">Order Date</option>
              <option value="expected_delivery_date">Expected Delivery</option>
              <option value="received_date">Received Date</option>
            </select>
          </div>
          
          <!-- Start date -->
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700">From</label>
            <input
              id="start_date"
              v-model="filters.start_date"
              type="date"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            />
          </div>
          
          <!-- End date -->
          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700">To</label>
            <input
              id="end_date"
              v-model="filters.end_date"
              type="date"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            />
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
            @click="fetchPurchaseOrders"
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
      
      <!-- Stats cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Expected today card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <span class="material-icons text-white">calendar_today</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Expected Today</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.expectedToday }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('expected_today')"
                class="font-medium text-blue-600 hover:text-blue-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Ready to receive card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <span class="material-icons text-white">inventory</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Ready to Receive</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.readyToReceive }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('ready_to_receive')"
                class="font-medium text-green-600 hover:text-green-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Partially received card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <span class="material-icons text-white">sync</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Partially Received</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.partiallyReceived }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('partially_received')"
                class="font-medium text-yellow-600 hover:text-yellow-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Overdue card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                <span class="material-icons text-white">warning</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Overdue</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.overdue }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('overdue')"
                class="font-medium text-red-600 hover:text-red-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Purchase Orders Data -->
      <div v-if="!loading && purchaseOrders.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                PO #
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Supplier
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Warehouse
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Expected Delivery
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Items
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
            <tr v-for="po in purchaseOrders" :key="po.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-blue-600">{{ po.po_number }}</div>
                <div class="text-sm text-gray-500">
                  {{ formatDate(po.order_date) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ po.supplier?.name || 'Unknown' }}</div>
                <div v-if="po.supplier_reference" class="text-xs text-gray-500">
                  Ref: {{ po.supplier_reference }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ po.warehouse?.name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div 
                  class="text-sm"
                  :class="isOverdue(po) ? 'text-red-600 font-medium' : 'text-gray-900'"
                >
                  {{ formatDate(po.expected_delivery_date) }}
                </div>
                <div v-if="isOverdue(po)" class="text-xs text-red-500">
                  {{ getDaysOverdue(po) }} days overdue
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="text-sm text-gray-900">
                    {{ po.items_count || 0 }} items
                  </div>
                  
                  <!-- Receipt progress -->
                  <div v-if="po.status === 'partially_received'" class="ml-2 flex items-center">
                    <span class="text-xs text-gray-500">
                      ({{ po.received_percentage || 0 }}%)
                    </span>
                    <div class="w-16 bg-gray-200 rounded-full h-1.5 ml-1">
                      <div 
                        class="bg-green-500 h-1.5 rounded-full" 
                        :style="{ width: `${po.received_percentage || 0}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getPOStatusClass(po.status)"
                >
                  {{ formatPOStatus(po.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-3">
                  <!-- View button -->
                  <router-link
                    :to="`/dashboard/purchase-orders/${po.id}`"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    <span class="material-icons text-sm">visibility</span>
                  </router-link>
                  
                  <!-- Receive button -->
                  <router-link
                    v-if="canReceivePO(po) && hasPermission('receiving.create')"
                    :to="`/dashboard/receiving/${po.id}`"
                    class="text-green-600 hover:text-green-900"
                    title="Receive Items"
                  >
                    <span class="material-icons text-sm">inventory</span>
                  </router-link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
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
      <div v-else-if="!loading" class="bg-white p-8 rounded-lg shadow text-center">
        <div class="text-gray-500 mb-4">
          <span class="material-icons text-6xl">inventory</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No purchase orders found</h3>
        <p class="text-gray-500 mb-6">
          {{ hasAppliedFilters ? 'Try adjusting your filters' : 'Create a purchase order to start receiving inventory' }}
        </p>
        <div v-if="hasPermission('purchase-orders.create') && !hasAppliedFilters">
          <router-link 
            to="/dashboard/purchase-orders/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Create Purchase Order
          </router-link>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted, watch } from 'vue';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Stores
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // State variables
  const loading = ref(true);
  const purchaseOrders = ref([]);
  const warehouses = ref([]);
  const suppliers = ref([]);
  const stats = ref({
    expectedToday: 0,
    readyToReceive: 0,
    partiallyReceived: 0,
    overdue: 0
  });
  
  // Date range filter type
  const dateRangeType = ref('expected_delivery_date');
  
  // Filter state
  const filters = reactive({
    search: '',
    warehouse_id: '',
    supplier_id: '',
    status: '',
    start_date: '',
    end_date: '',
    date_range_type: 'expected_delivery_date',
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
   * Check if any filters have been applied
   */
  const hasAppliedFilters = computed(() => {
    return filters.search || 
           filters.warehouse_id || 
           filters.supplier_id || 
           filters.status || 
           filters.start_date || 
           filters.end_date;
  });
  
  /**
   * Fetch purchase orders from API
   */
  async function fetchPurchaseOrders() {
    loading.value = true;
    
    try {
      // Update date range type in filters
      filters.date_range_type = dateRangeType.value;
      
      // Build query params for API request
      const params = { ...filters };
      
      // Make API request
      const response = await axios.get('/api/receiving/purchase-orders', { params });
      
      // Update component state with response data
      purchaseOrders.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total
      };
      
      // Update stats
      stats.value = response.data.stats || {
        expectedToday: 0,
        readyToReceive: 0,
        partiallyReceived: 0,
        overdue: 0
      };
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching purchase orders:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load purchase orders.');
      
      // Set empty data state
      purchaseOrders.value = [];
      pagination.value = {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0
      };
      
      stats.value = {
        expectedToday: 0,
        readyToReceive: 0,
        partiallyReceived: 0,
        overdue: 0
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
   * Fetch suppliers from API
   */
  async function fetchSuppliers() {
    try {
      const response = await axios.get('/api/suppliers', {
        params: { is_active: 1 }
      });
      
      suppliers.value = response.data;
    } catch (error) {
      console.error('Error fetching suppliers:', error);
      alertStore.setErrorAlert('Failed to load suppliers.');
      suppliers.value = [];
    }
  }
  
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
   * Format purchase order status to readable string
   * @param {string} status - PO status from API
   * @returns {string} Formatted status string
   */
  function formatPOStatus(status) {
    if (!status) return 'Unknown';
    
    // Convert snake_case to Title Case
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Get CSS class for purchase order status badge
   * @param {string} status - PO status
   * @returns {string} CSS classes for the status badge
   */
  function getPOStatusClass(status) {
    const statusClasses = {
      'draft': 'bg-gray-100 text-gray-800',
      'awaiting_approval': 'bg-yellow-100 text-yellow-800',
      'approved': 'bg-blue-100 text-blue-800',
      'sent': 'bg-indigo-100 text-indigo-800',
      'confirmed': 'bg-purple-100 text-purple-800',
      'partially_received': 'bg-yellow-100 text-yellow-800',
      'fully_received': 'bg-green-100 text-green-800',
      'closed': 'bg-gray-100 text-gray-800',
      'cancelled': 'bg-red-100 text-red-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Check if a purchase order is overdue
   * @param {Object} po - Purchase order object
   * @returns {boolean} True if overdue
   */
  function isOverdue(po) {
    if (!po.expected_delivery_date || po.status === 'fully_received' || po.status === 'closed' || po.status === 'cancelled') {
      return false;
    }
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const expectedDate = new Date(po.expected_delivery_date);
    expectedDate.setHours(0, 0, 0, 0);
    
    return expectedDate < today;
  }
  
  /**
   * Get number of days a purchase order is overdue
   * @param {Object} po - Purchase order object
   * @returns {number} Days overdue
   */
  function getDaysOverdue(po) {
    if (!isOverdue(po)) return 0;
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const expectedDate = new Date(po.expected_delivery_date);
    expectedDate.setHours(0, 0, 0, 0);
    
    const diffTime = Math.abs(today - expectedDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays;
  }
  
  /**
   * Check if purchase order can be received
   * @param {Object} po - Purchase order object
   * @returns {boolean} True if can be received
   */
  function canReceivePO(po) {
    const receivableStatuses = ['confirmed', 'partially_received'];
    return receivableStatuses.includes(po.status);
  }
  
  /**
   * Apply a quick filter based on predefined criteria
   * @param {string} filterType - Type of quick filter to apply
   */
  function applyQuickFilter(filterType) {
    // Reset filters first
    resetFilters(false);
    
    // Apply specific filter based on type
    switch (filterType) {
      case 'expected_today':
        dateRangeType.value = 'expected_delivery_date';
        filters.start_date = new Date().toISOString().split('T')[0];
        filters.end_date = new Date().toISOString().split('T')[0];
        break;
        
      case 'ready_to_receive':
        filters.status = 'confirmed';
        break;
        
      case 'partially_received':
        filters.status = 'partially_received';
        break;
        
      case 'overdue':
        dateRangeType.value = 'expected_delivery_date';
        filters.end_date = new Date().toISOString().split('T')[0];
        filters.status = 'sent,confirmed,partially_received';
        break;
    }
    
    // Update date range type in filters
    filters.date_range_type = dateRangeType.value;
    
    // Fetch purchase orders with new filters
    fetchPurchaseOrders();
  }
  
  /**
   * Change current page and fetch updated data
   * @param {number} page - Page number to navigate to
   */
  function changePage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    
    filters.page = page;
    fetchPurchaseOrders();
  }
  
  /**
   * Reset all filters to default values
   * @param {boolean} [fetchData=true] - Whether to fetch data after reset
   */
  function resetFilters(fetchData = true) {
    Object.assign(filters, {
      search: '',
      warehouse_id: '',
      supplier_id: '',
      status: '',
      start_date: '',
      end_date: '',
      date_range_type: 'expected_delivery_date',
      page: 1
    });
    
    dateRangeType.value = 'expected_delivery_date';
    
    if (fetchData) {
      fetchPurchaseOrders();
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
  
  // Watch for date range type changes
  watch(dateRangeType, () => {
    filters.date_range_type = dateRangeType.value;
  });
  
  // Initialize component
  onMounted(async () => {
    // Fetch reference data first
    await Promise.all([
      fetchWarehouses(),
      fetchSuppliers(),
    ]);
    
    // Then fetch purchase orders
    await fetchPurchaseOrders();
  });
  </script>
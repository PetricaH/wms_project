<!-- resources/js/views/orders/OrdersView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Orders</h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Create button -->
          <router-link 
            v-if="hasPermission('orders.create')"
            to="/dashboard/orders/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Create Order
          </router-link>
        </div>
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
                placeholder="Search by order #, customer"
                @keyup.enter="fetchOrders"
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
          
          <!-- Status filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select
              id="status"
              v-model="filters.status"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Statuses</option>
              <option value="pending,processing,awaiting_payment,paid">Open Orders</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="awaiting_payment">Awaiting Payment</option>
              <option value="paid">Paid</option>
              <option value="ready_to_pick">Ready to Pick</option>
              <option value="picking">Picking</option>
              <option value="picked">Picked</option>
              <option value="packing">Packing</option>
              <option value="packed">Packed</option>
              <option value="awaiting_shipment">Awaiting Shipment</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
              <option value="returned">Returned</option>
              <option value="completed">Completed</option>
              <option value="on_hold">On Hold</option>
            </select>
          </div>
          
          <!-- Payment status filter -->
          <div>
            <label for="payment_status" class="block text-sm font-medium text-gray-700">Payment Status</label>
            <select
              id="payment_status"
              v-model="filters.payment_status"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Payment Statuses</option>
              <option value="pending">Pending</option>
              <option value="authorized">Authorized</option>
              <option value="paid">Paid</option>
              <option value="partially_refunded">Partially Refunded</option>
              <option value="fully_refunded">Fully Refunded</option>
              <option value="failed">Failed</option>
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
              <option value="due_date">Due Date</option>
              <option value="shipped_date">Shipped Date</option>
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
            @click="fetchOrders"
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
        <!-- New orders card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <span class="material-icons text-white">shopping_cart</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">New Orders</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.newOrders }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('new_orders')"
                class="font-medium text-blue-600 hover:text-blue-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Ready to pick card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <span class="material-icons text-white">list_alt</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Ready to Pick</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.readyToPick }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('ready_to_pick')"
                class="font-medium text-green-600 hover:text-green-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Ready to ship card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <span class="material-icons text-white">local_shipping</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Ready to Ship</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.readyToShip }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('ready_to_ship')"
                class="font-medium text-yellow-600 hover:text-yellow-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Shipped today card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                <span class="material-icons text-white">inventory_2</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Shipped Today</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.shippedToday }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('shipped_today')"
                class="font-medium text-purple-600 hover:text-purple-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Orders Data -->
      <div v-if="!loading && orders.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Order #
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Warehouse
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Items
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Total
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
            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-blue-600">{{ order.order_number }}</div>
                <div v-if="order.external_order_id" class="text-xs text-gray-500">
                  Ref: {{ order.external_order_id }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ order.customer_name }}</div>
                <div class="text-sm text-gray-500">{{ order.customer_email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(order.order_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ order.warehouse?.name || 'Not assigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ order.items_count || 0 }} items
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(order.total_amount) }}
                <div v-if="order.payment_status !== 'paid'" class="text-xs font-medium" :class="getPaymentStatusClass(order.payment_status)">
                  {{ formatPaymentStatus(order.payment_status) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getOrderStatusClass(order.status)"
                >
                  {{ formatOrderStatus(order.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-3">
                  <!-- View button -->
                  <router-link
                    :to="`/dashboard/orders/${order.id}`"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    <span class="material-icons text-sm">visibility</span>
                  </router-link>
                  
                  <!-- Edit button (if allowed) -->
                  <router-link
                    v-if="canEditOrder(order) && hasPermission('orders.edit')"
                    :to="`/dashboard/orders/${order.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit Order"
                  >
                    <span class="material-icons text-sm">edit</span>
                  </router-link>
                  
                  <!-- Process button (depends on order status) -->
                  <component 
                    :is="getProcessButtonComponent(order)" 
                    v-if="getProcessButtonComponent(order) && hasPermission(getProcessPermission(order))"
                    :to="getProcessButtonRoute(order)"
                    class="text-green-600 hover:text-green-900"
                    :title="getProcessButtonTitle(order)"
                  >
                    <span class="material-icons text-sm">{{ getProcessButtonIcon(order) }}</span>
                  </component>
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
          <span class="material-icons text-6xl">shopping_bag</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No orders found</h3>
        <p class="text-gray-500 mb-6">
          {{ hasAppliedFilters ? 'Try adjusting your filters' : 'Create an order to get started' }}
        </p>
        <div v-if="hasPermission('orders.create') && !hasAppliedFilters">
          <router-link 
            to="/dashboard/orders/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Create Order
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
  const orders = ref([]);
  const warehouses = ref([]);
  const stats = ref({
    newOrders: 0,
    readyToPick: 0,
    readyToShip: 0,
    shippedToday: 0
  });
  
  // Date range filter type
  const dateRangeType = ref('order_date');
  
  // Filter state
  const filters = reactive({
    search: '',
    warehouse_id: '',
    status: '',
    payment_status: '',
    start_date: '',
    end_date: '',
    date_range_type: 'order_date',
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
           filters.status || 
           filters.payment_status || 
           filters.start_date || 
           filters.end_date;
  });
  
  /**
   * Fetch orders from API
   */
  async function fetchOrders() {
    loading.value = true;
    
    try {
      // Update date range type in filters
      filters.date_range_type = dateRangeType.value;
      
      // Build query params for API request
      const params = { ...filters };
      
      // Make API request
      const response = await axios.get('/api/orders', { params });
      
      // Update component state with response data
      orders.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total
      };
      
      // Update stats
      stats.value = response.data.stats || {
        newOrders: 0,
        readyToPick: 0,
        readyToShip: 0,
        shippedToday: 0
      };
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching orders:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load orders.');
      
      // Set empty data state
      orders.value = [];
      pagination.value = {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0
      };
      
      stats.value = {
        newOrders: 0,
        readyToPick: 0,
        readyToShip: 0,
        shippedToday: 0
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
   * Format currency amount with appropriate symbol
   * @param {number} amount - Amount to format
   * @returns {string} Formatted currency string
   */
  function formatCurrency(amount) {
    if (amount === undefined || amount === null) return 'N/A';
    
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  }
  
  /**
   * Format order status to readable string
   * @param {string} status - Order status from API
   * @returns {string} Formatted status string
   */
  function formatOrderStatus(status) {
    if (!status) return 'Unknown';
    
    // Convert snake_case to Title Case
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Format payment status to readable string
   * @param {string} status - Payment status from API
   * @returns {string} Formatted status string
   */
  function formatPaymentStatus(status) {
    if (!status) return 'Unknown';
    
    // Convert snake_case to Title Case
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Get CSS class for order status badge
   * @param {string} status - Order status
   * @returns {string} CSS classes for the status badge
   */
  function getOrderStatusClass(status) {
    const statusClasses = {
      'pending': 'bg-yellow-100 text-yellow-800',
      'processing': 'bg-blue-100 text-blue-800',
      'awaiting_payment': 'bg-orange-100 text-orange-800',
      'paid': 'bg-green-100 text-green-800',
      'ready_to_pick': 'bg-indigo-100 text-indigo-800',
      'picking': 'bg-indigo-100 text-indigo-800',
      'picked': 'bg-purple-100 text-purple-800',
      'packing': 'bg-purple-100 text-purple-800',
      'packed': 'bg-purple-100 text-purple-800',
      'awaiting_shipment': 'bg-blue-100 text-blue-800',
      'shipped': 'bg-green-100 text-green-800',
      'delivered': 'bg-green-100 text-green-800',
      'cancelled': 'bg-red-100 text-red-800',
      'returned': 'bg-red-100 text-red-800',
      'completed': 'bg-green-100 text-green-800',
      'on_hold': 'bg-gray-100 text-gray-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Get CSS class for payment status text
   * @param {string} status - Payment status
   * @returns {string} CSS classes for the status text
   */
  function getPaymentStatusClass(status) {
    const statusClasses = {
      'pending': 'text-yellow-600',
      'authorized': 'text-blue-600',
      'paid': 'text-green-600',
      'partially_refunded': 'text-orange-600',
      'fully_refunded': 'text-red-600',
      'failed': 'text-red-600'
    };
    
    return statusClasses[status] || 'text-gray-600';
  }
  
  /**
   * Check if an order can be edited
   * @param {Object} order - Order object
   * @returns {boolean} True if order can be edited
   */
  function canEditOrder(order) {
    const editableStatuses = ['pending', 'processing', 'awaiting_payment', 'on_hold'];
    return editableStatuses.includes(order.status);
  }
  
  /**
   * Get the component to use for the process button
   * @param {Object} order - Order object
   * @returns {string|null} Component name for the button, or null if no button
   */
  function getProcessButtonComponent(order) {
    if (!order) return null;
    
    // Different statuses need different actions
    switch (order.status) {
      case 'ready_to_pick':
      case 'picking':
        return 'router-link'; // Navigate to picking
        
      case 'picked':
      case 'packing':
        return 'router-link'; // Navigate to packing
        
      case 'packed':
      case 'awaiting_shipment':
        return 'router-link'; // Navigate to shipping
        
      default:
        return null; // No process button
    }
  }
  
  /**
   * Get the route for the process button
   * @param {Object} order - Order object
   * @returns {string|Object} Route for the button
   */
  function getProcessButtonRoute(order) {
    if (!order) return '#';
    
    // Different statuses go to different routes
    switch (order.status) {
      case 'ready_to_pick':
      case 'picking':
        return { name: 'picking', query: { order_id: order.id } };
        
      case 'picked':
      case 'packing':
        return { name: 'packing', query: { order_id: order.id } };
        
      case 'packed':
      case 'awaiting_shipment':
        return { name: 'shipping', query: { order_id: order.id } };
        
      default:
        return '#';
    }
  }
  
  /**
   * Get the title for the process button
   * @param {Object} order - Order object
   * @returns {string} Button title
   */
  function getProcessButtonTitle(order) {
    if (!order) return '';
    
    // Different statuses have different titles
    switch (order.status) {
      case 'ready_to_pick':
      case 'picking':
        return 'Pick Order';
        
      case 'picked':
      case 'packing':
        return 'Pack Order';
        
      case 'packed':
      case 'awaiting_shipment':
        return 'Ship Order';
        
      default:
        return '';
    }
  }
  
  /**
   * Get the icon for the process button
   * @param {Object} order - Order object
   * @returns {string} Material icon name
   */
  function getProcessButtonIcon(order) {
    if (!order) return '';
    
    // Different statuses have different icons
    switch (order.status) {
      case 'ready_to_pick':
      case 'picking':
        return 'content_paste';
        
      case 'picked':
      case 'packing':
        return 'inventory_2';
        
      case 'packed':
      case 'awaiting_shipment':
        return 'local_shipping';
        
      default:
        return '';
    }
  }
  
  /**
   * Get the required permission for the process button
   * @param {Object} order - Order object
   * @returns {string} Permission slug
   */
  function getProcessPermission(order) {
    if (!order) return '';
    
    // Different statuses require different permissions
    switch (order.status) {
      case 'ready_to_pick':
      case 'picking':
        return 'picking.create';
        
      case 'picked':
      case 'packing':
        return 'packing.create';
        
      case 'packed':
      case 'awaiting_shipment':
        return 'shipping.create';
        
      default:
        return '';
    }
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
      case 'new_orders':
        filters.status = 'pending,processing,awaiting_payment,paid';
        break;
        
      case 'ready_to_pick':
        filters.status = 'ready_to_pick';
        break;
        
      case 'ready_to_ship':
        filters.status = 'packed,awaiting_shipment';
        break;
        
      case 'shipped_today':
        dateRangeType.value = 'shipped_date';
        filters.start_date = new Date().toISOString().split('T')[0];
        filters.end_date = new Date().toISOString().split('T')[0];
        filters.status = 'shipped';
        break;
    }
    
    // Update date range type in filters
    filters.date_range_type = dateRangeType.value;
    
    // Fetch orders with new filters
    fetchOrders();
  }
  
  /**
   * Change current page and fetch updated data
   * @param {number} page - Page number to navigate to
   */
  function changePage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    
    filters.page = page;
    fetchOrders();
  }
  
  /**
   * Reset all filters to default values
   * @param {boolean} [fetchData=true] - Whether to fetch data after reset
   */
  function resetFilters(fetchData = true) {
    Object.assign(filters, {
      search: '',
      warehouse_id: '',
      status: '',
      payment_status: '',
      start_date: '',
      end_date: '',
      date_range_type: 'order_date',
      page: 1
    });
    
    dateRangeType.value = 'order_date';
    
    if (fetchData) {
      fetchOrders();
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
    // Fetch warehouses first for filter dropdown
    await fetchWarehouses();
    
    // Then fetch orders
    await fetchOrders();
  });
  </script>
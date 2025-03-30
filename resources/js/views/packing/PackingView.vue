<!-- resources/js/views/packing/PackingView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Order Packing</h1>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else>
        <!-- Orders ready for packing -->
        <div v-if="!activeOrder" class="mb-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Orders Ready for Packing</h2>
          
          <!-- Filter options -->
          <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    placeholder="Order #, Customer name"
                    @keyup.enter="fetchReadyToPack"
                  />
                </div>
              </div>
              
              <div class="flex items-end">
                <button
                  @click="fetchReadyToPack"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="material-icons -ml-1 mr-2 text-sm">filter_list</span>
                  Apply Filters
                </button>
              </div>
            </div>
          </div>
          
          <!-- Orders table -->
          <div v-if="readyToPackOrders.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
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
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Items
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Picker
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Picked At
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="order in readyToPackOrders" :key="order.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-600">{{ order.order_number }}</div>
                    <div class="text-xs text-gray-500">{{ formatDate(order.order_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ order.customer_name }}</div>
                    <div class="text-xs text-gray-500">{{ order.customer_email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800"
                    >
                      {{ formatOrderStatus(order.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ order.items_count }} items
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ order.picked_by_user?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDateTime(order.picked_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="startPacking(order)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      <span class="material-icons">inventory_2</span>
                      <span class="sr-only">Start Packing</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Empty state -->
          <div v-else class="bg-white p-8 rounded-lg shadow text-center">
            <div class="text-gray-500 mb-4">
              <span class="material-icons text-6xl">inventory_2</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No orders ready for packing</h3>
            <p class="text-gray-500 mb-6">
              All picked orders have been packed or there are no orders currently picked
            </p>
          </div>
        </div>
        
        <!-- Active packing interface -->
        <div v-else>
          <!-- Order header -->
          <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
              <div>
                <h2 class="text-lg font-medium text-gray-900">
                  Packing Order: <span class="text-blue-600">{{ activeOrder.order_number }}</span>
                </h2>
                <div class="text-sm text-gray-500">{{ activeOrder.customer_name }}</div>
              </div>
              
              <button
                @click="cancelPacking"
                class="mt-2 sm:mt-0 inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">arrow_back</span>
                Back to Orders
              </button>
            </div>
          </div>
          
          <!-- Packing interface -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order items to pack (left panel) -->
            <div class="lg:col-span-2">
              <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                  <h3 class="text-lg font-medium leading-6 text-gray-900">Items to Pack</h3>
                </div>
                
                <ul role="list" class="divide-y divide-gray-200">
                  <li 
                    v-for="item in orderItems" 
                    :key="item.id" 
                    class="px-4 py-4 sm:px-6 hover:bg-gray-50"
                    :class="{'bg-green-50': item.is_packed}"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                          <img 
                            v-if="item.product?.image_url"
                            :src="item.product.image_url"
                            :alt="item.name"
                            class="h-10 w-10 rounded-md object-cover"
                          />
                          <span v-else class="material-icons text-gray-400">inventory_2</span>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                          <div class="text-sm text-gray-500">
                            SKU: {{ item.sku }}
                            <span v-if="item.lot_number" class="ml-2">Lot: {{ item.lot_number }}</span>
                          </div>
                        </div>
                      </div>
                      
                      <div class="flex items-center">
                        <div class="text-sm text-gray-500 mr-4">
                          <span class="font-medium">{{ item.quantity_picked }}</span> 
                          of 
                          <span class="font-medium">{{ item.quantity }}</span>
                          picked
                        </div>
                        
                        <div v-if="item.is_packed" class="flex items-center text-green-600">
                          <span class="material-icons text-sm mr-1">check_circle</span>
                          <span class="text-sm font-medium">Packed</span>
                        </div>
                        <button
                          v-else
                          @click="packItem(item)"
                          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                          :disabled="isSubmitting"
                        >
                          <span class="material-icons -ml-1 mr-1 text-sm">add_circle</span>
                          Pack
                        </button>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
            
            <!-- Packing summary (right panel) -->
            <div>
              <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                  <h3 class="text-lg font-medium leading-6 text-gray-900">Packing Summary</h3>
                </div>
                
                <div class="px-4 py-5 sm:p-6">
                  <!-- Progress -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Packing Progress
                    </label>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                      <div 
                        class="bg-green-600 h-2.5 rounded-full" 
                        :style="{ width: `${packingProgress}%` }"
                      ></div>
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                      {{ packedItemsCount }} of {{ orderItems.length }} items packed
                    </div>
                  </div>
                  
                  <!-- Package selection -->
                  <div class="mb-4">
                    <label for="package_type" class="block text-sm font-medium text-gray-700 mb-1">
                      Packaging Type
                    </label>
                    <select
                      id="package_type"
                      v-model="packingData.package_type"
                      class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    >
                      <option value="">Select Packaging</option>
                      <option value="small_box">Small Box</option>
                      <option value="medium_box">Medium Box</option>
                      <option value="large_box">Large Box</option>
                      <option value="envelope">Envelope</option>
                      <option value="poly_mailer">Poly Mailer</option>
                      <option value="custom">Custom</option>
                    </select>
                  </div>
                  
                  <!-- Package dimensions -->
                  <div v-if="packingData.package_type === 'custom'" class="mb-4 grid grid-cols-3 gap-3">
                    <div>
                      <label for="length" class="block text-sm font-medium text-gray-700">Length</label>
                      <div class="mt-1">
                        <input
                          id="length"
                          v-model.number="packingData.dimensions.length"
                          type="number"
                          min="0"
                          step="0.1"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        />
                      </div>
                    </div>
                    <div>
                      <label for="width" class="block text-sm font-medium text-gray-700">Width</label>
                      <div class="mt-1">
                        <input
                          id="width"
                          v-model.number="packingData.dimensions.width"
                          type="number"
                          min="0"
                          step="0.1"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        />
                      </div>
                    </div>
                    <div>
                      <label for="height" class="block text-sm font-medium text-gray-700">Height</label>
                      <div class="mt-1">
                        <input
                          id="height"
                          v-model.number="packingData.dimensions.height"
                          type="number"
                          min="0"
                          step="0.1"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Package weight -->
                  <div class="mb-4">
                    <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                    <div class="mt-1">
                      <input
                        id="weight"
                        v-model.number="packingData.weight"
                        type="number"
                        min="0"
                        step="0.01"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      />
                    </div>
                  </div>
                  
                  <!-- Notes -->
                  <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <div class="mt-1">
                      <textarea
                        id="notes"
                        v-model="packingData.notes"
                        rows="3"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      ></textarea>
                    </div>
                  </div>
                  
                  <!-- Complete packing button -->
                  <button
                    @click="completePacking"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    :disabled="!canCompletePacking || isSubmitting"
                  >
                    <span v-if="isSubmitting" class="mr-2">
                      <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    </span>
                    Complete Packing
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, reactive, onMounted } from 'vue';
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
  const isSubmitting = ref(false);
  const warehouses = ref([]);
  const readyToPackOrders = ref([]);
  const activeOrder = ref(null);
  const orderItems = ref([]);
  
  // Filters for ready to pack orders
  const filters = reactive({
    search: '',
    warehouse_id: '',
    status: 'picked',
    page: 1
  });
  
  // Packing data for active order
  const packingData = reactive({
    package_type: '',
    dimensions: {
      length: 0,
      width: 0,
      height: 0
    },
    weight: 0,
    notes: ''
  });
  
  /**
   * Check if user has a specific permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Format date to readable string
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
   * Format date and time to readable string
   */
  function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    
    // Convert to JS Date object
    const date = new Date(dateTimeString);
    
    // Format with browser's locale date/time format
    return date.toLocaleString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
  
  /**
   * Format order status to readable string
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
   * Fetch orders ready for packing
   */
  async function fetchReadyToPack() {
    loading.value = true;
    
    try {
      // Add picked status to filter
      filters.status = 'picked';
      
      // Fetch orders with filter
      const response = await axios.get('/api/orders', { params: filters });
      
      readyToPackOrders.value = response.data.data || [];
      loading.value = false;
    } catch (error) {
      console.error('Error fetching ready to pack orders:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load orders ready for packing.');
      readyToPackOrders.value = [];
      loading.value = false;
    }
  }
  
  /**
   * Fetch warehouses for filter dropdown
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
   * Start packing an order
   */
  async function startPacking(order) {
    loading.value = true;
    
    try {
      // Set active order
      activeOrder.value = order;
      
      // Update order status to packing
      await axios.post(`/api/orders/${order.id}/status`, {
        status: 'packing'
      });
      
      // Fetch order items for packing
      const response = await axios.get(`/api/orders/${order.id}/items`, {
        params: { with_picking: true }
      });
      
      // Add is_packed property to track UI state
      orderItems.value = response.data.map(item => ({
        ...item,
        is_packed: item.status === 'packed'
      }));
      
      loading.value = false;
    } catch (error) {
      console.error('Error starting packing process:', error);
      alertStore.setApiErrorAlert(error, 'Failed to start packing process.');
      
      // Revert to order list
      activeOrder.value = null;
      orderItems.value = [];
      loading.value = false;
    }
  }
  
  /**
   * Pack a single item
   */
  async function packItem(item) {
    if (item.is_packed) return;
    
    isSubmitting.value = true;
    
    try {
      // Send packing request to API
      await axios.post(`/api/packing/item`, {
        order_id: activeOrder.value.id,
        order_item_id: item.id,
        quantity: item.quantity_picked
      });
      
      // Update item status in UI
      item.is_packed = true;
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error packing item:', error);
      alertStore.setApiErrorAlert(error, 'Failed to pack item.');
      isSubmitting.value = false;
    }
  }
  
  /**
   * Complete packing the entire order
   */
  async function completePacking() {
    if (!canCompletePacking.value) return;
    
    isSubmitting.value = true;
    
    try {
      // Send complete packing request
      await axios.post(`/api/packing/orders/${activeOrder.value.id}/complete`, {
        package_type: packingData.package_type,
        dimensions: packingData.dimensions,
        weight: packingData.weight,
        notes: packingData.notes
      });
      
      // Show success message
      alertStore.setSuccessAlert(`Order ${activeOrder.value.order_number} has been packed successfully.`);
      
      // Reset and return to order list
      cancelPacking();
      
      // Refresh order list
      await fetchReadyToPack();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error completing packing:', error);
      alertStore.setApiErrorAlert(error, 'Failed to complete packing process.');
      isSubmitting.value = false;
    }
  }
  
  /**
   * Cancel packing and return to order list
   */
  function cancelPacking() {
    activeOrder.value = null;
    orderItems.value = [];
    
    // Reset packing data
    Object.assign(packingData, {
      package_type: '',
      dimensions: {
        length: 0,
        width: 0,
        height: 0
      },
      weight: 0,
      notes: ''
    });
  }
  
  /**
   * Calculate packing progress as percentage
   */
  const packingProgress = computed(() => {
    if (!orderItems.value.length) return 0;
    
    const packedItems = orderItems.value.filter(item => item.is_packed).length;
    return Math.round((packedItems / orderItems.value.length) * 100);
  });
  
  /**
   * Count of packed items
   */
  const packedItemsCount = computed(() => {
    return orderItems.value.filter(item => item.is_packed).length;
  });
  
  /**
   * Check if all requirements are met to complete packing
   */
  const canCompletePacking = computed(() => {
    // All items must be packed
    const allPacked = orderItems.value.every(item => item.is_packed);
    
    // Package type must be selected
    const hasPackaging = !!packingData.package_type;
    
    // If custom packaging, dimensions must be entered
    const validDimensions = packingData.package_type !== 'custom' || 
      (packingData.dimensions.length > 0 && 
       packingData.dimensions.width > 0 && 
       packingData.dimensions.height > 0);
    
    // Package weight must be entered
    const hasWeight = packingData.weight > 0;
    
    return allPacked && hasPackaging && validDimensions && hasWeight;
  });
  
  // Check if we have an order ID in query params to start with
  onMounted(async () => {
    await fetchWarehouses();
    
    // Check if order ID is provided in query params
    const orderIdParam = route.query.order_id;
    
    if (orderIdParam) {
      try {
        // Fetch the specific order
        const response = await axios.get(`/api/orders/${orderIdParam}`);
        const order = response.data;
        
        // Start packing if the order is in 'picked' status
        if (order.status === 'picked') {
          await startPacking(order);
        } else {
          // If not in correct status, show orders list
          await fetchReadyToPack();
        }
      } catch (error) {
        console.error('Error fetching order:', error);
        alertStore.setApiErrorAlert(error, 'Failed to load order details.');
        await fetchReadyToPack();
      }
    } else {
      // No order ID, show list of orders ready for packing
      await fetchReadyToPack();
    }
    
    loading.value = false;
  });
  </script>
<template>
    <div class="p-6 bg-gray-50 min-h-screen">
      <h1 class="text-2xl font-semibold text-gray-800 mb-6">Order Picking</h1>
  
      <div v-if="isLoading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>
      </div>
  
      <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">{{ error }}</span>
      </div>
  
      <div v-else-if="!selectedOrder" class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-700 mb-4">Orders Ready for Picking</h2>
        <div v-if="ordersToPick.length === 0" class="text-gray-500">
          No orders are currently ready for picking.
        </div>
        <ul v-else class="divide-y divide-gray-200">
          <li v-for="order in ordersToPick" :key="order.id" class="py-4 flex justify-between items-center">
            <div>
              <p class="text-lg font-medium text-blue-600">Order #{{ order.order_number }}</p>
              <p class="text-sm text-gray-600">Customer: {{ order.customer_name }} | Items: {{ order.items_count || 'N/A' }}</p>
              <p class="text-sm text-gray-500">Due Date: {{ formatDate(order.due_date) }}</p>
            </div>
            <button
              @click="startPicking(order.id)"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Start Picking
            </button>
          </li>
        </ul>
      </div>
  
      <div v-else class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-medium text-gray-700">Picking Order #{{ selectedOrder.order_number }}</h2>
           <button
            @click="cancelPicking"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Back to List
          </button>
        </div>
  
        <div v-if="isPickListLoading" class="text-center py-4">Loading pick list...</div>
        <div v-else-if="pickListError" class="text-red-500 py-4">{{ pickListError }}</div>
        <div v-else>
          <h3 class="text-lg font-semibold text-gray-800 mb-3">Pick List:</h3>
          <ul class="divide-y divide-gray-200 mb-6">
            <li v-for="(item, index) in pickListItems" :key="item.id || index" class="py-4">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                <div>
                  <p class="font-medium text-gray-900">{{ item.product_name }} ({{ item.sku }})</p>
                  <p class="text-sm text-gray-500">Location: {{ item.location_code }}</p>
                  <p v-if="item.lot_number" class="text-sm text-gray-500">Lot: {{ item.lot_number }}</p>
                   <p class="text-sm text-gray-500">FIFO Date: {{ formatDate(item.fifo_date) }}</p>
                </div>
                <div class="text-center">
                  <p class="text-sm font-medium text-gray-500">Required</p>
                  <p class="text-lg font-semibold">{{ item.quantity_to_pick }}</p>
                </div>
                <div class="text-center">
                  <label :for="`picked-${item.id || index}`" class="block text-sm font-medium text-gray-700 mb-1">Picked</label>
                   <input
                    :id="`picked-${item.id || index}`"
                    type="number"
                    min="0"
                    :max="item.quantity_to_pick"
                    v-model.number="item.quantity_picked"
                    :disabled="item.is_picked"
                    @change="handleQuantityChange(item)"
                    class="w-20 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
                <div class="text-right">
                   <button
                    @click="confirmPick(item)"
                    :disabled="item.quantity_picked <= 0 || item.is_picked || isSubmitting"
                    :class="[
                      'px-4 py-2 rounded text-white',
                      item.is_picked ? 'bg-green-500 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
                      item.quantity_picked <= 0 || isSubmitting ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                  >
                    {{ item.is_picked ? 'Picked' : 'Confirm Pick' }}
                  </button>
                </div>
              </div>
               <p v-if="item.pickError" class="text-red-500 text-sm mt-1">{{ item.pickError }}</p>
            </li>
          </ul>
  
           <button
            @click="completeOrderPicking"
            :disabled="!allPicksConfirmed || isSubmitting"
            :class="[
              'w-full px-6 py-3 text-white rounded focus:outline-none focus:ring-2 focus:ring-offset-2',
              !allPicksConfirmed || isSubmitting ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
            ]"
          >
            {{ isSubmitting ? 'Completing...' : 'Complete Order Picking' }}
          </button>
          <p v-if="completionError" class="text-red-500 text-center mt-2">{{ completionError }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, computed } from 'vue';
  // Assume apiService and useAlertStore are correctly set up
  // import apiService from '@/services/apiService';
  // import { useAlertStore } from '@/stores/alert';
  
  // --- Mocks/Placeholders (Replace with actual imports) ---
  const apiService = { // Replace with your actual apiService import
    get: async (url) => {
      console.log(`Mock GET: ${url}`);
      if (url === '/api/orders/ready-for-picking') {
        await new Promise(resolve => setTimeout(resolve, 500)); // Simulate network delay
        return { data: [
          { id: 1, order_number: 'SO-001', customer_name: 'Alice Corp', items_count: 3, due_date: '2025-04-10T00:00:00Z' },
          { id: 2, order_number: 'SO-002', customer_name: 'Bob Industries', items_count: 5, due_date: '2025-04-12T00:00:00Z' },
        ]};
      }
      if (url.startsWith('/api/orders/')) { // Assume details / pick-list call
         await new Promise(resolve => setTimeout(resolve, 600));
         const orderId = url.split('/')[3];
         if (orderId === '1') {
             return { data: {
                 id: 1, order_number: 'SO-001',
                 // This structure is assumed based on FIFO requirements
                 pick_list_items: [
                   { id: 101, product_id: 10, sku: 'PROD-A', product_name: 'Product A', quantity_to_pick: 2, location_code: 'A-01-01', fifo_date: '2025-03-01T10:00:00Z', lot_number: 'LOT123' },
                   { id: 102, product_id: 20, sku: 'PROD-B', product_name: 'Product B', quantity_to_pick: 1, location_code: 'B-02-03', fifo_date: '2025-03-05T14:00:00Z' },
                 ]
             }};
         } else {
              return { data: { id: 2, order_number: 'SO-002', pick_list_items: [] } }; // Example empty list
         }
      }
      throw new Error(`Mock endpoint not found: ${url}`);
    },
    post: async (url, data) => {
      console.log(`Mock POST: ${url}`, data);
      await new Promise(resolve => setTimeout(resolve, 400));
       if (url.startsWith('/api/picking/record')) {
          // Simulate potential partial failure or success
          if (Math.random() < 0.1) throw new Error("Simulated pick recording error.");
          return { data: { message: 'Pick recorded successfully' }};
      }
      if (url.startsWith('/api/picking/orders/')) {
           if (Math.random() < 0.1) throw new Error("Simulated completion error.");
          return { data: { message: 'Order picking completed' } };
      }
      throw new Error(`Mock endpoint not found: ${url}`);
    }
  };
  const useAlertStore = () => ({ // Replace with your actual Pinia store import
    show: (alert) => console.log(`Alert: ${alert.message} (${alert.type})`)
  });
  // --- End Mocks ---
  
  const alertStore = useAlertStore();
  
  // --- Component State ---
  const isLoading = ref(false); // Loading indicator for the initial list
  const error = ref(null); // Error message for the initial list fetch
  const ordersToPick = ref([]); // List of orders ready for picking
  const selectedOrder = ref(null); // The order currently being picked
  const isPickListLoading = ref(false); // Loading state for the pick list details
  const pickListError = ref(null); // Error when fetching pick list
  const pickListItems = ref([]); // Items in the pick list for the selected order
  const isSubmitting = ref(false); // Loading state for pick confirmation or completion
  const completionError = ref(null); // Error during the final completion step
  
  // --- Computed Properties ---
  const allPicksConfirmed = computed(() => {
    // Check if every item in the pick list has been marked as picked (is_picked = true)
    return pickListItems.value.length > 0 && pickListItems.value.every(item => item.is_picked);
  });
  
  // --- Methods ---
  
  /**
   * Fetches the list of orders that are ready to be picked.
   */
  const fetchOrdersToPick = async () => {
    isLoading.value = true;
    error.value = null;
    selectedOrder.value = null; // Reset selection when fetching list
    pickListItems.value = []; // Clear old pick list
    try {
      // API Endpoint: GET /api/orders/ready-for-picking
      // Adjust endpoint if your API uses a different status filter like GET /api/orders?status=ready_to_pick
      const response = await apiService.get('/api/orders/ready-for-picking');
      ordersToPick.value = response.data;
    } catch (err) {
      console.error('Error fetching orders to pick:', err);
      error.value = 'Failed to load orders ready for picking. Please try again later.';
      alertStore.show({ message: error.value, type: 'error' });
    } finally {
      isLoading.value = false;
    }
  };
  
  /**
   * Fetches the pick list for a specific order and prepares the UI.
   * @param {number} orderId - The ID of the order to start picking.
   */
  const startPicking = async (orderId) => {
    const order = ordersToPick.value.find(o => o.id === orderId);
    if (!order) return;
  
    selectedOrder.value = order; // Set the selected order details
    isPickListLoading.value = true;
    pickListError.value = null;
    completionError.value = null; // Reset completion error
    pickListItems.value = [];
  
    try {
      // API Endpoint: GET /api/orders/{id}/pick-list
      // This endpoint should return items sorted by FIFO, including location and quantity.
      const response = await apiService.get(`/api/orders/${orderId}/pick-list`); // Or potentially just GET /api/orders/{id} if details include pickable items
      // Initialize pick list items with default state
      pickListItems.value = response.data.pick_list_items.map(item => ({
        ...item,
        quantity_picked: 0, // User input for quantity picked
        is_picked: false, // Flag to track if this specific item pick is confirmed
        pickError: null, // Error specific to this pick item
      }));
       if (pickListItems.value.length === 0) {
          pickListError.value = "This order has no items requiring picking.";
      }
    } catch (err) {
      console.error(`Error fetching pick list for order ${orderId}:`, err);
      pickListError.value = 'Failed to load pick list details. Please try again.';
      alertStore.show({ message: pickListError.value, type: 'error' });
      // Optionally revert to the list view if fetching fails catastrophically
      // selectedOrder.value = null;
    } finally {
      isPickListLoading.value = false;
    }
  };
  
  /**
   * Validates the picked quantity input for an item.
   * @param {object} item - The pick list item being changed.
   */
  const handleQuantityChange = (item) => {
    // Ensure quantity doesn't exceed required and isn't negative
    if (item.quantity_picked > item.quantity_to_pick) {
      item.quantity_picked = item.quantity_to_pick;
    }
    if (item.quantity_picked < 0) {
      item.quantity_picked = 0;
    }
     // Clear previous pick error when user modifies quantity
     item.pickError = null;
  };
  
  /**
   * Confirms the pick for a single item in the list.
   * @param {object} item - The pick list item to confirm.
   */
  const confirmPick = async (item) => {
    if (item.quantity_picked <= 0 || item.quantity_picked > item.quantity_to_pick) {
        item.pickError = "Picked quantity must be greater than 0 and not exceed required quantity.";
        return;
    }
  
    isSubmitting.value = true;
    item.pickError = null; // Reset previous error
  
    try {
      // API Endpoint: POST /api/picking/record
      // Send details like order_item_id (or line id), product_id, location_id, quantity picked, lot_number if applicable
      await apiService.post('/api/picking/record', {
        order_id: selectedOrder.value.id,
        order_item_id: item.id, // Assuming item.id corresponds to order_item_id or similar identifier
        product_id: item.product_id,
        location_id: item.location_id, // Assuming location_id is available or derivable from location_code
        quantity: item.quantity_picked,
        lot_number: item.lot_number, // Include if applicable
        // Potentially add fifo_layer identifier if your API needs it
      });
      item.is_picked = true; // Mark as picked on success
      alertStore.show({ message: `Pick confirmed for ${item.product_name}.`, type: 'success' });
    } catch (err) {
      console.error('Error confirming pick:', err);
      item.pickError = err.response?.data?.message || err.message || 'Failed to confirm pick.';
      alertStore.show({ message: `Error confirming pick for ${item.product_name}: ${item.pickError}`, type: 'error' });
    } finally {
      isSubmitting.value = false;
    }
  };
  
  /**
   * Finalizes the picking process for the entire order.
   */
  const completeOrderPicking = async () => {
    if (!allPicksConfirmed.value) {
      completionError.value = 'All items must be confirmed picked before completing the order.';
      return;
    }
  
    isSubmitting.value = true;
    completionError.value = null;
  
    try {
      // API Endpoint: POST /api/picking/orders/{orderId}/complete
      await apiService.post(`/api/picking/orders/${selectedOrder.value.id}/complete`, {
        // Optionally send summary data if needed by the backend
        // items: pickListItems.value.map(i => ({ id: i.id, quantity_picked: i.quantity_picked }))
      });
      alertStore.show({ message: `Order #${selectedOrder.value.order_number} picking completed successfully.`, type: 'success' });
      // Reset view to the list after successful completion
      await fetchOrdersToPick(); // Refresh the list
    } catch (err) {
      console.error('Error completing order picking:', err);
      completionError.value = err.response?.data?.message || err.message || 'Failed to complete order picking.';
      alertStore.show({ message: completionError.value, type: 'error' });
    } finally {
      isSubmitting.value = false;
    }
  };
  
  /**
   * Returns the user to the list of orders to pick.
   */
  const cancelPicking = () => {
    selectedOrder.value = null;
    pickListItems.value = [];
    pickListError.value = null;
    completionError.value = null;
    // Optionally refresh the list if state might have changed
    // fetchOrdersToPick();
  };
  
  /**
   * Formats a date string (ISO format assumed) to a more readable format.
   * @param {string} dateString - The ISO date string.
   * @returns {string} Formatted date or 'N/A'.
   */
  const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      try {
          const date = new Date(dateString);
          return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
      } catch (e) {
          return dateString; // Return original if parsing fails
      }
  };
  
  
  // --- Lifecycle Hooks ---
  onMounted(() => {
    fetchOrdersToPick(); // Fetch orders when the component is mounted
  });
  
  </script>
  
  <style scoped>
  /* Add any component-specific styles here if needed */
  </style>
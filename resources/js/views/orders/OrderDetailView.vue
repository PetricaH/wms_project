<!-- resources/js/views/orders/OrderDetailView.vue -->

<template>
    <div>
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else>
        <!-- Back button and actions -->
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center">
            <router-link
              to="/dashboard/orders"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-sm">arrow_back</span>
              Back to Orders
            </router-link>
            
            <!-- Order status badge -->
            <span 
              class="ml-4 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
              :class="getOrderStatusClass(order.status)"
            >
              {{ formatOrderStatus(order.status) }}
            </span>
            
            <!-- Payment status badge -->
            <span 
              class="ml-2 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
              :class="getPaymentStatusClass(order.payment_status)"
            >
              {{ formatPaymentStatus(order.payment_status) }}
            </span>
          </div>
          
          <!-- Action buttons -->
          <div class="flex space-x-2">
            <!-- Process button (depends on order status) -->
            <component 
              :is="getProcessButtonComponent(order)" 
              v-if="getProcessButtonComponent(order) && hasPermission(getProcessPermission(order))"
              :to="getProcessButtonRoute(order)"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <span class="material-icons -ml-1 mr-2 text-sm">{{ getProcessButtonIcon(order) }}</span>
              {{ getProcessButtonTitle(order) }}
            </component>
            
            <!-- Edit button -->
            <router-link 
              v-if="canEditOrder(order) && hasPermission('orders.edit')"
              :to="`/dashboard/orders/${order.id}/edit`"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">edit</span>
              Edit
            </router-link>
            
            <!-- Cancel button -->
            <button 
              v-if="canCancelOrder(order) && hasPermission('orders.cancel')"
              @click="confirmCancel"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">cancel</span>
              Cancel Order
            </button>
          </div>
        </div>
        
        <!-- Order header card -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
          <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
            <div>
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Order #{{ order.order_number }}
              </h3>
              <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Placed on {{ formatDate(order.order_date) }}
              </p>
            </div>
            <div v-if="order.external_order_id" class="text-sm text-gray-500">
              External Reference: {{ order.external_order_id }}
            </div>
          </div>
          
          <!-- Order details grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 sm:p-6">
            <!-- Customer information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Customer</h4>
              <div class="text-sm text-gray-900 font-medium mb-1">{{ order.customer_name }}</div>
              <div class="text-sm text-gray-500 mb-1">{{ order.customer_email }}</div>
              <div v-if="order.customer_phone" class="text-sm text-gray-500">{{ order.customer_phone }}</div>
            </div>
            
            <!-- Shipping address -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Shipping Address</h4>
              <div v-if="order.shipping_address" class="text-sm text-gray-900 whitespace-pre-line">{{ formatAddress(order.shipping_address) }}</div>
              <div v-else class="text-sm text-gray-500">No shipping address provided</div>
            </div>
            
            <!-- Billing address -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Billing Address</h4>
              <div v-if="order.billing_address" class="text-sm text-gray-900 whitespace-pre-line">{{ formatAddress(order.billing_address) }}</div>
              <div v-else class="text-sm text-gray-500">No billing address provided</div>
            </div>
          </div>
          
          <!-- Order fulfillment info -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 sm:px-6 sm:pb-6 sm:pt-0">
            <!-- Warehouse information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Fulfillment</h4>
              <div class="text-sm text-gray-900 font-medium mb-1">
                Warehouse: {{ order.warehouse?.name || 'Not assigned' }}
              </div>
              <div class="text-sm text-gray-500 mb-1">
                Shipping Method: {{ order.shipping_method || 'Not specified' }}
              </div>
              <div v-if="order.tracking_number" class="text-sm text-gray-900">
                Tracking: <span class="font-medium">{{ order.tracking_number }}</span>
                <span v-if="order.carrier" class="text-gray-500">({{ order.carrier }})</span>
              </div>
            </div>
            
            <!-- Payment information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Payment</h4>
              <div class="text-sm text-gray-900 font-medium mb-1">
                Method: {{ order.payment_method || 'Not specified' }}
              </div>
              <div v-if="order.payment_reference" class="text-sm text-gray-500 mb-1">
                Reference: {{ order.payment_reference }}
              </div>
              <div class="text-sm" :class="getPaymentStatusTextClass(order.payment_status)">
                Status: {{ formatPaymentStatus(order.payment_status) }}
              </div>
            </div>
            
            <!-- Dates information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Dates</h4>
              <div class="text-sm text-gray-500 mb-1">
                Order Date: <span class="text-gray-900">{{ formatDate(order.order_date) }}</span>
              </div>
              <div v-if="order.due_date" class="text-sm text-gray-500 mb-1">
                Due By: <span class="text-gray-900">{{ formatDate(order.due_date) }}</span>
              </div>
              <div v-if="order.shipped_date" class="text-sm text-gray-500 mb-1">
                Shipped: <span class="text-gray-900">{{ formatDate(order.shipped_date) }}</span>
              </div>
              <div v-if="order.delivered_date" class="text-sm text-gray-500">
                Delivered: <span class="text-gray-900">{{ formatDate(order.delivered_date) }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Order items section -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Order Items
            </h3>
          </div>
          
          <!-- Items table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Product
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    SKU
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Qty
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Unit Price
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in order.items" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <!-- Product image (if available) -->
                      <div class="h-10 w-10 flex-shrink-0">
                        <div v-if="item.product?.image_url" class="h-10 w-10 rounded-md overflow-hidden">
                          <img :src="item.product.image_url" :alt="item.name" class="h-full w-full object-cover">
                        </div>
                        <div v-else class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                          <span class="material-icons text-gray-400">inventory_2</span>
                        </div>
                      </div>
                      
                      <!-- Product name and details -->
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                        <div v-if="item.description" class="text-sm text-gray-500 truncate" style="max-width: 300px;">
                          {{ item.description }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.sku }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(item.unit_price) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.total) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="getItemStatusClass(item.status)"
                    >
                      {{ formatItemStatus(item.status) }}
                    </span>
                  </td>
                </tr>
                
                <!-- Summary row for order totals -->
                <tr class="bg-gray-50">
                  <td colspan="2" class="px-6 py-4 whitespace-nowrap"></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ getTotalQuantity() }} items
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    Subtotal:
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(order.subtotal) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap"></td>
                </tr>
                
                <!-- Tax row -->
                <tr v-if="order.tax_amount" class="bg-gray-50">
                  <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    Tax:
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(order.tax_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap"></td>
                </tr>
                
                <!-- Shipping row -->
                <tr v-if="order.shipping_amount" class="bg-gray-50">
                  <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    Shipping:
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(order.shipping_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap"></td>
                </tr>
                
                <!-- Discount row -->
                <tr v-if="order.discount_amount" class="bg-gray-50">
                  <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    Discount:
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    -{{ formatCurrency(order.discount_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap"></td>
                </tr>
                
                <!-- Total row -->
                <tr class="bg-gray-50">
                  <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                    Total:
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                    {{ formatCurrency(order.total_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Order history and notes -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Order history timeline -->
          <div class="md:col-span-2 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Order Timeline
              </h3>
            </div>
            
            <div class="p-4 sm:p-6">
              <div v-if="orderHistory.length === 0" class="text-center py-6 text-gray-500">
                No history records available
              </div>
              
              <ol v-else class="relative border-l border-gray-200 ml-3">
                <li v-for="(event, index) in orderHistory" :key="index" class="mb-6 ml-6">
                  <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white" :class="getTimelineIconClass(event.type)">
                    <span class="material-icons text-white text-sm">{{ getTimelineIcon(event.type) }}</span>
                  </span>
                  <div class="flex flex-col sm:flex-row sm:items-start justify-between">
                    <div>
                      <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">
                        {{ event.title }}
                      </h3>
                      <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
                        {{ formatDateTime(event.timestamp) }}
                      </time>
                      <p v-if="event.description" class="text-sm font-normal text-gray-500">
                        {{ event.description }}
                      </p>
                    </div>
                    <div v-if="event.user" class="mt-2 sm:mt-0 sm:ml-4 text-xs text-gray-500">
                      by {{ event.user }}
                    </div>
                  </div>
                </li>
              </ol>
            </div>
          </div>
          
          <!-- Notes and additional information -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Notes
              </h3>
            </div>
            
            <div class="p-4 sm:p-6">
              <!-- Customer notes -->
              <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Customer Notes</h4>
                <div v-if="order.customer_notes" class="p-3 bg-gray-50 rounded-md text-sm text-gray-900">
                  {{ order.customer_notes }}
                </div>
                <div v-else class="text-sm text-gray-500">
                  No customer notes
                </div>
              </div>
              
              <!-- Internal notes -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Internal Notes</h4>
                <div v-if="order.internal_notes" class="p-3 bg-gray-50 rounded-md text-sm text-gray-900">
                  {{ order.internal_notes }}
                </div>
                <div v-else-if="hasPermission('orders.edit')" class="text-sm text-gray-500">
                  No internal notes
                </div>
                
                <!-- Add note form -->
                <div v-if="hasPermission('orders.edit')" class="mt-4">
                  <textarea
                    v-model="newNote"
                    rows="3"
                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    placeholder="Add an internal note..."
                  ></textarea>
                  <div class="mt-2 flex justify-end">
                    <button
                      @click="addInternalNote"
                      :disabled="!newNote.trim() || isSubmitting"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                      <span v-if="isSubmitting" class="mr-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </span>
                      Add Note
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Cancel order confirmation dialog -->
        <div 
          v-if="showCancelDialog" 
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
              @click="showCancelDialog = false"
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
                      Cancel Order
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Are you sure you want to cancel this order? This action cannot be undone.
                      </p>
                      
                      <div class="mt-4">
                        <label for="cancel_reason" class="block text-sm font-medium text-gray-700">Reason for cancellation</label>
                        <select
                          id="cancel_reason"
                          v-model="cancelReason"
                          class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                          required
                        >
                          <option value="" disabled>Select a reason</option>
                          <option value="customer_request">Customer Request</option>
                          <option value="out_of_stock">Out of Stock</option>
                          <option value="payment_issue">Payment Issue</option>
                          <option value="shipping_issue">Shipping Issue</option>
                          <option value="other">Other</option>
                        </select>
                        
                        <div v-if="cancelReason === 'other'" class="mt-3">
                          <label for="cancel_note" class="block text-sm font-medium text-gray-700">Additional notes</label>
                          <textarea
                            id="cancel_note"
                            v-model="cancelNote"
                            rows="3"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                          ></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Dialog actions -->
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  @click="cancelOrder"
                  :disabled="!cancelReason || isSubmitting"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                >
                  <span v-if="isSubmitting" class="mr-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  {{ isSubmitting ? 'Cancelling...' : 'Cancel Order' }}
                </button>
                <button
                  @click="showCancelDialog = false"
                  type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                  Go Back
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted } from 'vue';
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
  const order = ref({
    items: []
  });
  const orderHistory = ref([]);
  const isSubmitting = ref(false);
  
  // Notes
  const newNote = ref('');
  
  // Cancel order state
  const showCancelDialog = ref(false);
  const cancelReason = ref('');
  const cancelNote = ref('');
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Fetch order details from API
   */
  async function fetchOrderDetails() {
    loading.value = true;
    
    try {
      const orderId = route.params.id;
      const response = await axios.get(`/api/orders/${orderId}`);
      
      // Update order data
      order.value = response.data.order;
      
      // Fetch order history
      await fetchOrderHistory();
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching order details:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load order details.');
      
      // Provide a way to return to orders list
      loading.value = false;
    }
  }
  
  /**
   * Fetch order history
   */
  async function fetchOrderHistory() {
    try {
      const orderId = route.params.id;
      const response = await axios.get(`/api/orders/${orderId}/history`);
      
      orderHistory.value = response.data;
    } catch (error) {
      console.error('Error fetching order history:', error);
      orderHistory.value = [];
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
   * Format date and time to readable string
   * @param {string} dateTimeString - Date/time string to format
   * @returns {string} Formatted date and time
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
   * Format order item status to readable string
   * @param {string} status - Item status from API
   * @returns {string} Formatted status string
   */
  function formatItemStatus(status) {
    if (!status) return 'Unknown';
    
    // Convert snake_case to Title Case
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
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
   * Format address object to readable string
   * @param {Object|string} address - Address object or string
   * @returns {string} Formatted address
   */
  function formatAddress(address) {
    if (!address) return 'No address provided';
    
    // If address is already a string, return it
    if (typeof address === 'string') return address;
    
    // If address is an object, format it
    try {
      const lines = [];
      
      if (address.name) lines.push(address.name);
      if (address.company) lines.push(address.company);
      if (address.address_line1) lines.push(address.address_line1);
      if (address.address_line2) lines.push(address.address_line2);
      
      const cityStateZip = [];
      if (address.city) cityStateZip.push(address.city);
      if (address.state) cityStateZip.push(address.state);
      if (address.postal_code) cityStateZip.push(address.postal_code);
      
      if (cityStateZip.length > 0) {
        lines.push(cityStateZip.join(', '));
      }
      
      if (address.country) lines.push(address.country);
      
      return lines.join('\n');
    } catch (error) {
      console.error('Error formatting address:', error);
      return JSON.stringify(address);
    }
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
   * Get CSS class for payment status badge
   * @param {string} status - Payment status
   * @returns {string} CSS classes for the status badge
   */
  function getPaymentStatusClass(status) {
    const statusClasses = {
      'pending': 'bg-yellow-100 text-yellow-800',
      'authorized': 'bg-blue-100 text-blue-800',
      'paid': 'bg-green-100 text-green-800',
      'partially_refunded': 'bg-orange-100 text-orange-800',
      'fully_refunded': 'bg-red-100 text-red-800',
      'failed': 'bg-red-100 text-red-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Get CSS class for payment status text
   * @param {string} status - Payment status
   * @returns {string} CSS classes for the status text
   */
  function getPaymentStatusTextClass(status) {
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
   * Get CSS class for item status badge
   * @param {string} status - Item status
   * @returns {string} CSS classes for the status badge
   */
  function getItemStatusClass(status) {
    const statusClasses = {
      'pending': 'bg-yellow-100 text-yellow-800',
      'allocated': 'bg-blue-100 text-blue-800',
      'partially_allocated': 'bg-blue-100 text-blue-800',
      'picking': 'bg-indigo-100 text-indigo-800',
      'picked': 'bg-purple-100 text-purple-800',
      'packed': 'bg-purple-100 text-purple-800',
      'shipped': 'bg-green-100 text-green-800',
      'backordered': 'bg-orange-100 text-orange-800',
      'cancelled': 'bg-red-100 text-red-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Get CSS class for timeline icon background
   * @param {string} type - Timeline event type
   * @returns {string} CSS background class
   */
  function getTimelineIconClass(type) {
    const typeClasses = {
      'status_change': 'bg-blue-500',
      'payment': 'bg-green-500',
      'shipping': 'bg-purple-500',
      'note': 'bg-gray-500',
      'cancellation': 'bg-red-500',
      'error': 'bg-red-500'
    };
    
    return typeClasses[type] || 'bg-gray-500';
  }
  
  /**
   * Get Material icon for timeline event
   * @param {string} type - Timeline event type
   * @returns {string} Icon name
   */
  function getTimelineIcon(type) {
    const typeIcons = {
      'status_change': 'sync',
      'payment': 'payments',
      'shipping': 'local_shipping',
      'note': 'note',
      'cancellation': 'cancel',
      'error': 'error'
    };
    
    return typeIcons[type] || 'event';
  }
  
  /**
   * Calculate total quantity of items in order
   * @returns {number} Total quantity
   */
  function getTotalQuantity() {
    if (!order.value.items || order.value.items.length === 0) return 0;
    
    return order.value.items.reduce((total, item) => total + item.quantity, 0);
  }
  
  /**
   * Check if an order can be edited
   * @param {Object} order - Order object
   * @returns {boolean} True if order can be edited
   */
  function canEditOrder(order) {
    if (!order) return false;
    
    const editableStatuses = ['pending', 'processing', 'awaiting_payment', 'on_hold'];
    return editableStatuses.includes(order.status);
  }
  
  /**
   * Check if an order can be cancelled
   * @param {Object} order - Order object
   * @returns {boolean} True if order can be cancelled
   */
  function canCancelOrder(order) {
    if (!order) return false;
    
    const cancellableStatuses = [
      'pending', 'processing', 'awaiting_payment', 'paid', 
      'ready_to_pick', 'on_hold'
    ];
    
    return cancellableStatuses.includes(order.status);
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
   * Show cancel confirmation dialog
   */
  function confirmCancel() {
    cancelReason.value = '';
    cancelNote.value = '';
    showCancelDialog.value = true;
  }
  
  /**
   * Cancel the order
   */
  async function cancelOrder() {
    if (!cancelReason.value) {
      alertStore.setErrorAlert('Please select a cancellation reason.');
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      await axios.post(`/api/orders/${order.value.id}/cancel`, {
        reason: cancelReason.value,
        notes: cancelNote.value
      });
      
      // Show success notification
      alertStore.setSuccessAlert('Order has been cancelled successfully.');
      
      // Close dialog and refresh data
      showCancelDialog.value = false;
      
      // Refresh order data
      await fetchOrderDetails();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error cancelling order:', error);
      alertStore.setApiErrorAlert(error, 'Failed to cancel order.');
      
      isSubmitting.value = false;
    }
  }
  
  /**
   * Add internal note to order
   */
  async function addInternalNote() {
    if (!newNote.value.trim()) return;
    
    isSubmitting.value = true;
    
    try {
      await axios.post(`/api/orders/${order.value.id}/notes`, {
        note: newNote.value
      });
      
      // Update order notes
      order.value.internal_notes = order.value.internal_notes 
        ? `${order.value.internal_notes}\n\n${newNote.value}`
        : newNote.value;
      
      // Clear note input
      newNote.value = '';
      
      // Refresh order history
      await fetchOrderHistory();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error adding note:', error);
      alertStore.setApiErrorAlert(error, 'Failed to add note.');
      
      isSubmitting.value = false;
    }
  }
  
  // Initialize component
  onMounted(() => {
    fetchOrderDetails();
  });
  </script>
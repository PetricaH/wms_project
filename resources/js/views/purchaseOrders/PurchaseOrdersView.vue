<!-- resources/js/views/purchaseOrders/PurchaseOrdersView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Purchase Orders</h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Export button -->
          <button 
            v-if="hasPermission('purchase-orders.export')"
            @click="exportPurchaseOrders"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">download</span>
            Export
          </button>
          
          <!-- Create button -->
          <router-link 
            v-if="hasPermission('purchase-orders.create')"
            to="/dashboard/purchase-orders/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Create Purchase Order
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
              <option value="draft">Draft</option>
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
        <!-- Drafts -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-gray-500 rounded-md p-3">
                <span class="material-icons text-white">edit_note</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Drafts</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.drafts }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('draft')"
                class="font-medium text-gray-600 hover:text-gray-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Awaiting Approval -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <span class="material-icons text-white">pending_actions</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Awaiting Approval</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.awaitingApproval }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('awaiting_approval')"
                class="font-medium text-yellow-600 hover:text-yellow-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- In Progress -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <span class="material-icons text-white">sync</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.inProgress }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('in_progress')"
                class="font-medium text-blue-600 hover:text-blue-500"
              >
                View all
              </button>
            </div>
          </div>
        </div>
        
        <!-- Completed -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <span class="material-icons text-white">check_circle</span>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                  <dd>
                    <div class="text-lg font-medium text-gray-900">{{ stats.completed }}</div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
              <button 
                @click="applyQuickFilter('completed')"
                class="font-medium text-green-600 hover:text-green-500"
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
                Date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Expected Delivery
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
            <tr v-for="po in purchaseOrders" :key="po.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-blue-600">{{ po.po_number }}</div>
                <div v-if="po.supplier_reference" class="text-xs text-gray-500">
                  Ref: {{ po.supplier_reference }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ po.supplier?.name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ po.warehouse?.name || 'Unknown' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(po.order_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div 
                  class="text-sm"
                  :class="isOverdue(po) ? 'text-red-600 font-medium' : 'text-gray-500'"
                >
                  {{ formatDate(po.expected_delivery_date) }}
                </div>
                <div v-if="isOverdue(po)" class="text-xs text-red-500">
                  {{ getDaysOverdue(po) }} days overdue
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(po.total_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getStatusClass(po.status)"
                >
                  {{ formatStatus(po.status) }}
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
                  
                  <!-- Edit button (if allowed) -->
                  <router-link
                    v-if="canEdit(po) && hasPermission('purchase-orders.edit')"
                    :to="`/dashboard/purchase-orders/${po.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit"
                  >
                    <span class="material-icons text-sm">edit</span>
                  </router-link>
                  
                  <!-- Receive button -->
                  <router-link
                    v-if="canReceive(po) && hasPermission('receiving.create')"
                    :to="`/dashboard/receiving/${po.id}`"
                    class="text-green-600 hover:text-green-900"
                    title="Receive Items"
                  >
                    <span class="material-icons text-sm">inventory</span>
                  </router-link>
                  
                  <!-- Action button based on status -->
                  <button
                    v-if="getStatusAction(po) && hasPermission(getStatusActionPermission(po))"
                    @click="executeStatusAction(po)"
                    :class="getStatusActionClass(po)"
                    :title="getStatusActionTitle(po)"
                  >
                    <span class="material-icons text-sm">{{ getStatusActionIcon(po) }}</span>
                  </button>
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
          <span class="material-icons text-6xl">receipt_long</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No purchase orders found</h3>
        <p class="text-gray-500 mb-6">
          {{ hasAppliedFilters ? 'Try adjusting your filters' : 'Create a purchase order to get started' }}
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
      
      <!-- Status action confirmation dialog -->
      <div 
        v-if="showActionDialog" 
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
            @click="showActionDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                  <span class="material-icons text-blue-600">{{ actionDialogIcon }}</span>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    {{ actionDialogTitle }}
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      {{ actionDialogMessage }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="confirmStatusAction"
                :disabled="isActionSubmitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isActionSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isActionSubmitting ? 'Processing...' : 'Confirm' }}
              </button>
              <button
                @click="showActionDialog = false"
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
    drafts: 0,
    awaitingApproval: 0,
    inProgress: 0,
    completed: 0
  });
  
  // Date range filter type
  const dateRangeType = ref('order_date');
  
  // Filter state
  const filters = reactive({
    search: '',
    warehouse_id: '',
    supplier_id: '',
    status: '',
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
  
  // Status action dialog state
  const showActionDialog = ref(false);
  const selectedPO = ref(null);
  const actionType = ref('');
  const isActionSubmitting = ref(false);
  
  // Computed properties for action dialog
  const actionDialogTitle = computed(() => {
    switch (actionType.value) {
      case 'approve':
        return 'Approve Purchase Order';
      case 'send':
        return 'Send Purchase Order to Supplier';
      case 'cancel':
        return 'Cancel Purchase Order';
      case 'close':
        return 'Close Purchase Order';
      default:
        return 'Confirm Action';
    }
  });
  
  const actionDialogMessage = computed(() => {
    if (!selectedPO.value) return '';
    
    switch (actionType.value) {
      case 'approve':
        return `Are you sure you want to approve purchase order ${selectedPO.value.po_number}?`;
      case 'send':
        return `Are you sure you want to mark purchase order ${selectedPO.value.po_number} as sent to the supplier?`;
      case 'cancel':
        return `Are you sure you want to cancel purchase order ${selectedPO.value.po_number}? This action cannot be undone.`;
      case 'close':
        return `Are you sure you want to close purchase order ${selectedPO.value.po_number}? No more items can be received once closed.`;
      default:
        return 'Are you sure you want to perform this action?';
    }
  });
  
  const actionDialogIcon = computed(() => {
    switch (actionType.value) {
      case 'approve':
        return 'check_circle';
      case 'send':
        return 'send';
      case 'cancel':
        return 'cancel';
      case 'close':
        return 'inventory';
      default:
        return 'help';
    }
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
      const response = await axios.get('/api/purchase-orders', { params });
      
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
        drafts: 0,
        awaitingApproval: 0,
        inProgress: 0,
        completed: 0
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
        drafts: 0,
        awaitingApproval: 0,
        inProgress: 0,
        completed: 0
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
   * Format purchase order status to readable string
   * @param {string} status - PO status from API
   * @returns {string} Formatted status string
   */
  function formatStatus(status) {
    if (!status) return 'Unknown';
    
    // Convert snake_case to Title Case
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Get CSS class for PO status badge
   * @param {string} status - PO status
   * @returns {string} CSS classes for the status badge
   */
  function getStatusClass(status) {
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
   * Check if a purchase order can be edited
   * @param {Object} po - Purchase order object
   * @returns {boolean} True if can be edited
   */
  function canEdit(po) {
    const editableStatuses = ['draft', 'awaiting_approval'];
    return editableStatuses.includes(po.status);
  }
  
  /**
   * Check if purchase order can be received
   * @param {Object} po - Purchase order object
   * @returns {boolean} True if can be received
   */
  function canReceive(po) {
    const receivableStatuses = ['confirmed', 'partially_received'];
    return receivableStatuses.includes(po.status);
  }
  
  /**
   * Get available status action for a purchase order
   * @param {Object} po - Purchase order object
   * @returns {string|null} Action type or null if no action available
   */
  function getStatusAction(po) {
    if (!po) return null;
    
    // Available actions based on current status
    switch (po.status) {
      case 'draft':
        return 'approve';
      case 'awaiting_approval':
        return 'approve';
      case 'approved':
        return 'send';
      case 'fully_received':
        return 'close';
      case 'partially_received':
        return 'close';
      default:
        return null;
    }
  }
  
  /**
   * Get required permission for status action
   * @param {Object} po - Purchase order object
   * @returns {string} Permission slug
   */
  function getStatusActionPermission(po) {
    const action = getStatusAction(po);
    
    if (!action) return '';
    
    const permissionMap = {
      'approve': 'purchase-orders.approve',
      'send': 'purchase-orders.send',
      'close': 'purchase-orders.close',
      'cancel': 'purchase-orders.cancel'
    };
    
    return permissionMap[action] || '';
  }
  
  /**
   * Get icon for status action button
   * @param {Object} po - Purchase order object
   * @returns {string} Material icon name
   */
  function getStatusActionIcon(po) {
    const action = getStatusAction(po);
    
    if (!action) return '';
    
    const iconMap = {
      'approve': 'check_circle',
      'send': 'send',
      'close': 'inventory',
      'cancel': 'cancel'
    };
    
    return iconMap[action] || '';
  }
  
  /**
   * Get title for status action button
   * @param {Object} po - Purchase order object
   * @returns {string} Button title
   */
  function getStatusActionTitle(po) {
    const action = getStatusAction(po);
    
    if (!action) return '';
    
    const titleMap = {
      'approve': 'Approve',
      'send': 'Send to Supplier',
      'close': 'Close PO',
      'cancel': 'Cancel'
    };
    
    return titleMap[action] || '';
  }
  
  /**
   * Get CSS class for status action button
   * @param {Object} po - Purchase order object
   * @returns {string} CSS classes
   */
  function getStatusActionClass(po) {
    const action = getStatusAction(po);
    
    if (!action) return '';
    
    const classMap = {
      'approve': 'text-green-600 hover:text-green-900',
      'send': 'text-blue-600 hover:text-blue-900',
      'close': 'text-gray-600 hover:text-gray-900',
      'cancel': 'text-red-600 hover:text-red-900'
    };
    
    return classMap[action] || '';
  }
  
  /**
   * Trigger status action for a purchase order
   * @param {Object} po - Purchase order to perform action on
   */
  function executeStatusAction(po) {
    // Set the PO and action type
    selectedPO.value = po;
    actionType.value = getStatusAction(po);
    
    // Show confirmation dialog
    showActionDialog.value = true;
  }
  
  /**
   * Confirm and execute the status action
   */
  async function confirmStatusAction() {
    if (!selectedPO.value || !actionType.value) {
      showActionDialog.value = false;
      return;
    }
    
    isActionSubmitting.value = true;
    
    try {
      // Make API request based on action type
      await axios.post(`/api/purchase-orders/${selectedPO.value.id}/${actionType.value}`);
      
      // Show success message
      alertStore.setSuccessAlert(`Purchase order ${selectedPO.value.po_number} has been ${actionType.value}d successfully.`);
      
      // Close dialog and refresh POs
      showActionDialog.value = false;
      isActionSubmitting.value = false;
      selectedPO.value = null;
      actionType.value = '';
      
      await fetchPurchaseOrders();
    } catch (error) {
      console.error(`Error ${actionType.value}ing purchase order:`, error);
      alertStore.setApiErrorAlert(error, `Failed to ${actionType.value} purchase order.`);
      
      isActionSubmitting.value = false;
    }
  }
  
  /**
   * Export purchase orders to CSV/Excel
   */
  async function exportPurchaseOrders() {
    try {
      // Use current filters for export
      const params = { 
        ...filters,
        date_range_type: dateRangeType.value,
        export: true
      };
      
      // Request export from API
      const response = await axios.get('/api/purchase-orders/export', { 
        params,
        responseType: 'blob'
      });
      
      // Create download link
      const url = URL.createObjectURL(new Blob([response.data]));
      const a = document.createElement('a');
      a.href = url;
      a.download = `purchase_orders_${new Date().toISOString().split('T')[0]}.csv`;
      a.click();
      
      // Clean up
      URL.revokeObjectURL(url);
      
      // Show success notification
      alertStore.setSuccessAlert('Purchase orders exported successfully.');
    } catch (error) {
      console.error('Error exporting purchase orders:', error);
      alertStore.setErrorAlert('Failed to export purchase orders. Please try again.');
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
      case 'draft':
        filters.status = 'draft';
        break;
        
      case 'awaiting_approval':
        filters.status = 'awaiting_approval';
        break;
        
      case 'in_progress':
        filters.status = 'approved,sent,confirmed,partially_received';
        break;
        
      case 'completed':
        filters.status = 'fully_received,closed';
        break;
    }
    
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
      date_range_type: 'order_date',
      page: 1
    });
    
    dateRangeType.value = 'order_date';
    
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
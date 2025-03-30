<!-- resources/js/views/products/ProductsView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Import button -->
          <button 
            v-if="hasPermission('products.import')"
            @click="showImportDialog = true"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">upload_file</span>
            Import
          </button>
          
          <!-- Export button -->
          <button 
            v-if="hasPermission('products.export')"
            @click="exportProducts"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">download</span>
            Export
          </button>
          
          <!-- Create button -->
          <router-link 
            v-if="hasPermission('products.create')"
            to="/dashboard/products/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add Product
          </router-link>
        </div>
      </div>
      
      <!-- Filters and search -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search input -->
          <div class="col-span-1 md:col-span-2">
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
                placeholder="Search by name, SKU, or UPC"
                @keyup.enter="fetchProducts"
              />
              <div v-if="filters.search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button @click="clearSearch" class="text-gray-400 hover:text-gray-500">
                  <span class="material-icons text-sm">close</span>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Category filter -->
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
            <select
              id="category"
              v-model="filters.category_id"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
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
        
        <!-- Filter actions -->
        <div class="mt-4 flex justify-end">
          <button
            @click="resetFilters"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Reset Filters
          </button>
          <button
            @click="fetchProducts"
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
      
      <!-- Products table -->
      <div v-else-if="products.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <!-- Image column -->
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Image
              </th>
              
              <!-- Product info column -->
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Product
              </th>
              
              <!-- SKU column -->
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                SKU
              </th>
              
              <!-- Category column -->
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Category
              </th>
              
              <!-- Price column -->
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Price
              </th>
              
              <!-- Stock column -->
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Stock
              </th>
              
              <!-- Status column -->
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              
              <!-- Actions column -->
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
              <!-- Product image -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="h-10 w-10 rounded border border-gray-200 flex items-center justify-center overflow-hidden bg-gray-100">
                  <img 
                    v-if="product.image_url" 
                    :src="product.image_url" 
                    :alt="product.name"
                    class="h-full w-full object-cover"
                  />
                  <span v-else class="material-icons text-gray-400">photo</span>
                </div>
              </td>
              
              <!-- Product name and description -->
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div>
                    <router-link 
                      :to="`/dashboard/products/${product.id}`"
                      class="text-sm font-medium text-blue-600 hover:text-blue-900"
                    >
                      {{ product.name }}
                    </router-link>
                    <div class="text-sm text-gray-500 truncate" style="max-width: 250px;">
                      {{ product.description || 'No description' }}
                    </div>
                  </div>
                </div>
              </td>
              
              <!-- SKU -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ product.sku }}
              </td>
              
              <!-- Category -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ product.category?.name || 'Uncategorized' }}
              </td>
              
              <!-- Price -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(product.price) }}
              </td>
              
              <!-- Stock level -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <!-- Stock level indicator -->
                  <div 
                    class="w-2 h-2 rounded-full mr-2"
                    :class="{
                      'bg-green-500': getStockLevel(product) === 'In Stock',
                      'bg-yellow-500': getStockLevel(product) === 'Low Stock',
                      'bg-red-500': getStockLevel(product) === 'Out of Stock'
                    }"
                  ></div>
                  <span class="text-sm text-gray-700">{{ getStockLevel(product) }}</span>
                </div>
                <div class="text-xs text-gray-500">
                  {{ product.total_quantity || 0 }} units
                </div>
              </td>
              
              <!-- Status -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              
              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <!-- View button -->
                  <router-link 
                    :to="`/dashboard/products/${product.id}`"
                    class="text-blue-600 hover:text-blue-900" 
                    title="View"
                  >
                    <span class="material-icons text-sm">visibility</span>
                  </router-link>
                  
                  <!-- Edit button -->
                  <router-link 
                    v-if="hasPermission('products.edit')"
                    :to="`/dashboard/products/${product.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900" 
                    title="Edit"
                  >
                    <span class="material-icons text-sm">edit</span>
                  </router-link>
                  
                  <!-- Delete button -->
                  <button 
                    v-if="hasPermission('products.delete')"
                    @click="confirmDelete(product)"
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
          <span class="material-icons text-6xl">inventory_2</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No products found</h3>
        <p class="text-gray-500 mb-6">Try adjusting your search or filter criteria</p>
        <div v-if="hasPermission('products.create')">
          <router-link 
            to="/dashboard/products/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add your first product
          </router-link>
        </div>
      </div>
      
      <!-- Import dialog -->
      <div 
        v-if="showImportDialog" 
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
            @click="showImportDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                  <span class="material-icons text-blue-600">upload_file</span>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Import Products
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Upload a CSV file to import products. Download a template to see the required format.
                    </p>
                    
                    <div class="mt-4">
                      <button
                        @click="downloadImportTemplate"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                      >
                        <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">download</span>
                        Download Template
                      </button>
                    </div>
                    
                    <div class="mt-4">
                      <label for="file-upload" class="block text-sm font-medium text-gray-700">
                        Choose file
                      </label>
                      <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                          <span class="material-icons text-gray-400 text-3xl">cloud_upload</span>
                          <div class="flex text-sm text-gray-600">
                            <label
                              for="file-upload"
                              class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                            >
                              <span>Upload a file</span>
                              <input 
                                id="file-upload" 
                                name="file-upload" 
                                type="file" 
                                class="sr-only" 
                                accept=".csv"
                                @change="handleFileUpload"
                              />
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs text-gray-500">
                            CSV up to 10MB
                          </p>
                        </div>
                      </div>
                      
                      <!-- Selected file details -->
                      <div v-if="importFile" class="mt-2 text-sm text-gray-500">
                        Selected file: {{ importFile.name }} ({{ formatFileSize(importFile.size) }})
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="importProducts"
                :disabled="!importFile || importing"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="importing" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ importing ? 'Importing...' : 'Import' }}
              </button>
              <button
                @click="showImportDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
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
                    Delete Product
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="deleteProduct"
                :disabled="deleting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="deleting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ deleting ? 'Deleting...' : 'Delete' }}
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
  import { ref, reactive, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Router and stores
  const router = useRouter();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // State variables
  const loading = ref(true);
  const products = ref([]);
  const categories = ref([]);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0
  });
  
  // Filter state
  const filters = reactive({
    search: '',
    category_id: '',
    is_active: '',
    page: 1
  });
  
  // Import/export state
  const showImportDialog = ref(false);
  const importFile = ref(null);
  const importing = ref(false);
  
  // Delete dialog state
  const showDeleteDialog = ref(false);
  const productToDelete = ref(null);
  const deleting = ref(false);
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Format currency amount with appropriate symbol
   * @param {number} amount - Amount to format
   * @returns {string} Formatted currency string
   */
  function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  }
  
  /**
   * Get stock level status text
   * @param {Object} product - Product object
   * @returns {string} Stock level status (In Stock, Low Stock, Out of Stock)
   */
  function getStockLevel(product) {
    const totalQuantity = product.total_quantity || 0;
    
    if (totalQuantity <= 0) {
      return 'Out of Stock';
    } else if (totalQuantity < 10) { // Threshold can be configurable
      return 'Low Stock';
    } else {
      return 'In Stock';
    }
  }
  
  /**
   * Format file size for display
   * @param {number} bytes - Size in bytes
   * @returns {string} Formatted size (e.g., "1.5 MB")
   */
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }
  
  /**
   * Fetch products from API
   */
  async function fetchProducts() {
    loading.value = true;
    
    try {
      // Build query params for API request
      const params = { ...filters };
      
      // Make API request
      const response = await axios.get('/api/products', { params });
      
      // Update component state with response data
      products.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total
      };
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching products:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load products.');
      
      // Set empty data state
      products.value = [];
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
   * Fetch categories for filter dropdown
   */
  async function fetchCategories() {
    try {
      const response = await axios.get('/api/categories');
      categories.value = response.data;
    } catch (error) {
      console.error('Error fetching categories:', error);
      categories.value = [];
    }
  }
  
  /**
   * Change current page and fetch updated data
   * @param {number} page - Page number to navigate to
   */
  function changePage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    
    filters.page = page;
    fetchProducts();
  }
  
  /**
   * Clear search field
   */
  function clearSearch() {
    filters.search = '';
    fetchProducts();
  }
  
  /**
   * Reset all filters to default values
   */
  function resetFilters() {
    Object.assign(filters, {
      search: '',
      category_id: '',
      is_active: '',
      page: 1
    });
    
    fetchProducts();
  }
  
  /**
   * Open delete confirmation dialog
   * @param {Object} product - Product to delete
   */
  function confirmDelete(product) {
    productToDelete.value = product;
    showDeleteDialog.value = true;
  }
  
  /**
   * Delete product
   */
  async function deleteProduct() {
    if (!productToDelete.value) return;
    
    deleting.value = true;
    
    try {
      await axios.delete(`/api/products/${productToDelete.value.id}`);
      
      // Remove product from list
      products.value = products.value.filter(p => p.id !== productToDelete.value.id);
      
      // Show success notification
      alertStore.setSuccessAlert(`Product "${productToDelete.value.name}" has been deleted.`);
      
      // Close dialog
      showDeleteDialog.value = false;
      productToDelete.value = null;
      deleting.value = false;
      
      // Reload data to update pagination
      fetchProducts();
    } catch (error) {
      console.error('Error deleting product:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete product.');
      
      deleting.value = false;
    }
  }
  
  /**
   * Handle file upload for import
   * @param {Event} event - File input change event
   */
  function handleFileUpload(event) {
    const file = event.target.files[0];
    
    if (file && file.type === 'text/csv') {
      importFile.value = file;
    } else {
      importFile.value = null;
      alertStore.setErrorAlert('Please select a valid CSV file.');
    }
  }
  
  /**
   * Download import template CSV
   */
  function downloadImportTemplate() {
    // This would typically fetch the template from an API endpoint
    // For simplicity, we'll create a CSV template manually
    
    const header = 'SKU,Name,Description,Category,Price,Cost,Weight,UPC,Status';
    const sampleRows = [
      'PROD-001,Product 1,Description for product 1,Electronics,99.99,50.00,1.5,123456789012,Active',
      'PROD-002,Product 2,Description for product 2,Clothing,29.99,15.00,0.5,123456789013,Active'
    ];
    
    const csvContent = [header, ...sampleRows].join('\n');
    
    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'product_import_template.csv';
    a.click();
    
    // Clean up
    URL.revokeObjectURL(url);
  }
  
  /**
   * Import products from CSV file
   */
  async function importProducts() {
    if (!importFile.value) return;
    
    importing.value = true;
    
    try {
      // Create form data with file
      const formData = new FormData();
      formData.append('file', importFile.value);
      
      // Submit import request
      const response = await axios.post('/api/products/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      
      // Show success notification
      alertStore.setSuccessAlert(`Successfully imported ${response.data.imported} products.`);
      
      // Reset state and refresh data
      importFile.value = null;
      showImportDialog.value = false;
      importing.value = false;
      fetchProducts();
    } catch (error) {
      console.error('Error importing products:', error);
      alertStore.setApiErrorAlert(error, 'Failed to import products.');
      
      importing.value = false;
    }
  }
  
  /**
   * Export products to CSV
   */
  async function exportProducts() {
    try {
      // Use current filters for export
      const params = { ...filters };
      
      // Request export from API
      const response = await axios.get('/api/products/export', { 
        params,
        responseType: 'blob'
      });
      
      // Create download link
      const url = URL.createObjectURL(new Blob([response.data]));
      const a = document.createElement('a');
      a.href = url;
      a.download = `products_export_${new Date().toISOString().split('T')[0]}.csv`;
      a.click();
      
      // Clean up
      URL.revokeObjectURL(url);
      
      // Show success notification
      alertStore.setSuccessAlert('Products exported successfully.');
    } catch (error) {
      console.error('Error exporting products:', error);
      alertStore.setErrorAlert('Failed to export products. Please try again.');
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
  
  // Initialize component
  onMounted(async () => {
    // Fetch categories first for filter dropdown
    await fetchCategories();
    
    // Then fetch products with default filters
    await fetchProducts();
  });
  </script>
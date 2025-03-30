<!-- resources/js/views/inventory/InventoryView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Inventory</h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Export button -->
          <button 
            v-if="hasPermission('inventory.export')"
            @click="exportInventory"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">download</span>
            Export
          </button>
          
          <!-- Adjust stock button -->
          <button 
            v-if="hasPermission('inventory.adjust')"
            @click="showAdjustmentDialog = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">edit</span>
            Adjust Stock
          </button>
        </div>
      </div>
      
      <!-- Filters and search -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                placeholder="Search by SKU, Name, or Location"
                @keyup.enter="fetchInventory"
              />
              <div v-if="filters.search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button @click="clearSearch" class="text-gray-400 hover:text-gray-500">
                  <span class="material-icons text-sm">close</span>
                </button>
              </div>
            </div>
          </div>
          
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
        </div>
        
        <!-- Additional filters in second row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
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
          
          <!-- Stock level filter -->
          <div>
            <label for="stock-level" class="block text-sm font-medium text-gray-700">Stock Level</label>
            <select
              id="stock-level"
              v-model="filters.stock_level"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="">All Stock Levels</option>
              <option value="in_stock">In Stock</option>
              <option value="low_stock">Low Stock</option>
              <option value="out_of_stock">Out of Stock</option>
            </select>
          </div>
          
          <!-- Group by filter -->
          <div>
            <label for="group-by" class="block text-sm font-medium text-gray-700">Group By</label>
            <select
              id="group-by"
              v-model="filters.group_by"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
            >
              <option value="none">No Grouping</option>
              <option value="product">Product</option>
              <option value="location">Location</option>
              <option value="warehouse">Warehouse</option>
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
            @click="fetchInventory"
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
      
      <!-- Inventory data table -->
      <div v-else-if="inventory.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <!-- Product columns -->
                <template v-if="filters.group_by !== 'location' && filters.group_by !== 'warehouse'">
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Product
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    SKU
                  </th>
                </template>
                
                <!-- Location columns -->
                <template v-if="filters.group_by !== 'product'">
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Location
                  </th>
                  <th v-if="filters.group_by !== 'warehouse'" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Warehouse
                  </th>
                </template>
                
                <!-- Common columns -->
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Quantity
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Reserved
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Available
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stock Level
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in inventory" :key="index" class="hover:bg-gray-50">
                <!-- Product columns -->
                <template v-if="filters.group_by !== 'location' && filters.group_by !== 'warehouse'">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <!-- Product image (if available) -->
                      <div class="h-10 w-10 rounded-md border border-gray-200 flex items-center justify-center overflow-hidden bg-gray-100 mr-3">
                        <img 
                          v-if="item.product?.image_url" 
                          :src="item.product.image_url" 
                          :alt="item.product?.name"
                          class="h-full w-full object-cover"
                        />
                        <span v-else class="material-icons text-gray-400">photo</span>
                      </div>
                      
                      <!-- Product name and category -->
                      <div>
                        <router-link 
                          :to="`/dashboard/products/${item.product?.id}`"
                          class="text-sm font-medium text-blue-600 hover:text-blue-900"
                        >
                          {{ item.product?.name || 'Unknown Product' }}
                        </router-link>
                        <div class="text-xs text-gray-500">
                          {{ item.product?.category?.name || 'Uncategorized' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.product?.sku || 'N/A' }}
                  </td>
                </template>
                
                <!-- Location columns -->
                <template v-if="filters.group_by !== 'product'">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.location?.name || 'Unknown Location' }}
                  </td>
                  <td v-if="filters.group_by !== 'warehouse'" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.location?.zone?.warehouse?.name || 'Unknown Warehouse' }}
                  </td>
                </template>
                
                <!-- Common columns -->
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ item.quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ item.reserved_quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.available_quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="getStockLevelClass(item)"
                  >
                    {{ getStockLevelText(item) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <!-- View product button -->
                    <router-link 
                      v-if="item.product?.id"
                      :to="`/dashboard/products/${item.product.id}`"
                      class="text-blue-600 hover:text-blue-900" 
                      title="View Product"
                    >
                      <span class="material-icons text-sm">visibility</span>
                    </router-link>
                    
                    <!-- Adjust stock button -->
                    <button 
                      v-if="hasPermission('inventory.adjust')"
                      @click="openAdjustmentDialog(item)"
                      class="text-indigo-600 hover:text-indigo-900" 
                      title="Adjust Stock"
                    >
                      <span class="material-icons text-sm">edit</span>
                    </button>
                    
                    <!-- Transfer stock button -->
                    <button 
                      v-if="hasPermission('inventory.transfer') && item.quantity > 0"
                      @click="openTransferDialog(item)"
                      class="text-green-600 hover:text-green-900" 
                      title="Transfer Stock"
                    >
                      <span class="material-icons text-sm">swap_horiz</span>
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
          <span class="material-icons text-6xl">inventory_2</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No inventory found</h3>
        <p class="text-gray-500 mb-6">Try adjusting your search or filter criteria</p>
      </div>
      
      <!-- Stock adjustment dialog -->
      <div 
        v-if="showAdjustmentDialog" 
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
            @click="showAdjustmentDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div>
                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    {{ selectedItem ? 'Adjust Stock Level' : 'Add New Stock' }}
                  </h3>
                  <div class="mt-4">
                    <form @submit.prevent="saveStockAdjustment">
                      <!-- Product selector (only for new stock) -->
                      <div v-if="!selectedItem" class="mb-4">
                        <label for="product" class="block text-sm font-medium text-gray-700">Product</label>
                        <div class="mt-1">
                          <select
                            id="product"
                            v-model="adjustment.product_id"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          >
                            <option value="" disabled>Select a product</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                              {{ product.name }} ({{ product.sku }})
                            </option>
                          </select>
                        </div>
                      </div>
                      
                      <!-- Location selector -->
                      <div class="mb-4">
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <div class="mt-1">
                          <select
                            id="location"
                            v-model="adjustment.location_id"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          >
                            <option value="" disabled>Select a location</option>
                            <option v-for="location in locations" :key="location.id" :value="location.id">
                              {{ location.name }} ({{ location.zone?.warehouse?.name }})
                            </option>
                          </select>
                        </div>
                      </div>
                      
                      <!-- Current quantity (for existing inventory) -->
                      <div v-if="selectedItem" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Current Quantity</label>
                        <div class="mt-1 flex items-center">
                          <span 
                            class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm"
                          >
                            Current
                          </span>
                          <input
                            type="text"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full sm:text-sm border-gray-300 rounded-r-md bg-gray-50"
                            :value="selectedItem.quantity"
                            disabled
                          />
                        </div>
                      </div>
                      
                      <!-- Adjustment type -->
                      <div class="mb-4">
                        <label for="adjustment-type" class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                        <div class="mt-1">
                          <select
                            id="adjustment-type"
                            v-model="adjustment.type"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          >
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Stock Level</option>
                          </select>
                        </div>
                      </div>
                      
                      <!-- Quantity -->
                      <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">
                          {{ adjustment.type === 'set' ? 'New Quantity' : 'Quantity to ' + adjustment.type }}
                        </label>
                        <div class="mt-1">
                          <input
                            id="quantity"
                            v-model.number="adjustment.quantity"
                            type="number"
                            min="0"
                            step="1"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          />
                        </div>
                      </div>
                      
                      <!-- Reason for adjustment -->
                      <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                        <div class="mt-1">
                          <select
                            id="reason"
                            v-model="adjustment.reason"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          >
                            <option value="inventory_count">Inventory Count</option>
                            <option value="damaged">Damaged</option>
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                            <option value="return">Return</option>
                            <option value="quality_control">Quality Control</option>
                            <option value="other">Other</option>
                          </select>
                        </div>
                      </div>
                      
                      <!-- Notes -->
                      <div class="mb-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <div class="mt-1">
                          <textarea
                            id="notes"
                            v-model="adjustment.notes"
                            rows="2"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                          ></textarea>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="saveStockAdjustment"
                :disabled="isSubmitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSubmitting ? 'Saving...' : 'Save' }}
              </button>
              <button
                @click="showAdjustmentDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Stock transfer dialog -->
      <div 
        v-if="showTransferDialog" 
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
            @click="showTransferDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div>
                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Transfer Stock
                  </h3>
                  <div class="mt-4">
                    <form @submit.prevent="saveStockTransfer">
                      <!-- Product information -->
                      <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Product</label>
                        <div class="mt-1 flex items-center">
                          <div class="h-8 w-8 rounded-md border border-gray-200 flex items-center justify-center overflow-hidden bg-gray-100 mr-2">
                            <img 
                              v-if="selectedItem?.product?.image_url" 
                              :src="selectedItem.product.image_url" 
                              :alt="selectedItem.product?.name"
                              class="h-full w-full object-cover"
                            />
                            <span v-else class="material-icons text-gray-400 text-sm">photo</span>
                          </div>
                          <div>
                            <div class="text-sm font-medium text-gray-900">
                              {{ selectedItem?.product?.name || 'Unknown Product' }}
                            </div>
                            <div class="text-xs text-gray-500">
                              {{ selectedItem?.product?.sku || 'No SKU' }}
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Source location -->
                      <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">From Location</label>
                        <div class="mt-1 text-sm text-gray-900">
                          {{ selectedItem?.location?.name || 'Unknown' }}
                          <span class="text-xs text-gray-500">
                            ({{ selectedItem?.location?.zone?.warehouse?.name || 'Unknown Warehouse' }})
                          </span>
                        </div>
                        <div class="mt-1 text-sm text-gray-700">
                          Available: <span class="font-medium">{{ selectedItem?.available_quantity || 0 }}</span>
                        </div>
                      </div>
                      
                      <!-- Destination location -->
                      <div class="mb-4">
                        <label for="destination" class="block text-sm font-medium text-gray-700">To Location</label>
                        <div class="mt-1">
                          <select
                            id="destination"
                            v-model="transfer.to_location_id"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          >
                            <option value="" disabled>Select a destination</option>
                            <option 
                              v-for="location in locations" 
                              :key="location.id" 
                              :value="location.id"
                              :disabled="location.id === selectedItem?.location_id"
                            >
                              {{ location.name }} ({{ location.zone?.warehouse?.name }})
                            </option>
                          </select>
                        </div>
                      </div>
                      
                      <!-- Quantity to transfer -->
                      <div class="mb-4">
                        <label for="transfer-quantity" class="block text-sm font-medium text-gray-700">Quantity to Transfer</label>
                        <div class="mt-1">
                          <input
                            id="transfer-quantity"
                            v-model.number="transfer.quantity"
                            type="number"
                            min="1"
                            :max="selectedItem?.available_quantity"
                            step="1"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          />
                        </div>
                      </div>
                      
                      <!-- Notes -->
                      <div class="mb-4">
                        <label for="transfer-notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <div class="mt-1">
                          <textarea
                            id="transfer-notes"
                            v-model="transfer.notes"
                            rows="2"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                          ></textarea>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="saveStockTransfer"
                :disabled="isSubmitting || !transfer.to_location_id || !transfer.quantity"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSubmitting ? 'Transferring...' : 'Transfer' }}
              </button>
              <button
                @click="showTransferDialog = false"
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
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Stores
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // State variables
  const loading = ref(true);
  const inventory = ref([]);
  const warehouses = ref([]);
  const zones = ref([]);
  const categories = ref([]);
  const products = ref([]);
  const locations = ref([]);
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
    warehouse_id: '',
    zone_id: '',
    category_id: '',
    stock_level: '',
    group_by: 'none',
    page: 1
  });
  
  // Dialog state
  const showAdjustmentDialog = ref(false);
  const showTransferDialog = ref(false);
  const selectedItem = ref(null);
  const isSubmitting = ref(false);
  
  // Adjustment form state
  const adjustment = reactive({
    product_id: '',
    location_id: '',
    type: 'add',
    quantity: 1,
    reason: 'inventory_count',
    notes: ''
  });
  
  // Transfer form state
  const transfer = reactive({
    to_location_id: '',
    quantity: 1,
    notes: ''
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
   * Get stock level text based on inventory item
   * @param {Object} item - Inventory item
   * @returns {string} Stock level text
   */
  function getStockLevelText(item) {
    const quantity = item.quantity || 0;
    
    if (quantity <= 0) {
      return 'Out of Stock';
    } else if (quantity < 10) { // Threshold can be configurable
      return 'Low Stock';
    } else {
      return 'In Stock';
    }
  }
  
  /**
   * Get CSS class for stock level badge
   * @param {Object} item - Inventory item
   * @returns {string} CSS classes for the badge
   */
  function getStockLevelClass(item) {
    const stockLevel = getStockLevelText(item);
    
    switch (stockLevel) {
      case 'In Stock':
        return 'bg-green-100 text-green-800';
      case 'Low Stock':
        return 'bg-yellow-100 text-yellow-800';
      case 'Out of Stock':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }
  
  /**
   * Fetch inventory data from API
   */
  async function fetchInventory() {
    loading.value = true;
    
    try {
      // Build query params for API request
      const params = { ...filters };
      
      // Make API request
      const response = await axios.get('/api/inventory', { params });
      
      // Update component state with response data
      inventory.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total
      };
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching inventory:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load inventory data.');
      
      // Set empty data state
      inventory.value = [];
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
   * Fetch warehouse list
   */
  async function fetchWarehouses() {
    try {
      const response = await axios.get('/api/warehouses');
      warehouses.value = response.data;
    } catch (error) {
      console.error('Error fetching warehouses:', error);
      warehouses.value = [];
    }
  }
  
  /**
   * Fetch zone list
   */
  async function fetchZones() {
    try {
      const response = await axios.get('/api/zones');
      zones.value = response.data;
    } catch (error) {
      console.error('Error fetching zones:', error);
      zones.value = [];
    }
  }
  
  /**
   * Fetch category list
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
   * Fetch product list for stock adjustments
   */
  async function fetchProducts() {
    try {
      const response = await axios.get('/api/products', { 
        params: { 
          is_active: 1,
          limit: 500,
          fields: 'id,name,sku'
        }
      });
      
      products.value = response.data.data;
    } catch (error) {
      console.error('Error fetching products:', error);
      products.value = [];
    }
  }
  
  /**
   * Fetch location list for stock adjustments and transfers
   */
  async function fetchLocations() {
    try {
      const response = await axios.get('/api/bin-locations', { 
        params: { 
          is_active: 1,
          with_relations: ['zone.warehouse'],
          limit: 500
        }
      });
      
      locations.value = response.data;
    } catch (error) {
      console.error('Error fetching locations:', error);
      locations.value = [];
    }
  }
  
  /**
   * Change current page and fetch updated data
   * @param {number} page - Page number to navigate to
   */
  function changePage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    
    filters.page = page;
    fetchInventory();
  }
  
  /**
   * Clear search field
   */
  function clearSearch() {
    filters.search = '';
    fetchInventory();
  }
  
  /**
   * Reset all filters to default values
   */
  function resetFilters() {
    Object.assign(filters, {
      search: '',
      warehouse_id: '',
      zone_id: '',
      category_id: '',
      stock_level: '',
      group_by: 'none',
      page: 1
    });
    
    fetchInventory();
  }
  
  /**
   * Handle warehouse selection change
   * Reset zone filter when warehouse changes
   */
  function warehouseChanged() {
    // Reset zone selection when warehouse changes
    filters.zone_id = '';
  }
  
  /**
   * Export inventory data to CSV
   */
  async function exportInventory() {
    try {
      // Use current filters for export
      const params = { ...filters };
      
      // Request export from API
      const response = await axios.get('/api/inventory/export', { 
        params,
        responseType: 'blob'
      });
      
      // Create download link
      const url = URL.createObjectURL(new Blob([response.data]));
      const a = document.createElement('a');
      a.href = url;
      a.download = `inventory_export_${new Date().toISOString().split('T')[0]}.csv`;
      a.click();
      
      // Clean up
      URL.revokeObjectURL(url);
      
      // Show success notification
      alertStore.setSuccessAlert('Inventory exported successfully.');
    } catch (error) {
      console.error('Error exporting inventory:', error);
      alertStore.setErrorAlert('Failed to export inventory. Please try again.');
    }
  }
  
  /**
   * Open adjustment dialog for a specific inventory item
   * @param {Object} item - Inventory item to adjust
   */
  function openAdjustmentDialog(item = null) {
    selectedItem.value = item;
    
    // Reset adjustment form
    Object.assign(adjustment, {
      product_id: item ? item.product_id : '',
      location_id: item ? item.location_id : locations.value.length > 0 ? locations.value[0].id : '',
      type: 'add',
      quantity: 1,
      reason: 'inventory_count',
      notes: ''
    });
    
    showAdjustmentDialog.value = true;
  }
  
  /**
   * Open transfer dialog for a specific inventory item
   * @param {Object} item - Inventory item to transfer
   */
  function openTransferDialog(item) {
    selectedItem.value = item;
    
    // Reset transfer form
    Object.assign(transfer, {
      to_location_id: '',
      quantity: 1,
      notes: ''
    });
    
    showTransferDialog.value = true;
  }
  
  /**
   * Save stock adjustment
   */
  async function saveStockAdjustment() {
    // Validate form
    if ((!selectedItem.value && !adjustment.product_id) || !adjustment.location_id) {
      alertStore.setErrorAlert('Please fill in all required fields.');
      return;
    }
    
    if (adjustment.quantity < 0 || isNaN(adjustment.quantity)) {
      alertStore.setErrorAlert('Quantity must be a positive number.');
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      await axios.post('/api/inventory/adjust', {
        product_id: selectedItem.value ? selectedItem.value.product_id : adjustment.product_id,
        location_id: adjustment.location_id,
        type: adjustment.type,
        quantity: adjustment.quantity,
        reason: adjustment.reason,
        notes: adjustment.notes
      });
      
      // Show success notification
      alertStore.setSuccessAlert('Stock has been adjusted successfully.');
      
      // Reset form and close dialog
      showAdjustmentDialog.value = false;
      isSubmitting.value = false;
      
      // Refresh inventory data
      fetchInventory();
    } catch (error) {
      console.error('Error adjusting stock:', error);
      alertStore.setApiErrorAlert(error, 'Failed to adjust stock.');
      
      isSubmitting.value = false;
    }
  }
  
  /**
   * Save stock transfer
   */
  async function saveStockTransfer() {
    // Validate form
    if (!selectedItem.value || !transfer.to_location_id || !transfer.quantity) {
      alertStore.setErrorAlert('Please fill in all required fields.');
      return;
    }
    
    if (transfer.quantity <= 0 || isNaN(transfer.quantity)) {
      alertStore.setErrorAlert('Quantity must be a positive number.');
      return;
    }
    
    if (transfer.quantity > selectedItem.value.available_quantity) {
      alertStore.setErrorAlert(`Cannot transfer more than available quantity (${selectedItem.value.available_quantity}).`);
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      await axios.post('/api/inventory/transfer', {
        product_id: selectedItem.value.product_id,
        from_location_id: selectedItem.value.location_id,
        to_location_id: transfer.to_location_id,
        quantity: transfer.quantity,
        notes: transfer.notes
      });
      
      // Show success notification
      alertStore.setSuccessAlert('Stock has been transferred successfully.');
      
      // Reset form and close dialog
      showTransferDialog.value = false;
      isSubmitting.value = false;
      
      // Refresh inventory data
      fetchInventory();
    } catch (error) {
      console.error('Error transferring stock:', error);
      alertStore.setApiErrorAlert(error, 'Failed to transfer stock.');
      
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
  
  // Initialize component
  onMounted(async () => {
    // Fetch all reference data first
    await Promise.all([
      fetchWarehouses(),
      fetchZones(),
      fetchCategories(),
      fetchProducts(),
      fetchLocations()
    ]);
    
    // Then fetch inventory with default filters
    await fetchInventory();
  });
  </script>
<!-- resources/js/views/products/ProductDetailView.vue -->

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
              to="/dashboard/products"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-sm">arrow_back</span>
              Back to Products
            </router-link>
            
            <!-- Status badge -->
            <span 
              class="ml-4 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
              :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
            >
              {{ product.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          
          <!-- Action buttons -->
          <div class="flex space-x-2">
            <!-- Edit button -->
            <router-link 
              v-if="hasPermission('products.edit')"
              :to="`/dashboard/products/${product.id}/edit`"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">edit</span>
              Edit
            </router-link>
            
            <!-- Delete button -->
            <button 
              v-if="hasPermission('products.delete')"
              @click="confirmDelete"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">delete</span>
              Delete
            </button>
          </div>
        </div>
        
        <!-- Product overview -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Product image -->
              <div class="md:col-span-1">
                <div class="bg-gray-100 border rounded-lg overflow-hidden h-64 flex items-center justify-center">
                  <img 
                    v-if="product.image_url" 
                    :src="product.image_url" 
                    :alt="product.name"
                    class="w-full h-full object-contain"
                  />
                  <span v-else class="material-icons text-gray-400 text-6xl">photo</span>
                </div>
              </div>
              
              <!-- Product details -->
              <div class="md:col-span-2">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                
                <!-- Category and SKU -->
                <div class="flex flex-wrap gap-y-1 gap-x-4 text-sm text-gray-500 mb-4">
                  <div v-if="product.category">
                    <span class="font-semibold">Category:</span> {{ product.category.name }}
                  </div>
                  <div>
                    <span class="font-semibold">SKU:</span> {{ product.sku }}
                  </div>
                  <div v-if="product.upc">
                    <span class="font-semibold">UPC:</span> {{ product.upc }}
                  </div>
                </div>
                
                <!-- Description -->
                <div class="mb-4">
                  <h2 class="text-lg font-medium text-gray-900 mb-2">Description</h2>
                  <p class="text-gray-600">{{ product.description || 'No description provided.' }}</p>
                </div>
                
                <!-- Price and cost -->
                <div class="mb-4 flex gap-x-8">
                  <div>
                    <h2 class="text-sm font-medium text-gray-500">Price</h2>
                    <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(product.price) }}</p>
                  </div>
                  <div>
                    <h2 class="text-sm font-medium text-gray-500">Cost</h2>
                    <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(product.cost) }}</p>
                  </div>
                  <div>
                    <h2 class="text-sm font-medium text-gray-500">Margin</h2>
                    <p 
                      class="text-lg font-semibold" 
                      :class="margin >= 0 ? 'text-green-600' : 'text-red-600'"
                    >
                      {{ formatPercent(margin) }}
                    </p>
                  </div>
                </div>
                
                <!-- Stock level -->
                <div>
                  <h2 class="text-sm font-medium text-gray-500 mb-1">Stock Level</h2>
                  <div class="flex items-center">
                    <div 
                      class="w-3 h-3 rounded-full mr-2"
                      :class="{
                        'bg-green-500': getStockLevel() === 'In Stock',
                        'bg-yellow-500': getStockLevel() === 'Low Stock',
                        'bg-red-500': getStockLevel() === 'Out of Stock'
                      }"
                    ></div>
                    <span 
                      class="text-base font-medium"
                      :class="{
                        'text-green-700': getStockLevel() === 'In Stock',
                        'text-yellow-700': getStockLevel() === 'Low Stock',
                        'text-red-700': getStockLevel() === 'Out of Stock'
                      }"
                    >
                      {{ getStockLevel() }}
                    </span>
                    <span class="ml-3 text-base text-gray-700">
                      {{ totalQuantity }} units
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Tabs for different sections -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                class="py-4 px-6 text-sm font-medium border-b-2 whitespace-nowrap"
                :class="[
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                <span class="material-icons mr-1 text-sm align-text-bottom">{{ tab.icon }}</span>
                {{ tab.name }}
              </button>
            </nav>
          </div>
          
          <div class="p-6">
            <!-- Inventory tab content -->
            <div v-if="activeTab === 'inventory'">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Inventory by Location</h2>
                
                <!-- Add stock button -->
                <button 
                  v-if="hasPermission('inventory.adjust')"
                  @click="showStockAdjustmentDialog = true"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="material-icons -ml-1 mr-2 text-sm">add</span>
                  Adjust Stock
                </button>
              </div>
              
              <!-- Inventory locations table -->
              <div class="bg-white overflow-hidden border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Location
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Warehouse
                      </th>
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
                        Last Updated
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="inventory.length === 0">
                      <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        No inventory records found for this product
                      </td>
                    </tr>
                    <tr 
                      v-for="(item, index) in inventory" 
                      :key="index"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 text-sm text-gray-900">
                        {{ item.location?.name || 'Unknown' }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ item.location?.zone?.warehouse?.name || 'Unknown' }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        {{ item.quantity }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ item.reserved_quantity }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900">
                        {{ item.available_quantity }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ formatDate(item.updated_at) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- Movement tab content -->
            <div v-if="activeTab === 'movement'">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Inventory Movements</h2>
                
                <!-- Date filter -->
                <div class="flex space-x-2">
                  <div>
                    <label for="from-date" class="sr-only">From</label>
                    <input
                      id="from-date"
                      v-model="movementFilters.from_date"
                      type="date"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                  <div>
                    <label for="to-date" class="sr-only">To</label>
                    <input
                      id="to-date"
                      v-model="movementFilters.to_date"
                      type="date"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                  <button
                    @click="fetchMovements"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    Filter
                  </button>
                </div>
              </div>
              
              <!-- Inventory movements table -->
              <div class="bg-white overflow-hidden border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        From
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        To
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Quantity
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Reference
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Performed By
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="movements.length === 0">
                      <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                        No movement records found for this product
                      </td>
                    </tr>
                    <tr 
                      v-for="(movement, index) in movements" 
                      :key="index"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ formatDateTime(movement.created_at) }}
                      </td>
                      <td class="px-6 py-4">
                        <span 
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="getMovementTypeClass(movement.movement_type)"
                        >
                          {{ formatMovementType(movement.movement_type) }}
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900">
                        {{ movement.from_location?.name || 'N/A' }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900">
                        {{ movement.to_location?.name || 'N/A' }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        {{ movement.quantity }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ formatReference(movement) }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ movement.performed_by_user?.name || 'System' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <!-- Movement pagination -->
              <div v-if="movementPagination.last_page > 1" class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ movementPagination.from }}</span>
                  to
                  <span class="font-medium">{{ movementPagination.to }}</span>
                  of
                  <span class="font-medium">{{ movementPagination.total }}</span>
                  results
                </div>
                
                <div class="flex space-x-2">
                  <button
                    @click="changeMovementPage(movementPagination.current_page - 1)"
                    :disabled="movementPagination.current_page === 1"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': movementPagination.current_page === 1 }"
                  >
                    Previous
                  </button>
                  <button
                    @click="changeMovementPage(movementPagination.current_page + 1)"
                    :disabled="movementPagination.current_page === movementPagination.last_page"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': movementPagination.current_page === movementPagination.last_page }"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Details tab content -->
            <div v-if="activeTab === 'details'">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Product Details</h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic details -->
                <div class="space-y-4">
                  <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-base font-medium text-gray-900 mb-2">Basic Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <p class="text-sm font-medium text-gray-500">SKU</p>
                        <p class="mt-1 text-sm text-gray-900">{{ product.sku }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">UPC</p>
                        <p class="mt-1 text-sm text-gray-900">{{ product.upc || 'N/A' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Category</p>
                        <p class="mt-1 text-sm text-gray-900">{{ product.category?.name || 'Uncategorized' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="mt-1 text-sm text-gray-900">{{ product.is_active ? 'Active' : 'Inactive' }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Pricing information -->
                  <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-base font-medium text-gray-900 mb-2">Pricing</h3>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <p class="text-sm font-medium text-gray-500">Price</p>
                        <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(product.price) }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Cost</p>
                        <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(product.cost) }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Margin</p>
                        <p 
                          class="mt-1 text-sm"
                          :class="margin >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                          {{ formatPercent(margin) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Physical characteristics -->
                <div class="space-y-4">
                  <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-base font-medium text-gray-900 mb-2">Physical Characteristics</h3>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <p class="text-sm font-medium text-gray-500">Weight</p>
                        <p class="mt-1 text-sm text-gray-900">{{ product.weight ? `${product.weight} kg` : 'N/A' }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Dimensions</p>
                        <p class="mt-1 text-sm text-gray-900">
                          {{ formatDimensions(product.dimensions) }}
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Additional attributes -->
                  <div v-if="product.attributes">
                    <h3 class="text-base font-medium text-gray-900 mb-2">Additional Attributes</h3>
                    <div class="bg-gray-50 rounded-md p-4">
                      <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
                        <template v-for="(value, key) in product.attributes" :key="key">
                          <dt class="text-sm font-medium text-gray-500">{{ formatAttributeName(key) }}</dt>
                          <dd class="text-sm text-gray-900">{{ value }}</dd>
                        </template>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Orders tab content -->
            <div v-if="activeTab === 'orders'">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Recent Orders</h2>
              </div>
              
              <!-- Recent orders table -->
              <div class="bg-white overflow-hidden border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Order #
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Customer
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Quantity
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="recentOrders.length === 0">
                      <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        No recent orders found for this product
                      </td>
                    </tr>
                    <tr 
                      v-for="order in recentOrders" 
                      :key="order.id"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 text-sm font-medium text-blue-600">
                        {{ order.order_number }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500">
                        {{ formatDate(order.order_date) }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900">
                        {{ order.customer_name }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900">
                        {{ order.quantity }}
                      </td>
                      <td class="px-6 py-4">
                        <span 
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="getOrderStatusClass(order.status)"
                        >
                          {{ formatOrderStatus(order.status) }}
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm font-medium">
                        <router-link 
                          :to="`/dashboard/orders/${order.id}`"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          View
                        </router-link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Stock Adjustment Dialog -->
        <div 
          v-if="showStockAdjustmentDialog" 
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
              @click="showStockAdjustmentDialog = false"
            ></div>
            
            <!-- Dialog panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                    <span class="material-icons text-blue-600">inventory</span>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                      Adjust Stock
                    </h3>
                    <div class="mt-4">
                      <form @submit.prevent="saveStockAdjustment">
                        <!-- Adjustment type -->
                        <div class="mb-4">
                          <label for="adjustment-type" class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                          <select
                            id="adjustment-type"
                            v-model="stockAdjustment.type"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            required
                          >
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                            <option value="set">Set Stock Level</option>
                          </select>
                        </div>
                        
                        <!-- Location -->
                        <div class="mb-4">
                          <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                          <select
                            id="location"
                            v-model="stockAdjustment.location_id"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            required
                          >
                            <option v-for="location in locations" :key="location.id" :value="location.id">
                              {{ location.name }} ({{ location.zone.warehouse.name }})
                            </option>
                          </select>
                        </div>
                        
                        <!-- Quantity -->
                        <div class="mb-4">
                          <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                          <div class="mt-1">
                            <input
                              id="quantity"
                              v-model.number="stockAdjustment.quantity"
                              type="number"
                              min="0"
                              step="1"
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              required
                            />
                          </div>
                        </div>
                        
                        <!-- Reason -->
                        <div class="mb-4">
                          <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                          <div class="mt-1">
                            <select
                              id="reason"
                              v-model="stockAdjustment.reason"
                              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
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
                              v-model="stockAdjustment.notes"
                              rows="3"
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
                  :disabled="adjustingStock"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                >
                  <span v-if="adjustingStock" class="mr-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  {{ adjustingStock ? 'Saving...' : 'Save' }}
                </button>
                <button
                  @click="showStockAdjustmentDialog = false"
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
                        Are you sure you want to delete this product? This action cannot be undone.
                      </p>
                      <p class="text-sm text-red-500 mt-2 font-medium">
                        This will also remove all inventory records associated with this product.
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
  const product = ref({});
  const inventory = ref([]);
  const movements = ref([]);
  const locations = ref([]);
  const recentOrders = ref([]);
  const activeTab = ref('inventory');
  
  // Tab definitions
  const tabs = [
    { id: 'inventory', name: 'Inventory', icon: 'inventory_2' },
    { id: 'movement', name: 'Movements', icon: 'sync_alt' },
    { id: 'details', name: 'Details', icon: 'info' },
    { id: 'orders', name: 'Orders', icon: 'receipt' }
  ];
  
  // Movement filters and pagination
  const movementFilters = reactive({
    page: 1,
    from_date: '',
    to_date: ''
  });
  const movementPagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0
  });
  
  // Delete dialog state
  const showDeleteDialog = ref(false);
  const deleting = ref(false);
  
  // Stock adjustment dialog state
  const showStockAdjustmentDialog = ref(false);
  const adjustingStock = ref(false);
  const stockAdjustment = reactive({
    type: 'add',
    location_id: '',
    quantity: 1,
    reason: 'inventory_count',
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
   * Format percentage value
   * @param {number} value - Percentage value (0-100)
   * @returns {string} Formatted percentage string
   */
  function formatPercent(value) {
    if (value === undefined || value === null) return 'N/A';
    
    return new Intl.NumberFormat('en-US', {
      style: 'percent',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(value / 100);
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
   * Format dimensions object to readable string
   * @param {Object|null} dimensions - Dimensions object (length, width, height)
   * @returns {string} Formatted dimensions string
   */
  function formatDimensions(dimensions) {
    if (!dimensions) return 'N/A';
    
    try {
      // If dimensions is stored as JSON string, parse it
      const dims = typeof dimensions === 'string' ? JSON.parse(dimensions) : dimensions;
      
      if (dims.length && dims.width && dims.height) {
        return `${dims.length} × ${dims.width} × ${dims.height} cm`;
      }
    } catch (error) {
      console.error('Error parsing dimensions:', error);
    }
    
    return 'N/A';
  }
  
  /**
   * Format movement type to readable string
   * @param {string} type - Movement type from API
   * @returns {string} Formatted movement type
   */
  function formatMovementType(type) {
    if (!type) return 'Unknown';
    
    // Convert snake_case to Title Case
    return type
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Get CSS class for movement type
   * @param {string} type - Movement type
   * @returns {string} CSS classes for type badge
   */
  function getMovementTypeClass(type) {
    const typeClasses = {
      receive: 'bg-green-100 text-green-800',
      pick: 'bg-red-100 text-red-800',
      transfer: 'bg-blue-100 text-blue-800',
      return: 'bg-purple-100 text-purple-800',
      adjust: 'bg-yellow-100 text-yellow-800'
    };
    
    // Get base type (before any underscore)
    const baseType = type?.split('_')[0] || '';
    
    return typeClasses[baseType] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Format reference information for movement
   * @param {Object} movement - Movement object
   * @returns {string} Formatted reference string
   */
  function formatReference(movement) {
    if (!movement.reference_type) return 'N/A';
    
    // Format different reference types
    switch (movement.reference_type) {
      case 'order':
        return `Order #${movement.reference?.order_number || movement.reference_id}`;
      case 'purchase_order':
        return `PO #${movement.reference?.po_number || movement.reference_id}`;
      case 'stock_adjustment':
        return `Adjustment #${movement.reference_id}`;
      case 'transfer':
        return `Transfer #${movement.reference_id}`;
      default:
        return `${formatMovementType(movement.reference_type)} #${movement.reference_id}`;
    }
  }
  
  /**
   * Format attribute name to readable string
   * @param {string} name - Attribute name
   * @returns {string} Formatted attribute name
   */
  function formatAttributeName(name) {
    if (!name) return '';
    
    // Convert camelCase or snake_case to Title Case
    return name
      .replace(/([A-Z])/g, ' $1') // Insert space before capital letters
      .replace(/_/g, ' ') // Replace underscores with spaces
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
      .trim();
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
   * Get CSS class for order status badge
   * @param {string} status - Order status
   * @returns {string} CSS classes for the status badge
   */
  function getOrderStatusClass(status) {
    const statusClasses = {
      pending: 'bg-yellow-100 text-yellow-800',
      processing: 'bg-blue-100 text-blue-800',
      awaiting_payment: 'bg-orange-100 text-orange-800',
      paid: 'bg-green-100 text-green-800',
      ready_to_pick: 'bg-indigo-100 text-indigo-800',
      picking: 'bg-indigo-100 text-indigo-800',
      picked: 'bg-purple-100 text-purple-800',
      packing: 'bg-purple-100 text-purple-800',
      packed: 'bg-purple-100 text-purple-800',
      awaiting_shipment: 'bg-blue-100 text-blue-800',
      shipped: 'bg-green-100 text-green-800',
      delivered: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800',
      returned: 'bg-red-100 text-red-800',
      completed: 'bg-green-100 text-green-800',
      on_hold: 'bg-gray-100 text-gray-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Fetch product data from API
   */
  async function fetchProduct() {
    loading.value = true;
    
    try {
      const productId = route.params.id;
      const response = await axios.get(`/api/products/${productId}`);
      
      // Update state
      product.value = response.data;
      
      // Fetch related data
      await Promise.all([
        fetchInventory(),
        fetchMovements(),
        fetchLocations(),
        fetchRecentOrders()
      ]);
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching product:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load product details.');
      
      // Provide a way to return to products list
      loading.value = false;
    }
  }
  
  /**
   * Fetch inventory data for this product
   */
  async function fetchInventory() {
    try {
      const productId = route.params.id;
      const response = await axios.get(`/api/products/${productId}/inventory`);
      inventory.value = response.data;
    } catch (error) {
      console.error('Error fetching inventory:', error);
      inventory.value = [];
    }
  }
  
  /**
   * Fetch movement history for this product
   */
  async function fetchMovements() {
    try {
      const productId = route.params.id;
      const params = { ...movementFilters };
      
      const response = await axios.get(`/api/products/${productId}/movements`, { params });
      
      movements.value = response.data.data;
      movementPagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total
      };
    } catch (error) {
      console.error('Error fetching movements:', error);
      movements.value = [];
      movementPagination.value = {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0
      };
    }
  }
  
  /**
   * Fetch location list for stock adjustments
   */
  async function fetchLocations() {
    try {
      const response = await axios.get('/api/bin-locations', { 
        params: { 
          is_active: 1,
          with_relations: ['zone.warehouse']
        }
      });
      
      locations.value = response.data;
      
      // Set default location if there are any locations
      if (locations.value.length > 0 && !stockAdjustment.location_id) {
        stockAdjustment.location_id = locations.value[0].id;
      }
    } catch (error) {
      console.error('Error fetching locations:', error);
      locations.value = [];
    }
  }
  
  /**
   * Fetch recent orders containing this product
   */
  async function fetchRecentOrders() {
    try {
      const productId = route.params.id;
      const response = await axios.get(`/api/products/${productId}/orders`);
      recentOrders.value = response.data;
    } catch (error) {
      console.error('Error fetching recent orders:', error);
      recentOrders.value = [];
    }
  }
  
  /**
   * Change movement history page
   * @param {number} page - Page number to navigate to
   */
  function changeMovementPage(page) {
    if (page < 1 || page > movementPagination.value.last_page) return;
    
    movementFilters.page = page;
    fetchMovements();
  }
  
  /**
   * Calculate profit margin percentage
   */
  const margin = computed(() => {
    if (!product.value || !product.value.price || !product.value.cost) return 0;
    
    const price = parseFloat(product.value.price);
    const cost = parseFloat(product.value.cost);
    
    if (price === 0) return 0;
    
    return ((price - cost) / price) * 100;
  });
  
  /**
   * Calculate total inventory quantity
   */
  const totalQuantity = computed(() => {
    if (!inventory.value || inventory.value.length === 0) return 0;
    
    return inventory.value.reduce((sum, item) => sum + (item.quantity || 0), 0);
  });
  
  /**
   * Get stock level status text
   */
  function getStockLevel() {
    const total = totalQuantity.value;
    
    if (total <= 0) {
      return 'Out of Stock';
    } else if (total < 10) { // Threshold can be configurable
      return 'Low Stock';
    } else {
      return 'In Stock';
    }
  }
  
  /**
   * Open delete confirmation dialog
   */
  function confirmDelete() {
    showDeleteDialog.value = true;
  }
  
  /**
   * Delete product
   */
  async function deleteProduct() {
    deleting.value = true;
    
    try {
      await axios.delete(`/api/products/${product.value.id}`);
      
      // Show success notification
      alertStore.setSuccessAlert(`Product "${product.value.name}" has been deleted.`);
      
      // Navigate back to products list
      deleting.value = false;
      showDeleteDialog.value = false;
      router.push('/dashboard/products');
    } catch (error) {
      console.error('Error deleting product:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete product.');
      
      deleting.value = false;
    }
  }
  
  /**
   * Save stock adjustment
   */
  async function saveStockAdjustment() {
    if (!stockAdjustment.location_id || stockAdjustment.quantity < 0) {
      alertStore.setErrorAlert('Please fill in all required fields with valid values.');
      return;
    }
    
    adjustingStock.value = true;
    
    try {
      await axios.post('/api/inventory/adjust', {
        product_id: product.value.id,
        location_id: stockAdjustment.location_id,
        type: stockAdjustment.type,
        quantity: stockAdjustment.quantity,
        reason: stockAdjustment.reason,
        notes: stockAdjustment.notes
      });
      
      // Show success notification
      alertStore.setSuccessAlert('Stock has been adjusted successfully.');
      
      // Reset form and close dialog
      stockAdjustment.quantity = 1;
      stockAdjustment.reason = 'inventory_count';
      stockAdjustment.notes = '';
      showStockAdjustmentDialog.value = false;
      adjustingStock.value = false;
      
      // Refresh inventory data
      await fetchInventory();
      await fetchMovements();
    } catch (error) {
      console.error('Error adjusting stock:', error);
      alertStore.setApiErrorAlert(error, 'Failed to adjust stock.');
      
      adjustingStock.value = false;
    }
  }
  
  // Initialize component
  onMounted(() => {
    fetchProduct();
  });
  </script>
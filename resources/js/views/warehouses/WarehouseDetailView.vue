<!-- resources/js/views/warehouses/WarehouseDetailView.vue -->

<template>
    <div>
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else>
        <!-- Back button and actions header -->
        <div class="flex flex-wrap justify-between items-center mb-6">
          <div class="flex items-center">
            <router-link
              to="/dashboard/warehouses"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-sm">arrow_back</span>
              Back to Warehouses
            </router-link>
            
            <!-- Status badge -->
            <span 
              class="ml-4 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
              :class="warehouse.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
            >
              {{ warehouse.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          
          <!-- Action buttons -->
          <div class="flex space-x-2 mt-2 sm:mt-0">
            <!-- Edit button -->
            <button 
              v-if="hasPermission('warehouses.edit')"
              @click="showEditDialog = true"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">edit</span>
              Edit
            </button>
            
            <!-- Add Zone button -->
            <button 
              v-if="hasPermission('zones.create')"
              @click="showCreateZoneDialog = true"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-2 text-sm">add</span>
              Add Zone
            </button>
          </div>
        </div>
        
        <!-- Warehouse details card -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ warehouse.name }}</h3>
            <p class="mt-1 text-sm text-gray-500">
              {{ warehouse.code }}
            </p>
          </div>
          
          <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Address and location -->
              <div>
                <h4 class="text-base font-medium text-gray-900 mb-2">Location</h4>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ warehouse.address || 'No address provided' }}</p>
              </div>
              
              <!-- Summary stats -->
              <div>
                <h4 class="text-base font-medium text-gray-900 mb-2">Summary</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm font-medium text-gray-500">Total Zones</p>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ warehouse.zones_count || 0 }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Bin Locations</p>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ warehouse.locations_count || 0 }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Total Inventory</p>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ warehouse.inventory_count || 0 }} items</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Utilization</p>
                    <div class="mt-1 flex items-center">
                      <div class="w-16 bg-gray-200 rounded-full h-2.5 mr-2">
                        <div 
                          class="h-2.5 rounded-full"
                          :style="{
                            width: `${warehouseUtilization}%`,
                            backgroundColor: getUtilizationColor(warehouseUtilization)
                          }"
                        ></div>
                      </div>
                      <span class="text-sm font-medium text-gray-900">{{ warehouseUtilization }}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Warehouse layout and zones container -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Warehouse zones list -->
          <div class="lg:col-span-1 bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Zones</h3>
              <button 
                v-if="hasPermission('zones.create')"
                @click="showCreateZoneDialog = true"
                class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <span class="material-icons text-xs mr-1">add</span>
                Add
              </button>
            </div>
            
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
              <div v-if="zones.length === 0" class="p-4 text-center text-gray-500">
                No zones defined for this warehouse
              </div>
              
              <div
                v-for="zone in zones"
                :key="zone.id"
                class="px-4 py-3 hover:bg-gray-50"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="font-medium text-gray-900">{{ zone.name }}</h4>
                    <p class="text-sm text-gray-500">{{ zone.code }}</p>
                    <div class="mt-1 flex items-center">
                      <span class="text-xs text-gray-500 mr-2">
                        {{ zone.bin_locations_count || 0 }} locations
                      </span>
                      <span 
                        class="px-1.5 py-0.5 text-xs rounded-full"
                        :class="zone.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                      >
                        {{ zone.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="flex space-x-2">
                    <!-- View zone button -->
                    <router-link
                      :to="{ name: 'bin-locations', query: { zone_id: zone.id } }"
                      class="text-blue-600 hover:text-blue-900"
                      title="View Bin Locations"
                    >
                      <span class="material-icons text-sm">location_on</span>
                    </router-link>
                    
                    <!-- Edit zone button -->
                    <button 
                      v-if="hasPermission('zones.edit')"
                      @click="editZone(zone)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Edit Zone"
                    >
                      <span class="material-icons text-sm">edit</span>
                    </button>
                    
                    <!-- Delete zone button -->
                    <button 
                      v-if="hasPermission('zones.delete')"
                      @click="confirmDeleteZone(zone)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete Zone"
                    >
                      <span class="material-icons text-sm">delete</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Warehouse layout visualization -->
          <div class="lg:col-span-2 bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Warehouse Layout</h3>
              <p class="mt-1 text-sm text-gray-500">
                Visual representation of zones and bin locations
              </p>
            </div>
            
            <div class="p-4">
              <!-- Warehouse layout component -->
              <WarehouseLayout :warehouse-id="warehouseId" />
            </div>
          </div>
        </div>
        
        <!-- Inventory by Zone chart -->
        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Inventory by Zone</h3>
            <p class="mt-1 text-sm text-gray-500">
              Distribution of inventory across warehouse zones
            </p>
          </div>
          
          <div class="p-4 h-80">
            <!-- Loading state for chart -->
            <div v-if="loadingInventory" class="flex justify-center items-center h-full">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
            
            <!-- No data message -->
            <div v-else-if="inventoryData.length === 0" class="flex justify-center items-center h-full">
              <p class="text-gray-500">No inventory data available</p>
            </div>
            
            <!-- Inventory chart -->
            <div v-else ref="chartContainer" class="h-full w-full"></div>
          </div>
        </div>
        
        <!-- Recent inventory movements -->
        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <div>
              <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Movements</h3>
              <p class="mt-1 text-sm text-gray-500">
                Latest inventory movements in this warehouse
              </p>
            </div>
            
            <router-link 
              :to="{ name: 'inventory', query: { warehouse_id: warehouseId } }"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              View All
            </router-link>
          </div>
          
          <div>
            <!-- Loading state for movements -->
            <div v-if="loadingMovements" class="flex justify-center items-center py-12">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
            
            <!-- No data message -->
            <div v-else-if="movements.length === 0" class="p-4 text-center text-gray-500">
              No recent movements found
            </div>
            
            <!-- Movements table -->
            <table v-else class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date/Time
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Product
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
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(movement, index) in movements" :key="index" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDateTime(movement.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="getMovementTypeClass(movement.movement_type)"
                    >
                      {{ formatMovementType(movement.movement_type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ movement.product?.name || 'Unknown' }}</div>
                    <div class="text-xs text-gray-500">{{ movement.product?.sku || 'No SKU' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ movement.from_location?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ movement.to_location?.name || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ movement.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatReference(movement) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Edit warehouse dialog -->
        <div 
          v-if="showEditDialog" 
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
              @click="showEditDialog = false"
            ></div>
            
            <!-- Dialog panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div>
                  <div class="mt-3 text-center sm:mt-0 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                      Edit Warehouse
                    </h3>
                    <div class="mt-4">
                      <form @submit.prevent="saveWarehouse">
                        <!-- Warehouse name -->
                        <div class="mb-4">
                          <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                          <div class="mt-1">
                            <input
                              id="name"
                              v-model="warehouseForm.name"
                              type="text"
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              required
                            />
                          </div>
                        </div>
                        
                        <!-- Warehouse code -->
                        <div class="mb-4">
                          <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
                          <div class="mt-1">
                            <input
                              id="code"
                              v-model="warehouseForm.code"
                              type="text"
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              required
                            />
                            <p class="mt-1 text-xs text-gray-500">
                              Unique identifier for the warehouse (e.g., WH-001)
                            </p>
                          </div>
                        </div>
                        
                        <!-- Warehouse address -->
                        <div class="mb-4">
                          <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                          <div class="mt-1">
                            <textarea
                              id="address"
                              v-model="warehouseForm.address"
                              rows="3"
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            ></textarea>
                          </div>
                        </div>
                        
                        <!-- Active status -->
                        <div class="mb-4 flex items-center">
                          <input
                            id="is_active"
                            v-model="warehouseForm.is_active"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                          />
                          <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active
                          </label>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Dialog actions -->
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  @click="saveWarehouse"
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
                  @click="showEditDialog = false"
                  type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Create zone dialog -->
        <div 
          v-if="showCreateZoneDialog" 
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
              @click="showCreateZoneDialog = false"
            ></div>
            
            <!-- Dialog panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div>
                  <div class="mt-3 text-center sm:mt-0 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                      {{ editingZone ? 'Edit Zone' : 'Add New Zone' }}
                    </h3>
                    <div class="mt-4">
                      <form @submit.prevent="saveZone">
                        <!-- Zone name -->
                        <div class="mb-4">
                          <label for="zone-name" class="block text-sm font-medium text-gray-700">Name</label>
                          <div class="mt-1">
                            <input
                              id="zone-name"
                              v-model="zoneForm.name"
                              type="text"
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              required
                            />
                          </div>
                        </div>
                        
                        <!-- Zone code -->
                        <div class="mb-4">
                          <label for="zone-code" class="block text-sm font-medium text-gray-700">Code</label>
                          <div class="mt-1">
                            <input
                              id="zone-code"
                              v-model="zoneForm.code"
                              type="text"
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                              required
                            />
                            <p class="mt-1 text-xs text-gray-500">
                              Unique identifier for the zone (e.g., ZONE-A)
                            </p>
                          </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-4">
                          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                          <div class="mt-1">
                            <textarea
                              id="description"
                              v-model="zoneForm.description"
                              rows="3"
                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            ></textarea>
                          </div>
                        </div>
                        
                        <!-- Active status -->
                        <div class="mb-4 flex items-center">
                          <input
                            id="zone-is-active"
                            v-model="zoneForm.is_active"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                          />
                          <label for="zone-is-active" class="ml-2 block text-sm text-gray-900">
                            Active
                          </label>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Dialog actions -->
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  @click="saveZone"
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
                  @click="showCreateZoneDialog = false"
                  type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Delete zone confirmation dialog -->
        <div 
          v-if="showDeleteZoneDialog" 
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
              @click="showDeleteZoneDialog = false"
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
                      Delete Zone
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">
                        Are you sure you want to delete "{{ zoneToDelete?.name }}"? This action cannot be undone.
                      </p>
                      <p class="text-sm text-red-500 mt-2 font-medium">
                        Deleting a zone will also delete all bin locations within it.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Dialog actions -->
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button
                  @click="deleteZone"
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
                  @click="showDeleteZoneDialog = false"
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
  import WarehouseLayout from '../../components/warehouse/WarehouseLayout.vue';
  import * as d3 from 'd3';
  import axios from 'axios';
  
  // Router and stores
  const route = useRoute();
  const router = useRouter();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // Get warehouse ID from the route params
  const warehouseId = computed(() => route.params.id);
  
  // State variables
  const loading = ref(true);
  const loadingInventory = ref(true);
  const loadingMovements = ref(true);
  const isSubmitting = ref(false);
  const warehouse = ref({});
  const zones = ref([]);
  const inventoryData = ref([]);
  const movements = ref([]);
  const chartContainer = ref(null);
  
  // Dialog states
  const showEditDialog = ref(false);
  const showCreateZoneDialog = ref(false);
  const showDeleteZoneDialog = ref(false);
  const editingZone = ref(false);
  const zoneToDelete = ref(null);
  
  // Form state for editing warehouse
  const warehouseForm = reactive({
    id: null,
    name: '',
    code: '',
    address: '',
    is_active: true
  });
  
  // Form state for creating/editing zone
  const zoneForm = reactive({
    id: null,
    name: '',
    code: '',
    warehouse_id: '',
    description: '',
    is_active: true
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
   * Calculate warehouse utilization percentage
   * Simple calculation based on inventory count and capacity
   * In a real app, this would be more sophisticated
   */
  const warehouseUtilization = computed(() => {
    // Mock calculation for demo purposes
    // In real app, would consider actual capacity and inventory levels
    const totalItems = warehouse.value.inventory_count || 0;
    const totalCapacity = warehouse.value.capacity || 1000; // Default capacity
    
    const utilization = Math.min(Math.round((totalItems / totalCapacity) * 100), 100);
    return utilization;
  });
  
  /**
   * Get appropriate color for utilization based on percentage
   * @param {number} percentage - Utilization percentage
   * @returns {string} CSS color value
   */
  function getUtilizationColor(percentage) {
    if (percentage < 50) return '#10B981'; // Green
    if (percentage < 80) return '#F59E0B'; // Yellow
    return '#EF4444'; // Red
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
   * Get CSS class for movement type badge
   * @param {string} type - Movement type
   * @returns {string} CSS classes for the badge
   */
  function getMovementTypeClass(type) {
    const typeClasses = {
      'receive': 'bg-green-100 text-green-800',
      'pick': 'bg-red-100 text-red-800',
      'transfer': 'bg-blue-100 text-blue-800',
      'return': 'bg-purple-100 text-purple-800',
      'adjust': 'bg-yellow-100 text-yellow-800'
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
   * Fetch warehouse data from API
   */
  async function fetchWarehouse() {
    loading.value = true;
    
    try {
      const response = await axios.get(`/api/warehouses/${warehouseId.value}`);
      warehouse.value = response.data;
      
      // Initialize warehouse form for editing
      Object.assign(warehouseForm, {
        id: warehouse.value.id,
        name: warehouse.value.name,
        code: warehouse.value.code,
        address: warehouse.value.address,
        is_active: warehouse.value.is_active
      });
      
      // Initialize zone form for creating
      zoneForm.warehouse_id = warehouse.value.id;
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching warehouse:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load warehouse details.');
      
      // Navigate back to warehouse list on error
      router.push('/dashboard/warehouses');
    }
  }
  
  /**
   * Fetch zones for this warehouse
   */
  async function fetchZones() {
    try {
      const response = await axios.get('/api/zones', {
        params: { 
          warehouse_id: warehouseId.value,
          with_counts: true
        }
      });
      
      zones.value = response.data;
    } catch (error) {
      console.error('Error fetching zones:', error);
      alertStore.setErrorAlert('Failed to load zones.');
      zones.value = [];
    }
  }
  
  /**
   * Fetch inventory data for chart
   */
  async function fetchInventoryData() {
    loadingInventory.value = true;
    
    try {
      const response = await axios.get(`/api/warehouses/${warehouseId.value}/inventory-by-zone`);
      inventoryData.value = response.data;
      
      loadingInventory.value = false;
      
      // Render chart when data is available
      if (inventoryData.value.length > 0) {
        renderInventoryChart();
      }
    } catch (error) {
      console.error('Error fetching inventory data:', error);
      inventoryData.value = [];
      loadingInventory.value = false;
    }
  }
  
  /**
   * Fetch recent inventory movements
   */
  async function fetchMovements() {
    loadingMovements.value = true;
    
    try {
      const response = await axios.get('/api/inventory/movements', {
        params: { 
          warehouse_id: warehouseId.value,
          limit: 5,
          sort: 'created_at',
          direction: 'desc',
          with_relations: ['product', 'from_location', 'to_location', 'reference']
        }
      });
      
      movements.value = response.data;
      loadingMovements.value = false;
    } catch (error) {
      console.error('Error fetching movements:', error);
      movements.value = [];
      loadingMovements.value = false;
    }
  }
  
  /**
   * Render inventory by zone chart using D3.js
   */
  function renderInventoryChart() {
    if (!chartContainer.value || inventoryData.value.length === 0) return;
    
    // Clear any existing chart
    d3.select(chartContainer.value).select('svg').remove();
    
    // Set up chart dimensions
    const margin = { top: 20, right: 30, bottom: 40, left: 60 };
    const width = chartContainer.value.clientWidth - margin.left - margin.right;
    const height = chartContainer.value.clientHeight - margin.top - margin.bottom;
    
    // Create SVG element
    const svg = d3.select(chartContainer.value)
      .append('svg')
      .attr('width', width + margin.left + margin.right)
      .attr('height', height + margin.top + margin.bottom)
      .append('g')
      .attr('transform', `translate(${margin.left},${margin.top})`);
    
    // Format data for chart
    const data = inventoryData.value;
    
    // Create X scale for zones
    const x = d3.scaleBand()
      .domain(data.map(d => d.zone_name))
      .range([0, width])
      .padding(0.3);
    
    // Create Y scale for inventory count
    const maxCount = d3.max(data, d => d.inventory_count);
    const y = d3.scaleLinear()
      .domain([0, maxCount * 1.1]) // Add 10% padding
      .range([height, 0]);
    
    // X axis
    svg.append('g')
      .attr('transform', `translate(0,${height})`)
      .call(d3.axisBottom(x))
      .selectAll('text')
        .attr('transform', 'translate(-10,0)rotate(-45)')
        .style('text-anchor', 'end');
    
    // Y axis
    svg.append('g')
      .call(d3.axisLeft(y));
    
    // Add Y axis label
    svg.append('text')
      .attr('transform', 'rotate(-90)')
      .attr('y', -40)
      .attr('x', -height / 2)
      .attr('text-anchor', 'middle')
      .attr('fill', '#4B5563') // text-gray-600
      .style('font-size', '12px')
      .text('Inventory Count');
    
    // Color scale
    const color = d3.scaleOrdinal()
      .domain(data.map(d => d.zone_name))
      .range(d3.schemeCategory10);
    
    // Add bars
    svg.selectAll('.bar')
      .data(data)
      .enter()
      .append('rect')
        .attr('class', 'bar')
        .attr('x', d => x(d.zone_name))
        .attr('y', d => y(d.inventory_count))
        .attr('width', x.bandwidth())
        .attr('height', d => height - y(d.inventory_count))
        .attr('fill', d => color(d.zone_name))
        .attr('rx', 3) // Rounded corners
        .attr('ry', 3);
    
    // Add bar labels
    svg.selectAll('.bar-label')
      .data(data)
      .enter()
      .append('text')
        .attr('class', 'bar-label')
        .attr('x', d => x(d.zone_name) + x.bandwidth() / 2)
        .attr('y', d => y(d.inventory_count) - 5)
        .attr('text-anchor', 'middle')
        .attr('fill', '#4B5563') // text-gray-600
        .style('font-size', '12px')
        .text(d => d.inventory_count);
    
    // Add chart title
    svg.append('text')
      .attr('x', width / 2)
      .attr('y', -5)
      .attr('text-anchor', 'middle')
      .attr('fill', '#1F2937') // text-gray-800
      .style('font-size', '14px')
      .style('font-weight', 'bold')
      .text('Inventory Distribution by Zone');
  }
  
  /**
   * Open zone editing dialog
   * @param {Object} zone - Zone to edit
   */
  function editZone(zone) {
    // Set editing mode
    editingZone.value = true;
    
    // Populate form with zone data
    Object.assign(zoneForm, {
      id: zone.id,
      name: zone.name,
      code: zone.code,
      warehouse_id: zone.warehouse_id,
      description: zone.description,
      is_active: zone.is_active
    });
    
    // Show dialog
    showCreateZoneDialog.value = true;
  }
  
  /**
   * Open delete confirmation dialog for zone
   * @param {Object} zone - Zone to delete
   */
  function confirmDeleteZone(zone) {
    zoneToDelete.value = zone;
    showDeleteZoneDialog.value = true;
  }
  
  /**
   * Save warehouse changes
   */
  async function saveWarehouse() {
    // Validate form
    if (!warehouseForm.name || !warehouseForm.code) {
      alertStore.setErrorAlert('Please fill in all required fields.');
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      // Update warehouse
      const response = await axios.put(`/api/warehouses/${warehouseForm.id}`, warehouseForm);
      
      // Update local state
      warehouse.value = response.data;
      
      // Show success message
      alertStore.setSuccessAlert(`Warehouse "${warehouseForm.name}" has been updated.`);
      
      // Close dialog
      showEditDialog.value = false;
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error updating warehouse:', error);
      alertStore.setApiErrorAlert(error, 'Failed to update warehouse.');
      
      isSubmitting.value = false;
    }
  }
  
  /**
   * Save zone (create or update)
   */
  async function saveZone() {
    // Validate form
    if (!zoneForm.name || !zoneForm.code) {
      alertStore.setErrorAlert('Please fill in all required fields.');
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      let response;
      
      if (editingZone.value) {
        // Update existing zone
        response = await axios.put(`/api/zones/${zoneForm.id}`, zoneForm);
        alertStore.setSuccessAlert(`Zone "${zoneForm.name}" has been updated.`);
      } else {
        // Create new zone
        response = await axios.post('/api/zones', zoneForm);
        alertStore.setSuccessAlert(`Zone "${zoneForm.name}" has been created.`);
      }
      
      // Refresh zones list
      await fetchZones();
      
      // Close dialog and reset form
      showCreateZoneDialog.value = false;
      editingZone.value = false;
      resetZoneForm();
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error saving zone:', error);
      alertStore.setApiErrorAlert(error, 'Failed to save zone.');
      
      isSubmitting.value = false;
    }
  }
  
  /**
   * Delete zone
   */
  async function deleteZone() {
    if (!zoneToDelete.value) return;
    
    isSubmitting.value = true;
    
    try {
      await axios.delete(`/api/zones/${zoneToDelete.value.id}`);
      
      // Show success notification
      alertStore.setSuccessAlert(`Zone "${zoneToDelete.value.name}" has been deleted.`);
      
      // Close dialog and refresh data
      showDeleteZoneDialog.value = false;
      zoneToDelete.value = null;
      await fetchZones();
      await fetchInventoryData(); // Refresh chart
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error deleting zone:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete zone.');
      
      isSubmitting.value = false;
    }
  }
  
  /**
   * Reset zone form to default values
   */
  function resetZoneForm() {
    Object.assign(zoneForm, {
      id: null,
      name: '',
      code: '',
      warehouse_id: warehouse.value.id, // Keep the current warehouse ID
      description: '',
      is_active: true
    });
  }
  
  // Initialize component
  onMounted(async () => {
    // Fetch warehouse first
    await fetchWarehouse();
    
    // Then fetch related data in parallel
    await Promise.all([
      fetchZones(),
      fetchInventoryData(),
      fetchMovements()
    ]);
    
    // Add window resize listener for chart responsiveness
    const handleResize = () => {
      renderInventoryChart();
    };
    
    window.addEventListener('resize', handleResize);
    
    // Clean up event listener
    return () => {
      window.removeEventListener('resize', handleResize);
    };
  });
  </script>
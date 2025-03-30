<!-- resources/js/views/zones/ZonesView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Warehouse Zones</h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Create button -->
          <button 
            v-if="hasPermission('zones.create')"
            @click="showCreateDialog = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add Zone
          </button>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="flex flex-wrap gap-4 items-center">
          <!-- Warehouse filter -->
          <div class="w-full sm:w-auto">
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
          <div class="w-full sm:w-auto">
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
          
          <!-- Search input -->
          <div class="w-full sm:w-auto sm:flex-1">
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
                placeholder="Search by name or code"
                @keyup.enter="fetchZones"
              />
            </div>
          </div>
          
          <!-- Filter actions -->
          <div class="w-full sm:w-auto flex justify-end sm:ml-auto space-x-2 mt-4 sm:mt-6">
            <button
              @click="resetFilters"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Reset
            </button>
            <button
              @click="fetchZones"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <!-- Zones table -->
      <div v-else-if="zones.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Zone
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Warehouse
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Code
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Locations
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
            <tr v-for="zone in zones" :key="zone.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-gray-900">{{ zone.name }}</div>
                <div v-if="zone.description" class="text-sm text-gray-500 truncate max-w-xs">{{ zone.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <router-link 
                  :to="`/dashboard/warehouses/${zone.warehouse?.id}`"
                  class="text-blue-600 hover:text-blue-900"
                >
                  {{ zone.warehouse?.name || 'Unknown' }}
                </router-link>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ zone.code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ zone.bin_locations_count || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="zone.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ zone.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-3">
                  <!-- View button -->
                  <button 
                    @click="viewZoneDetails(zone)"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    <span class="material-icons text-sm">visibility</span>
                  </button>
                  
                  <!-- Edit button -->
                  <button 
                    v-if="hasPermission('zones.edit')"
                    @click="editZone(zone)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit"
                  >
                    <span class="material-icons text-sm">edit</span>
                  </button>
                  
                  <!-- Delete button -->
                  <button 
                    v-if="hasPermission('zones.delete')"
                    @click="confirmDelete(zone)"
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
          <span class="material-icons text-6xl">grid_view</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No zones found</h3>
        <p class="text-gray-500 mb-6">
          {{ filters.warehouse_id || filters.search || filters.is_active ? 'Try adjusting your filters' : 'Add a zone to get started' }}
        </p>
        <div v-if="hasPermission('zones.create') && !filters.warehouse_id && !filters.search && !filters.is_active">
          <button 
            @click="showCreateDialog = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add your first zone
          </button>
        </div>
      </div>
      
      <!-- Create/Edit zone dialog -->
      <div 
        v-if="showCreateDialog || showEditDialog" 
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
            @click="closeDialogs"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div>
                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    {{ showEditDialog ? 'Edit Zone' : 'Add New Zone' }}
                  </h3>
                  <div class="mt-4">
                    <form @submit.prevent="saveZone">
                      <!-- Zone name -->
                      <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <div class="mt-1">
                          <input
                            id="name"
                            v-model="zoneForm.name"
                            type="text"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          />
                        </div>
                      </div>
                      
                      <!-- Zone code -->
                      <div class="mb-4">
                        <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
                        <div class="mt-1">
                          <input
                            id="code"
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
                      
                      <!-- Warehouse -->
                      <div class="mb-4">
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse</label>
                        <div class="mt-1">
                          <select
                            id="warehouse_id"
                            v-model="zoneForm.warehouse_id"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            required
                          >
                            <option value="" disabled>Select a warehouse</option>
                            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                              {{ warehouse.name }}
                            </option>
                          </select>
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
                          id="is_active"
                          v-model="zoneForm.is_active"
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
                @click="closeDialogs"
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
      
      <!-- Zone details dialog -->
      <div 
        v-if="showDetailsDialog && selectedZone" 
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
            @click="showDetailsDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div>
                <div class="flex justify-between items-start">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    {{ selectedZone.name }}
                  </h3>
                  <span 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="selectedZone.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  >
                    {{ selectedZone.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                
                <div class="mt-4 border-t border-gray-200 pt-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm font-medium text-gray-500">Code</p>
                      <p class="mt-1 text-sm text-gray-900">{{ selectedZone.code }}</p>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">Warehouse</p>
                      <p class="mt-1 text-sm text-blue-600">{{ selectedZone.warehouse?.name }}</p>
                    </div>
                  </div>
                  
                  <div class="mt-4">
                    <p class="text-sm font-medium text-gray-500">Description</p>
                    <p class="mt-1 text-sm text-gray-900">{{ selectedZone.description || 'No description provided' }}</p>
                  </div>
                </div>
                
                <!-- Bin Locations -->
                <div class="mt-4 border-t border-gray-200 pt-4">
                  <h4 class="text-base font-medium text-gray-900">Bin Locations</h4>
                  
                  <div v-if="isLoadingLocations" class="flex justify-center items-center py-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                  </div>
                  
                  <div v-else-if="binLocations.length === 0" class="mt-2 bg-gray-50 p-4 rounded text-center">
                    <p class="text-sm text-gray-500">No bin locations in this zone</p>
                    <button 
                      v-if="hasPermission('bin-locations.create')"
                      @click="goToCreateBinLocation"
                      class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      Add bin location
                    </button>
                  </div>
                  
                  <div v-else class="mt-2 border rounded overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                          </th>
                          <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                          </th>
                          <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr 
                          v-for="location in binLocations.slice(0, 5)" 
                          :key="location.id"
                          class="hover:bg-gray-50"
                        >
                          <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ location.name }}</td>
                          <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ location.code }}</td>
                          <td class="px-4 py-2 whitespace-nowrap">
                            <span 
                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                              :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                            >
                              {{ location.is_active ? 'Active' : 'Inactive' }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    
                    <!-- View more link if more than 5 locations -->
                    <div v-if="binLocations.length > 5" class="px-4 py-2 bg-gray-50 text-center">
                      <router-link 
                        :to="{ path: '/dashboard/bin-locations', query: { zone_id: selectedZone.id } }"
                        class="text-sm text-blue-600 hover:text-blue-800"
                      >
                        View all {{ binLocations.length }} locations
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                v-if="hasPermission('zones.edit')"
                @click="editZone(selectedZone)"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                <span class="material-icons text-sm mr-1">edit</span>
                Edit
              </button>
              <button
                @click="showDetailsDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Close
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
  const zones = ref([]);
  const warehouses = ref([]);
  const isSubmitting = ref(false);
  
  // Dialog state
  const showCreateDialog = ref(false);
  const showEditDialog = ref(false);
  const showDeleteDialog = ref(false);
  const showDetailsDialog = ref(false);
  const zoneToDelete = ref(null);
  const selectedZone = ref(null);
  
  // Form state
  const zoneForm = reactive({
    id: null,
    name: '',
    code: '',
    warehouse_id: '',
    description: '',
    is_active: true
  });
  
  // Filter state
  const filters = reactive({
    search: '',
    warehouse_id: '',
    is_active: '',
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
  
  // Bin locations for zone details
  const binLocations = ref([]);
  const isLoadingLocations = ref(false);
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Fetch zones from API
   */
  async function fetchZones() {
    loading.value = true;
    
    try {
      // Build query params for API request
      const params = { ...filters };
      
      // Make API request
      const response = await axios.get('/api/zones', { params });
      
      // Update component state with response data
      zones.value = response.data.data;
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        from: response.data.from,
        to: response.data.to,
        total: response.data.total
      };
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching zones:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load zones.');
      
      // Set empty data state
      zones.value = [];
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
   * Fetch bin locations for a zone
   * @param {number} zoneId - Zone ID to fetch locations for
   */
  async function fetchBinLocations(zoneId) {
    isLoadingLocations.value = true;
    
    try {
      const response = await axios.get('/api/bin-locations', {
        params: { zone_id: zoneId }
      });
      
      binLocations.value = response.data;
      isLoadingLocations.value = false;
    } catch (error) {
      console.error('Error fetching bin locations:', error);
      binLocations.value = [];
      isLoadingLocations.value = false;
    }
  }
  
  /**
   * Change current page and fetch updated data
   * @param {number} page - Page number to navigate to
   */
  function changePage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    
    filters.page = page;
    fetchZones();
  }
  
  /**
   * Reset all filters to default values
   */
  function resetFilters() {
    Object.assign(filters, {
      search: '',
      warehouse_id: '',
      is_active: '',
      page: 1
    });
    
    fetchZones();
  }
  
  /**
   * Open edit dialog for a zone
   * @param {Object} zone - Zone to edit
   */
  function editZone(zone) {
    // Populate form with zone data
    Object.assign(zoneForm, {
      id: zone.id,
      name: zone.name,
      code: zone.code,
      warehouse_id: zone.warehouse_id,
      description: zone.description,
      is_active: zone.is_active
    });
    
    // Close details dialog if open
    showDetailsDialog.value = false;
    
    // Open edit dialog
    showEditDialog.value = true;
  }
  
  /**
   * View zone details
   * @param {Object} zone - Zone to view
   */
  function viewZoneDetails(zone) {
    selectedZone.value = zone;
    showDetailsDialog.value = true;
    
    // Fetch bin locations for the zone
    fetchBinLocations(zone.id);
  }
  
  /**
   * Navigate to create bin location page
   */
  function goToCreateBinLocation() {
    // Close details dialog
    showDetailsDialog.value = false;
    
    // Navigate to bin locations page with zone preselected
    router.push({
      path: '/dashboard/bin-locations/create',
      query: { zone_id: selectedZone.value.id }
    });
  }
  
  /**
   * Open delete confirmation dialog
   * @param {Object} zone - Zone to delete
   */
  function confirmDelete(zone) {
    zoneToDelete.value = zone;
    showDeleteDialog.value = true;
  }
  
  /**
   * Close all dialogs
   */
  function closeDialogs() {
    showCreateDialog.value = false;
    showEditDialog.value = false;
    showDeleteDialog.value = false;
    showDetailsDialog.value = false;
    
    // Reset form
    resetForm();
  }
  
  /**
   * Reset form to default values
   */
  function resetForm() {
    Object.assign(zoneForm, {
      id: null,
      name: '',
      code: '',
      warehouse_id: '',
      description: '',
      is_active: true
    });
  }
  
  /**
   * Save zone (create or update)
   */
  async function saveZone() {
    // Validate form
    if (!zoneForm.name || !zoneForm.code || !zoneForm.warehouse_id) {
      alertStore.setErrorAlert('Please fill in all required fields.');
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      let response;
      
      if (zoneForm.id) {
        // Update existing zone
        response = await axios.put(`/api/zones/${zoneForm.id}`, zoneForm);
        alertStore.setSuccessAlert(`Zone "${zoneForm.name}" has been updated.`);
      } else {
        // Create new zone
        response = await axios.post('/api/zones', zoneForm);
        alertStore.setSuccessAlert(`Zone "${zoneForm.name}" has been created.`);
      }
      
      // Close dialogs and refresh data
      closeDialogs();
      await fetchZones();
      
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
      showDeleteDialog.value = false;
      zoneToDelete.value = null;
      await fetchZones();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error deleting zone:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete zone.');
      
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
    // Fetch warehouses first for filter dropdown and form
    await fetchWarehouses();
    
    // If warehouse filter is in URL query, apply it
    const queryWarehouseId = parseInt(router.currentRoute.value.query.warehouse_id);
    if (queryWarehouseId) {
      filters.warehouse_id = queryWarehouseId;
    }
    
    // Then fetch zones with default filters
    await fetchZones();
    
    // Set default warehouse selection in form if there's at least one warehouse
    if (warehouses.value.length > 0 && !zoneForm.warehouse_id) {
      zoneForm.warehouse_id = warehouses.value[0].id;
    }
  });
  </script>
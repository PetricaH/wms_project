<!-- resources/js/views/warehouses/WarehousesView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Warehouses</h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Create button -->
          <button 
            v-if="hasPermission('warehouses.create')"
            @click="showCreateDialog = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add Warehouse
          </button>
        </div>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <!-- Warehouses grid -->
      <div v-else-if="warehouses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="warehouse in warehouses" 
          :key="warehouse.id"
          class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow"
        >
          <!-- Warehouse header -->
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <div>
              <h3 class="text-lg font-medium leading-6 text-gray-900">{{ warehouse.name }}</h3>
              <p class="text-sm text-gray-500">{{ warehouse.code }}</p>
            </div>
            <span 
              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              :class="warehouse.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
            >
              {{ warehouse.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          
          <!-- Warehouse body -->
          <div class="px-4 py-5 sm:p-6">
            <!-- Location info -->
            <div class="mb-4">
              <div class="text-sm font-medium text-gray-500 mb-1">Location</div>
              <p class="text-sm text-gray-900">{{ warehouse.address }}</p>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
              <div class="text-center">
                <div class="text-2xl font-semibold text-gray-900">{{ warehouse.zones_count || 0 }}</div>
                <div class="text-xs text-gray-500">Zones</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-semibold text-gray-900">{{ warehouse.locations_count || 0 }}</div>
                <div class="text-xs text-gray-500">Locations</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-semibold text-gray-900">{{ warehouse.inventory_count || 0 }}</div>
                <div class="text-xs text-gray-500">Items</div>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200 flex justify-between">
            <router-link 
              :to="`/dashboard/warehouses/${warehouse.id}`"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">visibility</span>
              View
            </router-link>
            
            <div class="flex space-x-2">
              <!-- Edit button -->
              <button 
                v-if="hasPermission('warehouses.edit')"
                @click="editWarehouse(warehouse)"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">edit</span>
                Edit
              </button>
              
              <!-- Delete button -->
              <button 
                v-if="hasPermission('warehouses.delete')"
                @click="confirmDelete(warehouse)"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">delete</span>
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty state -->
      <div v-else class="bg-white p-8 rounded-lg shadow text-center">
        <div class="text-gray-500 mb-4">
          <span class="material-icons text-6xl">warehouse</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No warehouses found</h3>
        <p class="text-gray-500 mb-6">Add a warehouse to get started</p>
        <div v-if="hasPermission('warehouses.create')">
          <button 
            @click="showCreateDialog = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <span class="material-icons -ml-1 mr-2 text-sm">add</span>
            Add your first warehouse
          </button>
        </div>
      </div>
      
      <!-- Create/Edit warehouse dialog -->
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
                    {{ showEditDialog ? 'Edit Warehouse' : 'Add New Warehouse' }}
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
                    Delete Warehouse
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete "{{ warehouseToDelete?.name }}"? This action cannot be undone.
                    </p>
                    <p class="text-sm text-red-500 mt-2 font-medium">
                      Deleting a warehouse will also delete all zones and locations within it.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="deleteWarehouse"
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
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, onMounted } from 'vue';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Stores
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // State variables
  const loading = ref(true);
  const warehouses = ref([]);
  const isSubmitting = ref(false);
  
  // Dialog state
  const showCreateDialog = ref(false);
  const showEditDialog = ref(false);
  const showDeleteDialog = ref(false);
  const warehouseToDelete = ref(null);
  
  // Form state
  const warehouseForm = reactive({
    id: null,
    name: '',
    code: '',
    address: '',
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
   * Fetch warehouses from API
   */
  async function fetchWarehouses() {
    loading.value = true;
    
    try {
      const response = await axios.get('/api/warehouses', {
        params: {
          with_counts: true
        }
      });
      
      warehouses.value = response.data;
      loading.value = false;
    } catch (error) {
      console.error('Error fetching warehouses:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load warehouses.');
      
      warehouses.value = [];
      loading.value = false;
    }
  }
  
  /**
   * Open edit dialog for a warehouse
   * @param {Object} warehouse - Warehouse to edit
   */
  function editWarehouse(warehouse) {
    // Populate form with warehouse data
    Object.assign(warehouseForm, {
      id: warehouse.id,
      name: warehouse.name,
      code: warehouse.code,
      address: warehouse.address,
      is_active: warehouse.is_active
    });
    
    showEditDialog.value = true;
  }
  
  /**
   * Open delete confirmation dialog
   * @param {Object} warehouse - Warehouse to delete
   */
  function confirmDelete(warehouse) {
    warehouseToDelete.value = warehouse;
    showDeleteDialog.value = true;
  }
  
  /**
   * Close all dialogs
   */
  function closeDialogs() {
    showCreateDialog.value = false;
    showEditDialog.value = false;
    showDeleteDialog.value = false;
    
    // Reset form
    resetForm();
  }
  
  /**
   * Reset form to default values
   */
  function resetForm() {
    Object.assign(warehouseForm, {
      id: null,
      name: '',
      code: '',
      address: '',
      is_active: true
    });
  }
  
  /**
   * Save warehouse (create or update)
   */
  async function saveWarehouse() {
    // Validate form
    if (!warehouseForm.name || !warehouseForm.code) {
      alertStore.setErrorAlert('Please fill in all required fields.');
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      let response;
      
      if (warehouseForm.id) {
        // Update existing warehouse
        response = await axios.put(`/api/warehouses/${warehouseForm.id}`, warehouseForm);
        alertStore.setSuccessAlert(`Warehouse "${warehouseForm.name}" has been updated.`);
      } else {
        // Create new warehouse
        response = await axios.post('/api/warehouses', warehouseForm);
        alertStore.setSuccessAlert(`Warehouse "${warehouseForm.name}" has been created.`);
      }
      
      // Close dialogs and refresh data
      closeDialogs();
      await fetchWarehouses();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error saving warehouse:', error);
      alertStore.setApiErrorAlert(error, 'Failed to save warehouse.');
      
      isSubmitting.value = false;
    }
  }
  
  /**
   * Delete warehouse
   */
  async function deleteWarehouse() {
    if (!warehouseToDelete.value) return;
    
    isSubmitting.value = true;
    
    try {
      await axios.delete(`/api/warehouses/${warehouseToDelete.value.id}`);
      
      // Show success notification
      alertStore.setSuccessAlert(`Warehouse "${warehouseToDelete.value.name}" has been deleted.`);
      
      // Close dialog and refresh data
      showDeleteDialog.value = false;
      warehouseToDelete.value = null;
      await fetchWarehouses();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error deleting warehouse:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete warehouse.');
      
      isSubmitting.value = false;
    }
  }
  
  // Initialize component
  onMounted(() => {
    fetchWarehouses();
  });
  </script>
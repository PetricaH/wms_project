<!-- resources/js/components/warehouse/WarehouseLayout.vue -->

<template>
    <div class="warehouse-layout">
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <!-- Warehouse layout visualization -->
      <div v-else class="bg-white rounded-lg shadow overflow-auto">
        <!-- Controls for the layout -->
        <div class="flex flex-wrap gap-2 p-4 border-b border-gray-200">
          <!-- Zoom controls -->
          <div class="flex items-center space-x-2">
            <button
              @click="zoomOut"
              class="p-1 rounded-md border border-gray-300 hover:bg-gray-100"
              :disabled="zoomLevel <= 0.5"
            >
              <span class="material-icons text-gray-500">zoom_out</span>
            </button>
            <span class="text-sm text-gray-600">{{ Math.round(zoomLevel * 100) }}%</span>
            <button
              @click="zoomIn"
              class="p-1 rounded-md border border-gray-300 hover:bg-gray-100"
              :disabled="zoomLevel >= 2.0"
            >
              <span class="material-icons text-gray-500">zoom_in</span>
            </button>
          </div>
          
          <!-- Filters -->
          <div class="flex items-center space-x-2 ml-4">
            <span class="text-sm text-gray-700">Filter:</span>
            <select
              v-model="filter.type"
              class="text-sm border-gray-300 rounded-md"
            >
              <option value="none">None</option>
              <option value="stock">Stock Level</option>
              <option value="utilization">Utilization</option>
              <option value="product">Product</option>
            </select>
            
            <!-- Product filter (shown only when product filter is selected) -->
            <select
              v-if="filter.type === 'product'"
              v-model="filter.productId"
              class="text-sm border-gray-300 rounded-md"
            >
              <option value="">Select Product</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
          </div>
          
          <!-- Display mode -->
          <div class="flex items-center space-x-2 ml-4">
            <span class="text-sm text-gray-700">View:</span>
            <div class="flex rounded-md shadow-sm">
              <button
                @click="displayMode = '2d'"
                class="px-3 py-1 text-sm border border-gray-300 rounded-l-md"
                :class="displayMode === '2d' ? 'bg-blue-50 text-blue-600 border-blue-500' : 'bg-white text-gray-700'"
              >
                2D
              </button>
              <button
                @click="displayMode = '3d'"
                class="px-3 py-1 text-sm border border-gray-300 border-l-0 rounded-r-md"
                :class="displayMode === '3d' ? 'bg-blue-50 text-blue-600 border-blue-500' : 'bg-white text-gray-700'"
              >
                3D
              </button>
            </div>
          </div>
          
          <!-- Legend -->
          <div class="flex items-center space-x-2 ml-auto">
            <span class="text-sm text-gray-700">Legend:</span>
            <div v-if="filter.type === 'stock'" class="flex items-center space-x-2">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-red-500 rounded-sm"></div>
                <span class="text-xs ml-1">Out of Stock</span>
              </div>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-yellow-500 rounded-sm"></div>
                <span class="text-xs ml-1">Low Stock</span>
              </div>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-sm"></div>
                <span class="text-xs ml-1">In Stock</span>
              </div>
            </div>
            <div v-else-if="filter.type === 'utilization'" class="flex items-center space-x-2">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-100 rounded-sm"></div>
                <span class="text-xs ml-1">Empty</span>
              </div>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-300 rounded-sm"></div>
                <span class="text-xs ml-1">Low</span>
              </div>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-sm"></div>
                <span class="text-xs ml-1">Medium</span>
              </div>
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-700 rounded-sm"></div>
                <span class="text-xs ml-1">High</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- 2D Warehouse Layout -->
        <div 
          v-if="displayMode === '2d'"
          class="warehouse-grid p-6 relative"
          :style="{
            transform: `scale(${zoomLevel})`,
            transformOrigin: 'top left',
            height: `${totalHeight}px`,
            width: `${totalWidth}px`
          }"
        >
          <!-- Warehouse boundary -->
          <div 
            class="absolute border-4 border-gray-800 rounded-lg"
            :style="{
              top: 0,
              left: 0,
              width: `${warehouseWidth}px`,
              height: `${warehouseHeight}px`
            }"
          >
            <!-- Warehouse name label -->
            <div class="absolute top-0 left-0 bg-gray-800 text-white px-2 py-1 text-sm font-medium rounded-br-md">
              {{ warehouse.name }}
            </div>
          </div>
          
          <!-- Zones -->
          <div
            v-for="zone in zones"
            :key="zone.id"
            class="absolute border-2 border-gray-600 rounded-md"
            :style="{
              top: `${zone.position.y}px`,
              left: `${zone.position.x}px`,
              width: `${zone.position.width}px`,
              height: `${zone.position.height}px`
            }"
          >
            <!-- Zone name label -->
            <div class="absolute top-0 left-0 bg-gray-600 text-white px-2 py-1 text-xs font-medium rounded-br-md">
              {{ zone.name }}
            </div>
            
            <!-- Bin locations within zone -->
            <div
              v-for="location in zone.binLocations"
              :key="location.id"
              class="absolute border border-gray-400 rounded-sm flex items-center justify-center cursor-pointer hover:bg-gray-50"
              :style="{
                top: `${location.position.y}px`,
                left: `${location.position.x}px`,
                width: `${location.position.width}px`,
                height: `${location.position.height}px`,
                backgroundColor: getLocationColor(location)
              }"
              @click="showLocationDetails(location)"
            >
              <!-- Location code -->
              <span class="text-xs font-medium" :class="getTextColor(location)">
                {{ location.code }}
              </span>
            </div>
          </div>
          
          <!-- Other warehouse elements (aisles, doors, etc) -->
          <div 
            v-for="(aisle, index) in aisles" 
            :key="`aisle-${index}`"
            class="absolute bg-gray-100 border border-dashed border-gray-300"
            :style="{
              top: `${aisle.y}px`,
              left: `${aisle.x}px`,
              width: `${aisle.width}px`,
              height: `${aisle.height}px`
            }"
          ></div>
          
          <!-- Doors -->
          <div 
            v-for="(door, index) in doors"
            :key="`door-${index}`"
            class="absolute"
            :style="{
              top: `${door.y}px`,
              left: `${door.x}px`,
              width: `${door.width}px`,
              height: `${door.height}px`
            }"
          >
            <div class="w-full h-full flex items-center justify-center">
              <span class="material-icons text-gray-600">
                {{ door.type === 'entrance' ? 'door_front' : 'door_back' }}
              </span>
            </div>
          </div>
        </div>
        
        <!-- 3D Warehouse Layout -->
        <div v-else-if="displayMode === '3d'" class="warehouse-3d">
          <!-- For simplicity, we'll show a placeholder for the 3D view -->
          <div class="flex flex-col items-center justify-center p-12 bg-gray-50 text-center">
            <span class="material-icons text-gray-400 text-6xl mb-4">view_in_ar</span>
            <p class="text-gray-500">3D view would render here using three.js or similar library</p>
            <p class="text-sm text-gray-400 mt-2">This would include features like rotating view, examining racks from different angles, etc.</p>
          </div>
        </div>
      </div>
      
      <!-- Location details dialog -->
      <div 
        v-if="showDetailDialog && selectedLocation" 
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
            @click="showDetailDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center" id="modal-title">
                    <span class="material-icons mr-2 text-gray-500">location_on</span>
                    {{ selectedLocation.name }}
                  </h3>
                  <div class="mt-1 text-sm text-gray-500">
                    {{ selectedLocation.zone?.name }} / {{ selectedLocation.zone?.warehouse?.name }}
                  </div>
                  
                  <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded p-2">
                      <div class="text-xs text-gray-500">Position</div>
                      <div class="text-sm">
                        {{ selectedLocation.position?.aisle || 'N/A' }}-{{ selectedLocation.position?.bay || 'N/A' }}-{{ selectedLocation.position?.level || 'N/A' }}
                      </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded p-2">
                      <div class="text-xs text-gray-500">Capacity</div>
                      <div class="text-sm">
                        {{ selectedLocation.capacity || 'Unlimited' }}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Inventory items in this location -->
                  <div class="mt-4">
                    <h4 class="font-medium text-gray-900 text-sm mb-2">Inventory Items</h4>
                    
                    <div v-if="locationInventory.length === 0" class="text-center py-4 bg-gray-50 rounded">
                      <p class="text-sm text-gray-500">No items in this location</p>
                    </div>
                    
                    <div v-else class="border rounded overflow-hidden">
                      <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                          <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Product
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Quantity
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                              Lot/Batch
                            </th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr v-for="item in locationInventory" :key="item.id">
                            <td class="px-4 py-2 whitespace-nowrap">
                              <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">
                                  {{ item.product?.name || 'Unknown' }}
                                </div>
                              </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                              <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                              <div class="text-xs text-gray-500">
                                {{ item.reserved_quantity > 0 ? `(${item.reserved_quantity} reserved)` : '' }}
                              </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                              {{ item.lot_number || 'N/A' }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                v-if="hasPermission('inventory.adjust')"
                @click="goToAdjustStock"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                <span class="material-icons text-sm mr-1">edit</span>
                Adjust Stock
              </button>
              <button
                @click="showDetailDialog = false"
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
  import { ref, reactive, computed, watch, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Props definition
  const props = defineProps({
    /**
     * Warehouse ID to display
     */
    warehouseId: {
      type: [Number, String],
      required: true
    }
  });
  
  // Router and stores
  const router = useRouter();
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // State variables
  const loading = ref(true);
  const warehouse = ref({});
  const zones = ref([]);
  const products = ref([]);
  const inventoryData = ref([]);
  
  // Layout configuration
  const zoomLevel = ref(1.0);
  const displayMode = ref('2d');
  const warehouseWidth = ref(1000);
  const warehouseHeight = ref(800);
  const totalWidth = ref(1050); // Extra space for margins
  const totalHeight = ref(850); // Extra space for margins
  
  // Layout elements (these would come from the backend in a real app)
  const aisles = ref([
    { x: 150, y: 50, width: 700, height: 60 },
    { x: 150, y: 200, width: 700, height: 60 },
    { x: 150, y: 350, width: 700, height: 60 },
    { x: 150, y: 500, width: 700, height: 60 },
    { x: 150, y: 650, width: 700, height: 60 }
  ]);
  
  const doors = ref([
    { x: 10, y: 350, width: 50, height: 60, type: 'entrance' },
    { x: 940, y: 350, width: 50, height: 60, type: 'exit' }
  ]);
  
  // Filter state
  const filter = reactive({
    type: 'none',
    productId: ''
  });
  
  // Location details dialog
  const showDetailDialog = ref(false);
  const selectedLocation = ref(null);
  const locationInventory = ref([]);
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Fetch warehouse data from API
   */
  async function fetchWarehouseData() {
    loading.value = true;
    
    try {
      // Fetch warehouse details
      const response = await axios.get(`/api/warehouses/${props.warehouseId}`);
      warehouse.value = response.data;
      
      // Fetch zones
      const zonesResponse = await axios.get('/api/zones', { 
        params: { 
          warehouse_id: props.warehouseId,
          with_bin_locations: true
        }
      });
      
      // Process zones data to include position information
      // In a real app, this would come from the backend, here we mock it
      const processedZones = zonesResponse.data.map((zone, zIndex) => {
        // Simple layout logic for visualization purposes
        const zonePositionX = 50 + (zIndex % 2) * 450;
        const zonePositionY = 20 + Math.floor(zIndex / 2) * 250;
        
        // Process bin locations within zone
        const binLocations = zone.bin_locations.map((location, lIndex) => {
          // Parse position data if available, or generate mock positions
          let position = {};
          
          try {
            position = typeof location.position === 'string' 
              ? JSON.parse(location.position) 
              : location.position || {};
          } catch (e) {
            position = {};
          }
          
          // Generate visual positions for the layout
          // In a real app, these would be stored in the database
          const row = Math.floor(lIndex / 4);
          const col = lIndex % 4;
          
          return {
            ...location,
            position: {
              ...position,
              x: 20 + col * 100, // Visual position in the layout
              y: 30 + row * 60,  // Visual position in the layout
              width: 80,
              height: 40
            }
          };
        });
        
        return {
          ...zone,
          binLocations,
          position: {
            x: zonePositionX,
            y: zonePositionY,
            width: 420,
            height: 200
          }
        };
      });
      
      zones.value = processedZones;
      
      // Fetch inventory data
      const inventoryResponse = await axios.get('/api/inventory', {
        params: { warehouse_id: props.warehouseId }
      });
      
      inventoryData.value = inventoryResponse.data.data;
      
      // Fetch products list for filtering
      const productsResponse = await axios.get('/api/products', {
        params: { limit: 100, fields: 'id,name,sku' }
      });
      
      products.value = productsResponse.data.data;
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching warehouse data:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load warehouse layout data.');
      loading.value = false;
    }
  }
  
  /**
   * Fetch inventory for a specific location
   * @param {Object} location - Bin location to fetch inventory for
   */
  async function fetchLocationInventory(location) {
    try {
      const response = await axios.get('/api/inventory', {
        params: { location_id: location.id, with_product: true }
      });
      
      locationInventory.value = response.data.data;
    } catch (error) {
      console.error('Error fetching location inventory:', error);
      locationInventory.value = [];
    }
  }
  
  /**
   * Show details for a specific location
   * @param {Object} location - Bin location to show details for
   */
  function showLocationDetails(location) {
    selectedLocation.value = location;
    fetchLocationInventory(location);
    showDetailDialog.value = true;
  }
  
  /**
   * Navigate to inventory view with selected location
   */
  function goToAdjustStock() {
    // Close dialog
    showDetailDialog.value = false;
    
    // Navigate to inventory view with filters
    router.push({
      path: '/dashboard/inventory',
      query: {
        location_id: selectedLocation.value.id,
        adjust: true
      }
    });
  }
  
  /**
   * Get background color for a location based on filter and inventory
   * @param {Object} location - Bin location
   * @returns {string} CSS color value
   */
  function getLocationColor(location) {
    // Find inventory items for this location
    const inventoryItems = inventoryData.value.filter(item => 
      item.location_id === location.id
    );
    
    // No filter or no inventory
    if (filter.type === 'none' || inventoryItems.length === 0) {
      return '#f9fafb'; // Light gray background
    }
    
    // Stock level filter
    if (filter.type === 'stock') {
      const totalQuantity = inventoryItems.reduce((sum, item) => sum + item.quantity, 0);
      
      if (totalQuantity <= 0) {
        return '#fee2e2'; // Red background for out of stock
      } else if (totalQuantity < 10) {
        return '#fef3c7'; // Yellow background for low stock
      } else {
        return '#d1fae5'; // Green background for in stock
      }
    }
    
    // Utilization filter
    if (filter.type === 'utilization') {
      const totalItems = inventoryItems.reduce((sum, item) => sum + item.quantity, 0);
      const capacity = location.capacity || 100; // Default capacity if not specified
      const utilization = capacity > 0 ? totalItems / capacity : 0;
      
      if (utilization === 0) {
        return '#dbeafe'; // Empty (light blue)
      } else if (utilization < 0.33) {
        return '#93c5fd'; // Low utilization
      } else if (utilization < 0.67) {
        return '#3b82f6'; // Medium utilization
      } else {
        return '#1d4ed8'; // High utilization
      }
    }
    
    // Product filter
    if (filter.type === 'product' && filter.productId) {
      const hasProduct = inventoryItems.some(item => 
        item.product_id === parseInt(filter.productId) && item.quantity > 0
      );
      
      return hasProduct ? '#d1fae5' : '#f9fafb'; // Green if has product, light gray if not
    }
    
    return '#f9fafb'; // Default light gray
  }
  
  /**
   * Get text color for a location based on filter and inventory
   * @param {Object} location - Bin location
   * @returns {string} CSS class for text color
   */
  function getTextColor(location) {
    if (filter.type === 'utilization') {
      // Find inventory items for this location
      const inventoryItems = inventoryData.value.filter(item => 
        item.location_id === location.id
      );
      
      const totalItems = inventoryItems.reduce((sum, item) => sum + item.quantity, 0);
      const capacity = location.capacity || 100; // Default capacity if not specified
      const utilization = capacity > 0 ? totalItems / capacity : 0;
      
      // For higher utilization levels, use white text for contrast
      if (utilization >= 0.33) {
        return 'text-white';
      }
    }
    
    return 'text-gray-900';
  }
  
  /**
   * Zoom in the warehouse layout
   */
  function zoomIn() {
    if (zoomLevel.value < 2.0) {
      zoomLevel.value = Math.min(zoomLevel.value + 0.1, 2.0);
    }
  }
  
  /**
   * Zoom out the warehouse layout
   */
  function zoomOut() {
    if (zoomLevel.value > 0.5) {
      zoomLevel.value = Math.max(zoomLevel.value - 0.1, 0.5);
    }
  }
  
  // Watch for filter changes to update layout
  watch([() => filter.type, () => filter.productId], () => {
    // In a real app, you might want to fetch data based on the filter
    // For simplicity, we just update the UI based on current data
  });
  
  // Initialize component
  onMounted(() => {
    fetchWarehouseData();
  });
  </script>
  
  <style scoped>
  .warehouse-grid {
    transition: transform 0.2s ease;
  }
  </style>
<!-- resources/js/views/products/ProductCreateView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Create Product</h1>
        
        <!-- Back button -->
        <router-link
          to="/dashboard/products"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">arrow_back</span>
          Back to Products
        </router-link>
      </div>
      
      <!-- Form content -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <form @submit.prevent="saveProduct">
          <div class="p-6 sm:p-8">
            <!-- Basic Information Section -->
            <div class="mb-8">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Product Name <span class="text-red-500">*</span></label>
                  <input
                    id="name"
                    v-model="product.name"
                    type="text"
                    required
                    :class="{'border-red-300': errors.name}"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  />
                  <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>
                
                <!-- SKU -->
                <div>
                  <label for="sku" class="block text-sm font-medium text-gray-700">SKU <span class="text-red-500">*</span></label>
                  <input
                    id="sku"
                    v-model="product.sku"
                    type="text"
                    required
                    :class="{'border-red-300': errors.sku}"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  />
                  <p v-if="errors.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
                  <p class="mt-1 text-xs text-gray-500">Stock Keeping Unit - Must be unique</p>
                </div>
                
                <!-- UPC/EAN -->
                <div>
                  <label for="upc" class="block text-sm font-medium text-gray-700">UPC/EAN</label>
                  <input
                    id="upc"
                    v-model="product.upc"
                    type="text"
                    :class="{'border-red-300': errors.upc}"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  />
                  <p v-if="errors.upc" class="mt-1 text-sm text-red-600">{{ errors.upc }}</p>
                  <p class="mt-1 text-xs text-gray-500">Universal Product Code or EAN</p>
                </div>
                
                <!-- Category -->
                <div>
                  <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                  <select
                    id="category_id"
                    v-model="product.category_id"
                    :class="{'border-red-300': errors.category_id}"
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">Select Category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                </div>
              </div>
              
              <!-- Description -->
              <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  id="description"
                  v-model="product.description"
                  rows="4"
                  :class="{'border-red-300': errors.description}"
                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
              </div>
            </div>
            
            <!-- Pricing Information -->
            <div class="mb-8">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Pricing Information</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Price -->
                <div>
                  <label for="price" class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      id="price"
                      v-model="product.price"
                      type="number"
                      min="0"
                      step="0.01"
                      required
                      :class="{'border-red-300': errors.price}"
                      class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">USD</span>
                    </div>
                  </div>
                  <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
                </div>
                
                <!-- Cost -->
                <div>
                  <label for="cost" class="block text-sm font-medium text-gray-700">Cost <span class="text-red-500">*</span></label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      id="cost"
                      v-model="product.cost"
                      type="number"
                      min="0"
                      step="0.01"
                      required
                      :class="{'border-red-300': errors.cost}"
                      class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">USD</span>
                    </div>
                  </div>
                  <p v-if="errors.cost" class="mt-1 text-sm text-red-600">{{ errors.cost }}</p>
                </div>
                
                <!-- Profit Margin -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Profit Margin</label>
                  <div class="mt-1 flex items-center">
                    <span 
                      class="text-lg font-medium"
                      :class="{'text-green-600': profitMargin >= 0, 'text-red-600': profitMargin < 0}"
                    >
                      {{ formatPercent(profitMargin) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Physical Properties -->
            <div class="mb-8">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Physical Properties</h2>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Weight -->
                <div>
                  <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                  <input
                    id="weight"
                    v-model="product.weight"
                    type="number"
                    min="0"
                    step="0.001"
                    :class="{'border-red-300': errors.weight}"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  />
                  <p v-if="errors.weight" class="mt-1 text-sm text-red-600">{{ errors.weight }}</p>
                </div>
                
                <!-- Dimensions -->
                <div>
                  <label for="length" class="block text-sm font-medium text-gray-700">Length (cm)</label>
                  <input
                    id="length"
                    v-model="dimensions.length"
                    type="number"
                    min="0"
                    step="0.1"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  />
                </div>
                
                <div>
                  <label for="width" class="block text-sm font-medium text-gray-700">Width (cm)</label>
                  <input
                    id="width"
                    v-model="dimensions.width"
                    type="number"
                    min="0"
                    step="0.1"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  />
                </div>
                
                <div>
                  <label for="height" class="block text-sm font-medium text-gray-700">Height (cm)</label>
                  <input
                    id="height"
                    v-model="dimensions.height"
                    type="number"
                    min="0"
                    step="0.1"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  />
                </div>
              </div>
            </div>
            
            <!-- Additional Settings -->
            <div class="mb-8">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Additional Settings</h2>
              
              <!-- Status toggle -->
              <div class="flex items-center">
                <button
                  type="button"
                  @click="product.is_active = !product.is_active"
                  :class="[
                    product.is_active ? 'bg-blue-600' : 'bg-gray-200',
                    'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'
                  ]"
                >
                  <span
                    :class="[
                      product.is_active ? 'translate-x-5' : 'translate-x-0',
                      'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200'
                    ]"
                  ></span>
                </button>
                <span class="ml-3">
                  <span class="text-sm font-medium text-gray-900">Active</span>
                  <span class="text-sm text-gray-500 ml-1">({{ product.is_active ? 'Yes' : 'No' }})</span>
                </span>
              </div>
              
              <!-- Additional attributes (key-value pairs) -->
              <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Attributes</label>
                
                <div class="space-y-3">
                  <!-- Attribute rows -->
                  <div v-for="(attribute, index) in attributes" :key="index" class="flex space-x-4">
                    <div class="w-1/3">
                      <input
                        v-model="attribute.key"
                        type="text"
                        placeholder="Attribute name"
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      />
                    </div>
                    <div class="flex-1">
                      <input
                        v-model="attribute.value"
                        type="text"
                        placeholder="Attribute value"
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      />
                    </div>
                    <div>
                      <button 
                        type="button" 
                        @click="removeAttribute(index)"
                        class="inline-flex items-center p-1.5 border border-transparent rounded-full text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                      >
                        <span class="material-icons text-sm">remove_circle</span>
                      </button>
                    </div>
                  </div>
                  
                  <!-- Add attribute button -->
                  <div>
                    <button 
                      type="button" 
                      @click="addAttribute"
                      class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      <span class="material-icons -ml-0.5 mr-1 text-sm">add_circle</span>
                      Add Attribute
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Form Actions -->
          <div class="px-6 py-3 bg-gray-50 text-right sm:px-8">
            <button
              type="button"
              @click="$router.push('/dashboard/products')"
              class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <span v-if="submitting" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ submitting ? 'Saving...' : 'Save Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Router and stores
  const router = useRouter();
  const alertStore = useAlertStore();
  
  // State variables
  const submitting = ref(false);
  const categories = ref([]);
  const errors = ref({});
  
  // Product form data
  const product = reactive({
    name: '',
    sku: '',
    upc: '',
    category_id: '',
    description: '',
    price: 0,
    cost: 0,
    weight: 0,
    dimensions: null,
    attributes: null,
    is_active: true
  });
  
  // Dimensions are handled separately then combined into JSON
  const dimensions = reactive({
    length: '',
    width: '',
    height: ''
  });
  
  // Additional attributes as key-value pairs
  const attributes = ref([
    { key: '', value: '' }
  ]);
  
  /**
   * Add a new empty attribute row
   */
  function addAttribute() {
    attributes.value.push({ key: '', value: '' });
  }
  
  /**
   * Remove an attribute at the specified index
   * @param {number} index - Index of the attribute to remove
   */
  function removeAttribute(index) {
    attributes.value.splice(index, 1);
    
    // Always have at least one empty attribute row
    if (attributes.value.length === 0) {
      addAttribute();
    }
  }
  
  /**
   * Calculate profit margin percentage
   * @returns {number} - Profit margin as a percentage
   */
  const profitMargin = computed(() => {
    if (!product.price || !product.cost) return 0;
    
    const price = parseFloat(product.price);
    const cost = parseFloat(product.cost);
    
    if (price === 0) return 0;
    
    return ((price - cost) / price) * 100;
  });
  
  /**
   * Format percentage value for display
   * @param {number} value - Percentage value (0-100)
   * @returns {string} Formatted percentage string
   */
  function formatPercent(value) {
    return new Intl.NumberFormat('en-US', {
      style: 'percent',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(value / 100);
  }
  
  /**
   * Fetch categories from API
   */
  async function fetchCategories() {
    try {
      const response = await axios.get('/api/categories');
      categories.value = response.data;
    } catch (error) {
      console.error('Error fetching categories:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load categories.');
      categories.value = [];
    }
  }
  
  /**
   * Prepare product data for submission
   * Formats dimensions and attributes as JSON
   * @returns {Object} - Prepared product data
   */
  function prepareProductData() {
    // Create a copy of the product data
    const productData = { ...product };
    
    // Add dimensions if any values are set
    if (dimensions.length || dimensions.width || dimensions.height) {
      productData.dimensions = JSON.stringify(dimensions);
    }
    
    // Add attributes if any are set
    const attrs = {};
    let hasAttributes = false;
    
    attributes.value.forEach(attr => {
      if (attr.key && attr.value) {
        attrs[attr.key] = attr.value;
        hasAttributes = true;
      }
    });
    
    if (hasAttributes) {
      productData.attributes = JSON.stringify(attrs);
    }
    
    return productData;
  }
  
  /**
   * Save product to API
   */
  async function saveProduct() {
    // Reset errors
    errors.value = {};
    
    // Set submitting state
    submitting.value = true;
    
    try {
      // Prepare product data
      const productData = prepareProductData();
      
      // Create product via API
      const response = await axios.post('/api/products', productData);
      
      // Show success notification
      alertStore.setSuccessAlert('Product created successfully.');
      
      // Navigate to the product detail page
      router.push(`/dashboard/products/${response.data.id}`);
    } catch (error) {
      console.error('Error creating product:', error);
      
      // Handle validation errors
      if (error.response && error.response.status === 422) {
        errors.value = error.response.data.errors;
        
        // Show general error message
        alertStore.setErrorAlert('Please correct the errors in the form.');
      } else {
        // Show generic error message
        alertStore.setApiErrorAlert(error, 'Failed to create product.');
      }
      
      // Reset submitting state
      submitting.value = false;
    }
  }
  
  // Initialize component
  onMounted(async () => {
    // Fetch categories for dropdown
    await fetchCategories();
  });
  </script>
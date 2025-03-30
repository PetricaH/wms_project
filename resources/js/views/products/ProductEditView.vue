<!-- resources/js/views/products/ProductEditView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <!-- Page title with product name -->
        <h1 class="text-2xl font-semibold text-gray-900">
          Edit Product: {{ product.name }}
        </h1>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <!-- Cancel button -->
          <router-link 
            :to="{ name: 'product-detail', params: { id: productId } }"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </router-link>
          
          <!-- Save button -->
          <button 
            @click="saveProduct"
            :disabled="isSubmitting"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="isSubmitting" class="mr-2">
              <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ isSubmitting ? 'Saving...' : 'Save Product' }}
          </button>
        </div>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <!-- Error alert -->
      <div v-if="formError" class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <span class="material-icons text-red-400">error</span>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">
              {{ formError }}
            </p>
          </div>
        </div>
      </div>
      
      <!-- Product form -->
      <div v-if="!loading" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6">
          <form @submit.prevent="saveProduct">
            <!-- Form sections with tabs -->
            <div class="border-b border-gray-200 mb-6">
              <nav class="-mb-px flex space-x-8">
                <button
                  v-for="tab in tabs"
                  :key="tab.id"
                  @click.prevent="activeTab = tab.id"
                  class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
                  :class="[
                    activeTab === tab.id
                      ? 'border-blue-500 text-blue-600'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]"
                >
                  {{ tab.name }}
                </button>
              </nav>
            </div>
            
            <!-- Basic Information Tab -->
            <div v-if="activeTab === 'basic'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.name }"
                    required
                  />
                  <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>
                
                <!-- SKU -->
                <div>
                  <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                  <input
                    id="sku"
                    v-model="form.sku"
                    type="text"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.sku }"
                    required
                  />
                  <p v-if="errors.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
                </div>
                
                <!-- UPC -->
                <div>
                  <label for="upc" class="block text-sm font-medium text-gray-700">UPC/EAN</label>
                  <input
                    id="upc"
                    v-model="form.upc"
                    type="text"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.upc }"
                  />
                  <p v-if="errors.upc" class="mt-1 text-sm text-red-600">{{ errors.upc }}</p>
                </div>
                
                <!-- Category -->
                <div>
                  <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                  <select
                    id="category_id"
                    v-model="form.category_id"
                    class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.category_id }"
                  >
                    <option value="">No Category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                  <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                </div>
              </div>
              
              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  :class="{ 'border-red-300': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
              </div>
              
              <!-- Status -->
              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                  Active
                </label>
              </div>
            </div>
            
            <!-- Pricing Tab -->
            <div v-if="activeTab === 'pricing'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Price -->
                <div>
                  <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      id="price"
                      v-model="form.price"
                      type="number"
                      min="0"
                      step="0.01"
                      class="mt-1 block w-full pl-7 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.price }"
                      required
                    />
                  </div>
                  <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
                </div>
                
                <!-- Cost -->
                <div>
                  <label for="cost" class="block text-sm font-medium text-gray-700">Cost</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      id="cost"
                      v-model="form.cost"
                      type="number"
                      min="0"
                      step="0.01"
                      class="mt-1 block w-full pl-7 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.cost }"
                      required
                    />
                  </div>
                  <p v-if="errors.cost" class="mt-1 text-sm text-red-600">{{ errors.cost }}</p>
                </div>
                
                <!-- Margin (calculated, read-only) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Margin</label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                      type="text"
                      :value="calculateMargin"
                      class="mt-1 block w-full border border-gray-300 bg-gray-50 rounded-md shadow-sm py-2 px-3 focus:outline-none sm:text-sm"
                      readonly
                    />
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Physical Attributes Tab -->
            <div v-if="activeTab === 'physical'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Weight -->
                <div>
                  <label for="weight" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                  <input
                    id="weight"
                    v-model="form.weight"
                    type="number"
                    min="0"
                    step="0.01"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.weight }"
                  />
                  <p v-if="errors.weight" class="mt-1 text-sm text-red-600">{{ errors.weight }}</p>
                </div>
              </div>
              
              <!-- Dimensions -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Dimensions (cm)</label>
                <div class="mt-1 grid grid-cols-3 gap-4">
                  <div>
                    <label for="length" class="block text-xs font-medium text-gray-500">Length</label>
                    <input
                      id="length"
                      v-model="dimensions.length"
                      type="number"
                      min="0"
                      step="0.1"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                  </div>
                  <div>
                    <label for="width" class="block text-xs font-medium text-gray-500">Width</label>
                    <input
                      id="width"
                      v-model="dimensions.width"
                      type="number"
                      min="0"
                      step="0.1"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                  </div>
                  <div>
                    <label for="height" class="block text-xs font-medium text-gray-500">Height</label>
                    <input
                      id="height"
                      v-model="dimensions.height"
                      type="number"
                      min="0"
                      step="0.1"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                  </div>
                </div>
                <p v-if="errors.dimensions" class="mt-1 text-sm text-red-600">{{ errors.dimensions }}</p>
              </div>
            </div>
            
            <!-- Custom Attributes Tab -->
            <div v-if="activeTab === 'attributes'" class="space-y-6">
              <!-- Dynamic attributes list -->
              <div class="space-y-4">
                <div v-for="(attribute, index) in customAttributes" :key="index" class="grid grid-cols-2 gap-4">
                  <div>
                    <label :for="`attr-key-${index}`" class="block text-sm font-medium text-gray-700">Attribute Name</label>
                    <input
                      :id="`attr-key-${index}`"
                      v-model="attribute.key"
                      type="text"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                  </div>
                  <div class="flex items-start">
                    <div class="flex-grow">
                      <label :for="`attr-value-${index}`" class="block text-sm font-medium text-gray-700">Value</label>
                      <input
                        :id="`attr-value-${index}`"
                        v-model="attribute.value"
                        type="text"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      />
                    </div>
                    <button
                      @click.prevent="removeAttribute(index)"
                      class="mt-6 ml-2 p-1 bg-red-100 text-red-600 rounded hover:bg-red-200"
                    >
                      <span class="material-icons text-sm">close</span>
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Add attribute button -->
              <div>
                <button
                  @click.prevent="addAttribute"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="material-icons -ml-0.5 mr-2 text-sm">add</span>
                  Add Attribute
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted, watch } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Router and stores
  const route = useRoute();
  const router = useRouter();
  const alertStore = useAlertStore();
  
  // Get product ID from route params
  const productId = computed(() => route.params.id);
  
  // State variables
  const loading = ref(true);
  const isSubmitting = ref(false);
  const formError = ref('');
  const product = ref({});
  const categories = ref([]);
  
  // Form validation errors
  const errors = reactive({
    name: '',
    sku: '',
    upc: '',
    category_id: '',
    description: '',
    price: '',
    cost: '',
    weight: '',
    dimensions: ''
  });
  
  // Form tabs
  const activeTab = ref('basic');
  const tabs = [
    { id: 'basic', name: 'Basic Information' },
    { id: 'pricing', name: 'Pricing' },
    { id: 'physical', name: 'Physical Attributes' },
    { id: 'attributes', name: 'Custom Attributes' }
  ];
  
  // Form data
  const form = reactive({
    name: '',
    sku: '',
    upc: '',
    category_id: '',
    description: '',
    price: 0,
    cost: 0,
    weight: 0,
    is_active: true
  });
  
  // Dimensions (stored as JSON in database)
  const dimensions = reactive({
    length: 0,
    width: 0,
    height: 0
  });
  
  // Custom attributes (stored as JSON in database)
  const customAttributes = ref([]);
  
  /**
   * Calculate margin percentage based on price and cost
   */
  const calculateMargin = computed(() => {
    if (!form.price || form.price <= 0) return '0%';
    
    const price = parseFloat(form.price);
    const cost = parseFloat(form.cost || 0);
    
    if (cost >= price) return '0%';
    
    const margin = ((price - cost) / price) * 100;
    return `${margin.toFixed(2)}%`;
  });
  
  /**
   * Fetch product data from API
   */
  async function fetchProduct() {
    loading.value = true;
    
    try {
      const response = await axios.get(`/api/products/${productId.value}`);
      
      // Store product data
      product.value = response.data;
      
      // Populate form with product data
      Object.assign(form, {
        name: response.data.name || '',
        sku: response.data.sku || '',
        upc: response.data.upc || '',
        category_id: response.data.category_id || '',
        description: response.data.description || '',
        price: response.data.price || 0,
        cost: response.data.cost || 0,
        weight: response.data.weight || 0,
        is_active: response.data.is_active === 1 // Convert to boolean
      });
      
      // Parse dimensions from JSON if available
      if (response.data.dimensions) {
        try {
          const dims = typeof response.data.dimensions === 'string' 
            ? JSON.parse(response.data.dimensions) 
            : response.data.dimensions;
          
          Object.assign(dimensions, {
            length: dims.length || 0,
            width: dims.width || 0,
            height: dims.height || 0
          });
        } catch (e) {
          console.error('Error parsing dimensions:', e);
        }
      }
      
      // Parse attributes from JSON if available
      if (response.data.attributes) {
        try {
          const attrs = typeof response.data.attributes === 'string'
            ? JSON.parse(response.data.attributes)
            : response.data.attributes;
          
          // Convert object to array of key-value pairs
          customAttributes.value = Object.entries(attrs).map(([key, value]) => ({ key, value }));
        } catch (e) {
          console.error('Error parsing attributes:', e);
        }
      }
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching product:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load product details.');
      
      // Navigate back to products list on error
      router.push({ name: 'products' });
    }
  }
  
  /**
   * Fetch categories for dropdown
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
   * Add a new custom attribute
   */
  function addAttribute() {
    customAttributes.value.push({ key: '', value: '' });
  }
  
  /**
   * Remove a custom attribute
   * @param {number} index - Index of attribute to remove
   */
  function removeAttribute(index) {
    customAttributes.value.splice(index, 1);
  }
  
  /**
   * Validate form fields
   * @returns {boolean} True if form is valid
   */
  function validateForm() {
    // Reset all errors
    Object.keys(errors).forEach(key => {
      errors[key] = '';
    });
    
    let isValid = true;
    
    // Validate required fields
    if (!form.name || form.name.trim() === '') {
      errors.name = 'Product name is required';
      isValid = false;
    }
    
    if (!form.sku || form.sku.trim() === '') {
      errors.sku = 'SKU is required';
      isValid = false;
    }
    
    // Validate price and cost
    if (form.price < 0) {
      errors.price = 'Price cannot be negative';
      isValid = false;
    }
    
    if (form.cost < 0) {
      errors.cost = 'Cost cannot be negative';
      isValid = false;
    }
    
    // Validate weight
    if (form.weight < 0) {
      errors.weight = 'Weight cannot be negative';
      isValid = false;
    }
    
    return isValid;
  }
  
  /**
   * Save product changes
   */
  async function saveProduct() {
    // Validate form
    if (!validateForm()) {
      // Scroll to first error
      const firstErrorField = Object.keys(errors).find(key => errors[key]);
      if (firstErrorField) {
        document.getElementById(firstErrorField)?.focus();
      }
      
      // Show error at top of form
      formError.value = 'Please correct the errors in the form';
      return;
    }
    
    // Clear form error
    formError.value = '';
    
    // Set loading state
    isSubmitting.value = true;
    
    try {
      // Prepare data for submission
      const formData = { ...form };
      
      // Add dimensions as JSON
      formData.dimensions = JSON.stringify(dimensions);
      
      // Add attributes as JSON
      const attributesObject = {};
      customAttributes.value.forEach(attr => {
        if (attr.key && attr.key.trim() !== '') {
          attributesObject[attr.key.trim()] = attr.value;
        }
      });
      formData.attributes = JSON.stringify(attributesObject);
      
      // Submit to API
      const response = await axios.put(`/api/products/${productId.value}`, formData);
      
      // Show success message
      alertStore.setSuccessAlert('Product updated successfully');
      
      // Navigate back to product detail view
      router.push({ name: 'product-detail', params: { id: productId.value } });
    } catch (error) {
      console.error('Error updating product:', error);
      
      if (error.response && error.response.status === 422) {
        // Validation errors
        const validationErrors = error.response.data.errors;
        
        // Update error messages
        Object.keys(validationErrors).forEach(key => {
          if (errors.hasOwnProperty(key)) {
            errors[key] = validationErrors[key][0];
          }
        });
        
        formError.value = 'Please correct the errors in the form';
      } else {
        // Other errors
        alertStore.setApiErrorAlert(error, 'Failed to update product');
        formError.value = 'An error occurred while saving the product';
      }
      
      isSubmitting.value = false;
    }
  }
  
  // Watch for tab changes to handle form validation
  watch(activeTab, (newTab) => {
    // Clear form error when changing tabs
    formError.value = '';
  });
  
  // Initialize component
  onMounted(async () => {
    // Fetch categories and product data in parallel
    await Promise.all([
      fetchCategories(),
      fetchProduct()
    ]);
  });
  </script>
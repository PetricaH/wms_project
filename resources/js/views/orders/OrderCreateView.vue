<template>
    <div class="p-6 bg-gray-50 min-h-screen">
      <h1 class="text-2xl font-semibold text-gray-800 mb-6">Create New Order</h1>
  
       <div v-if="isLoading" class="text-center py-10">Loading initial data...</div>
       <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">{{ error }}</div>
  
      <form v-else @submit.prevent="submitOrder" class="space-y-8 bg-white p-8 shadow rounded-lg">
  
        <section>
          <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Customer & Order Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name *</label>
               <input type="text" id="customer_name" v-model="orderData.customer_name" required
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                     :class="{ 'border-red-500': validationErrors.customer_name }"/>
               <p v-if="validationErrors.customer_name" class="text-red-500 text-xs mt-1">{{ validationErrors.customer_name[0] }}</p>
            </div>
             <div>
              <label for="customer_email" class="block text-sm font-medium text-gray-700">Customer Email</label>
              <input type="email" id="customer_email" v-model="orderData.customer_email"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                     :class="{ 'border-red-500': validationErrors.customer_email }"/>
               <p v-if="validationErrors.customer_email" class="text-red-500 text-xs mt-1">{{ validationErrors.customer_email[0] }}</p>
            </div>
             <div>
              <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
               <input type="tel" id="customer_phone" v-model="orderData.customer_phone"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                     :class="{ 'border-red-500': validationErrors.customer_phone }"/>
               <p v-if="validationErrors.customer_phone" class="text-red-500 text-xs mt-1">{{ validationErrors.customer_phone[0] }}</p>
            </div>
  
            <div>
              <label for="order_date" class="block text-sm font-medium text-gray-700">Order Date *</label>
              <input type="date" id="order_date" v-model="orderData.order_date" required
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                     :class="{ 'border-red-500': validationErrors.order_date }"/>
               <p v-if="validationErrors.order_date" class="text-red-500 text-xs mt-1">{{ validationErrors.order_date[0] }}</p>
            </div>
             <div>
              <label for="warehouse" class="block text-sm font-medium text-gray-700">Fulfillment Warehouse *</label>
               <select id="warehouse" v-model="orderData.warehouse_id" required
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                      :class="{ 'border-red-500': validationErrors.warehouse_id }">
                <option value="">Select Warehouse</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }} ({{ warehouse.code }})
                </option>
              </select>
               <p v-if="validationErrors.warehouse_id" class="text-red-500 text-xs mt-1">{{ validationErrors.warehouse_id[0] }}</p>
            </div>
             <div>
              <label for="currency" class="block text-sm font-medium text-gray-700">Currency *</label>
              <input type="text" id="currency" v-model="orderData.currency" required placeholder="e.g., USD"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-500': validationErrors.currency }"/>
              <p v-if="validationErrors.currency" class="text-red-500 text-xs mt-1">{{ validationErrors.currency[0] }}</p>
            </div>
              <div>
                  <label for="source" class="block text-sm font-medium text-gray-700">Order Source</label>
                  <input type="text" id="source" v-model="orderData.source" placeholder="e.g., website, phone"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-500': validationErrors.source }"/>
                  <p v-if="validationErrors.source" class="text-red-500 text-xs mt-1">{{ validationErrors.source[0] }}</p>
              </div>
               <div>
                  <label for="external_order_id" class="block text-sm font-medium text-gray-700">External Order ID</label>
                  <input type="text" id="external_order_id" v-model="orderData.external_order_id" placeholder="ID from e-commerce platform"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-500': validationErrors.external_order_id }"/>
                  <p v-if="validationErrors.external_order_id" class="text-red-500 text-xs mt-1">{{ validationErrors.external_order_id[0] }}</p>
              </div>
          </div>
        </section>
  
        <section>
            <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Shipping Address *</h2>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                      <label for="shipping_line1" class="block text-sm font-medium text-gray-700">Address Line 1 *</label>
                      <input type="text" id="shipping_line1" v-model="orderData.shipping_address.line1" required class="mt-1 block w-full address-input" :class="{ 'border-red-500': validationErrors['shipping_address.line1'] }"/>
                       <p v-if="validationErrors['shipping_address.line1']" class="text-red-500 text-xs mt-1">{{ validationErrors['shipping_address.line1'][0] }}</p>
                  </div>
                   <div>
                      <label for="shipping_city" class="block text-sm font-medium text-gray-700">City *</label>
                      <input type="text" id="shipping_city" v-model="orderData.shipping_address.city" required class="mt-1 block w-full address-input" :class="{ 'border-red-500': validationErrors['shipping_address.city'] }"/>
                       <p v-if="validationErrors['shipping_address.city']" class="text-red-500 text-xs mt-1">{{ validationErrors['shipping_address.city'][0] }}</p>
                  </div>
                   <div>
                      <label for="shipping_state" class="block text-sm font-medium text-gray-700">State/Province *</label>
                      <input type="text" id="shipping_state" v-model="orderData.shipping_address.state" required class="mt-1 block w-full address-input" :class="{ 'border-red-500': validationErrors['shipping_address.state'] }"/>
                      <p v-if="validationErrors['shipping_address.state']" class="text-red-500 text-xs mt-1">{{ validationErrors['shipping_address.state'][0] }}</p>
                  </div>
                   <div>
                      <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700">Postal Code *</label>
                      <input type="text" id="shipping_postal_code" v-model="orderData.shipping_address.postal_code" required class="mt-1 block w-full address-input" :class="{ 'border-red-500': validationErrors['shipping_address.postal_code'] }"/>
                       <p v-if="validationErrors['shipping_address.postal_code']" class="text-red-500 text-xs mt-1">{{ validationErrors['shipping_address.postal_code'][0] }}</p>
                  </div>
                   <div>
                      <label for="shipping_country" class="block text-sm font-medium text-gray-700">Country *</label>
                      <input type="text" id="shipping_country" v-model="orderData.shipping_address.country" required placeholder="e.g., USA" class="mt-1 block w-full address-input" :class="{ 'border-red-500': validationErrors['shipping_address.country'] }"/>
                       <p v-if="validationErrors['shipping_address.country']" class="text-red-500 text-xs mt-1">{{ validationErrors['shipping_address.country'][0] }}</p>
                  </div>
             </div>
        </section>
        <section>
          <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Order Items</h2>
           <p v-if="validationErrors.items" class="text-red-500 text-sm mb-2">{{ validationErrors.items[0] }}</p>
  
          <div v-for="(item, index) in orderData.items" :key="index" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 items-end p-4 border rounded">
            <div>
              <label :for="'product-' + index" class="block text-sm font-medium text-gray-700">Product *</label>
               <select :id="'product-' + index" v-model="item.product_id" required
                      @change="updateItemDetails(item)"
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                      :class="{ 'border-red-500': validationErrors[`items.${index}.product_id`] }">
                <option value="">Select Product</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.name }} ({{ product.sku }})
                </option>
              </select>
               <p v-if="validationErrors[`items.${index}.product_id`]" class="text-red-500 text-xs mt-1">{{ validationErrors[`items.${index}.product_id`][0] }}</p>
            </div>
            <div>
              <label :for="'quantity-' + index" class="block text-sm font-medium text-gray-700">Quantity *</label>
              <input type="number" :id="'quantity-' + index" v-model.number="item.quantity" required min="1"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      :class="{ 'border-red-500': validationErrors[`items.${index}.quantity`] }"/>
               <p v-if="validationErrors[`items.${index}.quantity`]" class="text-red-500 text-xs mt-1">{{ validationErrors[`items.${index}.quantity`][0] }}</p>
            </div>
             <div>
              <label :for="'uom-' + index" class="block text-sm font-medium text-gray-700">Unit</label>
               <input type="text" :id="'uom-' + index" v-model="item.unit_of_measure" readonly placeholder="Auto"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm"/>
            </div>
            <div>
              <label :for="'price-' + index" class="block text-sm font-medium text-gray-700">Unit Price *</label>
              <input type="number" step="0.01" :id="'price-' + index" v-model.number="item.unit_price" required min="0"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                     :class="{ 'border-red-500': validationErrors[`items.${index}.unit_price`] }"/>
               <p v-if="validationErrors[`items.${index}.unit_price`]" class="text-red-500 text-xs mt-1">{{ validationErrors[`items.${index}.unit_price`][0] }}</p>
            </div>
            <div>
               <button type="button" @click="removeItem(index)"
                      class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Remove
              </button>
            </div>
          </div>
  
           <button type="button" @click="addItem"
                  class="mt-2 px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Add Item
          </button>
        </section>
  
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t">
              <div>
                  <label for="internal_notes" class="block text-sm font-medium text-gray-700">Internal Notes</label>
                  <textarea id="internal_notes" v-model="orderData.internal_notes" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
              </div>
               <div class="text-right space-y-1">
                  <p class="text-md text-gray-600">Subtotal: <span class="font-semibold">{{ formatCurrency(totalSubtotal) }}</span></p>
                  <p class="text-xl font-bold text-gray-900">Total: <span class="font-semibold">{{ formatCurrency(totalSubtotal) }}</span></p>
              </div>
        </section>
  
  
        <div class="flex justify-end pt-8 border-t">
           <button type="submit" :disabled="isSubmitting"
                  class="px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
            {{ isSubmitting ? 'Creating Order...' : 'Create Order' }}
          </button>
        </div>
          <div v-if="submitError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4" role="alert">
              <strong class="font-bold">Order Creation Failed:</strong>
              <span class="block sm:inline">{{ submitError }}</span>
          </div>
      </form>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, onMounted, computed } from 'vue';
  import { useRouter } from 'vue-router';
  // Assume apiService and useAlertStore are correctly set up
  // import apiService from '@/services/apiService';
  // import { useAlertStore } from '@/stores/alert';
  
  // --- Mocks/Placeholders ---
  const mockWarehouses = [ { id: 1, name: 'Main Warehouse', code: 'WH-MAIN' }, { id: 2, name: 'East Dock', code: 'WH-EAST' }];
  const mockProducts = [
      { id: 10, name: 'Product A', sku: 'PROD-A', price: 19.99, unit_of_measure: 'EA' }, // Use 'price' for selling price
      { id: 20, name: 'Product B', sku: 'PROD-B', price: 49.50, unit_of_measure: 'EA' },
      { id: 30, name: 'Case of Bolts', sku: 'BOLT-CS', price: 75.00, unit_of_measure: 'CS' },
  ];
  const apiService = {
    get: async (url) => {
      console.log(`Mock GET: ${url}`);
      await new Promise(resolve => setTimeout(resolve, 300));
      if (url === '/api/warehouses') return { data: mockWarehouses };
      if (url === '/api/products') return { data: mockProducts };
      throw new Error(`Mock GET endpoint not found: ${url}`);
    },
    post: async (url, data) => {
      console.log(`Mock POST: ${url}`, data);
      await new Promise(resolve => setTimeout(resolve, 800));
      if (url === '/api/orders') {
          // Simulate validation error
          if (!data.customer_name || data.items.length === 0 || !data.shipping_address.city) {
               const error = new Error("Validation Failed");
               error.response = {
                  status: 422,
                  data: {
                      message: "The given data was invalid.",
                      errors: {
                          customer_name: !data.customer_name ? ["Customer name is required."] : undefined,
                          items: data.items.length === 0 ? ["Add at least one item."] : undefined,
                           "shipping_address.city": !data.shipping_address.city ? ["Shipping city is required."] : undefined,
                          // Add more specific item errors if needed
                      }
                  }
              };
               throw error;
          }
          // Simulate general error
          if (Math.random() < 0.1) throw new Error("Simulated server error during order creation.");
          // Simulate success
          return { data: { id: Math.floor(Math.random() * 1000) + 5000, order_number: `SO-${Math.floor(Math.random() * 10000)}`, ...data } };
      }
      throw new Error(`Mock POST endpoint not found: ${url}`);
    }
  };
  const useAlertStore = () => ({
    show: (alert) => console.log(`Alert: ${alert.message} (${alert.type})`)
  });
  const router = { push: (path) => console.log(`Router push to: ${path}`) }; // Mock router
  // --- End Mocks ---
  
  const alertStore = useAlertStore();
  // const router = useRouter(); // Use actual router
  
  // --- Component State ---
  const isLoading = ref(false); // Loading initial data (warehouses, products)
  const error = ref(null); // Error fetching initial data
  const isSubmitting = ref(false); // Form submission state
  const submitError = ref(null); // Error from submission
  const validationErrors = ref({}); // Validation errors from backend
  
  const warehouses = ref([]);
  const products = ref([]);
  
  // Reactive object for the order form data
  const orderData = reactive({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    order_date: new Date().toISOString().split('T')[0], // Default today
    warehouse_id: '',
    currency: 'USD',
    source: '',
    external_order_id: '',
    shipping_address: {
      line1: '',
      line2: '',
      city: '',
      state: '',
      postal_code: '',
      country: ''
    },
    billing_address: { /* Add fields if needed, or logic to copy shipping */ },
    internal_notes: '',
    // Add other fields like payment_method, shipping_method if needed
    items: [
      // Initial empty item line
      // { product_id: '', quantity: 1, unit_of_measure: '', unit_price: 0 }
    ]
  });
  
  // --- Computed Properties ---
  const totalSubtotal = computed(() => {
      return orderData.items.reduce((sum, item) => {
          const quantity = Number(item.quantity) || 0;
          const price = Number(item.unit_price) || 0;
          return sum + (quantity * price);
      }, 0);
  });
  
  // --- Methods ---
  
  /**
   * Fetches necessary data (warehouses, products) for the form.
   */
  const fetchInitialData = async () => {
    isLoading.value = true;
    error.value = null;
    try {
      const [warehouseRes, productRes] = await Promise.all([
        apiService.get('/api/warehouses'),
        apiService.get('/api/products?is_active=true') // Fetch active products
      ]);
      warehouses.value = warehouseRes.data;
      products.value = productRes.data;
  
       // Add one initial empty item line after data is loaded
       if (orderData.items.length === 0) {
           addItem();
       }
    } catch (err) {
      console.error("Error fetching initial data for Order form:", err);
      error.value = "Failed to load required data. Please try refreshing.";
      alertStore.show({ message: error.value, type: 'error' });
    } finally {
      isLoading.value = false;
    }
  };
  
  /**
   * Adds a new empty item line to the order.
   */
  const addItem = () => {
    orderData.items.push({
      product_id: '',
      quantity: 1,
      unit_of_measure: '', // Auto-filled from product
      unit_price: 0, // Auto-filled from product
      // Add other item fields: sku, name, description (can be auto-filled), tax_rate, discount...
    });
  };
  
  /**
   * Removes an item line from the order.
   * @param {number} index - The index of the item to remove.
   */
  const removeItem = (index) => {
    orderData.items.splice(index, 1);
  };
  
  /**
   * Updates item details (price, UoM) based on the selected product.
   * @param {object} item - The order item line being modified.
   */
  const updateItemDetails = (item) => {
      const selectedProduct = products.value.find(p => p.id === item.product_id);
      if (selectedProduct) {
          item.unit_price = selectedProduct.price || 0; // Use selling price
          item.unit_of_measure = selectedProduct.unit_of_measure || '';
          // Optionally fill item.sku, item.name too
      } else {
           item.unit_price = 0;
           item.unit_of_measure = '';
      }
  };
  
  /**
   * Formats a number as currency.
   */
  const formatCurrency = (value) => {
      const amount = Number(value) || 0;
      return `${orderData.currency} ${amount.toFixed(2)}`;
  };
  
  
  /**
   * Validates and submits the new order data to the backend.
   */
  const submitOrder = async () => {
    isSubmitting.value = true;
    submitError.value = null;
    validationErrors.value = {};
  
    // Simple Client-side Checks (Backend validation is primary)
     if (orderData.items.length === 0) {
         submitError.value = "Please add at least one item to the order.";
         isSubmitting.value = false;
         return;
     }
      // Check for required address fields
      const requiredAddressFields = ['line1', 'city', 'state', 'postal_code', 'country'];
      const missingAddressField = requiredAddressFields.find(field => !orderData.shipping_address[field]);
      if (missingAddressField) {
           submitError.value = `Shipping address field "${missingAddressField}" is required.`;
           isSubmitting.value = false;
           return;
      }
  
  
    try {
      // API Endpoint: POST /api/orders
      const response = await apiService.post('/api/orders', orderData);
      alertStore.show({ message: `Order ${response.data.order_number} created successfully!`, type: 'success' });
      // Redirect to the new order's detail page or order list
      router.push(`/orders/${response.data.id}`); // Adjust route as necessary
    } catch (err) {
       if (err.response && err.response.status === 422) {
        validationErrors.value = err.response.data.errors;
        submitError.value = err.response.data.message || "Validation failed. Please check the fields.";
        alertStore.show({ message: "Please correct the errors in the form.", type: 'warning' });
      } else {
        console.error("Error creating order:", err);
        submitError.value = err.response?.data?.message || err.message || 'An unexpected error occurred during order creation.';
        alertStore.show({ message: `Failed to create order: ${submitError.value}`, type: 'error' });
      }
    } finally {
      isSubmitting.value = false;
    }
  };
  
  // --- Lifecycle Hooks ---
  onMounted(() => {
    fetchInitialData(); // Fetch warehouses, products on mount
  });
  </script>
  
  <style scoped>
  /* Enhance styling for address inputs or other elements */
  .address-input {
     @apply mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm;
  }
  </style>
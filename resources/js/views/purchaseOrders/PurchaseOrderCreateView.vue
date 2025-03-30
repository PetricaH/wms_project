<!-- resources/js/views/purchaseOrders/PurchaseOrderCreateView.vue -->

<template>
    <div>
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Create Purchase Order</h1>
        
        <!-- Cancel button -->
        <router-link
          to="/dashboard/purchase-orders"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <span class="material-icons -ml-1 mr-2 text-gray-500 text-sm">cancel</span>
          Cancel
        </router-link>
      </div>
      
      <!-- Loading state -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <!-- Form -->
      <div v-else class="bg-white shadow rounded-lg">
        <form @submit.prevent="savePurchaseOrder">
          <!-- Form header section -->
          <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Purchase Order Information</h3>
            <p class="mt-1 text-sm text-gray-500">
              Fill in the details to create a new purchase order.
            </p>
          </div>
          
          <!-- Form fields -->
          <div class="p-6 bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Basic Information -->
              <div class="col-span-1 md:col-span-2">
                <h4 class="text-base font-medium text-gray-900 mb-3">Basic Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Warehouse selection -->
                  <div>
                    <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Warehouse *</label>
                    <select
                      id="warehouse_id"
                      v-model="formData.warehouse_id"
                      class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      required
                    >
                      <option value="" disabled>Select a warehouse</option>
                      <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                        {{ warehouse.name }}
                      </option>
                    </select>
                    <p v-if="errors.warehouse_id" class="mt-1 text-sm text-red-600">{{ errors.warehouse_id }}</p>
                  </div>
                  
                  <!-- Supplier selection -->
                  <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier *</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                      <select
                        id="supplier_id"
                        v-model="formData.supplier_id"
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        required
                        @change="supplierChanged"
                      >
                        <option value="" disabled>Select a supplier</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                          {{ supplier.name }}
                        </option>
                      </select>
                      <!-- Add supplier button -->
                      <button
                        v-if="hasPermission('suppliers.create')"
                        type="button"
                        @click="showAddSupplierDialog = true"
                        class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                      >
                        <span class="material-icons -ml-0.5 mr-1 text-sm">add</span>
                        New
                      </button>
                    </div>
                    <p v-if="errors.supplier_id" class="mt-1 text-sm text-red-600">{{ errors.supplier_id }}</p>
                  </div>
                  
                  <!-- Order date -->
                  <div>
                    <label for="order_date" class="block text-sm font-medium text-gray-700">Order Date *</label>
                    <input
                      type="date"
                      id="order_date"
                      v-model="formData.order_date"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      required
                    />
                    <p v-if="errors.order_date" class="mt-1 text-sm text-red-600">{{ errors.order_date }}</p>
                  </div>
                  
                  <!-- Expected delivery date -->
                  <div>
                    <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700">Expected Delivery Date *</label>
                    <input
                      type="date"
                      id="expected_delivery_date"
                      v-model="formData.expected_delivery_date"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      required
                    />
                    <p v-if="errors.expected_delivery_date" class="mt-1 text-sm text-red-600">{{ errors.expected_delivery_date }}</p>
                  </div>
                  
                  <!-- Supplier reference -->
                  <div>
                    <label for="supplier_reference" class="block text-sm font-medium text-gray-700">Supplier Reference</label>
                    <input
                      type="text"
                      id="supplier_reference"
                      v-model="formData.supplier_reference"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      placeholder="Supplier's reference number"
                    />
                    <p v-if="errors.supplier_reference" class="mt-1 text-sm text-red-600">{{ errors.supplier_reference }}</p>
                  </div>
                  
                  <!-- Currency -->
                  <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700">Currency *</label>
                    <select
                      id="currency"
                      v-model="formData.currency"
                      class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      required
                    >
                      <option value="" disabled>Select currency</option>
                      <option value="USD">USD - US Dollar</option>
                      <option value="EUR">EUR - Euro</option>
                      <option value="GBP">GBP - British Pound</option>
                      <option value="CAD">CAD - Canadian Dollar</option>
                      <option value="AUD">AUD - Australian Dollar</option>
                      <option value="JPY">JPY - Japanese Yen</option>
                    </select>
                    <p v-if="errors.currency" class="mt-1 text-sm text-red-600">{{ errors.currency }}</p>
                  </div>
                </div>
                
                <!-- Payment terms and partial receiving -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                  <div>
                    <label for="payment_terms" class="block text-sm font-medium text-gray-700">Payment Terms</label>
                    <input
                      type="text"
                      id="payment_terms"
                      v-model="formData.payment_terms"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      placeholder="e.g., Net 30"
                    />
                    <p v-if="errors.payment_terms" class="mt-1 text-sm text-red-600">{{ errors.payment_terms }}</p>
                  </div>
                  
                  <div class="flex items-start mt-5">
                    <div class="flex items-center h-5">
                      <input
                        id="allows_partial_receiving"
                        v-model="formData.allows_partial_receiving"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="allows_partial_receiving" class="font-medium text-gray-700">Allow Partial Receiving</label>
                      <p class="text-gray-500">Enable to allow receiving items in multiple shipments</p>
                    </div>
                  </div>
                </div>
                
                <!-- Notes -->
                <div class="mt-4">
                  <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                  <textarea
                    id="notes"
                    v-model="formData.notes"
                    rows="3"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    placeholder="Additional notes or instructions"
                  ></textarea>
                  <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
                </div>
              </div>
            </div>
            
            <!-- Line Items -->
            <div class="mt-8">
              <h4 class="text-base font-medium text-gray-900 mb-3">Line Items</h4>
              <p v-if="errors.items" class="mt-1 text-sm text-red-600">{{ errors.items }}</p>
              
              <!-- Product selector -->
              <div class="bg-gray-50 p-4 rounded-md mb-4">
                <div class="flex flex-wrap gap-4 items-end">
                  <div class="w-full md:w-80">
                    <label for="product_selector" class="block text-sm font-medium text-gray-700">Add Product</label>
                    <select
                      id="product_selector"
                      v-model="selectedProduct"
                      class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                      <option value="">Select a product</option>
                      <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.name }} ({{ product.sku }})
                      </option>
                    </select>
                  </div>
                  
                  <div>
                    <label for="product_quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input
                      type="number"
                      id="product_quantity"
                      v-model.number="productQuantity"
                      min="1"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      placeholder="Qty"
                    />
                  </div>
                  
                  <div>
                    <button
                      type="button"
                      @click="addProductToOrder"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                      :disabled="!selectedProduct || !productQuantity || productQuantity < 1"
                    >
                      Add to Order
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Items table -->
              <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Product
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Quantity
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unit Price
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unit of Measure
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total
                      </th>
                      <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="formData.items.length === 0">
                      <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                        No items added to this purchase order
                      </td>
                    </tr>
                    <tr v-for="(item, index) in formData.items" :key="index" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ item.product.name }}</div>
                        <div class="text-sm text-gray-500">{{ item.product.sku }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <input
                          type="number"
                          v-model.number="item.quantity_ordered"
                          min="1"
                          @change="updateItemTotal(item)"
                          class="focus:ring-blue-500 focus:border-blue-500 block w-20 shadow-sm sm:text-sm border-gray-300 rounded-md"
                        />
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <input
                          type="number"
                          v-model.number="item.unit_price"
                          min="0"
                          step="0.01"
                          @change="updateItemTotal(item)"
                          class="focus:ring-blue-500 focus:border-blue-500 block w-24 shadow-sm sm:text-sm border-gray-300 rounded-md"
                        />
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <select
                          v-model="item.unit_of_measure"
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                          <option value="each">Each</option>
                          <option value="case">Case</option>
                          <option value="pallet">Pallet</option>
                          <option value="kg">Kg</option>
                          <option value="lb">Lb</option>
                        </select>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ formatCurrency(item.total) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button
                          type="button"
                          @click="removeItem(index)"
                          class="text-red-600 hover:text-red-900"
                        >
                          <span class="material-icons text-sm">delete</span>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50">
                    <tr>
                      <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                        Subtotal
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900">
                        {{ formatCurrency(subtotal) }}
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                        Tax
                      </td>
                      <td class="px-6 py-4">
                        <input
                          type="number"
                          v-model.number="formData.tax_amount"
                          min="0"
                          step="0.01"
                          class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                          placeholder="0.00"
                        />
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                        Shipping
                      </td>
                      <td class="px-6 py-4">
                        <input
                          type="number"
                          v-model.number="formData.shipping_cost"
                          min="0"
                          step="0.01"
                          class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                          placeholder="0.00"
                        />
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="4" class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                        Total
                      </td>
                      <td class="px-6 py-4 text-base font-bold text-gray-900">
                        {{ formatCurrency(totalAmount) }}
                      </td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          
          <!-- Form actions -->
          <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
            <!-- Save as draft button -->
            <button
              type="button"
              @click="saveAsDraft"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="isSaving"
            >
              <span v-if="isSaving && saveAction === 'draft'" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              Save as Draft
            </button>
            
            <!-- Save and submit button -->
            <button
              type="submit"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :disabled="isSaving"
            >
              <span v-if="isSaving && saveAction === 'submit'" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              Save and Submit
            </button>
          </div>
        </form>
      </div>
      
      <!-- Add supplier dialog -->
      <div 
        v-if="showAddSupplierDialog" 
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
            @click="showAddSupplierDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div>
                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Add New Supplier
                  </h3>
                  <div class="mt-4">
                    <!-- Supplier form -->
                    <form @submit.prevent="saveSupplier">
                      <!-- Supplier name -->
                      <div class="mb-4">
                        <label for="supplier_name" class="block text-sm font-medium text-gray-700">Supplier Name *</label>
                        <input
                          type="text"
                          id="supplier_name"
                          v-model="supplierForm.name"
                          required
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                        />
                        <p v-if="supplierErrors.name" class="mt-1 text-sm text-red-600">{{ supplierErrors.name }}</p>
                      </div>
                      
                      <!-- Supplier code -->
                      <div class="mb-4">
                        <label for="supplier_code" class="block text-sm font-medium text-gray-700">Supplier Code *</label>
                        <input
                          type="text"
                          id="supplier_code"
                          v-model="supplierForm.code"
                          required
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                          placeholder="E.g., SUP-001"
                        />
                        <p v-if="supplierErrors.code" class="mt-1 text-sm text-red-600">{{ supplierErrors.code }}</p>
                      </div>
                      
                      <!-- Contact information -->
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                          <label for="contact_name" class="block text-sm font-medium text-gray-700">Contact Name</label>
                          <input
                            type="text"
                            id="contact_name"
                            v-model="supplierForm.contact_name"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                          />
                        </div>
                        <div>
                          <label for="contact_email" class="block text-sm font-medium text-gray-700">Email</label>
                          <input
                            type="email"
                            id="contact_email"
                            v-model="supplierForm.email"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                          />
                          <p v-if="supplierErrors.email" class="mt-1 text-sm text-red-600">{{ supplierErrors.email }}</p>
                        </div>
                      </div>
                      
                      <!-- Active status -->
                      <div class="mb-4 flex items-center">
                        <input
                          id="supplier_active"
                          v-model="supplierForm.is_active"
                          type="checkbox"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="supplier_active" class="ml-2 block text-sm text-gray-900">
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
                @click="saveSupplier"
                :disabled="isSavingSupplier"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSavingSupplier" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSavingSupplier ? 'Saving...' : 'Save Supplier' }}
              </button>
              <button
                @click="showAddSupplierDialog = false"
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
  
  // Loading and saving states
  const isLoading = ref(true);
  const isSaving = ref(false);
  const saveAction = ref(''); // 'draft' or 'submit'
  
  // Options for dropdowns
  const warehouses = ref([]);
  const suppliers = ref([]);
  const products = ref([]);
  
  // New product form
  const selectedProduct = ref('');
  const productQuantity = ref(1);
  
  // Add supplier dialog
  const showAddSupplierDialog = ref(false);
  const isSavingSupplier = ref(false);
  const supplierForm = reactive({
    name: '',
    code: '',
    contact_name: '',
    email: '',
    is_active: true
  });
  const supplierErrors = reactive({});
  
  // PO form data
  const formData = reactive({
    warehouse_id: '',
    supplier_id: '',
    order_date: new Date().toISOString().split('T')[0], // Today's date
    expected_delivery_date: '',
    supplier_reference: '',
    currency: 'USD',
    payment_terms: '',
    notes: '',
    allows_partial_receiving: true,
    tax_amount: 0,
    shipping_cost: 0,
    items: []
  });
  
  // Form errors
  const errors = reactive({});
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Calculate subtotal from line items
   */
  const subtotal = computed(() => {
    return formData.items.reduce((sum, item) => sum + (item.total || 0), 0);
  });
  
  /**
   * Calculate total amount including tax and shipping
   */
  const totalAmount = computed(() => {
    return subtotal.value + (formData.tax_amount || 0) + (formData.shipping_cost || 0);
  });
  
  /**
   * Format currency amount with appropriate symbol
   * @param {number} amount - Amount to format
   * @returns {string} Formatted currency string
   */
  function formatCurrency(amount) {
    if (amount === undefined || amount === null) return 'N/A';
    
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: formData.currency || 'USD'
    }).format(amount);
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
      
      // Set default warehouse if available
      if (warehouses.value.length > 0 && !formData.warehouse_id) {
        formData.warehouse_id = warehouses.value[0].id;
      }
    } catch (error) {
      console.error('Error fetching warehouses:', error);
      alertStore.setErrorAlert('Failed to load warehouses.');
    }
  }
  
  /**
   * Fetch suppliers from API
   */
  async function fetchSuppliers() {
    try {
      const response = await axios.get('/api/suppliers', {
        params: { is_active: 1 }
      });
      
      suppliers.value = response.data;
    } catch (error) {
      console.error('Error fetching suppliers:', error);
      alertStore.setErrorAlert('Failed to load suppliers.');
    }
  }
  
  /**
   * Fetch products from API
   */
  async function fetchProducts() {
    try {
      const response = await axios.get('/api/products', {
        params: { 
          is_active: 1,
          limit: 500,
          with_pricing: true
        }
      });
      
      products.value = response.data.data;
    } catch (error) {
      console.error('Error fetching products:', error);
      alertStore.setErrorAlert('Failed to load products.');
    }
  }
  
  /**
   * Handler for supplier change
   * Sets default currency and payment terms based on selected supplier
   */
  function supplierChanged() {
    if (!formData.supplier_id) return;
    
    const supplier = suppliers.value.find(s => s.id === formData.supplier_id);
    if (supplier) {
      if (supplier.currency) {
        formData.currency = supplier.currency;
      }
      
      if (supplier.payment_terms) {
        formData.payment_terms = supplier.payment_terms;
      }
    }
  }
  
  /**
   * Add product to the order
   */
  function addProductToOrder() {
    if (!selectedProduct.value || !productQuantity.value || productQuantity.value < 1) return;
    
    const product = products.value.find(p => p.id === selectedProduct.value);
    if (!product) return;
    
    // Create new line item
    const newItem = {
      product_id: product.id,
      product: product,
      quantity_ordered: productQuantity.value,
      unit_price: product.cost || 0,
      unit_of_measure: 'each',
      total: (product.cost || 0) * productQuantity.value
    };
    
    // Add to items array
    formData.items.push(newItem);
    
    // Reset form
    selectedProduct.value = '';
    productQuantity.value = 1;
  }
  
  /**
   * Update item total when quantity or price changes
   * @param {Object} item - Line item to update
   */
  function updateItemTotal(item) {
    item.total = (item.unit_price || 0) * (item.quantity_ordered || 0);
  }
  
  /**
   * Remove item from the order
   * @param {number} index - Index of item to remove
   */
  function removeItem(index) {
    formData.items.splice(index, 1);
  }
  
  /**
   * Save as draft
   */
  function saveAsDraft() {
    saveAction.value = 'draft';
    formData.status = 'draft';
    savePurchaseOrder();
  }
  
  /**
   * Save purchase order
   */
  async function savePurchaseOrder() {
    // Validate form
    if (!validateForm()) return;
    
    // Set saving state
    isSaving.value = true;
    
    if (saveAction.value !== 'draft') {
      formData.status = 'awaiting_approval';
    }
    
    try {
      // Format data for API
      const requestData = {
        ...formData,
        subtotal: subtotal.value,
        total_amount: totalAmount.value,
        // Format items for API
        items: formData.items.map(item => ({
          product_id: item.product_id,
          quantity_ordered: item.quantity_ordered,
          unit_price: item.unit_price,
          unit_of_measure: item.unit_of_measure,
          subtotal: item.total,
          total: item.total
        }))
      };
      
      // Submit to API
      const response = await axios.post('/api/purchase-orders', requestData);
      
      // Show success notification
      alertStore.setSuccessAlert(
        saveAction.value === 'draft' 
          ? 'Purchase order saved as draft.' 
          : 'Purchase order created and submitted for approval.'
      );
      
      // Navigate to purchase order detail
      router.push(`/dashboard/purchase-orders/${response.data.id}`);
    } catch (error) {
      console.error('Error saving purchase order:', error);
      
      // Handle validation errors
      if (error.response && error.response.status === 422) {
        const validationErrors = error.response.data.errors;
        
        // Map backend validation errors to form fields
        Object.keys(validationErrors).forEach(key => {
          if (errors.hasOwnProperty(key)) {
            errors[key] = validationErrors[key][0];
          } else if (key.startsWith('items.')) {
            errors.items = validationErrors[key][0];
          }
        });
        
        alertStore.setErrorAlert('Please correct the errors in the form.');
      } else {
        alertStore.setApiErrorAlert(error, 'Failed to save purchase order.');
      }
      
      isSaving.value = false;
    }
  }
  
  /**
   * Save new supplier
   */
  async function saveSupplier() {
    // Clear previous errors
    Object.keys(supplierErrors).forEach(key => {
      supplierErrors[key] = '';
    });
    
    // Basic validation
    if (!supplierForm.name || !supplierForm.code) {
      if (!supplierForm.name) supplierErrors.name = 'Supplier name is required';
      if (!supplierForm.code) supplierErrors.code = 'Supplier code is required';
      return;
    }
    
    isSavingSupplier.value = true;
    
    try {
      // Submit to API
      const response = await axios.post('/api/suppliers', supplierForm);
      
      // Add to suppliers list and select it
      suppliers.value.push(response.data);
      formData.supplier_id = response.data.id;
      
      // Show success notification
      alertStore.setSuccessAlert('Supplier created successfully.');
      
      // Close dialog and reset form
      showAddSupplierDialog.value = false;
      Object.assign(supplierForm, {
        name: '',
        code: '',
        contact_name: '',
        email: '',
        is_active: true
      });
      
      isSavingSupplier.value = false;
    } catch (error) {
      console.error('Error saving supplier:', error);
      
      // Handle validation errors
      if (error.response && error.response.status === 422) {
        const validationErrors = error.response.data.errors;
        
        // Map backend validation errors to form fields
        Object.keys(validationErrors).forEach(key => {
          if (supplierErrors.hasOwnProperty(key)) {
            supplierErrors[key] = validationErrors[key][0];
          }
        });
      } else {
        alertStore.setApiErrorAlert(error, 'Failed to create supplier.');
      }
      
      isSavingSupplier.value = false;
    }
  }
  
  /**
   * Validate the form before submission
   * @returns {boolean} True if form is valid
   */
  function validateForm() {
    // Clear previous errors
    Object.keys(errors).forEach(key => {
      errors[key] = '';
    });
    
    let isValid = true;
    
    // Required fields
    if (!formData.warehouse_id) {
      errors.warehouse_id = 'Warehouse is required';
      isValid = false;
    }
    
    if (!formData.supplier_id) {
      errors.supplier_id = 'Supplier is required';
      isValid = false;
    }
    
    if (!formData.order_date) {
      errors.order_date = 'Order date is required';
      isValid = false;
    }
    
    if (!formData.expected_delivery_date) {
      errors.expected_delivery_date = 'Expected delivery date is required';
      isValid = false;
    }
    
    if (!formData.currency) {
      errors.currency = 'Currency is required';
      isValid = false;
    }
    
    // Line items validation
    if (formData.items.length === 0) {
      errors.items = 'At least one item is required';
      isValid = false;
    }
    
    // Date validation
    if (formData.order_date && formData.expected_delivery_date) {
      const orderDate = new Date(formData.order_date);
      const deliveryDate = new Date(formData.expected_delivery_date);
      
      if (deliveryDate < orderDate) {
        errors.expected_delivery_date = 'Expected delivery date cannot be before order date';
        isValid = false;
      }
    }
    
    return isValid;
  }
  
  // Initialize component
  onMounted(async () => {
    // Fetch reference data
    await Promise.all([
      fetchWarehouses(),
      fetchSuppliers(),
      fetchProducts()
    ]);
    
    // Set default expected delivery date (7 days from now)
    const defaultDeliveryDate = new Date();
    defaultDeliveryDate.setDate(defaultDeliveryDate.getDate() + 7);
    formData.expected_delivery_date = defaultDeliveryDate.toISOString().split('T')[0];
    
    isLoading.value = false;
  });
  </script>
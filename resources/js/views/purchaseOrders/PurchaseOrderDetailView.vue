<!-- resources/js/views/purchaseOrders/PurchaseOrderDetailView.vue -->

<template>
    <div>
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else>
        <!-- Back button and actions bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
          <!-- Back button -->
          <div class="flex items-center">
            <router-link
              to="/dashboard/purchase-orders"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-sm">arrow_back</span>
              Back to Purchase Orders
            </router-link>
          </div>
          
          <!-- Action buttons -->
          <div class="flex flex-wrap gap-2">
            <!-- Edit button -->
            <router-link
              v-if="canEditPO && hasPermission('purchase-orders.edit')"
              :to="`/dashboard/purchase-orders/${purchaseOrder.id}/edit`"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">edit</span>
              Edit
            </router-link>
            
            <!-- Approve button -->
            <button
              v-if="purchaseOrder.status === 'awaiting_approval' && hasPermission('purchase-orders.approve')"
              @click="confirmApprove"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              <span class="material-icons -ml-1 mr-1 text-sm">check_circle</span>
              Approve
            </button>
            
            <!-- Send to supplier button -->
            <button
              v-if="purchaseOrder.status === 'approved' && hasPermission('purchase-orders.send')"
              @click="confirmSend"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-sm">send</span>
              Send to Supplier
            </button>
            
            <!-- Receive button -->
            <router-link
              v-if="canReceivePO && hasPermission('receiving.create')"
              :to="`/dashboard/receiving/${purchaseOrder.id}`"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <span class="material-icons -ml-1 mr-1 text-sm">inventory</span>
              Receive
            </router-link>
            
            <!-- Close button -->
            <button
              v-if="canClosePO && hasPermission('purchase-orders.close')"
              @click="confirmClose"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">check</span>
              Close PO
            </button>
            
            <!-- Cancel button -->
            <button
              v-if="canCancelPO && hasPermission('purchase-orders.cancel')"
              @click="confirmCancel"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">cancel</span>
              Cancel
            </button>
            
            <!-- Print/Export button -->
            <button
              @click="printPO"
              class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="material-icons -ml-1 mr-1 text-gray-500 text-sm">print</span>
              Print
            </button>
          </div>
        </div>
        
        <!-- Purchase order header -->
        <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <div>
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Purchase Order #{{ purchaseOrder.po_number }}
              </h3>
              <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Created on {{ formatDate(purchaseOrder.order_date) }}
              </p>
            </div>
            
            <!-- Status badge -->
            <div>
              <span 
                class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="getPOStatusClass(purchaseOrder.status)"
              >
                {{ formatPOStatus(purchaseOrder.status) }}
              </span>
            </div>
          </div>
          
          <!-- PO details grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4 py-5 sm:p-6 border-b border-gray-200">
            <!-- Supplier information -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Supplier Information</h4>
              <div class="bg-gray-50 rounded-md p-4">
                <p class="text-sm font-medium text-gray-900">{{ purchaseOrder.supplier?.name || 'N/A' }}</p>
                <p v-if="purchaseOrder.supplier?.contact_name" class="text-sm text-gray-700 mt-1">
                  Contact: {{ purchaseOrder.supplier.contact_name }}
                </p>
                <p v-if="purchaseOrder.supplier?.email" class="text-sm text-gray-700 mt-1">
                  Email: {{ purchaseOrder.supplier.email }}
                </p>
                <p v-if="purchaseOrder.supplier?.phone" class="text-sm text-gray-700 mt-1">
                  Phone: {{ purchaseOrder.supplier.phone }}
                </p>
                <div v-if="purchaseOrder.supplier_reference" class="mt-3 pt-3 border-t border-gray-200">
                  <p class="text-sm text-gray-700">
                    <span class="font-medium">Supplier Reference:</span> {{ purchaseOrder.supplier_reference }}
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Shipping information -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Shipping Information</h4>
              <div class="bg-gray-50 rounded-md p-4">
                <p class="text-sm font-medium text-gray-900">
                  Warehouse: {{ purchaseOrder.warehouse?.name || 'N/A' }}
                </p>
                <div class="text-sm text-gray-700 mt-1">
                  <p v-if="shippingAddress">
                    {{ shippingAddress }}
                  </p>
                  <p v-else-if="purchaseOrder.warehouse?.address">
                    {{ purchaseOrder.warehouse.address }}
                  </p>
                  <p v-else>
                    No address specified
                  </p>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-200">
                  <div class="flex justify-between">
                    <p class="text-sm text-gray-700">
                      <span class="font-medium">Expected Delivery:</span>
                      <span 
                        :class="isOverdue ? 'text-red-600 font-medium' : 'text-gray-900'"
                      >
                        {{ formatDate(purchaseOrder.expected_delivery_date) }}
                      </span>
                    </p>
                    <p v-if="isOverdue" class="text-sm text-red-600 font-medium">
                      {{ getDaysOverdue() }} days overdue
                    </p>
                  </div>
                  <p v-if="purchaseOrder.received_date" class="text-sm text-gray-700 mt-1">
                    <span class="font-medium">Received On:</span> {{ formatDate(purchaseOrder.received_date) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Notes, payment terms, etc. -->
          <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Payment terms -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Payment Terms</h4>
                <p class="text-sm text-gray-900">
                  {{ purchaseOrder.payment_terms || purchaseOrder.supplier?.payment_terms || 'Not specified' }}
                </p>
              </div>
              
              <!-- Currency -->
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Currency</h4>
                <p class="text-sm text-gray-900">
                  {{ purchaseOrder.currency || purchaseOrder.supplier?.currency || 'USD' }}
                </p>
              </div>
            </div>
            
            <!-- Notes section -->
            <div class="mt-6">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Notes</h4>
              <div class="bg-gray-50 rounded-md p-4">
                <p v-if="purchaseOrder.notes" class="text-sm text-gray-700 whitespace-pre-wrap">
                  {{ purchaseOrder.notes }}
                </p>
                <p v-else class="text-sm text-gray-500 italic">
                  No notes added
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Order items -->
        <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Order Items
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
              {{ purchaseOrder.purchase_order_items?.length || 0 }} items ordered
            </p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    #
                  </th>
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
                    Subtotal
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="!purchaseOrder.purchase_order_items || purchaseOrder.purchase_order_items.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    No items in this purchase order
                  </td>
                </tr>
                <tr 
                  v-for="item in purchaseOrder.purchase_order_items" 
                  :key="item.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.line_number }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="h-10 w-10 rounded border border-gray-200 flex items-center justify-center overflow-hidden bg-gray-100 mr-3">
                        <img 
                          v-if="item.product?.image_url" 
                          :src="item.product.image_url" 
                          :alt="item.product?.name"
                          class="h-full w-full object-cover"
                        />
                        <span v-else class="material-icons text-gray-400">photo</span>
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">
                          {{ item.product?.name || 'Unknown Product' }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ item.product?.sku || 'No SKU' }}
                          <span v-if="item.supplier_product_code">
                            (Supplier Code: {{ item.supplier_product_code }})
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 font-medium">
                      {{ item.quantity_ordered }}
                    </div>
                    <div v-if="item.quantity_received > 0" class="text-sm text-gray-500">
                      {{ item.quantity_received }} received
                    </div>
                    <div v-if="item.quantity_rejected > 0" class="text-sm text-red-500">
                      {{ item.quantity_rejected }} rejected
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.unit_price) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.subtotal) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="getItemStatusClass(item.status)"
                    >
                      {{ formatItemStatus(item.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td colspan="4" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                    Subtotal:
                  </td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">
                    {{ formatCurrency(purchaseOrder.subtotal) }}
                  </td>
                  <td></td>
                </tr>
                <tr v-if="purchaseOrder.tax_amount">
                  <td colspan="4" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                    Tax:
                  </td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">
                    {{ formatCurrency(purchaseOrder.tax_amount) }}
                  </td>
                  <td></td>
                </tr>
                <tr v-if="purchaseOrder.shipping_cost">
                  <td colspan="4" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                    Shipping:
                  </td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">
                    {{ formatCurrency(purchaseOrder.shipping_cost) }}
                  </td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="4" class="px-6 py-4 text-base font-bold text-gray-900 text-right">
                    Total:
                  </td>
                  <td class="px-6 py-4 text-base font-bold text-gray-900">
                    {{ formatCurrency(purchaseOrder.total_amount) }}
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        
        <!-- Receiving History -->
        <div class="bg-white shadow rounded-lg mb-6 overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Receiving History
            </h3>
          </div>
          
          <div v-if="receivingHistory.length === 0" class="p-6 text-center">
            <p class="text-sm text-gray-500">
              No receiving history found for this purchase order
            </p>
          </div>
          
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Received By
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Items Received
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Items Rejected
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Notes
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr 
                  v-for="(receipt, index) in receivingHistory" 
                  :key="index"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDateTime(receipt.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ receipt.received_by }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ receipt.quantity_received }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ receipt.quantity_rejected }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    {{ receipt.notes || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Activity Log -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Activity Log
            </h3>
          </div>
          
          <div v-if="activityLog.length === 0" class="p-6 text-center">
            <p class="text-sm text-gray-500">
              No activity log entries for this purchase order
            </p>
          </div>
          
          <div v-else class="px-4 py-5 sm:p-6">
            <ul class="space-y-4">
              <li 
                v-for="(activity, index) in activityLog" 
                :key="index"
                class="bg-gray-50 rounded-md p-4"
              >
                <div class="flex justify-between items-start">
                  <div class="flex items-start space-x-2">
                    <span 
                      class="material-icons text-sm mt-0.5"
                      :class="getActivityIconColor(activity.action)"
                    >
                      {{ getActivityIcon(activity.action) }}
                    </span>
                    <div>
                      <p class="text-sm text-gray-900">
                        {{ activity.description }}
                      </p>
                      <p v-if="activity.details" class="text-sm text-gray-500 mt-1">
                        {{ activity.details }}
                      </p>
                    </div>
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatDateTime(activity.timestamp) }}
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <!-- Action confirmation modals -->
      
      <!-- Approve confirmation dialog -->
      <div 
        v-if="showApproveDialog" 
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
            @click="showApproveDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                  <span class="material-icons text-blue-600">check_circle</span>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Approve Purchase Order
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to approve this purchase order? Once approved, it can be sent to the supplier.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="approvePO"
                :disabled="isSubmitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSubmitting ? 'Approving...' : 'Approve' }}
              </button>
              <button
                @click="showApproveDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Send confirmation dialog -->
      <div 
        v-if="showSendDialog" 
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
            @click="showSendDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                  <span class="material-icons text-blue-600">send</span>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Send Purchase Order to Supplier
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to send this purchase order to the supplier? This will generate a PDF document and mark the PO as sent.
                    </p>
                    <div class="mt-4">
                      <label for="send-method" class="block text-sm font-medium text-gray-700">Send Method</label>
                      <select
                        id="send-method"
                        v-model="sendMethod"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                      >
                        <option value="email">Email</option>
                        <option value="print">Print Only</option>
                        <option value="manual">Mark as Sent (Manual)</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="sendPO"
                :disabled="isSubmitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSubmitting ? 'Processing...' : 'Send' }}
              </button>
              <button
                @click="showSendDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Close confirmation dialog -->
      <div 
        v-if="showCloseDialog" 
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
            @click="showCloseDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 sm:mx-0 sm:h-10 sm:w-10">
                  <span class="material-icons text-gray-600">check</span>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Close Purchase Order
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to close this purchase order? This will mark the PO as completed and no further receiving will be allowed.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="closePO"
                :disabled="isSubmitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSubmitting ? 'Closing...' : 'Close' }}
              </button>
              <button
                @click="showCloseDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Cancel confirmation dialog -->
      <div 
        v-if="showCancelDialog" 
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
            @click="showCancelDialog = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <span class="material-icons text-red-600">cancel</span>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    Cancel Purchase Order
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to cancel this purchase order? This action cannot be undone.
                    </p>
                    <div class="mt-4">
                      <label for="cancellation-reason" class="block text-sm font-medium text-gray-700">Reason for Cancellation</label>
                      <textarea
                        id="cancellation-reason"
                        v-model="cancellationReason"
                        rows="3"
                        class="mt-1 shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Please provide a reason for cancelling this purchase order"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="cancelPO"
                :disabled="isSubmitting || !cancellationReason"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isSubmitting ? 'Cancelling...' : 'Cancel Purchase Order' }}
              </button>
              <button
                @click="showCancelDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Go Back
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
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
  const purchaseOrder = ref({});
  const isSubmitting = ref(false);
  
  // Action dialog states
  const showApproveDialog = ref(false);
  const showSendDialog = ref(false);
  const showCloseDialog = ref(false);
  const showCancelDialog = ref(false);
  const sendMethod = ref('email');
  const cancellationReason = ref('');
  
  // Data for receiving and activity log
  const receivingHistory = ref([]);
  const activityLog = ref([]);
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  /**
   * Fetch purchase order details from API
   */
  async function fetchPurchaseOrder() {
    loading.value = true;
    
    try {
      const purchaseOrderId = route.params.id;
      const response = await axios.get(`/api/purchase-orders/${purchaseOrderId}`);
      
      // Update state
      purchaseOrder.value = response.data;
      
      // Fetch additional data
      await Promise.all([
        fetchReceivingHistory(),
        fetchActivityLog()
      ]);
      
      loading.value = false;
    } catch (error) {
      console.error('Error fetching purchase order:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load purchase order details.');
      
      // Provide a way to return to purchase orders list
      loading.value = false;
    }
  }
  
  /**
   * Fetch receiving history for this purchase order
   */
  async function fetchReceivingHistory() {
    try {
      const purchaseOrderId = route.params.id;
      const response = await axios.get(`/api/purchase-orders/${purchaseOrderId}/receiving-history`);
      
      receivingHistory.value = response.data;
    } catch (error) {
      console.error('Error fetching receiving history:', error);
      receivingHistory.value = [];
    }
  }
  
  /**
   * Fetch activity log for this purchase order
   */
  async function fetchActivityLog() {
    try {
      const purchaseOrderId = route.params.id;
      const response = await axios.get(`/api/purchase-orders/${purchaseOrderId}/activity-log`);
      
      activityLog.value = response.data;
    } catch (error) {
      console.error('Error fetching activity log:', error);
      activityLog.value = [];
    }
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
   * Format currency amount with appropriate symbol
   * @param {number} amount - Amount to format
   * @returns {string} Formatted currency string
   */
  function formatCurrency(amount) {
    if (amount === undefined || amount === null) return 'N/A';
    
    const currency = purchaseOrder.value.currency || 'USD';
    
    return new Intl.NumberFormat(undefined, {
      style: 'currency',
      currency: currency
    }).format(amount);
  }
  
  /**
   * Format purchase order status to readable string
   * @param {string} status - PO status from API
   * @returns {string} Formatted status string
   */
  function formatPOStatus(status) {
    if (!status) return 'Unknown';
    
    // Convert snake_case to Title Case
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Format item status to readable string
   * @param {string} status - Item status from API
   * @returns {string} Formatted status string
   */
  function formatItemStatus(status) {
    if (!status) return 'Unknown';
    
    // Convert snake_case to Title Case
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Get CSS class for purchase order status badge
   * @param {string} status - PO status
   * @returns {string} CSS classes for the status badge
   */
  function getPOStatusClass(status) {
    const statusClasses = {
      'draft': 'bg-gray-100 text-gray-800',
      'awaiting_approval': 'bg-yellow-100 text-yellow-800',
      'approved': 'bg-blue-100 text-blue-800',
      'sent': 'bg-indigo-100 text-indigo-800',
      'confirmed': 'bg-purple-100 text-purple-800',
      'partially_received': 'bg-yellow-100 text-yellow-800',
      'fully_received': 'bg-green-100 text-green-800',
      'closed': 'bg-gray-100 text-gray-800',
      'cancelled': 'bg-red-100 text-red-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Get CSS class for item status badge
   * @param {string} status - Item status
   * @returns {string} CSS classes for the status badge
   */
  function getItemStatusClass(status) {
    const statusClasses = {
      'pending': 'bg-yellow-100 text-yellow-800',
      'partially_received': 'bg-yellow-100 text-yellow-800',
      'fully_received': 'bg-green-100 text-green-800',
      'over_received': 'bg-orange-100 text-orange-800',
      'cancelled': 'bg-red-100 text-red-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Get activity log icon based on action
   * @param {string} action - Activity action
   * @returns {string} Material icon name
   */
  function getActivityIcon(action) {
    const actionIcons = {
      'created': 'add_circle',
      'updated': 'edit',
      'approved': 'check_circle',
      'sent': 'send',
      'received': 'inventory',
      'closed': 'done_all',
      'cancelled': 'cancel'
    };
    
    return actionIcons[action] || 'info';
  }
  
  /**
   * Get activity log icon color based on action
   * @param {string} action - Activity action
   * @returns {string} CSS color class
   */
  function getActivityIconColor(action) {
    const actionColors = {
      'created': 'text-blue-500',
      'updated': 'text-blue-500',
      'approved': 'text-green-500',
      'sent': 'text-indigo-500',
      'received': 'text-purple-500',
      'closed': 'text-gray-500',
      'cancelled': 'text-red-500'
    };
    
    return actionColors[action] || 'text-gray-500';
  }
  
  /**
   * Check if the purchase order can be edited
   */
  const canEditPO = computed(() => {
    const editableStatuses = ['draft', 'awaiting_approval'];
    return editableStatuses.includes(purchaseOrder.value.status);
  });
  
  /**
   * Check if the purchase order can be received
   */
  const canReceivePO = computed(() => {
    const receivableStatuses = ['confirmed', 'partially_received'];
    return receivableStatuses.includes(purchaseOrder.value.status);
  });
  
  /**
   * Check if the purchase order can be closed
   */
  const canClosePO = computed(() => {
    const closeableStatuses = ['fully_received', 'partially_received'];
    return closeableStatuses.includes(purchaseOrder.value.status);
  });
  
  /**
   * Check if the purchase order can be cancelled
   */
  const canCancelPO = computed(() => {
    const cancelableStatuses = ['draft', 'awaiting_approval', 'approved', 'sent', 'confirmed'];
    return cancelableStatuses.includes(purchaseOrder.value.status);
  });
  
  /**
   * Get shipping address from purchase order
   */
  const shippingAddress = computed(() => {
    return purchaseOrder.value.shipping_address || null;
  });
  
  /**
   * Check if the purchase order is overdue
   */
  const isOverdue = computed(() => {
    if (!purchaseOrder.value.expected_delivery_date || 
      purchaseOrder.value.status === 'fully_received' || 
      purchaseOrder.value.status === 'closed' || 
      purchaseOrder.value.status === 'cancelled') {
      return false;
    }
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const expectedDate = new Date(purchaseOrder.value.expected_delivery_date);
    expectedDate.setHours(0, 0, 0, 0);
    
    return expectedDate < today;
  });
  
  /**
   * Get number of days a purchase order is overdue
   * @returns {number} Days overdue
   */
  function getDaysOverdue() {
    if (!isOverdue.value) return 0;
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const expectedDate = new Date(purchaseOrder.value.expected_delivery_date);
    expectedDate.setHours(0, 0, 0, 0);
    
    const diffTime = Math.abs(today - expectedDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays;
  }
  
  /**
   * Confirm approve action
   */
  function confirmApprove() {
    showApproveDialog.value = true;
  }
  
  /**
   * Approve purchase order
   */
  async function approvePO() {
    isSubmitting.value = true;
    
    try {
      await axios.post(`/api/purchase-orders/${purchaseOrder.value.id}/approve`);
      
      // Update status and refresh data
      showApproveDialog.value = false;
      alertStore.setSuccessAlert('Purchase order has been approved.');
      
      // Refetch data
      await fetchPurchaseOrder();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error approving purchase order:', error);
      alertStore.setApiErrorAlert(error, 'Failed to approve purchase order.');
      
      isSubmitting.value = false;
      showApproveDialog.value = false;
    }
  }
  
  /**
   * Confirm send action
   */
  function confirmSend() {
    showSendDialog.value = true;
  }
  
  /**
   * Send purchase order to supplier
   */
  async function sendPO() {
    isSubmitting.value = true;
    
    try {
      await axios.post(`/api/purchase-orders/${purchaseOrder.value.id}/send`, {
        method: sendMethod.value
      });
      
      // Update status and refresh data
      showSendDialog.value = false;
      alertStore.setSuccessAlert('Purchase order has been sent to the supplier.');
      
      // Refetch data
      await fetchPurchaseOrder();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error sending purchase order:', error);
      alertStore.setApiErrorAlert(error, 'Failed to send purchase order.');
      
      isSubmitting.value = false;
      showSendDialog.value = false;
    }
  }
  
  /**
   * Confirm close action
   */
  function confirmClose() {
    showCloseDialog.value = true;
  }
  
  /**
   * Close purchase order
   */
  async function closePO() {
    isSubmitting.value = true;
    
    try {
      await axios.post(`/api/purchase-orders/${purchaseOrder.value.id}/close`);
      
      // Update status and refresh data
      showCloseDialog.value = false;
      alertStore.setSuccessAlert('Purchase order has been closed.');
      
      // Refetch data
      await fetchPurchaseOrder();
      
      isSubmitting.value = false;
    } catch (error) {
      console.error('Error closing purchase order:', error);
      alertStore.setApiErrorAlert(error, 'Failed to close purchase order.');
      
      isSubmitting.value = false;
      showCloseDialog.value = false;
    }
  }
  
  /**
   * Confirm cancel action
   */
  function confirmCancel() {
    showCancelDialog.value = true;
  }
  
  /**
   * Cancel purchase order
   */
  async function cancelPO() {
    if (!cancellationReason.value) {
      alertStore.setErrorAlert('Please provide a reason for cancellation.');
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      await axios.post(`/api/purchase-orders/${purchaseOrder.value.id}/cancel`, {
        reason: cancellationReason.value
      });
      
      // Update status and refresh data
      showCancelDialog.value = false;
      alertStore.setSuccessAlert('Purchase order has been cancelled.');
      
      // Refetch data
      await fetchPurchaseOrder();
      
      isSubmitting.value = false;
      cancellationReason.value = '';
    } catch (error) {
      console.error('Error cancelling purchase order:', error);
      alertStore.setApiErrorAlert(error, 'Failed to cancel purchase order.');
      
      isSubmitting.value = false;
      showCancelDialog.value = false;
    }
  }
  
  /**
   * Print or download purchase order PDF
   */
  function printPO() {
    // Open in new window or download PDF
    window.open(`/api/purchase-orders/${purchaseOrder.value.id}/print`, '_blank');
  }
  
  // Initialize component
  onMounted(() => {
    fetchPurchaseOrder();
  });
  </script>
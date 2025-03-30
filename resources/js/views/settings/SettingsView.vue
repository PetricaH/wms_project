<!-- resources/js/views/settings/SettingsView.vue -->

<template>
    <div>
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else class="flex flex-col md:flex-row gap-6">
        <!-- Settings navigation sidebar -->
        <div class="w-full md:w-64 shrink-0">
          <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-base font-medium leading-6 text-gray-900">Settings</h3>
            </div>
            <nav class="space-y-1 px-2 py-3">
              <button
                v-for="(section, index) in settingSections"
                :key="index"
                @click="activeSection = section.id"
                class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md text-left"
                :class="activeSection === section.id 
                  ? 'bg-blue-50 text-blue-700' 
                  : 'text-gray-700 hover:bg-gray-50'"
              >
                <span class="material-icons mr-3 text-lg" 
                  :class="activeSection === section.id ? 'text-blue-500' : 'text-gray-400'">
                  {{ section.icon }}
                </span>
                {{ section.name }}
              </button>
            </nav>
          </div>
        </div>
        
        <!-- Settings content area -->
        <div class="flex-1">
          <div class="bg-white rounded-lg shadow">
            <!-- Company Settings Form -->
            <div v-if="activeSection === 'company'" class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Company Information</h3>
              
              <form @submit.prevent="saveCompanySettings">
                <!-- Company name -->
                <div class="mb-4">
                  <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                  <input
                    id="company_name"
                    v-model="companySettings.name"
                    type="text"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    required
                  />
                </div>
                
                <!-- Contact email -->
                <div class="mb-4">
                  <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                  <input
                    id="contact_email"
                    v-model="companySettings.contact_email"
                    type="email"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  />
                </div>
                
                <!-- Contact phone -->
                <div class="mb-4">
                  <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                  <input
                    id="contact_phone"
                    v-model="companySettings.contact_phone"
                    type="text"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  />
                </div>
                
                <!-- Address fields -->
                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                  <div class="col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <input
                      id="address"
                      v-model="companySettings.address"
                      type="text"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                  
                  <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input
                      id="city"
                      v-model="companySettings.city"
                      type="text"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                  
                  <div>
                    <label for="state" class="block text-sm font-medium text-gray-700">State/Province</label>
                    <input
                      id="state"
                      v-model="companySettings.state"
                      type="text"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                  
                  <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                    <input
                      id="postal_code"
                      v-model="companySettings.postal_code"
                      type="text"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                  
                  <div>
                    <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                    <input
                      id="country"
                      v-model="companySettings.country"
                      type="text"
                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    />
                  </div>
                </div>
                
                <!-- Tax ID -->
                <div class="mb-4">
                  <label for="tax_id" class="block text-sm font-medium text-gray-700">Tax ID / VAT Number</label>
                  <input
                    id="tax_id"
                    v-model="companySettings.tax_id"
                    type="text"
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  />
                </div>
                
                <!-- Currency -->
                <div class="mb-4">
                  <label for="currency" class="block text-sm font-medium text-gray-700">Default Currency</label>
                  <select
                    id="currency"
                    v-model="companySettings.currency"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                  >
                    <option value="USD">US Dollar (USD)</option>
                    <option value="EUR">Euro (EUR)</option>
                    <option value="GBP">British Pound (GBP)</option>
                    <option value="CAD">Canadian Dollar (CAD)</option>
                    <option value="AUD">Australian Dollar (AUD)</option>
                    <option value="JPY">Japanese Yen (JPY)</option>
                  </select>
                </div>
                
                <!-- Form actions -->
                <div class="pt-5 border-t border-gray-200">
                  <div class="flex justify-end">
                    <button
                      type="button"
                      @click="resetCompanySettings"
                      class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      Reset
                    </button>
                    <button
                      type="submit"
                      :disabled="saving"
                      class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                      <span v-if="saving" class="mr-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </span>
                      {{ saving ? 'Saving...' : 'Save' }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
            
            <!-- Inventory Settings Form -->
            <div v-else-if="activeSection === 'inventory'" class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Inventory Settings</h3>
              
              <form @submit.prevent="saveInventorySettings">
                <!-- Low stock threshold -->
                <div class="mb-4">
                  <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700">
                    Low Stock Threshold (Quantity)
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm w-full max-w-xs">
                    <input
                      id="low_stock_threshold"
                      v-model.number="inventorySettings.low_stock_threshold"
                      type="number"
                      min="0"
                      class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">units</span>
                    </div>
                  </div>
                  <p class="mt-1 text-sm text-gray-500">
                    Products with quantity below this threshold will be marked as low stock.
                  </p>
                </div>
                
                <!-- FIFO settings -->
                <div class="mb-4">
                  <div class="flex items-start">
                    <div class="h-5 flex items-center">
                      <input
                        id="strict_fifo"
                        v-model="inventorySettings.strict_fifo"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="strict_fifo" class="font-medium text-gray-700">Enforce Strict FIFO</label>
                      <p class="text-gray-500">
                        When enabled, the system will strictly enforce FIFO picking order. When disabled, the system will suggest FIFO but allow overrides.
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Lot tracking -->
                <div class="mb-4">
                  <div class="flex items-start">
                    <div class="h-5 flex items-center">
                      <input
                        id="lot_tracking"
                        v-model="inventorySettings.lot_tracking"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="lot_tracking" class="font-medium text-gray-700">Enable Lot Tracking</label>
                      <p class="text-gray-500">
                        Track inventory by lot/batch number for traceability.
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Expiry date tracking -->
                <div class="mb-4">
                  <div class="flex items-start">
                    <div class="h-5 flex items-center">
                      <input
                        id="expiry_tracking"
                        v-model="inventorySettings.expiry_tracking"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="expiry_tracking" class="font-medium text-gray-700">Enable Expiry Date Tracking</label>
                      <p class="text-gray-500">
                        Track and manage products with expiration dates.
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Expiry date notification days -->
                <div 
                  v-if="inventorySettings.expiry_tracking"
                  class="mb-4 ml-7"
                >
                  <label for="expiry_notification_days" class="block text-sm font-medium text-gray-700">
                    Expiry Notification Days
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm w-full max-w-xs">
                    <input
                      id="expiry_notification_days"
                      v-model.number="inventorySettings.expiry_notification_days"
                      type="number"
                      min="1"
                      class="focus:ring-blue-500 focus:border-blue-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">days</span>
                    </div>
                  </div>
                  <p class="mt-1 text-sm text-gray-500">
                    System will notify when products are nearing expiration.
                  </p>
                </div>
                
                <!-- Auto stock allocation -->
                <div class="mb-4">
                  <div class="flex items-start">
                    <div class="h-5 flex items-center">
                      <input
                        id="auto_allocate"
                        v-model="inventorySettings.auto_allocate_stock"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="auto_allocate" class="font-medium text-gray-700">Auto Allocate Stock</label>
                      <p class="text-gray-500">
                        Automatically allocate inventory to orders when they're created.
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Default UOM -->
                <div class="mb-4">
                  <label for="default_uom" class="block text-sm font-medium text-gray-700">Default Unit of Measure</label>
                  <select
                    id="default_uom"
                    v-model="inventorySettings.default_uom"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                  >
                    <option value="each">Each (EA)</option>
                    <option value="case">Case (CS)</option>
                    <option value="box">Box (BX)</option>
                    <option value="pallet">Pallet (PL)</option>
                    <option value="kg">Kilogram (KG)</option>
                    <option value="lb">Pound (LB)</option>
                  </select>
                </div>
                
                <!-- Form actions -->
                <div class="pt-5 border-t border-gray-200">
                  <div class="flex justify-end">
                    <button
                      type="button"
                      @click="resetInventorySettings"
                      class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      Reset
                    </button>
                    <button
                      type="submit"
                      :disabled="saving"
                      class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                      <span v-if="saving" class="mr-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </span>
                      {{ saving ? 'Saving...' : 'Save' }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
            
            <!-- Notification Settings Form -->
            <div v-else-if="activeSection === 'notifications'" class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Notification Settings</h3>
              
              <form @submit.prevent="saveNotificationSettings">
                <!-- Email notifications toggle -->
                <div class="mb-4">
                  <div class="flex items-start">
                    <div class="h-5 flex items-center">
                      <input
                        id="email_notifications"
                        v-model="notificationSettings.email_enabled"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="email_notifications" class="font-medium text-gray-700">Enable Email Notifications</label>
                      <p class="text-gray-500">
                        Send system notifications via email.
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Notification types -->
                <div v-if="notificationSettings.email_enabled" class="mb-4 ml-7">
                  <div class="text-sm font-medium text-gray-700 mb-2">Notification Types</div>
                  
                  <div class="space-y-2">
                    <div class="flex items-start">
                      <div class="h-5 flex items-center">
                        <input
                          id="notify_low_stock"
                          v-model="notificationSettings.notify_low_stock"
                          type="checkbox"
                          class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notify_low_stock" class="font-medium text-gray-700">Low Stock Alerts</label>
                      </div>
                    </div>
                    
                    <div class="flex items-start">
                      <div class="h-5 flex items-center">
                        <input
                          id="notify_expiring_inventory"
                          v-model="notificationSettings.notify_expiring_inventory"
                          type="checkbox"
                          class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                          :disabled="!inventorySettings.expiry_tracking"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notify_expiring_inventory" class="font-medium text-gray-700">
                          Expiring Inventory
                        </label>
                        <p v-if="!inventorySettings.expiry_tracking" class="text-gray-400 text-xs">
                          Enable expiry tracking in Inventory Settings first
                        </p>
                      </div>
                    </div>
                    
                    <div class="flex items-start">
                      <div class="h-5 flex items-center">
                        <input
                          id="notify_new_orders"
                          v-model="notificationSettings.notify_new_orders"
                          type="checkbox"
                          class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notify_new_orders" class="font-medium text-gray-700">New Orders</label>
                      </div>
                    </div>
                    
                    <div class="flex items-start">
                      <div class="h-5 flex items-center">
                        <input
                          id="notify_purchase_orders"
                          v-model="notificationSettings.notify_purchase_orders"
                          type="checkbox"
                          class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notify_purchase_orders" class="font-medium text-gray-700">Purchase Order Status Updates</label>
                      </div>
                    </div>
                    
                    <div class="flex items-start">
                      <div class="h-5 flex items-center">
                        <input
                          id="notify_receiving"
                          v-model="notificationSettings.notify_receiving"
                          type="checkbox"
                          class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label for="notify_receiving" class="font-medium text-gray-700">Receiving Updates</label>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Email recipients -->
                <div v-if="notificationSettings.email_enabled" class="mb-4">
                  <label for="notification_emails" class="block text-sm font-medium text-gray-700">
                    Notification Recipients (comma-separated emails)
                  </label>
                  <div class="mt-1">
                    <textarea
                      id="notification_emails"
                      v-model="notificationSettings.notification_emails"
                      rows="2"
                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      placeholder="admin@example.com, warehouse@example.com"
                    ></textarea>
                  </div>
                </div>
                
                <!-- In-app notifications -->
                <div class="mb-4">
                  <div class="flex items-start">
                    <div class="h-5 flex items-center">
                      <input
                        id="in_app_notifications"
                        v-model="notificationSettings.in_app_enabled"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="in_app_notifications" class="font-medium text-gray-700">Enable In-App Notifications</label>
                      <p class="text-gray-500">
                        Show notifications within the application interface.
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Daily summary -->
                <div class="mb-4">
                  <div class="flex items-start">
                    <div class="h-5 flex items-center">
                      <input
                        id="daily_summary"
                        v-model="notificationSettings.daily_summary"
                        type="checkbox"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                      />
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="daily_summary" class="font-medium text-gray-700">Send Daily Summary</label>
                      <p class="text-gray-500">
                        Receive a daily summary of warehouse activity.
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Form actions -->
                <div class="pt-5 border-t border-gray-200">
                  <div class="flex justify-end">
                    <button
                      type="button"
                      @click="resetNotificationSettings"
                      class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      Reset
                    </button>
                    <button
                      type="submit"
                      :disabled="saving"
                      class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    >
                      <span v-if="saving" class="mr-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </span>
                      {{ saving ? 'Saving...' : 'Save' }}
                    </button>
                  </div>
                </div>
              </form>
            </div>
            
            <!-- User Management Section -->
            <div v-else-if="activeSection === 'users'" class="px-4 py-5 sm:p-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">User Management</h3>
                
                <button 
                  v-if="hasPermission('users.create')"
                  @click="showUserModal = true; resetUserForm()"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="material-icons -ml-1 mr-2 text-sm">add</span>
                  Add User
                </button>
              </div>
              
              <!-- User list table -->
              <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role
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
                    <tr v-if="loadingUsers" class="h-20">
                      <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                        <div class="flex justify-center items-center">
                          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                          <span class="ml-2">Loading users...</span>
                        </div>
                      </td>
                    </tr>
                    <tr v-else-if="users.length === 0">
                      <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                        No users found
                      </td>
                    </tr>
                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500 font-medium">
                              {{ getUserInitials(user) }}
                            </span>
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ user.email }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex flex-wrap gap-1">
                          <span 
                            v-for="role in user.roles" 
                            :key="role.id"
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                          >
                            {{ role.name }}
                          </span>
                          <span v-if="!user.roles || user.roles.length === 0" class="text-gray-400">
                            No role assigned
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span 
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                        >
                          {{ user.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-3">
                          <!-- Edit button -->
                          <button 
                            v-if="hasPermission('users.edit')"
                            @click="editUser(user)"
                            class="text-indigo-600 hover:text-indigo-900"
                            title="Edit User"
                          >
                            <span class="material-icons text-sm">edit</span>
                          </button>
                          
                          <!-- Delete button -->
                          <button 
                            v-if="hasPermission('users.delete') && user.id !== currentUserId"
                            @click="confirmDeleteUser(user)"
                            class="text-red-600 hover:text-red-900"
                            title="Delete User"
                          >
                            <span class="material-icons text-sm">delete</span>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- Role Management Section -->
            <div v-else-if="activeSection === 'roles'" class="px-4 py-5 sm:p-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Role Management</h3>
                
                <button 
                  v-if="hasPermission('roles.create')"
                  @click="showRoleModal = true; resetRoleForm()"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="material-icons -ml-1 mr-2 text-sm">add</span>
                  Add Role
                </button>
              </div>
              
              <!-- Role list table -->
              <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Description
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Users
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Permissions
                      </th>
                      <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="loadingRoles" class="h-20">
                      <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                        <div class="flex justify-center items-center">
                          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                          <span class="ml-2">Loading roles...</span>
                        </div>
                      </td>
                    </tr>
                    <tr v-else-if="roles.length === 0">
                      <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                        No roles found
                      </td>
                    </tr>
                    <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ role.description || 'No description' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ role.users_count || 0 }} users
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div 
                            class="w-full bg-gray-200 rounded-full h-2.5"
                            title="Percentage of total permissions"
                          >
                            <div 
                              class="bg-blue-600 h-2.5 rounded-full" 
                              :style="{ width: `${getPermissionPercentage(role)}%` }"
                            ></div>
                          </div>
                          <span class="ml-2 text-xs text-gray-500">
                            {{ role.permissions?.length || 0 }} permissions
                          </span>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-3">
                          <!-- Edit button -->
                          <button 
                            v-if="hasPermission('roles.edit')"
                            @click="editRole(role)"
                            class="text-indigo-600 hover:text-indigo-900"
                            title="Edit Role"
                          >
                            <span class="material-icons text-sm">edit</span>
                          </button>
                          
                          <!-- Delete button -->
                          <button 
                            v-if="hasPermission('roles.delete') && role.name !== 'Admin'"
                            @click="confirmDeleteRole(role)"
                            class="text-red-600 hover:text-red-900"
                            title="Delete Role"
                          >
                            <span class="material-icons text-sm">delete</span>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- User add/edit modal -->
      <div 
        v-if="showUserModal" 
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
            @click="showUserModal = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ editingUser ? 'Edit User' : 'Add New User' }}
                  </h3>
                  <div class="mt-4">
                    <form @submit.prevent="saveUser">
                      <!-- Name field -->
                      <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input
                          id="name"
                          v-model="userForm.name"
                          type="text"
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                          required
                        />
                      </div>
                      
                      <!-- Email field -->
                      <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                          id="email"
                          v-model="userForm.email"
                          type="email"
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                          required
                        />
                      </div>
                      
                      <!-- Password field (only for new users) -->
                      <div v-if="!editingUser" class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                          id="password"
                          v-model="userForm.password"
                          type="password"
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                          required
                        />
                        <p class="mt-1 text-xs text-gray-500">
                          Password must be at least 8 characters and include numbers and special characters.
                        </p>
                      </div>
                      
                      <!-- Roles field -->
                      <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Roles</label>
                        <div class="mt-2 space-y-2">
                          <div 
                            v-for="role in roles" 
                            :key="role.id"
                            class="flex items-start"
                          >
                            <div class="h-5 flex items-center">
                              <input
                                :id="`role-${role.id}`"
                                :value="role.id"
                                v-model="userForm.roles"
                                type="checkbox"
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                              />
                            </div>
                            <div class="ml-3 text-sm">
                              <label :for="`role-${role.id}`" class="font-medium text-gray-700">{{ role.name }}</label>
                              <p v-if="role.description" class="text-gray-500">{{ role.description }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Active status -->
                      <div class="mb-4 flex items-start">
                        <div class="h-5 flex items-center">
                          <input
                            id="is_active"
                            v-model="userForm.is_active"
                            type="checkbox"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                          />
                        </div>
                        <div class="ml-3 text-sm">
                          <label for="is_active" class="font-medium text-gray-700">Active</label>
                          <p class="text-gray-500">Inactive users cannot log in to the system.</p>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="saveUser"
                :disabled="saving"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="saving" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
              <button
                @click="showUserModal = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Role add/edit modal -->
      <div 
        v-if="showRoleModal" 
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
            @click="showRoleModal = false"
          ></div>
          
          <!-- Dialog panel -->
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ editingRole ? 'Edit Role' : 'Add New Role' }}
                  </h3>
                  <div class="mt-4">
                    <form @submit.prevent="saveRole">
                      <!-- Name field -->
                      <div class="mb-4">
                        <label for="role_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input
                          id="role_name"
                          v-model="roleForm.name"
                          type="text"
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                          required
                        />
                      </div>
                      
                      <!-- Description field -->
                      <div class="mb-4">
                        <label for="role_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                          id="role_description"
                          v-model="roleForm.description"
                          rows="2"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        ></textarea>
                      </div>
                      
                      <!-- Permissions section -->
                      <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                        
                        <div class="border border-gray-300 rounded-md p-4 max-h-72 overflow-y-auto">
                          <div class="space-y-4">
                            <!-- Group permissions by category -->
                            <div v-for="(group, category) in permissionGroups" :key="category">
                              <div class="flex items-center justify-between">
                                <h4 class="text-sm font-medium text-gray-900 capitalize">{{ category }}</h4>
                                <button 
                                  type="button"
                                  @click="togglePermissionGroup(category)"
                                  class="text-xs text-blue-600 hover:text-blue-500"
                                >
                                  {{ isGroupSelected(category) ? 'Deselect All' : 'Select All' }}
                                </button>
                              </div>
                              <div class="mt-2 grid grid-cols-2 gap-2">
                                <div 
                                  v-for="permission in group" 
                                  :key="permission.id"
                                  class="flex items-start"
                                >
                                  <div class="h-5 flex items-center">
                                    <input
                                      :id="`permission-${permission.id}`"
                                      :value="permission.id"
                                      v-model="roleForm.permissions"
                                      type="checkbox"
                                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                    />
                                  </div>
                                  <div class="ml-2 text-xs">
                                    <label :for="`permission-${permission.id}`" class="font-medium text-gray-700">
                                      {{ formatPermissionName(permission.name) }}
                                    </label>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="saveRole"
                :disabled="saving"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="saving" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
              <button
                @click="showRoleModal = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Delete user confirmation dialog -->
      <div 
        v-if="showDeleteUserDialog" 
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
            @click="showDeleteUserDialog = false"
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
                    Delete User
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete the user "{{ userToDelete?.name }}"? This action cannot be undone.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="deleteUser"
                :disabled="saving"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="saving" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ saving ? 'Deleting...' : 'Delete' }}
              </button>
              <button
                @click="showDeleteUserDialog = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Delete role confirmation dialog -->
      <div 
        v-if="showDeleteRoleDialog" 
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
            @click="showDeleteRoleDialog = false"
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
                    Delete Role
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Are you sure you want to delete the role "{{ roleToDelete?.name }}"? Users with this role will lose the associated permissions.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Dialog actions -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                @click="deleteRole"
                :disabled="saving"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                <span v-if="saving" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ saving ? 'Deleting...' : 'Delete' }}
              </button>
              <button
                @click="showDeleteRoleDialog = false"
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
  import { useAuthStore } from '../../stores/auth';
  import { useAlertStore } from '../../stores/alert';
  import axios from 'axios';
  
  // Stores
  const authStore = useAuthStore();
  const alertStore = useAlertStore();
  
  // Component state
  const loading = ref(true);
  const saving = ref(false);
  const activeSection = ref('company');
  
  // Setting sections
  const settingSections = [
    { id: 'company', name: 'Company Information', icon: 'business' },
    { id: 'inventory', name: 'Inventory Settings', icon: 'inventory_2' },
    { id: 'notifications', name: 'Notifications', icon: 'notifications' },
    { id: 'users', name: 'User Management', icon: 'people' },
    { id: 'roles', name: 'Role Management', icon: 'admin_panel_settings' },
  ];
  
  /**
   * Check if user has a specific permission
   * @param {string} permission - Permission slug to check
   * @returns {boolean} True if user has permission
   */
  function hasPermission(permission) {
    return authStore.hasPermission(permission);
  }
  
  // COMPANY SETTINGS
  // ----------------
  const companySettings = reactive({
    name: '',
    contact_email: '',
    contact_phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    tax_id: '',
    currency: 'USD'
  });
  
  /**
   * Save company settings
   */
  async function saveCompanySettings() {
    if (!companySettings.name) {
      alertStore.setErrorAlert('Company name is required.');
      return;
    }
    
    saving.value = true;
    
    try {
      await axios.post('/api/settings/company', companySettings);
      alertStore.setSuccessAlert('Company settings saved successfully.');
      saving.value = false;
    } catch (error) {
      console.error('Error saving company settings:', error);
      alertStore.setApiErrorAlert(error, 'Failed to save company settings.');
      saving.value = false;
    }
  }
  
  /**
   * Reset company settings to original values
   */
  function resetCompanySettings() {
    fetchCompanySettings();
  }
  
  /**
   * Fetch company settings from API
   */
  async function fetchCompanySettings() {
    try {
      const response = await axios.get('/api/settings/company');
      Object.assign(companySettings, response.data);
    } catch (error) {
      console.error('Error fetching company settings:', error);
      alertStore.setErrorAlert('Failed to load company settings.');
    }
  }
  
  // INVENTORY SETTINGS
  // -----------------
  const inventorySettings = reactive({
    low_stock_threshold: 10,
    strict_fifo: true,
    lot_tracking: false,
    expiry_tracking: false,
    expiry_notification_days: 30,
    auto_allocate_stock: true,
    default_uom: 'each'
  });
  
  /**
   * Save inventory settings
   */
  async function saveInventorySettings() {
    saving.value = true;
    
    try {
      await axios.post('/api/settings/inventory', inventorySettings);
      alertStore.setSuccessAlert('Inventory settings saved successfully.');
      saving.value = false;
    } catch (error) {
      console.error('Error saving inventory settings:', error);
      alertStore.setApiErrorAlert(error, 'Failed to save inventory settings.');
      saving.value = false;
    }
  }
  
  /**
   * Reset inventory settings to original values
   */
  function resetInventorySettings() {
    fetchInventorySettings();
  }
  
  /**
   * Fetch inventory settings from API
   */
  async function fetchInventorySettings() {
    try {
      const response = await axios.get('/api/settings/inventory');
      Object.assign(inventorySettings, response.data);
    } catch (error) {
      console.error('Error fetching inventory settings:', error);
      alertStore.setErrorAlert('Failed to load inventory settings.');
    }
  }
  
  // NOTIFICATION SETTINGS
  // --------------------
  const notificationSettings = reactive({
    email_enabled: true,
    in_app_enabled: true,
    daily_summary: false,
    notify_low_stock: true,
    notify_expiring_inventory: true,
    notify_new_orders: true,
    notify_purchase_orders: true,
    notify_receiving: true,
    notification_emails: ''
  });
  
  /**
   * Save notification settings
   */
  async function saveNotificationSettings() {
    saving.value = true;
    
    try {
      await axios.post('/api/settings/notifications', notificationSettings);
      alertStore.setSuccessAlert('Notification settings saved successfully.');
      saving.value = false;
    } catch (error) {
      console.error('Error saving notification settings:', error);
      alertStore.setApiErrorAlert(error, 'Failed to save notification settings.');
      saving.value = false;
    }
  }
  
  /**
   * Reset notification settings to original values
   */
  function resetNotificationSettings() {
    fetchNotificationSettings();
  }
  
  /**
   * Fetch notification settings from API
   */
  async function fetchNotificationSettings() {
    try {
      const response = await axios.get('/api/settings/notifications');
      Object.assign(notificationSettings, response.data);
    } catch (error) {
      console.error('Error fetching notification settings:', error);
      alertStore.setErrorAlert('Failed to load notification settings.');
    }
  }
  
  // USER MANAGEMENT
  // --------------
  const users = ref([]);
  const loadingUsers = ref(false);
  const showUserModal = ref(false);
  const showDeleteUserDialog = ref(false);
  const editingUser = ref(false);
  const userToDelete = ref(null);
  
  // Get current user ID
  const currentUserId = computed(() => authStore.user?.id);
  
  // User form for create/edit
  const userForm = reactive({
    id: null,
    name: '',
    email: '',
    password: '',
    roles: [],
    is_active: true
  });
  
  /**
   * Reset user form to default values
   */
  function resetUserForm() {
    Object.assign(userForm, {
      id: null,
      name: '',
      email: '',
      password: '',
      roles: [],
      is_active: true
    });
    
    editingUser.value = false;
  }
  
  /**
   * Get user initials for avatar
   * @param {Object} user - User object
   * @returns {string} User initials
   */
  function getUserInitials(user) {
    if (!user || !user.name) return '?';
    
    return user.name
      .split(' ')
      .map(part => part.charAt(0).toUpperCase())
      .slice(0, 2)
      .join('');
  }
  
  /**
   * Fetch users from API
   */
  async function fetchUsers() {
    loadingUsers.value = true;
    
    try {
      const response = await axios.get('/api/users');
      users.value = response.data;
      loadingUsers.value = false;
    } catch (error) {
      console.error('Error fetching users:', error);
      alertStore.setErrorAlert('Failed to load users.');
      users.value = [];
      loadingUsers.value = false;
    }
  }
  
  /**
   * Open edit user modal
   * @param {Object} user - User to edit
   */
  function editUser(user) {
    resetUserForm();
    
    // Populate form with user data
    Object.assign(userForm, {
      id: user.id,
      name: user.name,
      email: user.email,
      is_active: user.is_active,
      roles: user.roles?.map(role => role.id) || []
    });
    
    editingUser.value = true;
    showUserModal.value = true;
  }
  
  /**
   * Confirm delete user
   * @param {Object} user - User to delete
   */
  function confirmDeleteUser(user) {
    userToDelete.value = user;
    showDeleteUserDialog.value = true;
  }
  
  /**
   * Save user (create or update)
   */
  async function saveUser() {
    // Validate form
    if (!userForm.name || !userForm.email || (!editingUser.value && !userForm.password)) {
      alertStore.setErrorAlert('Please fill in all required fields.');
      return;
    }
    
    if (!editingUser.value && userForm.password.length < 8) {
      alertStore.setErrorAlert('Password must be at least 8 characters long.');
      return;
    }
    
    saving.value = true;
    
    try {
      let response;
      
      if (editingUser.value) {
        // Update existing user
        response = await axios.put(`/api/users/${userForm.id}`, {
          name: userForm.name,
          email: userForm.email,
          roles: userForm.roles,
          is_active: userForm.is_active
        });
        
        alertStore.setSuccessAlert(`User "${userForm.name}" has been updated.`);
      } else {
        // Create new user
        response = await axios.post('/api/users', userForm);
        alertStore.setSuccessAlert(`User "${userForm.name}" has been created.`);
      }
      
      // Close modal and refresh data
      showUserModal.value = false;
      await fetchUsers();
      
      saving.value = false;
    } catch (error) {
      console.error('Error saving user:', error);
      alertStore.setApiErrorAlert(error, 'Failed to save user.');
      
      saving.value = false;
    }
  }
  
  /**
   * Delete user
   */
  async function deleteUser() {
    if (!userToDelete.value) return;
    
    saving.value = true;
    
    try {
      await axios.delete(`/api/users/${userToDelete.value.id}`);
      
      // Show success notification
      alertStore.setSuccessAlert(`User "${userToDelete.value.name}" has been deleted.`);
      
      // Close dialog and refresh data
      showDeleteUserDialog.value = false;
      userToDelete.value = null;
      await fetchUsers();
      
      saving.value = false;
    } catch (error) {
      console.error('Error deleting user:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete user.');
      
      saving.value = false;
    }
  }
  
  // ROLE MANAGEMENT
  // --------------
  const roles = ref([]);
  const permissions = ref([]);
  const loadingRoles = ref(false);
  const showRoleModal = ref(false);
  const showDeleteRoleDialog = ref(false);
  const editingRole = ref(false);
  const roleToDelete = ref(null);
  
  // Role form for create/edit
  const roleForm = reactive({
    id: null,
    name: '',
    description: '',
    permissions: []
  });
  
  /**
   * Reset role form to default values
   */
  function resetRoleForm() {
    Object.assign(roleForm, {
      id: null,
      name: '',
      description: '',
      permissions: []
    });
    
    editingRole.value = false;
  }
  
  /**
   * Calculate percentage of permissions assigned to a role
   * @param {Object} role - Role object
   * @returns {number} Percentage of assigned permissions
   */
  function getPermissionPercentage(role) {
    if (!role.permissions || !permissions.value.length) return 0;
    
    return Math.round((role.permissions.length / permissions.value.length) * 100);
  }
  
  /**
   * Format permission name for display
   * @param {string} name - Raw permission name
   * @returns {string} Formatted permission name
   */
  function formatPermissionName(name) {
    if (!name) return '';
    
    // Convert slug (e.g., "products.create") to readable form (e.g., "Create")
    const parts = name.split('.');
    if (parts.length > 1) {
      const action = parts[1];
      return action.charAt(0).toUpperCase() + action.slice(1);
    }
    
    return name;
  }
  
  /**
   * Group permissions by category
   */
  const permissionGroups = computed(() => {
    const groups = {};
    
    permissions.value.forEach(permission => {
      const parts = permission.name.split('.');
      const category = parts[0];
      
      if (!groups[category]) {
        groups[category] = [];
      }
      
      groups[category].push(permission);
    });
    
    return groups;
  });
  
  /**
   * Check if all permissions in a group are selected
   * @param {string} category - Permission category
   * @returns {boolean} True if all permissions in the group are selected
   */
  function isGroupSelected(category) {
    const groupPermissions = permissionGroups.value[category] || [];
    const groupPermissionIds = groupPermissions.map(p => p.id);
    
    return groupPermissionIds.every(id => roleForm.permissions.includes(id));
  }
  
  /**
   * Toggle all permissions in a group
   * @param {string} category - Permission category
   */
  function togglePermissionGroup(category) {
    const groupPermissions = permissionGroups.value[category] || [];
    const groupPermissionIds = groupPermissions.map(p => p.id);
    
    if (isGroupSelected(category)) {
      // Deselect all in the group
      roleForm.permissions = roleForm.permissions.filter(id => !groupPermissionIds.includes(id));
    } else {
      // Select all in the group
      const newPermissions = [...roleForm.permissions];
      
      groupPermissionIds.forEach(id => {
        if (!newPermissions.includes(id)) {
          newPermissions.push(id);
        }
      });
      
      roleForm.permissions = newPermissions;
    }
  }
  
  /**
   * Fetch roles from API
   */
  async function fetchRoles() {
    loadingRoles.value = true;
    
    try {
      const response = await axios.get('/api/roles', {
        params: { with_permissions: true, with_users_count: true }
      });
      
      roles.value = response.data;
      loadingRoles.value = false;
    } catch (error) {
      console.error('Error fetching roles:', error);
      alertStore.setErrorAlert('Failed to load roles.');
      roles.value = [];
      loadingRoles.value = false;
    }
  }
  
  /**
   * Fetch permissions from API
   */
  async function fetchPermissions() {
    try {
      const response = await axios.get('/api/permissions');
      permissions.value = response.data;
    } catch (error) {
      console.error('Error fetching permissions:', error);
      alertStore.setErrorAlert('Failed to load permissions.');
      permissions.value = [];
    }
  }
  
  /**
   * Open edit role modal
   * @param {Object} role - Role to edit
   */
  function editRole(role) {
    resetRoleForm();
    
    // Populate form with role data
    Object.assign(roleForm, {
      id: role.id,
      name: role.name,
      description: role.description,
      permissions: role.permissions?.map(permission => permission.id) || []
    });
    
    editingRole.value = true;
    showRoleModal.value = true;
  }
  
  /**
   * Confirm delete role
   * @param {Object} role - Role to delete
   */
  function confirmDeleteRole(role) {
    roleToDelete.value = role;
    showDeleteRoleDialog.value = true;
  }
  
  /**
   * Save role (create or update)
   */
  async function saveRole() {
    // Validate form
    if (!roleForm.name) {
      alertStore.setErrorAlert('Role name is required.');
      return;
    }
    
    saving.value = true;
    
    try {
      let response;
      
      if (editingRole.value) {
        // Update existing role
        response = await axios.put(`/api/roles/${roleForm.id}`, roleForm);
        alertStore.setSuccessAlert(`Role "${roleForm.name}" has been updated.`);
      } else {
        // Create new role
        response = await axios.post('/api/roles', roleForm);
        alertStore.setSuccessAlert(`Role "${roleForm.name}" has been created.`);
      }
      
      // Close modal and refresh data
      showRoleModal.value = false;
      await fetchRoles();
      
      saving.value = false;
    } catch (error) {
      console.error('Error saving role:', error);
      alertStore.setApiErrorAlert(error, 'Failed to save role.');
      
      saving.value = false;
    }
  }
  
  /**
   * Delete role
   */
  async function deleteRole() {
    if (!roleToDelete.value) return;
    
    saving.value = true;
    
    try {
      await axios.delete(`/api/roles/${roleToDelete.value.id}`);
      
      // Show success notification
      alertStore.setSuccessAlert(`Role "${roleToDelete.value.name}" has been deleted.`);
      
      // Close dialog and refresh data
      showDeleteRoleDialog.value = false;
      roleToDelete.value = null;
      await fetchRoles();
      
      saving.value = false;
    } catch (error) {
      console.error('Error deleting role:', error);
      alertStore.setApiErrorAlert(error, 'Failed to delete role.');
      
      saving.value = false;
    }
  }
  
  /**
   * Initialize component data
   */
  async function initialize() {
    loading.value = true;
    
    try {
      // Fetch all settings data in parallel
      await Promise.all([
        fetchCompanySettings(),
        fetchInventorySettings(),
        fetchNotificationSettings(),
        fetchUsers(),
        fetchRoles(),
        fetchPermissions()
      ]);
      
      loading.value = false;
    } catch (error) {
      console.error('Error initializing settings:', error);
      alertStore.setErrorAlert('Failed to load settings data.');
      loading.value = false;
    }
  }
  
  // Initialize component on mount
  onMounted(() => {
    initialize();
  });
  </script>
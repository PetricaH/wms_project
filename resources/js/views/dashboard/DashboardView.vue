<!-- resources/js/views/dashboard/DashboardView.vue -->

<template>
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 mb-6">Dashboard</h1>
      
      <!-- Loading state -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else>
        <!-- Key metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Pending Orders metric -->
          <MetricCard 
            title="Pending Orders" 
            :value="metrics.pendingOrders" 
            icon="shopping_bag"
            color="blue"
            trend-direction="up"
            :trend-value="metrics.pendingOrdersTrend"
            trend-label="vs. last period"
          />
          
          <!-- Items to Ship metric -->
          <MetricCard 
            title="Items to Ship" 
            :value="metrics.itemsToShip" 
            icon="local_shipping"
            color="green"
            trend-direction="down"
            :trend-value="metrics.itemsToShipTrend"
            trend-label="vs. last period"
          />
          
          <!-- Low Stock Items metric -->
          <MetricCard 
            title="Low Stock Items" 
            :value="metrics.lowStockItems" 
            icon="inventory"
            color="red"
            :is-negative="true"
            trend-direction="up"
            :trend-value="metrics.lowStockItemsTrend"
            trend-label="vs. last period"
          />
          
          <!-- Receiving Due metric -->
          <MetricCard 
            title="Receiving Due" 
            :value="metrics.receivingDue" 
            icon="receipt_long"
            color="purple"
            trend-direction="none"
            :trend-value="0"
            trend-label="vs. last period"
          />
        </div>
        
        <!-- Charts and tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Orders chart -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Orders Overview</h3>
              <p class="text-sm text-gray-500">Last 30 days order activity</p>
            </div>
            <div class="p-4 h-80">
              <OrdersChart :data="chartData.orders" />
            </div>
          </div>
          
          <!-- Inventory movement chart -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Inventory Movement</h3>
              <p class="text-sm text-gray-500">Stock in/out over time</p>
            </div>
            <div class="p-4 h-80">
              <InventoryChart :data="chartData.inventory" />
            </div>
          </div>
        </div>
        
        <!-- Recent activities and alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Recent orders -->
          <div class="bg-white rounded-lg shadow lg:col-span-2">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
              <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Orders</h3>
                <p class="text-sm text-gray-500">Latest customer orders</p>
              </div>
              <router-link 
                to="/dashboard/orders" 
                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
              >
                View all
              </router-link>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Order #
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Customer
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-if="recentOrders.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                      No recent orders found
                    </td>
                  </tr>
                  <tr 
                    v-for="order in recentOrders" 
                    :key="order.id"
                    class="hover:bg-gray-50 cursor-pointer"
                    @click="navigateToOrder(order.id)"
                  >
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                      {{ order.order_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ order.customer_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(order.order_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatCurrency(order.total_amount) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="getStatusClass(order.status)"
                      >
                        {{ formatStatus(order.status) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- System alerts and notifications -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg font-medium leading-6 text-gray-900">System Alerts</h3>
              <p class="text-sm text-gray-500">Important notifications</p>
            </div>
            <div class="divide-y divide-gray-200">
              <div v-if="systemAlerts.length === 0" class="p-6 text-center">
                <span class="text-gray-500 text-sm">No alerts at this time</span>
              </div>
              <div
                v-for="(alert, index) in systemAlerts"
                :key="index"
                class="p-4"
              >
                <div class="flex items-start">
                  <div 
                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                    :class="getAlertIconClass(alert.type)"
                  >
                    <span class="material-icons text-white text-sm">{{ getAlertIcon(alert.type) }}</span>
                  </div>
                  <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ alert.title }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ alert.message }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ formatDate(alert.timestamp) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAlertStore } from '../../stores/alert';
  import MetricCard from '../../components/dashboard/MetricCard.vue';
  import OrdersChart from '../../components/dashboard/OrdersChart.vue';
  import InventoryChart from '../../components/dashboard/InventoryChart.vue';
  import axios from 'axios';
  
  // Router for navigation
  const router = useRouter();
  const alertStore = useAlertStore();
  
  // State variables
  const isLoading = ref(true);
  const metrics = ref({
    pendingOrders: 0,
    pendingOrdersTrend: 0,
    itemsToShip: 0,
    itemsToShipTrend: 0,
    lowStockItems: 0,
    lowStockItemsTrend: 0,
    receivingDue: 0
  });
  const chartData = ref({
    orders: [],
    inventory: []
  });
  const recentOrders = ref([]);
  const systemAlerts = ref([]);
  
  /**
   * Format date to readable string
   * @param {string} dateString - Date string to format
   * @returns {string} Formatted date
   */
  function formatDate(dateString) {
    // Convert to JS Date object
    const date = new Date(dateString);
    
    // Format with browser's locale date format
    // For a more consistent format you could use a library like date-fns
    return date.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }
  
  /**
   * Format currency with appropriate symbol and formatting
   * @param {number} amount - Amount to format
   * @returns {string} Formatted currency amount
   */
  function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  }
  
  /**
   * Make order status more readable by capitalizing and replacing underscores
   * @param {string} status - Original status from API
   * @returns {string} Formatted status string
   */
  function formatStatus(status) {
    return status
      .replace(/_/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
  
  /**
   * Get CSS class for order status badge
   * @param {string} status - Order status
   * @returns {string} CSS classes for the status badge
   */
  function getStatusClass(status) {
    const statusClasses = {
      pending: 'bg-yellow-100 text-yellow-800',
      processing: 'bg-blue-100 text-blue-800',
      awaiting_payment: 'bg-orange-100 text-orange-800',
      paid: 'bg-green-100 text-green-800',
      ready_to_pick: 'bg-indigo-100 text-indigo-800',
      picking: 'bg-indigo-100 text-indigo-800',
      picked: 'bg-purple-100 text-purple-800',
      packing: 'bg-purple-100 text-purple-800',
      packed: 'bg-purple-100 text-purple-800',
      awaiting_shipment: 'bg-blue-100 text-blue-800',
      shipped: 'bg-green-100 text-green-800',
      delivered: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800',
      returned: 'bg-red-100 text-red-800',
      completed: 'bg-green-100 text-green-800',
      on_hold: 'bg-gray-100 text-gray-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
  }
  
  /**
   * Get appropriate icon for alert type
   * @param {string} type - Alert type
   * @returns {string} Material icon name
   */
  function getAlertIcon(type) {
    switch (type) {
      case 'warning':
        return 'warning';
      case 'error':
        return 'error';
      case 'success':
        return 'check_circle';
      case 'info':
      default:
        return 'info';
    }
  }
  
  /**
   * Get CSS classes for alert icon background
   * @param {string} type - Alert type
   * @returns {string} CSS classes
   */
  function getAlertIconClass(type) {
    switch (type) {
      case 'warning':
        return 'bg-yellow-500';
      case 'error':
        return 'bg-red-500';
      case 'success':
        return 'bg-green-500';
      case 'info':
      default:
        return 'bg-blue-500';
    }
  }
  
  /**
   * Navigate to order detail view
   * @param {number} orderId - Order ID to navigate to
   */
  function navigateToOrder(orderId) {
    router.push({ name: 'order-detail', params: { id: orderId } });
  }
  
  /**
   * Fetch dashboard data from API
   */
  async function fetchDashboardData() {
    isLoading.value = true;
    
    try {
      // In a real application, you'd fetch each data set from the API
      // Here we'll use a single endpoint for simplicity
      const response = await axios.get('/api/dashboard');
      
      // Update state with API response data
      metrics.value = response.data.metrics;
      chartData.value = response.data.charts;
      recentOrders.value = response.data.recentOrders;
      systemAlerts.value = response.data.alerts;
      
      isLoading.value = false;
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
      alertStore.setApiErrorAlert(error, 'Failed to load dashboard data.');
      
      // Fallback to mock data for development/demo purposes
      loadMockData();
    }
  }
  
  /**
   * Load mock data for development/demo
   * In a real application, this would be replaced with actual API calls
   */
  function loadMockData() {
    // Mock metrics
    metrics.value = {
      pendingOrders: 24,
      pendingOrdersTrend: 12,
      itemsToShip: 156,
      itemsToShipTrend: -5,
      lowStockItems: 8,
      lowStockItemsTrend: 3,
      receivingDue: 3
    };
    
    // Mock chart data
    chartData.value = {
      // Orders chart data - last 7 days
      orders: [
        { date: '2024-03-24', created: 12, shipped: 10, cancelled: 1 },
        { date: '2024-03-25', created: 15, shipped: 9, cancelled: 2 },
        { date: '2024-03-26', created: 18, shipped: 14, cancelled: 0 },
        { date: '2024-03-27', created: 14, shipped: 12, cancelled: 1 },
        { date: '2024-03-28', created: 16, shipped: 13, cancelled: 1 },
        { date: '2024-03-29', created: 21, shipped: 17, cancelled: 2 },
        { date: '2024-03-30', created: 19, shipped: 15, cancelled: 0 }
      ],
      
      // Inventory chart data - last 7 days
      inventory: [
        { date: '2024-03-24', incoming: 45, outgoing: 37 },
        { date: '2024-03-25', incoming: 30, outgoing: 42 },
        { date: '2024-03-26', incoming: 65, outgoing: 52 },
        { date: '2024-03-27', incoming: 25, outgoing: 38 },
        { date: '2024-03-28', incoming: 40, outgoing: 43 },
        { date: '2024-03-29', incoming: 55, outgoing: 40 },
        { date: '2024-03-30', incoming: 35, outgoing: 48 }
      ]
    };
    
    // Mock recent orders
    recentOrders.value = [
      {
        id: 1,
        order_number: 'ORD-10258',
        customer_name: 'John Smith',
        order_date: '2024-03-30',
        total_amount: 435.29,
        status: 'processing'
      },
      {
        id: 2,
        order_number: 'ORD-10257',
        customer_name: 'Emily Johnson',
        order_date: '2024-03-29',
        total_amount: 152.75,
        status: 'shipped'
      },
      {
        id: 3,
        order_number: 'ORD-10256',
        customer_name: 'Michael Williams',
        order_date: '2024-03-29',
        total_amount: 89.99,
        status: 'delivered'
      },
      {
        id: 4,
        order_number: 'ORD-10255',
        customer_name: 'Jessica Brown',
        order_date: '2024-03-28',
        total_amount: 214.50,
        status: 'awaiting_payment'
      },
      {
        id: 5,
        order_number: 'ORD-10254',
        customer_name: 'Robert Davis',
        order_date: '2024-03-28',
        total_amount: 65.25,
        status: 'cancelled'
      }
    ];
    
    // Mock system alerts
    systemAlerts.value = [
      {
        type: 'warning',
        title: 'Low Stock Alert',
        message: '8 products are below their minimum stock level',
        timestamp: '2024-03-30T09:15:00Z'
      },
      {
        type: 'info',
        title: 'Purchase Orders Due',
        message: '3 purchase orders are expected to arrive today',
        timestamp: '2024-03-30T08:30:00Z'
      },
      {
        type: 'error',
        title: 'Shipping Integration Error',
        message: 'FedEx API integration is experiencing connectivity issues',
        timestamp: '2024-03-29T15:45:00Z'
      }
    ];
    
    isLoading.value = false;
  }
  
  // Fetch data when component mounts
  onMounted(() => {
    fetchDashboardData();
  });
  </script>
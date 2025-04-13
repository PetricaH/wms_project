<template>
    <div class="sidebar-navigation">
      <!-- Logo and brand -->
      <div class="flex items-center justify-center h-16 bg-gray-900">
        <h1 class="text-xl font-bold text-white">WMS System</h1>
      </div>
      
      <!-- Navigation links -->
      <nav class="mt-5 px-2 space-y-1">
        <!-- Dashboard -->
        <SidebarNavItem 
          :icon="'dashboard'"
          :name="'Dashboard'"
          :route="{ name: 'dashboard' }"
          :active="isRouteActive('dashboard')"
        />
        
        <!-- Inventory Section -->
        <SidebarNavSection 
          :title="'Inventory'"
          :icon="'inventory_2'"
          :expanded="isExpanded('inventory')"
          @toggle="toggleSection('inventory')"
        >
          <SidebarNavItem 
            :icon="'category'"
            :name="'Products'"
            :route="{ name: 'products' }"
            :active="isRouteActive('products')"
            :permission="'products.view'"
          />
          <SidebarNavItem 
            :icon="'inventory'"
            :name="'Inventory Levels'"
            :route="{ name: 'inventory' }"
            :active="isRouteActive('inventory')"
            :permission="'inventory.view'"
          />
          <SidebarNavItem 
            :icon="'swap_horiz'"
            :name="'Stock Adjustments'"
            :route="{ name: 'stock-adjustments' }"
            :active="isRouteActive('stock-adjustments')"
            :permission="'inventory.adjust'"
          />
          <SidebarNavItem 
            :icon="'list_alt'"
            :name="'Categories'"
            :route="{ name: 'categories' }"
            :active="isRouteActive('categories')"
            :permission="'categories.view'"
          />
        </SidebarNavSection>
        
        <!-- Warehouse Section -->
        <SidebarNavSection 
          :title="'Warehouse'"
          :icon="'warehouse'"
          :expanded="isExpanded('warehouse')"
          @toggle="toggleSection('warehouse')"
        >
          <SidebarNavItem 
            :icon="'store'"
            :name="'Warehouses'"
            :route="{ name: 'warehouses' }"
            :active="isRouteActive('warehouses')"
            :permission="'warehouses.view'"
          />
          <SidebarNavItem 
            :icon="'grid_view'"
            :name="'Zones'"
            :route="{ name: 'zones' }"
            :active="isRouteActive('zones')"
            :permission="'zones.view'"
          />
          <SidebarNavItem 
            :icon="'location_on'"
            :name="'Bin Locations'"
            :route="{ name: 'bin-locations' }"
            :active="isRouteActive('bin-locations')"
            :permission="'bin-locations.view'"
          />
        </SidebarNavSection>
        
        <!-- Purchasing Section -->
        <SidebarNavSection 
          :title="'Purchasing'"
          :icon="'shopping_cart'"
          :expanded="isExpanded('purchasing')"
          @toggle="toggleSection('purchasing')"
        >
          <SidebarNavItem 
            :icon="'description'"
            :name="'Purchase Orders'"
            :route="{ name: 'purchase-orders' }"
            :active="isRouteActive('purchase-orders')"
            :permission="'purchase-orders.view'"
          />
          <SidebarNavItem 
            :icon="'inventory'"
            :name="'Receiving'"
            :route="{ name: 'receiving' }"
            :active="isRouteActive('receiving')"
            :permission="'receiving.view'"
          />
          <SidebarNavItem 
            :icon="'business'"
            :name="'Suppliers'"
            :route="{ name: 'suppliers' }"
            :active="isRouteActive('suppliers')"
            :permission="'suppliers.view'"
          />
        </SidebarNavSection>
        
        <!-- Orders Section -->
        <SidebarNavSection 
          :title="'Orders'"
          :icon="'shopping_bag'"
          :expanded="isExpanded('orders')"
          @toggle="toggleSection('orders')"
        >
          <SidebarNavItem 
            :icon="'receipt_long'"
            :name="'All Orders'"
            :route="{ name: 'orders' }"
            :active="isRouteActive('orders')"
            :permission="'orders.view'"
          />
          <SidebarNavItem 
            :icon="'checklist'"
            :name="'Picking'"
            :route="{ name: 'picking' }"
            :active="isRouteActive('picking')"
            :permission="'picking.view'"
          />
          <SidebarNavItem 
            :icon="'inventory_2'"
            :name="'Packing'"
            :route="{ name: 'packing' }"
            :active="isRouteActive('packing')"
            :permission="'packing.view'"
          />
          <SidebarNavItem 
            :icon="'local_shipping'"
            :name="'Shipping'"
            :route="{ name: 'shipping' }"
            :active="isRouteActive('shipping')"
            :permission="'shipping.view'"
          />
        </SidebarNavSection>
        
        <!-- Reports Section -->
        <SidebarNavSection 
          :title="'Reports'"
          :icon="'bar_chart'"
          :expanded="isExpanded('reports')"
          @toggle="toggleSection('reports')"
        >
          <SidebarNavItem 
            :icon="'analytics'"
            :name="'Sales Reports'"
            :route="{ name: 'sales-reports' }"
            :active="isRouteActive('sales-reports')"
            :permission="'reports.view'"
          />
          <SidebarNavItem 
            :icon="'inventory'"
            :name="'Inventory Reports'"
            :route="{ name: 'inventory-reports' }"
            :active="isRouteActive('inventory-reports')"
            :permission="'reports.view'"
          />
          <SidebarNavItem 
            :icon="'receipt'"
            :name="'Purchase Reports'"
            :route="{ name: 'purchase-reports' }"
            :active="isRouteActive('purchase-reports')"
            :permission="'reports.view'"
          />
        </SidebarNavSection>
        
        <!-- Settings -->
        <SidebarNavItem 
          :icon="'settings'"
          :name="'Settings'"
          :route="{ name: 'settings' }"
          :active="isRouteActive('settings')"
          :permission="'settings.view'"
        />
        
        <!-- Users & Permissions -->
        <SidebarNavSection 
          :title="'Users & Permissions'"
          :icon="'admin_panel_settings'"
          :expanded="isExpanded('users')"
          @toggle="toggleSection('users')"
        >
          <SidebarNavItem 
            :icon="'people'"
            :name="'Users'"
            :route="{ name: 'users' }"
            :active="isRouteActive('users')"
            :permission="'users.view'"
          />
          <SidebarNavItem 
            :icon="'shield'"
            :name="'Roles'"
            :route="{ name: 'roles' }"
            :active="isRouteActive('roles')"
            :permission="'roles.view'"
          />
          <SidebarNavItem 
            :icon="'verified_user'"
            :name="'Permissions'"
            :route="{ name: 'permissions' }"
            :active="isRouteActive('permissions')"
            :permission="'permissions.view'"
          />
        </SidebarNavSection>
      </nav>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import SidebarNavItem from './SidebarNavItem.vue'; 
  import SidebarNavSection from './SidebarNavSection.vue';
  
  const route = useRoute();
  
  // Track expanded sections
  const expandedSections = ref({
    inventory: true,   // Start with inventory expanded by default
    warehouse: false,
    purchasing: false,
    orders: false,
    reports: false,
    users: false
  });
  
  /**
   * Check if a route is active
   * @param {string} routeName - Route name to check
   * @returns {boolean} True if route is active
   */
  function isRouteActive(routeName) {
    return route.name === routeName || route.name?.startsWith(routeName + '-');
  }
  
  /**
   * Check if a section is expanded
   * @param {string} section - Section name
   * @returns {boolean} True if section is expanded
   */
  function isExpanded(section) {
    return expandedSections.value[section] || false;
  }
  
  /**
   * Toggle a section's expanded state
   * @param {string} section - Section to toggle
   */
  function toggleSection(section) {
    expandedSections.value[section] = !expandedSections.value[section];
  }
  
  /**
   * Auto-expand section based on current route
   */
  function autoExpandSections() {
    // Inventory routes
    if (['products', 'inventory', 'stock-adjustments', 'categories'].some(name => isRouteActive(name))) {
      expandedSections.value.inventory = true;
    }
    
    // Warehouse routes
    if (['warehouses', 'zones', 'bin-locations'].some(name => isRouteActive(name))) {
      expandedSections.value.warehouse = true;
    }
    
    // Purchasing routes
    if (['purchase-orders', 'receiving', 'suppliers'].some(name => isRouteActive(name))) {
      expandedSections.value.purchasing = true;
    }
    
    // Orders routes
    if (['orders', 'picking', 'packing', 'shipping'].some(name => isRouteActive(name))) {
      expandedSections.value.orders = true;
    }
    
    // Reports routes
    if (['sales-reports', 'inventory-reports', 'purchase-reports'].some(name => isRouteActive(name))) {
      expandedSections.value.reports = true;
    }
    
    // Users routes
    if (['users', 'roles', 'permissions'].some(name => isRouteActive(name))) {
      expandedSections.value.users = true;
    }
  }
  
  // Initialize auto-expand on component mount
  onMounted(() => {
    autoExpandSections();
  });
  </script>
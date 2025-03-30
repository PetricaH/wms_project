// resources/js/router/index.js

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Import layout components
import DefaultLayout from '../layouts/DefaultLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';

// Lazy-load route components for better performance
// This uses dynamic imports which webpack/vite will code-split
const routes = [
  {
    // Authentication routes use a different layout
    path: '/',
    component: AuthLayout,
    meta: { requiresGuest: true },
    children: [
      {
        path: '',
        name: 'login',
        component: () => import('../views/auth/LoginView.vue'),
        meta: { title: 'Login' }
      },
      {
        path: 'register',
        name: 'register',
        component: () => import('../views/auth/RegisterView.vue'),
        meta: { title: 'Register' }
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: () => import('../views/auth/ForgotPasswordView.vue'),
        meta: { title: 'Forgot Password' }
      }
    ]
  },
  {
    // Main application routes use the default layout with sidebar
    path: '/dashboard',
    component: DefaultLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('../views/dashboard/DashboardView.vue'),
        meta: { title: 'Dashboard', permission: 'dashboard.view' }
      },
      // Inventory Management Routes
      {
        path: 'inventory',
        name: 'inventory',
        component: () => import('../views/inventory/InventoryView.vue'),
        meta: { title: 'Inventory', permission: 'inventory.view' }
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('../views/products/ProductsView.vue'),
        meta: { title: 'Products', permission: 'products.view' }
      },
      {
        path: 'products/:id',
        name: 'product-detail',
        component: () => import('../views/products/ProductDetailView.vue'),
        meta: { title: 'Product Detail', permission: 'products.view' }
      },
      {
        path: 'products/create',
        name: 'product-create',
        component: () => import('../views/products/ProductCreateView.vue'),
        meta: { title: 'Create Product', permission: 'products.create' }
      },
      {
        path: 'products/:id/edit',
        name: 'product-edit',
        component: () => import('../views/products/ProductEditView.vue'),
        meta: { title: 'Edit Product', permission: 'products.edit' }
      },
      // Warehouse Management Routes
      {
        path: 'warehouses',
        name: 'warehouses',
        component: () => import('../views/warehouses/WarehousesView.vue'),
        meta: { title: 'Warehouses', permission: 'warehouses.view' }
      },
      {
        path: 'warehouses/:id',
        name: 'warehouse-detail',
        component: () => import('../views/warehouses/WarehouseDetailView.vue'),
        meta: { title: 'Warehouse Detail', permission: 'warehouses.view' }
      },
      {
        path: 'zones',
        name: 'zones',
        component: () => import('../views/zones/ZonesView.vue'),
        meta: { title: 'Zones', permission: 'zones.view' }
      },
      {
        path: 'bin-locations',
        name: 'bin-locations',
        component: () => import('../views/binLocations/BinLocationsView.vue'),
        meta: { title: 'Bin Locations', permission: 'bin-locations.view' }
      },
      // Purchase Order Routes
      {
        path: 'purchase-orders',
        name: 'purchase-orders',
        component: () => import('../views/purchaseOrders/PurchaseOrdersView.vue'),
        meta: { title: 'Purchase Orders', permission: 'purchase-orders.view' }
      },
      {
        path: 'purchase-orders/create',
        name: 'purchase-order-create',
        component: () => import('../views/purchaseOrders/PurchaseOrderCreateView.vue'),
        meta: { title: 'Create Purchase Order', permission: 'purchase-orders.create' }
      },
      {
        path: 'purchase-orders/:id',
        name: 'purchase-order-detail',
        component: () => import('../views/purchaseOrders/PurchaseOrderDetailView.vue'),
        meta: { title: 'Purchase Order Detail', permission: 'purchase-orders.view' }
      },
      // Receiving Routes
      {
        path: 'receiving',
        name: 'receiving',
        component: () => import('../views/receiving/ReceivingView.vue'),
        meta: { title: 'Receiving', permission: 'receiving.view' }
      },
      // Order Management Routes
      {
        path: 'orders',
        name: 'orders',
        component: () => import('../views/orders/OrdersView.vue'),
        meta: { title: 'Orders', permission: 'orders.view' }
      },
      {
        path: 'orders/create',
        name: 'order-create',
        component: () => import('../views/orders/OrderCreateView.vue'),
        meta: { title: 'Create Order', permission: 'orders.create' }
      },
      {
        path: 'orders/:id',
        name: 'order-detail',
        component: () => import('../views/orders/OrderDetailView.vue'),
        meta: { title: 'Order Detail', permission: 'orders.view' }
      },
      // Picking and Packing Routes
      {
        path: 'picking',
        name: 'picking',
        component: () => import('../views/picking/PickingView.vue'),
        meta: { title: 'Picking', permission: 'picking.view' }
      },
      {
        path: 'packing',
        name: 'packing',
        component: () => import('../views/packing/PackingView.vue'),
        meta: { title: 'Packing', permission: 'packing.view' }
      },
      // User and Settings Routes
      {
        path: 'profile',
        name: 'profile',
        component: () => import('../views/profile/ProfileView.vue'),
        meta: { title: 'Profile' }
      },
      {
        path: 'settings',
        name: 'settings',
        component: () => import('../views/settings/SettingsView.vue'),
        meta: { title: 'Settings', permission: 'settings.view' }
      },
    ]
  },
  // 404 Not Found route
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('../views/errors/NotFoundView.vue'),
    meta: { title: 'Page Not Found' }
  }
];

// Create the router instance
const router = createRouter({
  history: createWebHistory(), // Use HTML5 history mode for cleaner URLs
  routes,
  // Scroll to top when navigating to a new route
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Navigation guards to protect routes based on authentication and permissions
router.beforeEach(async (to, from, next) => {
  // Update document title
  document.title = `${to.meta.title || 'WMS'} - Warehouse Management System`;
  
  const authStore = useAuthStore();
  
  // Check if the route requires authentication
  if (to.meta.requiresAuth) {
    // If not authenticated, redirect to login
    if (!authStore.isAuthenticated) {
      return next({ name: 'login', query: { redirect: to.fullPath } });
    }
    
    // Check if the user has permission to access the route
    if (to.meta.permission && !authStore.hasPermission(to.meta.permission)) {
      return next({ name: 'dashboard' }); // Redirect to dashboard if no permission
    }
  }
  
  // Check if the route requires a guest (non-authenticated user)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return next({ name: 'dashboard' }); // Redirect to dashboard if already logged in
  }
  
  next(); // Continue to the route
});

export default router;
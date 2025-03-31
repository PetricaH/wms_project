// resources/js/router/index.js

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth'; // Ensure Pinia is initialized before router

// Import layout components
import DefaultLayout from '../layouts/DefaultLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';

// Lazy-load route components
const routes = [
  {
    path: '/home',
    name: 'home',
    component: () => import('../views/HomeView.vue'),
    meta: { 
      requiresAuth: true,  // Still requires authentication
      title: 'Home'        // But no specific permission required
    }
  },
  {
    path: '/',
    component: AuthLayout,
    meta: { requiresGuest: true }, // Apply to all children
    children: [
      {
        path: '', // Default to login
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
    path: '/dashboard',
    component: DefaultLayout,
    meta: { requiresAuth: true }, // Apply to all children
    children: [
      {
        path: '', // Default to dashboard view
        name: 'dashboard',
        component: () => import('../views/dashboard/DashboardView.vue'),
        meta: { title: 'Dashboard', permission: 'dashboard.view' } // Permission check added
      },
       // --- Add other protected routes here ---
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
       // ... other routes from your example ...
       {
        path: 'profile',
        name: 'profile',
        component: () => import('../views/profile/ProfileView.vue'),
        meta: { title: 'Profile' } // No specific permission needed?
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
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition;
    return { top: 0 };
  }
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  console.log(`Navigating from ${from.fullPath} to ${to.fullPath}`); // DEBUG LOG

  // Update document title
  document.title = `${to.meta.title || 'WMS'} - Warehouse Management System`;

  // Get auth store
  let authStore;
  try {
      authStore = useAuthStore();
  } catch (error) {
      console.error("Pinia store 'auth' not initialized yet?", error);
      return next({ name: 'login' });
  }

  const isAuthenticated = authStore.isAuthenticated;
  console.log(`Is Authenticated: ${isAuthenticated}`); 

  // Check if the route requires authentication
  if (to.meta.requiresAuth) {
    console.log(`Route ${to.path} requires auth.`);
    if (!isAuthenticated) {
      console.log("User not authenticated. Redirecting to login.");
      return next({ name: 'login', query: { redirect: to.fullPath } });
    }

    // Check for permission AFTER confirming authentication
    const requiredPermission = to.meta.permission;
    if (requiredPermission) {
        console.log(`Route requires permission: ${requiredPermission}`);
        const hasPerm = authStore.hasPermission(requiredPermission);
        console.log(`User has permission: ${hasPerm}`);
        
        if (!hasPerm) {
            console.log("User lacks permission. Redirecting to home.");
            
            // IMPORTANT CHANGE: Redirect to a different route to prevent loops
            // Don't redirect to dashboard (that's what was causing the loop)
            if (to.name === 'dashboard') {
                // If we're already trying to go to dashboard, redirect to a "no access" page
                // or a safe home page that doesn't require special permissions
                return next({ name: 'home' }); // Create this route
            } else {
                // If trying to access another restricted page, go to dashboard
                return next({ name: 'home' });
            }
        }
    }
  }

  // Check if the route requires a guest
  if (to.meta.requiresGuest) {
    console.log(`Route ${to.path} requires guest.`);
    if (isAuthenticated) {
      console.log("User is authenticated. Redirecting to dashboard.");
      return next({ name: 'dashboard' });
    }
  }

  console.log("Guard passed. Proceeding to route.");
  next();
});
export default router;

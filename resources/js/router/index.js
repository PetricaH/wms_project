// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Import views
// Note: These paths assume you'll create these components in the resources/js/views directory
import LoginView from '/resources/views/auth/Login.vue'
import RegisterView from '/resources/views/auth/Register.vue'
import DashboardView from '/resources/js/components/Dashboard.vue'
import NotFoundView from '/resources/js/components/NotFound.vue'

/**
 * Define routes with meta information for authentication
 */
const routes = [
  {
    path: '/',
    redirect: { name: 'login' }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { 
      requiresAuth: false,
      layout: 'auth'
    }
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { 
      requiresAuth: false,
      layout: 'auth'
    }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { 
      requiresAuth: true,
      layout: 'default'
    }
  },
  {
    // Not found route - must be at the end
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
    meta: { layout: 'error' }
  }
]

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes
})

/**
 * Global navigation guard
 * - Checks if route requires authentication
 * - Redirects to login if not authenticated
 * - Fetches user data if token exists but user data is missing
 */
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Check if the route requires authentication
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  
  // Check if user is authenticated or if there's a token without user data
  const isAuthenticated = authStore.isAuthenticated
  const hasToken = !!authStore.token
  
  // If route requires auth and user is not authenticated
  if (requiresAuth && !isAuthenticated) {
    // If there's a token but no user data, try to fetch user data first
    if (hasToken && !authStore.user) {
      try {
        await authStore.fetchCurrentUser()
        
        // If user fetch was successful, continue to requested route
        if (authStore.user) {
          return next()
        }
        
        // Otherwise, redirect to login
        return next({ name: 'login' })
      } catch (error) {
        // If error occurs during fetch, redirect to login
        return next({ name: 'login' })
      }
    }
    
    // No token or user, redirect to login
    return next({ name: 'login' })
  }
  
  // If user is authenticated and tries to access login/register page,
  // redirect to dashboard instead
  if (isAuthenticated && (to.name === 'login' || to.name === 'register')) {
    return next({ name: 'dashboard' })
  }
  
  // Continue with the navigation
  next()
})

export default router
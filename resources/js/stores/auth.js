// resources/js/stores/auth.js
import { defineStore } from 'pinia'
import axios from 'axios'

/**
 * Authentication store using Pinia
 * Handles user authentication state and API token management
 */
export const useAuthStore = defineStore('auth', {
  // State properties
  state: () => ({
    // User information
    user: null,
    
    // Tenant information
    tenant: null,
    
    // Authentication token from Laravel Sanctum
    token: localStorage.getItem('token') || null,
    
    // Loading state
    loading: false
  }),
  
  // Getters (computed properties)
  getters: {
    // Check if user is authenticated
    isAuthenticated: (state) => !!state.token && !!state.user,
    
    // Get current tenant ID
    tenantId: (state) => state.tenant?.id,
    
    // Get tenant name
    tenantName: (state) => state.tenant?.data?.name || 'Unknown Tenant',
    
    // Get user roles
    userRoles: (state) => state.user?.roles || [],
    
    // Check if user has specific role
    hasRole: (state) => (role) => {
      return state.user?.roles?.some(r => r.slug === role)
    }
  },
  
  // Actions (methods that modify state)
  actions: {
    /**
     * Set the current authenticated user
     * @param {Object} userData - User data from API
     */
    setUser(userData) {
      this.user = userData
    },
    
    /**
     * Set the current tenant
     * @param {Object} tenantData - Tenant data from API
     */
    setTenant(tenantData) {
      this.tenant = tenantData
    },
    
    /**
     * Set the authentication token
     * @param {String} token - JWT token from Laravel Sanctum
     */
    setToken(token) {
      this.token = token
      
      // Store token in localStorage for persistence
      if (token) {
        localStorage.setItem('token', token)
        
        // Set the default Authorization header for all future requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      } else {
        localStorage.removeItem('token')
        delete axios.defaults.headers.common['Authorization']
      }
    },
    
    /**
     * Register a new user with a new tenant
     * @param {Object} registerData - Registration form data
     */
    async register(registerData) {
      this.loading = true
      
      try {
        const response = await axios.post('/api/auth/register', registerData)
        
        // Set user, tenant and token from response
        this.setUser(response.data.user)
        this.setTenant(response.data.tenant)
        this.setToken(response.data.token)
        
        return response.data
      } finally {
        this.loading = false
      }
    },
    
    /**
     * Login user with email and password
     * @param {Object} credentials - Login credentials
     */
    async login(credentials) {
      this.loading = true
      
      try {
        const response = await axios.post('/api/auth/login', credentials)
        
        // Set user, tenant and token from response
        this.setUser(response.data.user)
        this.setTenant(response.data.tenant)
        this.setToken(response.data.token)
        
        return response.data
      } finally {
        this.loading = false
      }
    },
    
    /**
     * Fetch current user information
     * Useful after page refresh to restore user state
     */
    async fetchCurrentUser() {
      // Skip if no token exists
      if (!this.token) return null
      
      this.loading = true
      
      try {
        // Set the authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        
        const response = await axios.get('/api/auth/user')
        
        // Update user and tenant data
        this.setUser(response.data.user)
        this.setTenant(response.data.user.tenant)
        
        return response.data.user
      } catch (error) {
        // If request fails (e.g., token expired), logout user
        if (error.response?.status === 401) {
          this.logout()
        }
        return null
      } finally {
        this.loading = false
      }
    },
    
    /**
     * Logout user
     */
    async logout() {
      // Skip if no token exists
      if (!this.token) return
      
      this.loading = true
      
      try {
        // Call logout API if we have a token
        if (this.token) {
          await axios.post('/api/auth/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Clear user data regardless of API call result
        this.setUser(null)
        this.setTenant(null)
        this.setToken(null)
        this.loading = false
      }
    }
  }
})
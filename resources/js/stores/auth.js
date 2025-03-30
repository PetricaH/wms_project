// resources/js/stores/auth.js

import { defineStore } from 'pinia';
import axios from 'axios';

/**
 * Auth Store - Manages authentication state throughout the application
 * 
 * This store handles:
 * - User login/logout
 * - User registration
 * - Token management
 * - Permission checking
 * - User profile data
 */
export const useAuthStore = defineStore('auth', {
  // State: reactive properties for authentication
  state: () => ({
    // User token for API authentication
    token: localStorage.getItem('token') || null,
    
    // User profile information
    user: JSON.parse(localStorage.getItem('user')) || null,
    
    // User roles and permissions
    roles: JSON.parse(localStorage.getItem('roles')) || [],
    permissions: JSON.parse(localStorage.getItem('permissions')) || [],
    
    // Loading and error states
    loading: false,
    error: null
  }),
  
  // Getters: computed properties for the store
  getters: {
    /**
     * Check if the user is authenticated
     * @returns {boolean} True if user has a valid token
     */
    isAuthenticated: (state) => !!state.token && !!state.user,
    
    /**
     * Get the current user's name or email
     * @returns {string} User's display name
     */
    userName: (state) => state.user ? (state.user.name || state.user.email) : 'Guest',
    
    /**
     * Get the current user's roles as a list of strings
     * @returns {Array<string>} List of role names
     */
    userRoles: (state) => state.roles.map(role => role.name),
    
    /**
     * Get the current user's permissions as a list of strings
     * @returns {Array<string>} List of permission slugs
     */
    userPermissions: (state) => state.permissions.map(permission => permission.slug),
    
    /**
     * Get the authentication header for API requests
     * @returns {Object} Headers object with Authorization token
     */
    authHeader: (state) => ({
      headers: {
        Authorization: `Bearer ${state.token}`
      }
    })
  },
  
  // Actions: methods that change state or have side effects
  actions: {
    /**
     * Set axios default authorization header with the current token
     * Called after successful login or on application initialization
     */
    setAxiosAuthHeader() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      } else {
        delete axios.defaults.headers.common['Authorization'];
      }
    },
    
    /**
     * Persist authentication data to localStorage
     * Called after state changes to keep data across page refreshes
     */
    persistAuthData() {
      if (this.token) {
        localStorage.setItem('token', this.token);
      } else {
        localStorage.removeItem('token');
      }
      
      if (this.user) {
        localStorage.setItem('user', JSON.stringify(this.user));
      } else {
        localStorage.removeItem('user');
      }
      
      localStorage.setItem('roles', JSON.stringify(this.roles));
      localStorage.setItem('permissions', JSON.stringify(this.permissions));
    },
    
    /**
     * Authenticate user with email and password
     * @param {Object} credentials - User login credentials
     * @param {string} credentials.email - User email
     * @param {string} credentials.password - User password
     * @returns {Promise<Object>} User data on success
     */
    async login(credentials) {
      this.loading = true;
      this.error = null;
      
      try {
        // Make API request to login endpoint
        const response = await axios.post('/api/auth/login', credentials);
        
        // Store authentication data
        this.token = response.data.token;
        this.user = response.data.user;
        this.roles = response.data.roles || [];
        this.permissions = response.data.permissions || [];
        
        // Setup axios and persist data
        this.setAxiosAuthHeader();
        this.persistAuthData();
        
        this.loading = false;
        return response.data;
      } catch (error) {
        this.loading = false;
        this.error = error.response?.data?.message || 'Authentication failed';
        throw error;
      }
    },
    
    /**
     * Register a new user
     * @param {Object} userData - User registration data
     * @returns {Promise<Object>} User data on success
     */
    async register(userData) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.post('/api/auth/register', userData);
        
        // Store authentication data if auto-login is enabled
        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          this.roles = response.data.roles || [];
          this.permissions = response.data.permissions || [];
          
          this.setAxiosAuthHeader();
          this.persistAuthData();
        }
        
        this.loading = false;
        return response.data;
      } catch (error) {
        this.loading = false;
        this.error = error.response?.data?.message || 'Registration failed';
        throw error;
      }
    },
    
    /**
     * Logout current user
     * @returns {Promise<void>}
     */
    async logout() {
      this.loading = true;
      
      try {
        // Only make API request if we have a token
        if (this.token) {
          await axios.post('/api/auth/logout', {}, this.authHeader);
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        // Clear authentication data regardless of API success
        this.token = null;
        this.user = null;
        this.roles = [];
        this.permissions = [];
        
        // Remove auth header and clear persisted data
        this.setAxiosAuthHeader();
        this.persistAuthData();
        
        this.loading = false;
      }
    },
    
    /**
     * Fetch current user profile data
     * @returns {Promise<Object>} User data on success
     */
    async fetchUserProfile() {
      if (!this.token) return null;
      
      this.loading = true;
      
      try {
        const response = await axios.get('/api/auth/me', this.authHeader);
        
        // Update user data
        this.user = response.data.user;
        this.roles = response.data.roles || [];
        this.permissions = response.data.permissions || [];
        
        this.persistAuthData();
        
        this.loading = false;
        return response.data;
      } catch (error) {
        // If unauthorized, clear auth data
        if (error.response?.status === 401) {
          this.logout();
        }
        
        this.loading = false;
        this.error = error.response?.data?.message || 'Failed to fetch user data';
        throw error;
      }
    },
    
    /**
     * Check if user has a specific permission
     * @param {string} permission - Permission slug to check
     * @returns {boolean} True if user has the permission
     */
    hasPermission(permission) {
      if (!this.isAuthenticated) return false;
      
      // Check direct permissions
      if (this.userPermissions.includes(permission)) return true;
      
      // Check permission wildcards (e.g., 'products.*' includes 'products.create')
      const wildcardPermissions = this.userPermissions.filter(p => p.endsWith('.*'));
      for (const wildcardPermission of wildcardPermissions) {
        const prefix = wildcardPermission.slice(0, -2); // Remove '.*'
        if (permission.startsWith(prefix)) return true;
      }
      
      return false;
    },
    
    /**
     * Check if user has a specific role
     * @param {string} role - Role name to check
     * @returns {boolean} True if user has the role
     */
    hasRole(role) {
      return this.userRoles.includes(role);
    }
  }
});

// Initialize axios auth header when the module is imported
// This ensures the auth header is set after page refresh
// const authStore = useAuthStore();
// authStore.setAxiosAuthHeader();
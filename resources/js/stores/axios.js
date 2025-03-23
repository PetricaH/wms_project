// resources/js/services/axios.js
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

/**
 * Setup Axios instance with interceptors for API calls
 * Handles authentication headers and tenant headers
 */
export function setupAxios() {
  // Set base URL for API requests
  axios.defaults.baseURL = '/api'
  
  // Request interceptor
  axios.interceptors.request.use(
    config => {
      const authStore = useAuthStore()
      
      // Add authentication token if it exists
      if (authStore.token) {
        config.headers['Authorization'] = `Bearer ${authStore.token}`
      }
      
      // Add tenant ID header if it exists
      if (authStore.tenantId) {
        config.headers['X-Tenant'] = authStore.tenantId
      }
      
      return config
    },
    error => {
      return Promise.reject(error)
    }
  )
  
  // Response interceptor
  axios.interceptors.response.use(
    response => {
      return response
    },
    error => {
      const authStore = useAuthStore()
      
      // Handle authentication errors
      if (error.response?.status === 401) {
        // Unauthorized - token expired or invalid
        authStore.logout()
        // Redirect to login page
        window.location.href = '/login'
      } else if (error.response?.status === 403) {
        // Forbidden - user doesn't have permission
        console.error('Permission denied')
        // You can handle this by showing a notification
      } else if (error.response?.status === 404 && error.response?.data?.error === 'Tenant not found') {
        // Tenant not found
        authStore.logout()
        window.location.href = '/login'
      }
      
      return Promise.reject(error)
    }
  )
}

/**
 * Create an API instance with JSON content type by default
 */
export const api = axios.create({
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Setup the same interceptors for the API instance
export function setupApiInterceptors() {
  // Re-use the same interceptor setup but for the api instance
  api.interceptors.request.use(
    config => {
      const authStore = useAuthStore()
      
      if (authStore.token) {
        config.headers['Authorization'] = `Bearer ${authStore.token}`
      }
      
      if (authStore.tenantId) {
        config.headers['X-Tenant'] = authStore.tenantId
      }
      
      return config
    },
    error => Promise.reject(error)
  )
  
  api.interceptors.response.use(
    response => response,
    error => {
      const authStore = useAuthStore()
      
      if (error.response?.status === 401) {
        authStore.logout()
        window.location.href = '/login'
      }
      
      return Promise.reject(error)
    }
  )
}

// Export default axios instance
export default axios
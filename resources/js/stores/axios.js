// resources/js/stores/axios.js

import axios from 'axios';

/**
 * Set up Axios interceptors for API request/response handling
 * 
 * This function configures Axios interceptors to:
 * 1. Add authentication tokens to outgoing requests
 * 2. Handle authentication errors (401) by redirecting to login
 * 3. Handle and format other API errors
 * 4. Refresh tokens when needed
 * 
 * Note: This function should be called after Pinia initialization
 */
export function setupAxios() {
  // Request interceptor - add auth token
  axios.interceptors.request.use(
    (config) => {
      // Get auth token from localStorage if it exists
      const token = localStorage.getItem('token');
      
      // Add token to request headers if available
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
      }
      
      return config;
    },
    (error) => {
      // Handle request errors
      console.error('API Request Error:', error);
      return Promise.reject(error);
    }
  );
  
  // Response interceptor - handle errors and token refresh
  axios.interceptors.response.use(
    (response) => {
      // Return successful responses as-is
      return response;
    },
    async (error) => {
      const originalRequest = error.config;
      
      // Handle authentication errors (401)
      if (error.response && error.response.status === 401 && !originalRequest._retry) {
        originalRequest._retry = true;
        
        // Clear token from localStorage
        localStorage.removeItem('token');
        
        // Redirect to login page
        window.location.href = '/';
        
        return Promise.reject(error);
      }
      
      // For other errors, just reject the promise
      // Alert handling can be done where the request is made
      return Promise.reject(error);
    }
  );

  // Configure default settings
  axios.defaults.baseURL = '/api';
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  axios.defaults.headers.post['Content-Type'] = 'application/json';
  
  // Initialize token from localStorage if present
  const token = localStorage.getItem('token');
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  }
}

// Alias for backward compatibility
export const setupApiInterceptors = setupAxios;

/**
 * Create an API request cancellation token
 * Useful for canceling pending requests when components unmount
 * 
 * @returns {Object} Cancellation source object with token and cancel method
 */
export function createCancelToken() {
  return axios.CancelToken.source();
}

export default axios;
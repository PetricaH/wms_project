// resources/js/stores/auth.js

import { defineStore } from 'pinia';
import axios from 'axios'; // Or your configured apiClient instance

/**
 * Auth Store - Manages authentication state throughout the application
 */
export const useAuthStore = defineStore('auth', {
  // State: reactive properties for authentication
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
    // --- ADD TENANT STATE ---
    tenant: JSON.parse(localStorage.getItem('tenant')) || null, // Load tenant from storage
    // --- END ADD ---
    roles: JSON.parse(localStorage.getItem('roles')) || [], // Consider deriving from user object
    permissions: JSON.parse(localStorage.getItem('permissions')) || [], // Consider deriving from user object
    loading: false,
    error: null
  }),

  // Getters: computed properties for the store
  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    userName: (state) => state.user ? (state.user.name || state.user.email) : 'Guest',
    // Getter for tenant name (matching Dashboard expectation)
    tenantName: (state) => state.tenant?.data?.name || 'Unknown Tenant', // Adjust if structure differs
    userRoles: (state) => state.user?.roles?.map(role => role.name) || state.roles.map(role => role.name) || [],
    userPermissions: (state) => {
        const permissionsFromRoles = state.user?.roles?.flatMap(role => role.permissions?.map(p => p.slug) || []) || [];
        const directPermissions = state.permissions.map(permission => permission.slug) || [];
        return [...new Set([...permissionsFromRoles, ...directPermissions])];
    },
    authHeader: (state) => ({
      headers: {
        Authorization: `Bearer ${state.token}`
      }
    })
  },

  // Actions: methods that change state or have side effects
  actions: {
    setAxiosAuthHeader() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      } else {
        delete axios.defaults.headers.common['Authorization'];
      }
    },

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
      // --- PERSIST TENANT ---
      if (this.tenant) {
        localStorage.setItem('tenant', JSON.stringify(this.tenant)); // Save tenant
      } else {
        localStorage.removeItem('tenant'); // Clear tenant
      }
      // --- END PERSIST ---

      // Persisting roles/permissions separately might be redundant if included in user object
      // localStorage.setItem('roles', JSON.stringify(this.roles));
      // localStorage.setItem('permissions', JSON.stringify(this.permissions));
    },

    async login(credentials) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/login', credentials);

        // --- STORE USER AND TENANT ---
        this.token = response.data.token;
        this.user = response.data.user;
        this.tenant = response.data.tenant; // <-- STORE THE TENANT OBJECT
        // --- END STORE ---

        // Reset roles/permissions if they are derived from user/tenant now
        // this.roles = [];
        // this.permissions = [];

        this.setAxiosAuthHeader();
        this.persistAuthData();
        this.loading = false;
        return response.data;
      } catch (error) {
        this.loading = false;
        this.error = error.response?.data?.message || error.response?.data?.error || 'Authentication failed';
        console.error("Login Error:", error.response || error);
        throw error; // Re-throw error so component can catch it
      }
    },

    async register(userData) {
        this.loading = true;
        this.error = null;
        try {
            const response = await axios.post('/register', userData);

            // Store auth data if registration includes auto-login
            if (response.data.token && response.data.user) {
                this.token = response.data.token;
                this.user = response.data.user;
                this.tenant = response.data.tenant; // <-- STORE TENANT ON REGISTER TOO
                // this.roles = []; // Reset if derived
                // this.permissions = []; // Reset if derived

                this.setAxiosAuthHeader();
                this.persistAuthData();
            }

            this.loading = false;
            return response.data;
        } catch (error) {
            this.loading = false;
            this.error = error.response?.data?.message || error.response?.data?.errors || 'Registration failed';
            console.error("Register Error:", error.response || error);
            throw error;
        }
    },


    async logout() {
      this.loading = true;
      try {
        if (this.token) {
          await axios.post('/auth/logout', {}, this.authHeader);
        }
      } catch (error) {
        console.error('Logout API error:', error.response || error);
      } finally {
        // --- CLEAR ALL AUTH STATE ---
        this.token = null;
        this.user = null;
        this.tenant = null; // <-- CLEAR TENANT
        this.roles = [];
        this.permissions = [];
        // --- END CLEAR ---

        this.setAxiosAuthHeader();
        this.persistAuthData(); // This will remove items from localStorage
        this.loading = false;

        // Optional: Redirect to login after logout
        // router.push('/login');
      }
    },

    async fetchUserProfile() {
      // This action might need adjustment if it should also fetch/update tenant info
      if (!this.token || this.loading) return null;

      this.loading = true;
      this.error = null;
      try {
        // Assuming /auth/user returns user and potentially updated tenant info
        const response = await axios.get('/auth/user');

        this.user = response.data.user;
        // --- UPDATE TENANT IF RETURNED ---
        if (response.data.tenant) {
             this.tenant = response.data.tenant;
        }
        // --- END UPDATE ---

        // Reset roles/permissions if derived
        // this.roles = [];
        // this.permissions = [];

        this.persistAuthData();
        this.loading = false;
        return response.data;
      } catch (error) {
         console.error("Fetch User Profile Error:", error.response || error);
         this.loading = false; // Ensure loading is false on error
        if (error.response?.status === 401) {
          console.log("fetchUserProfile: Received 401, logging out.");
          await this.logout(); // Ensure logout clears state
        } else {
            this.error = error.response?.data?.message || 'Failed to fetch user data';
            // Optionally re-throw non-401 errors if needed by caller
            // throw error;
        }
        return null; // Indicate failure
      }
    },

    // hasPermission and hasRole getters might be sufficient if roles/permissions are nested in user object
    // If not, ensure they use the separate this.roles/this.permissions state
    hasPermission(permission) {
      if (!this.isAuthenticated) return false;
      const userPerms = this.userPermissions; // Use the getter
      if (userPerms.includes(permission)) return true;
      const wildcardPermissions = userPerms.filter(p => p.endsWith('.*'));
      for (const wildcardPermission of wildcardPermissions) {
        const prefix = wildcardPermission.slice(0, -2);
        if (permission.startsWith(prefix + '.')) return true;
        if (permission === prefix) return true;
      }
      return false;
    },

    hasRole(role) {
       if (!this.isAuthenticated) return false;
      return this.userRoles.includes(role); // Use the getter
    }
  }
});

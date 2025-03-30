// resources/js/stores/alert.js

import { defineStore } from 'pinia';

/**
 * Alert Store - Manages global alert notifications
 * 
 * This store provides centralized management of alert messages
 * that can be shown throughout the application to provide
 * feedback to the user.
 */
export const useAlertStore = defineStore('alert', {
  // State: reactive properties for alerts
  state: () => ({
    alert: {
      show: false,
      type: 'info',
      message: '',
      timeout: 5000
    }
  }),
  
  // Actions: methods to modify alert state
  actions: {
    /**
     * Set an alert to be displayed
     * @param {Object} alertData - Alert configuration
     * @param {string} alertData.type - Alert type (success, error, warning, info)
     * @param {string} alertData.message - Alert message
     * @param {number} [alertData.timeout=5000] - Auto-dismiss timeout (0 for no auto-dismiss)
     */
    setAlert({ type = 'info', message, timeout = 5000 }) {
      // Validate the alert type
      const validTypes = ['success', 'error', 'warning', 'info'];
      
      if (!validTypes.includes(type)) {
        console.warn(`Invalid alert type: ${type}. Using 'info' instead.`);
        type = 'info';
      }
      
      // Set the alert data
      this.alert = {
        show: true,
        type,
        message,
        timeout
      };
      
      // Automatically clear the alert after timeout if specified
      if (timeout > 0) {
        setTimeout(() => {
          this.clearAlert();
        }, timeout);
      }
    },
    
    /**
     * Show a success alert
     * @param {string} message - Success message to display
     * @param {number} [timeout=5000] - Auto-dismiss timeout
     */
    setSuccessAlert(message, timeout = 5000) {
      this.setAlert({ type: 'success', message, timeout });
    },
    
    /**
     * Show an error alert
     * @param {string} message - Error message to display
     * @param {number} [timeout=5000] - Auto-dismiss timeout
     */
    setErrorAlert(message, timeout = 5000) {
      this.setAlert({ type: 'error', message, timeout });
    },
    
    /**
     * Show a warning alert
     * @param {string} message - Warning message to display
     * @param {number} [timeout=5000] - Auto-dismiss timeout
     */
    setWarningAlert(message, timeout = 5000) {
      this.setAlert({ type: 'warning', message, timeout });
    },
    
    /**
     * Show an info alert
     * @param {string} message - Info message to display
     * @param {number} [timeout=5000] - Auto-dismiss timeout
     */
    setInfoAlert(message, timeout = 5000) {
      this.setAlert({ type: 'info', message, timeout });
    },
    
    /**
     * Clear the current alert
     */
    clearAlert() {
      this.alert.show = false;
    },
    
    /**
     * Show an alert based on API error response
     * @param {Error} error - Axios error object
     * @param {string} [fallbackMessage='An error occurred'] - Fallback message if no error details available
     */
    setApiErrorAlert(error, fallbackMessage = 'An error occurred. Please try again.') {
      let message = fallbackMessage;
      
      // Extract error message from response if available
      if (error.response) {
        if (error.response.data && error.response.data.message) {
          message = error.response.data.message;
        } else if (error.response.data && error.response.data.error) {
          message = error.response.data.error;
        } else if (error.response.status === 404) {
          message = 'The requested resource was not found.';
        } else if (error.response.status === 403) {
          message = 'You do not have permission to perform this action.';
        } else if (error.response.status === 401) {
          message = 'You must be logged in to perform this action.';
        } else if (error.response.status === 422) {
          // Format validation errors
          if (error.response.data && error.response.data.errors) {
            const validationErrors = error.response.data.errors;
            message = '<strong>Validation errors:</strong><ul class="mt-1 ml-4 list-disc">';
            
            for (const field in validationErrors) {
              if (Object.prototype.hasOwnProperty.call(validationErrors, field)) {
                validationErrors[field].forEach(errorMessage => {
                  message += `<li>${errorMessage}</li>`;
                });
              }
            }
            
            message += '</ul>';
          }
        }
      } else if (error.message) {
        message = error.message;
      }
      
      this.setAlert({ type: 'error', message, timeout: 8000 }); // Longer timeout for errors
    }
  }
});
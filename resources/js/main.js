// resources/js/main.js

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import apiClient from './utils/axios';

// Import styles
import '../css/app.css';

// Configure default axios base URL
// This ensures all axios calls will have the proper base URL
axios.defaults.baseURL = '/api';
// Replace the global axios with our configured instance 
// for components that import axios directly
window.axios = apiClient;

// Create Pinia instance (state management)
const pinia = createPinia();

// Create and mount Vue application
const app = createApp(App);

// Register plugins
app.use(pinia);
app.use(router);

// Mount app
app.mount('#app');
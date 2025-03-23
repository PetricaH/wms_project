// resources/js/app.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { setupAxios, setupApiInterceptors } from './stores/axios';

// Create the Vue application instance
const app = createApp(App);

// Setup Pinia for state management
const pinia = createPinia();
app.use(pinia);

// Setup Axios with interceptors
setupAxios();
setupApiInterceptors();

// Add router
app.use(router);

// Mount the app
app.mount('#app');
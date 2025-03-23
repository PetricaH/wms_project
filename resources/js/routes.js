import Dashboard from '/resources/views/Dashboard.vue';
import Login from '/resources/views/auth/Login.vue';
import Register from '/resources/views/auth/Register.vue';

const routes = [
    { 
        path: '/', 
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    { 
        path: '/login', 
        component: Login 
    },
    { 
        path: '/register', 
        component: Register 
    },
];

export default routes;
<template>
    <component :is="currentLayout">
      <router-view />
    </component>
  </template>
  
  <script>
  import { computed } from 'vue'
  import { useRoute } from 'vue-router'
  
  // Import layouts
  import DefaultLayout from '/resources/js/components/DefaultLayout.vue'
  import AuthLayout from '/resources/js/components/AuthLayout.vue'
  import ErrorLayout from '/resources/js/components/ErrorLayout.vue'
  
  export default {
    name: 'App',
    
    components: {
      DefaultLayout,
      AuthLayout,
      ErrorLayout
    },
    
    setup() {
      const route = useRoute()
      
      // Determine which layout to use based on the route's meta.layout property
      const currentLayout = computed(() => {
        const layout = route.meta.layout || 'default'
        
        // Map layout name to component
        const layouts = {
          default: DefaultLayout,
          auth: AuthLayout,
          error: ErrorLayout
        }
        
        return layouts[layout] || DefaultLayout
      })
      
      return {
        currentLayout
      }
    }
  }
  </script>
<!-- resources/js/components/navigation/BreadcrumbNav.vue -->

<template>
    <nav class="flex" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <!-- Home/Dashboard Link -->
        <li class="inline-flex items-center">
          <router-link 
            to="/dashboard" 
            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
          >
            <span class="material-icons text-gray-400 mr-1 text-sm">home</span>
            Dashboard
          </router-link>
        </li>
        
        <!-- Dynamic breadcrumb items -->
        <li 
          v-for="(crumb, index) in breadcrumbs" 
          :key="index"
          class="inline-flex items-center"
        >
          <!-- Separator between items -->
          <span class="material-icons text-gray-400 mx-1 text-sm">chevron_right</span>
          
          <!-- Last item (current page) has different styling -->
          <span
            v-if="index === breadcrumbs.length - 1"
            class="text-sm font-medium text-gray-500"
            aria-current="page"
          >
            {{ crumb.name }}
          </span>
          
          <!-- Clickable breadcrumb -->
          <router-link
            v-else
            :to="crumb.path"
            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900"
          >
            {{ crumb.name }}
          </router-link>
        </li>
      </ol>
    </nav>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  import { useRoute } from 'vue-router';
  
  const route = useRoute();
  
  /**
   * Generate breadcrumbs based on the current route
   * This uses route metadata and naming conventions to build the breadcrumb trail
   */
  const breadcrumbs = computed(() => {
    // Start with empty breadcrumbs array
    const crumbs = [];
    
    // Get route matched objects which contain the route information
    const { matched } = route;
    
    // Special case to handle dynamic routes
    if (route.params.id) {
      // Get just the path segments
      const pathSegments = route.path.split('/');
      
      // We exclude the first empty segment and 'dashboard'
      // Starting from index 2 (after /dashboard/)
      let currentPath = '/dashboard';
      
      for (let i = 2; i < pathSegments.length - 1; i++) {
        if (pathSegments[i]) {
          currentPath += `/${pathSegments[i]}`;
          
          // Find route info for this segment
          const matchedRoute = matched.find(m => m.path === currentPath || 
            m.path === currentPath + '/' || 
            m.path.startsWith(currentPath + '/'));
          
          if (matchedRoute) {
            crumbs.push({
              name: formatRouteName(matchedRoute.name || pathSegments[i]),
              path: currentPath
            });
          }
        }
      }
      
      // Add the detail page with ID
      if (route.meta && route.meta.title) {
        crumbs.push({
          name: route.meta.title,
          path: route.fullPath
        });
      } else {
        // Fallback for detail page
        const resourceName = pathSegments[pathSegments.length - 2]; // e.g., 'products'
        crumbs.push({
          name: `${formatRouteName(resourceName).slice(0, -1)} Details`, // 'Product Details'
          path: route.fullPath
        });
      }
      
      return crumbs;
    }
    
    // Standard routes without params
    for (let i = 1; i < matched.length; i++) {
      const matchedRoute = matched[i];
      
      // Skip the default layout
      if (matchedRoute.path === '' || matchedRoute.path === '/') continue;
      
      // Use route metadata title if available, otherwise format the route name
      const name = matchedRoute.meta?.title || formatRouteName(matchedRoute.name);
      
      // Determine the path to this point
      const path = matched
        .slice(1, i + 1)
        .map(m => m.path)
        .join('')
        .replace('//', '/');
      
      crumbs.push({ name, path });
    }
    
    return crumbs;
  });
  
  /**
   * Format route name for display
   * Converts kebab-case or camelCase to Title Case with spaces
   * @param {string} name - Route name to format
   * @returns {string} Formatted name
   */
  function formatRouteName(name) {
    if (!name) return '';
    
    // Replace hyphens and underscores with spaces
    const spacedName = name.replace(/[-_]/g, ' ');
    
    // Insert space before capital letters for camelCase
    const withSpaces = spacedName.replace(/([A-Z])/g, ' $1');
    
    // Capitalize first letter of each word and trim extra spaces
    return withSpaces
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')
      .trim();
  }
  </script>
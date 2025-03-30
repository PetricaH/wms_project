<!-- resources/js/components/navigation/SidebarNavItem.vue -->

<template>
    <!-- Only render navigation item if it's visible based on permissions -->
    <div v-if="item.visible" class="mb-1">
      <!-- Parent menu item with dropdown (if it has children) -->
      <div
        v-if="hasChildren"
        @click="toggleExpanded"
        class="flex items-center justify-between px-4 py-3 text-sm rounded-md cursor-pointer"
        :class="[isActive ? 'bg-gray-700' : 'hover:bg-gray-700']"
      >
        <div class="flex items-center">
          <!-- Icon (if available) -->
          <span v-if="item.icon" class="material-icons text-lg mr-3">{{ item.icon }}</span>
          {{ item.name }}
        </div>
        
        <!-- Dropdown arrow indicator -->
        <span class="material-icons text-sm transition-transform duration-200" :class="{ 'rotate-180': expanded }">
          expand_more
        </span>
      </div>
      
      <!-- Single navigation item (no children) -->
      <router-link
        v-else
        :to="item.route"
        class="flex items-center px-4 py-3 text-sm rounded-md"
        :class="[isActive ? 'bg-gray-700' : 'hover:bg-gray-700']"
        @click="$emit('click')"
      >
        <!-- Icon (if available) -->
        <span v-if="item.icon" class="material-icons text-lg mr-3">{{ item.icon }}</span>
        {{ item.name }}
      </router-link>
      
      <!-- Dropdown menu for children items -->
      <div
        v-if="hasChildren"
        class="mt-1 pl-4 overflow-hidden transition-all duration-300"
        :class="{ 'max-h-0': !expanded, 'max-h-96': expanded }"
      >
        <!-- Recursively render child items -->
        <div
          v-for="child in visibleChildren"
          :key="child.name"
          class="mb-1"
        >
          <router-link
            :to="child.route"
            class="flex items-center px-4 py-2 text-sm rounded-md"
            :class="[
              isChildActive(child) ? 'bg-gray-700' : 'hover:bg-gray-700',
              !expanded ? 'pointer-events-none' : ''
            ]"
            @click="$emit('click')"
          >
            {{ child.name }}
          </router-link>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue';
  import { useRoute } from 'vue-router';
  
  // Props definition
  const props = defineProps({
    /**
     * Navigation item object
     * @property {string} name - Display name
     * @property {string} [icon] - Optional material icon name
     * @property {Object} [route] - Vue Router route object
     * @property {boolean} visible - Whether the item should be shown
     * @property {Array} [children] - Child navigation items
     */
    item: {
      type: Object,
      required: true
    }
  });
  
  // Emit click event to parent
  const emit = defineEmits(['click']);
  
  const route = useRoute();
  const expanded = ref(false);
  
  /**
   * Toggle dropdown expanded state
   */
  function toggleExpanded() {
    expanded.value = !expanded.value;
    
    // If expanding and any child is active, emit click
    if (expanded.value && visibleChildren.value.some(child => isChildActive(child))) {
      emit('click');
    }
  }
  
  /**
   * Check if the navigation item has children
   */
  const hasChildren = computed(() => {
    return props.item.children && props.item.children.length > 0;
  });
  
  /**
   * Filter children that are visible based on permissions
   */
  const visibleChildren = computed(() => {
    if (!hasChildren.value) return [];
    return props.item.children.filter(child => child.visible);
  });
  
  /**
   * Check if the current item is active
   * For parent items, it's active if any child is active
   */
  const isActive = computed(() => {
    if (props.item.route) {
      // If it has a direct route, check if that route is active
      return route.name === props.item.route.name;
    } else if (hasChildren.value) {
      // If it has children, it's active if any child is active
      return visibleChildren.value.some(child => isChildActive(child));
    }
    return false;
  });
  
  /**
   * Check if a child item is active
   * @param {Object} child - Child navigation item
   * @returns {boolean} True if the child is active
   */
  function isChildActive(child) {
    return route.name === child.route.name;
  }
  
  // Auto-expand dropdown if a child is active
  if (hasChildren.value && isActive.value) {
    expanded.value = true;
  }
  </script>
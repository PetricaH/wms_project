<!-- resources/js/components/dashboard/MetricCard.vue -->

<template>
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-5">
        <!-- Card header with title and icon -->
        <div class="flex items-center justify-between mb-3">
          <!-- Metric title -->
          <h3 class="text-gray-500 text-sm font-medium">{{ title }}</h3>
          
          <!-- Icon with background based on color prop -->
          <div 
            class="w-8 h-8 rounded-full flex items-center justify-center" 
            :class="`bg-${color}-100 text-${color}-600`"
          >
            <span class="material-icons text-lg">{{ icon }}</span>
          </div>
        </div>
        
        <!-- Metric value -->
        <div class="flex items-baseline">
          <p 
            class="text-2xl font-semibold" 
            :class="{ 'text-red-600': isNegative && value > 0 }"
          >
            {{ formattedValue }}
          </p>
        </div>
        
        <!-- Trend indicator -->
        <div class="flex items-center mt-2" v-if="trendDirection !== 'none'">
          <!-- Trend arrow icon -->
          <span 
            class="material-icons text-sm mr-1" 
            :class="trendClass"
          >{{ trendIcon }}</span>
          
          <!-- Trend percentage -->
          <p class="text-xs font-medium" :class="trendClass">
            {{ Math.abs(trendValue) }}%
          </p>
          
          <!-- Trend description -->
          <p class="text-xs text-gray-500 ml-2">{{ trendLabel }}</p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue';
  
  /**
   * Props definition for the metric card component
   */
  const props = defineProps({
    // Title displayed in the card
    title: {
      type: String,
      required: true
    },
    
    // Numeric value to display
    value: {
      type: [Number, String],
      required: true
    },
    
    // Icon to display (material icon name)
    icon: {
      type: String,
      required: true
    },
    
    // Color theme for the card (will be used in Tailwind classes)
    color: {
      type: String,
      default: 'blue',
      validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple', 'gray', 'indigo'].includes(value)
    },
    
    // Prefix to add before the value (e.g., $ for currency)
    prefix: {
      type: String,
      default: ''
    },
    
    // Suffix to add after the value (e.g., % for percentage)
    suffix: {
      type: String,
      default: ''
    },
    
    // Trend direction - whether value is increasing, decreasing, or not changing
    trendDirection: {
      type: String,
      default: 'none',
      validator: (value) => ['up', 'down', 'none'].includes(value)
    },
    
    // Trend value (percentage change)
    trendValue: {
      type: Number,
      default: 0
    },
    
    // Label describing the trend (e.g., "vs. last month")
    trendLabel: {
      type: String,
      default: ''
    },
    
    // Whether higher values are negative (e.g., for "errors" or "complaints")
    isNegative: {
      type: Boolean,
      default: false
    }
  });
  
  /**
   * Format the value with prefix and suffix
   */
  const formattedValue = computed(() => {
    let formatted = props.value;
    
    // If value is a number, format it
    if (!isNaN(props.value)) {
      // Add thousands separators
      formatted = new Intl.NumberFormat().format(props.value);
    }
    
    // Add prefix and suffix
    return `${props.prefix}${formatted}${props.suffix}`;
  });
  
  /**
   * Get the trend icon based on direction
   */
  const trendIcon = computed(() => {
    switch (props.trendDirection) {
      case 'up':
        return 'arrow_upward';
      case 'down':
        return 'arrow_downward';
      default:
        return '';
    }
  });
  
  /**
   * Determine trend class based on direction and whether higher is better or worse
   */
  const trendClass = computed(() => {
    // For standard metrics (higher is better)
    if (!props.isNegative) {
      return props.trendDirection === 'up' 
        ? 'text-green-500' 
        : props.trendDirection === 'down' 
          ? 'text-red-500' 
          : 'text-gray-500';
    } 
    // For negative metrics (lower is better)
    else {
      return props.trendDirection === 'down' 
        ? 'text-green-500' 
        : props.trendDirection === 'up' 
          ? 'text-red-500' 
          : 'text-gray-500';
    }
  });
  </script>
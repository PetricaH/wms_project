<!-- resources/js/components/dashboard/OrdersChart.vue -->

<template>
    <div class="h-full">
      <!-- No data message -->
      <div v-if="!data || data.length === 0" class="h-full flex items-center justify-center">
        <p class="text-gray-500">No order data available</p>
      </div>
      
      <!-- The chart container -->
      <div v-else ref="chartContainer" class="h-full w-full"></div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue';
  import * as d3 from 'd3';
  
  // Props definition for the chart
  const props = defineProps({
    /**
     * Array of order data objects with the following structure:
     * [
     *   { date: '2024-03-24', created: 12, shipped: 10, cancelled: 1 },
     *   { date: '2024-03-25', created: 15, shipped: 9, cancelled: 2 },
     *   ...
     * ]
     */
    data: {
      type: Array,
      required: true
    }
  });
  
  // Reference to the chart container element
  const chartContainer = ref(null);
  
  /**
   * Initialize the chart when mounted and when data changes
   */
  function renderChart() {
    if (!chartContainer.value || !props.data || props.data.length === 0) return;
    
    // Clear any existing chart
    d3.select(chartContainer.value).select('svg').remove();
    
    // Set up chart dimensions
    const margin = { top: 20, right: 30, bottom: 30, left: 40 };
    const width = chartContainer.value.clientWidth - margin.left - margin.right;
    const height = chartContainer.value.clientHeight - margin.top - margin.bottom;
    
    // Create SVG element
    const svg = d3.select(chartContainer.value)
      .append('svg')
      .attr('width', width + margin.left + margin.right)
      .attr('height', height + margin.top + margin.bottom)
      .append('g')
      .attr('transform', `translate(${margin.left},${margin.top})`);
    
    // Parse dates and create date array
    const dates = props.data.map(d => new Date(d.date));
    
    // Create X scale
    const x = d3.scaleTime()
      .domain(d3.extent(dates))
      .range([0, width]);
    
    // X axis
    svg.append('g')
      .attr('transform', `translate(0,${height})`)
      .call(d3.axisBottom(x).ticks(Math.min(dates.length, 7)).tickFormat(d3.timeFormat('%b %d')));
    
    // Find the maximum value for the Y axis
    const maxValue = d3.max(props.data, d => Math.max(d.created, d.shipped, d.cancelled));
    
    // Create Y scale
    const y = d3.scaleLinear()
      .domain([0, maxValue * 1.1]) // Add 10% headroom
      .range([height, 0]);
    
    // Y axis
    svg.append('g')
      .call(d3.axisLeft(y).ticks(5));
    
    // Line generators for each data series
    const createLine = (accessor) => {
      return d3.line()
        .x(d => x(new Date(d.date)))
        .y(d => y(accessor(d)))
        .curve(d3.curveMonotoneX); // Smoother curve
    };
    
    // Draw created orders line
    svg.append('path')
      .datum(props.data)
      .attr('fill', 'none')
      .attr('stroke', '#3b82f6') // blue-500
      .attr('stroke-width', 2)
      .attr('d', createLine(d => d.created));
    
    // Draw shipped orders line
    svg.append('path')
      .datum(props.data)
      .attr('fill', 'none')
      .attr('stroke', '#10b981') // green-500
      .attr('stroke-width', 2)
      .attr('d', createLine(d => d.shipped));
    
    // Draw cancelled orders line
    svg.append('path')
      .datum(props.data)
      .attr('fill', 'none')
      .attr('stroke', '#ef4444') // red-500
      .attr('stroke-width', 2)
      .attr('d', createLine(d => d.cancelled));
    
    // Add data points for created orders
    svg.selectAll('.dot-created')
      .data(props.data)
      .enter()
      .append('circle')
      .attr('class', 'dot-created')
      .attr('cx', d => x(new Date(d.date)))
      .attr('cy', d => y(d.created))
      .attr('r', 4)
      .attr('fill', '#3b82f6'); // blue-500
    
    // Add data points for shipped orders
    svg.selectAll('.dot-shipped')
      .data(props.data)
      .enter()
      .append('circle')
      .attr('class', 'dot-shipped')
      .attr('cx', d => x(new Date(d.date)))
      .attr('cy', d => y(d.shipped))
      .attr('r', 4)
      .attr('fill', '#10b981'); // green-500
    
    // Add data points for cancelled orders
    svg.selectAll('.dot-cancelled')
      .data(props.data)
      .enter()
      .append('circle')
      .attr('class', 'dot-cancelled')
      .attr('cx', d => x(new Date(d.date)))
      .attr('cy', d => y(d.cancelled))
      .attr('r', 4)
      .attr('fill', '#ef4444'); // red-500
    
    // Add legend
    const legend = svg.append('g')
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10)
      .attr('text-anchor', 'end')
      .selectAll('g')
      .data([
        { label: 'Created', color: '#3b82f6' },
        { label: 'Shipped', color: '#10b981' },
        { label: 'Cancelled', color: '#ef4444' }
      ])
      .enter().append('g')
      .attr('transform', (d, i) => `translate(${width},${i * 20})`);
    
    // Legend colored rectangles
    legend.append('rect')
      .attr('x', -18)
      .attr('width', 18)
      .attr('height', 18)
      .attr('fill', d => d.color);
    
    // Legend text
    legend.append('text')
      .attr('x', -24)
      .attr('y', 9)
      .attr('dy', '0.35em')
      .text(d => d.label);
  }
  
  // Initialize chart when mounted
  onMounted(() => {
    if (props.data && props.data.length > 0) {
      renderChart();
      
      // Handle window resize
      const handleResize = () => {
        renderChart();
      };
      
      window.addEventListener('resize', handleResize);
      
      // Clean up event listener
      return () => {
        window.removeEventListener('resize', handleResize);
      };
    }
  });
  
  // Re-render chart when data changes
  watch(() => props.data, () => {
    renderChart();
  }, { deep: true });
  </script>
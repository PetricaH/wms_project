<!-- resources/js/components/dashboard/InventoryChart.vue -->

<template>
    <div class="h-full">
      <!-- No data message -->
      <div v-if="!data || data.length === 0" class="h-full flex items-center justify-center">
        <p class="text-gray-500">No inventory data available</p>
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
     * Array of inventory data objects with the following structure:
     * [
     *   { date: '2024-03-24', incoming: 45, outgoing: 37 },
     *   { date: '2024-03-25', incoming: 30, outgoing: 42 },
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
    
    // Parse dates
    const dates = props.data.map(d => new Date(d.date));
    
    // Create X scale for dates
    const x = d3.scaleBand()
      .domain(dates.map(d => d.toISOString().split('T')[0])) // Use date string for band scale
      .range([0, width])
      .padding(0.3);
    
    // X axis
    svg.append('g')
      .attr('transform', `translate(0,${height})`)
      .call(d3.axisBottom(x).tickFormat(d => {
        const date = new Date(d);
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
      }))
      .selectAll("text")
        .attr("transform", "rotate(-45)")
        .style("text-anchor", "end")
        .attr("dx", "-.8em")
        .attr("dy", ".15em");
    
    // Find the maximum value for the Y axis
    const maxValue = d3.max(props.data, d => Math.max(d.incoming, d.outgoing));
    
    // Create Y scale
    const y = d3.scaleLinear()
      .domain([0, maxValue * 1.1]) // Add 10% headroom
      .range([height, 0]);
    
    // Y axis
    svg.append('g')
      .call(d3.axisLeft(y).ticks(5));
    
    // Add bars for incoming inventory
    svg.selectAll('.bar-incoming')
      .data(props.data)
      .enter()
      .append('rect')
      .attr('class', 'bar-incoming')
      .attr('x', d => x(new Date(d.date).toISOString().split('T')[0]))
      .attr('width', x.bandwidth() / 2)
      .attr('y', d => y(d.incoming))
      .attr('height', d => height - y(d.incoming))
      .attr('fill', '#3b82f6') // blue-500 for incoming
      .attr('rx', 2) // rounded corners
      .attr('ry', 2)
      .append('title') // Tooltip on hover
      .text(d => `Incoming: ${d.incoming}`);
    
    // Add bars for outgoing inventory
    svg.selectAll('.bar-outgoing')
      .data(props.data)
      .enter()
      .append('rect')
      .attr('class', 'bar-outgoing')
      .attr('x', d => x(new Date(d.date).toISOString().split('T')[0]) + x.bandwidth() / 2)
      .attr('width', x.bandwidth() / 2)
      .attr('y', d => y(d.outgoing))
      .attr('height', d => height - y(d.outgoing))
      .attr('fill', '#ef4444') // red-500 for outgoing
      .attr('rx', 2) // rounded corners
      .attr('ry', 2)
      .append('title') // Tooltip on hover
      .text(d => `Outgoing: ${d.outgoing}`);
    
    // Add value labels on top of bars (if there's enough space)
    if (height > 150) {
      // Incoming labels
      svg.selectAll('.label-incoming')
        .data(props.data)
        .enter()
        .append('text')
        .attr('class', 'label-incoming')
        .attr('x', d => x(new Date(d.date).toISOString().split('T')[0]) + x.bandwidth() / 4)
        .attr('y', d => y(d.incoming) - 5)
        .attr('text-anchor', 'middle')
        .attr('font-size', '10px')
        .attr('fill', '#3b82f6')
        .text(d => d.incoming);
      
      // Outgoing labels
      svg.selectAll('.label-outgoing')
        .data(props.data)
        .enter()
        .append('text')
        .attr('class', 'label-outgoing')
        .attr('x', d => x(new Date(d.date).toISOString().split('T')[0]) + x.bandwidth() * 3/4)
        .attr('y', d => y(d.outgoing) - 5)
        .attr('text-anchor', 'middle')
        .attr('font-size', '10px')
        .attr('fill', '#ef4444')
        .text(d => d.outgoing);
    }
    
    // Add legend
    const legend = svg.append('g')
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10)
      .attr('text-anchor', 'end')
      .selectAll('g')
      .data([
        { label: 'Incoming', color: '#3b82f6' },
        { label: 'Outgoing', color: '#ef4444' }
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
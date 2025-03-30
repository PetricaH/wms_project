<template>
    <div class="not-found-view">
      <h1>404 - Page Not Found</h1>
      <p>The page you're looking for doesn't exist.</p>
      <router-link to="/" class="home-link">Return to Home</router-link>
    </div>
  </template>
  
  <script>
  export default {
    name: 'NotFoundView',
    // You can add component logic here if needed
  }
  </script>
  
  <style scoped>
  .not-found-view {
    text-align: center;
    padding: 2rem;
    margin-top: 3rem;
  }
  
  h1 {
    color: #dc3545;
    font-size: 2.5rem;
    margin-bottom: 1rem;
  }
  
  p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
  }
  
  .home-link {
    display: inline-block;
    padding: 0.5rem 1rem;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s;
  }
  
  .home-link:hover {
    background-color: #0056b3;
  }
  </style>
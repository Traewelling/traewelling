<template>
  <div>
    <h1>Vue Router Demo App</h1>

    <p>
      <router-link :to="{ name: 'welcome' }">Home</router-link> |
      <router-link :to="{ name: 'statuses.active' }">Hello World</router-link>
    </p>

    <div class="container">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
export default {}
</script>
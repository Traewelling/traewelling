<template>
  <div class="statuses">
    <div class="loading" v-if="loading">
      Loading...
    </div>

    <div v-if="error" class="error">
      <p>{{ error }}</p>

      <p>
        <button @click.prevent="fetchData">
          Try Again
        </button>
      </p>
    </div>

    <ul v-if="statuses">
      <li v-for="status in statuses">
        <strong>ID:</strong> {{ status.id }},
        <strong>type:</strong> {{ type }}
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      loading: false,
      statuses: null,
      error: null,
    };
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.error = this.statuses = null;
      this.loading = true;
      axios
          .get('/api/statuses')
          .then(response => {
            this.loading = false;
            this.statuses = response.data.statuses;
          })
          .catch(error => {
            this.loading = false;
            this.error = error.response.data.message || error.message;
          });
    }
  }
}
</script>

<style scoped>

</style>
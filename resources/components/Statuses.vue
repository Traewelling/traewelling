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

    <div v-if="statuses">
      <Status v-for="status in statuses" :status="status"></Status>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Status from '../components/Status'

export default {
  data() {
    return {
      loading: false,
      statuses: null,
      error: null,
    };
  },
  components: {
    Status
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.error   = this.statuses = null;
      this.loading = true;
      axios
          .get('/api/v1/statuses')
          .then(response => {
            this.loading  = false;
            this.statuses = response.data.data;
          })
          .catch(error => {
            this.loading = false;
            this.error   = error.response.data.message || error.message;
          });
    }
  }
}
</script>

<style scoped>

</style>
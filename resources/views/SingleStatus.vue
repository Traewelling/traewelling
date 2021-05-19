<template>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
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

      <div v-if="status">
        <Status :status="status"></Status>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import axios from "axios";
import Status from "../components/Status";

export default {
  name: "SingleStatus",
  data() {
    return {
      error: false,
      loading: false,
      status: null
    }
  },
  created() {
    this.fetchData();
  },
  components: {
    Status
  },
  methods: {
    fetchData() {
      this.error   = null;
      this.loading = true;
      axios
          .get('/api/v1/statuses/' + this.$route.params.id)
          .then(response => {
            this.loading    = false;
            this.status = response.data.data;
            console.log(this.status);
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
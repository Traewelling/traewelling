<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
        <div class="loading" v-if="loading">
          __('vue.loading')
        </div>

        <div v-if="error" class="error">
          <p>{{ error }}</p>

          <p>
            <button @click.prevent="fetchData">
              __('vue.tryAgain')
            </button>
          </p>
        </div>

        <div v-if="statuses">
          <Status v-for="status in statuses" :status="status" v-bind:stopovers="stopovers"></Status>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Status from "../components/Status";

export default {
  data() {
    return {
      loading: false,
      statuses: null,
      error: null,
      stopovers: null
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
          .then((response) => {
            this.loading  = false;
            this.statuses = response.data.data;
            this.fetchStopovers();
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.response.data.message || error.message;
          });
    },
    fetchStopovers() {
      let tripIds = "";
      this.statuses.forEach((status) => {
        tripIds += (status.train.trip + ",");
      });
      axios
          .get("/api/v1/stopovers/" + tripIds)
          .then((response) => {
            this.stopovers = response.data.data;
          })
          .catch((error) => {
            console.error(error);
          })
    }
  },
}
</script>

<style scoped>

</style>

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
          <h5>{{ moment(status.train.origin.departure).format("dddd[,] LL") }}</h5>
          <Status :status="status" :polyline="polyline"></Status>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Status from "../components/Status";
import moment from "moment";

export default {
  name: "SingleStatus",
  data() {
    return {
      error: false,
      loading: false,
      status: null,
      polyline: null,
      moment: moment
    };
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
      this.fetchPolyline();
      axios
          .get("/api/v1/statuses/" + this.$route.params.id)
          .then((response) => {
            this.loading = false;
            this.status  = response.data.data;
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.response.data.message || error.message;
          });
    },
    fetchPolyline() {
      axios
          .get("/api/v1/polyline/" + this.$route.params.id)
          .then((response) => {
            this.polyline = [response.data.data[0].coordinatesArray];
          })
          .catch((error) => {
            console.error(error);
          })
    }
  }
}
</script>

<style scoped>

</style>
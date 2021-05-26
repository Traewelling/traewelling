<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
        <h4> __('menu.active') </h4>
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

        <div class="card-img-top" v-if="polylines">
          <Map class="map embed-responsive embed-responsive-1by1" :poly-lines="polylines"></Map>
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
import Map from "../components/Map";
import moment from "moment";

export default {
  data() {
    return {
      loading: true,
      statuses: null,
      error: null,
      stopovers: null,
      polylines: null
    };
  },
  components: {
    Status,
    Map
  },
  created() {
    this.fetchData();
    this.startRefresh();
  },
  methods: {
    fetchData() {
      const oldStatuses = this.statuses;
      this.error   = this.statuses = null;
      axios
          .get('/api/v1/statuses')
          .then((response) => {
            this.loading  = false;
            if(oldStatuses != response.data.data) {
              this.statuses = response.data.data;
              this.fetchPolyline();
              this.fetchStopovers();
            }
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
    },
    fetchPolyline() {
      let tripIds = "";
      this.statuses.forEach((status) => {
        tripIds += (status.id + ",");
      });
      axios
          .get("/api/v1/polyline/" + tripIds)
          .then((response) => {
            this.polylines = [response.data.data];
          })
          .catch((error) => {
            console.error(error);
          })
    },
    startRefresh() {
      setInterval(() => (this.fetchData()), 70000);
    }
  },
}
</script>

<style scoped>

</style>

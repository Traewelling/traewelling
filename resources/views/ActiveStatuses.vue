<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
      <div class="card sticky-top">
        <Map class="map embed-responsive embed-responsive-1by1" :poly-lines="polylines"></Map>
      </div>
    </div>
      <div class="col-md-8 col-lg-6">
        <div class="loading" v-if="loading">
           {{ i18n.get("_.vue.loading") }}
        </div>

        <div v-if="error" class="error">
          <p>{{ error }}</p>

          <p>
            <button @click.prevent="fetchData">
              {{ i18n.get("_.vue.tryAgain") }}
            </button>
          </p>
        </div>
        <div v-if="statuses">
          <h4 class="mt-4"> {{ i18n.get("_.menu.active") }} </h4>
          <Status v-for="status in statuses" :status="status" v-bind:stopovers="stopovers" v-bind:key="status.id"></Status>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Status from "../components/Status";
import Map from "../components/Map";
import {StatusModel} from "../js/APImodels";

export default {
  data() {
    return {
      loading: true,
      error: null,
      interval: null,
      statuses: [StatusModel],
      stopovers: null, //ToDo Typedef
      polylines: null //ToDo Typedef
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
          .get("/statuses")
          .then((response) => {
            this.loading  = false;
            // FixMe: Why is this comparison not working correctly?
            if(oldStatuses != response.data.data) {
              this.statuses = response.data.data;
              this.fetchPolyline();
              this.fetchStopovers();
            }
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.data.message || error.message;
          });
    },
    fetchStopovers() {
      let tripIds = "";
      this.statuses.forEach((status) => {
        tripIds += (status.train.trip + ",");
      });
      axios
          .get("/stopovers/" + tripIds)
          .then((response) => {
            this.stopovers = response.data.data;
          })
          .catch((error) => {
            console.error(error);
          });
    },
    fetchPolyline() {
      let tripIds = "";
      this.statuses.forEach((status) => {
        tripIds += (status.id + ",");
      });
      axios
          .get("/polyline/" + tripIds)
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
};
</script>

<style scoped>

</style>

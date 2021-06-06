<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
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

        <div v-if="status">
          <h5>{{ moment(status.train.origin.departure).format("dddd[,] LL") }}</h5>
          <Status :status="status" :polyline="polyline" :stopovers="stopovers"></Status>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Status from "../components/Status";
import moment from "moment";
import {StatusModel} from "../js/APImodels";

export default {
  name: "SingleStatus",
  data() {
    return {
      error: false,
      loading: false,
      status: StatusModel,
      polyline: null, //ToDo Typedef
      stopovers: null, //ToDo Typedef
      moment: moment
    };
  },
  created() {
    if (this.statusData == null) {
      this.fetchData();
    } else {
      this.status = this.statusData;
      this.fetchPolyline();
    }
  },
  components: {
    Status
  },
  props: {
    statusData: null
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
            this.fetchStopovers();
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
            this.polyline = [response.data.data];
          })
          .catch((error) => {
            console.error(error);
          })
    },
    fetchStopovers() {
      axios
          .get("/api/v1/stopovers/" + this.status.train.trip)
          .then((response) => {
            this.stopovers = response.data.data;
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
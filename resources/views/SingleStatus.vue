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
          <Status :status="status" :polyline="polyline" :stopovers="stopovers" :likes="likes" show-date="true"></Status>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Status from "../components/Status";
import moment from "moment";
import {ProfileModel, StatusModel} from "../js/APImodels";

export default {
  name: "SingleStatus",
  data() {
    return {
      error: false,
      loading: false,
      status: StatusModel,
      polyline: null, //ToDo Typedef
      stopovers: null, //ToDo Typedef
      likes: null,
      moment: moment
    };
  },
  created() {
    if (this.statusData == null) {
      this.fetchData();
    } else {
      this.status = this.statusData;
      this.fetchPolyline();
      this.fetchLikes();
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
      axios
          .get("/statuses/" + this.$route.params.id)
          .then((response) => {
            this.loading = false;
            this.status  = response.data.data;
            this.fetchPolyline();
            this.fetchStopovers();
            this.fetchLikes();
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.data.message || error.message;
          });
    },
    fetchPolyline() {
      axios
          .get("/polyline/" + this.status.id)
          .then((response) => {
            this.polyline = [response.data.data];
          })
          .catch((error) => {
            console.error(error);
          })
    },
    fetchStopovers() {
      axios
          .get("/stopovers/" + this.status.train.trip)
          .then((response) => {
            this.stopovers = response.data.data;
          })
          .catch((error) => {
            console.error(error);
          })
    },
    fetchLikes() {
      axios
          .get("/statuses/" + this.status.id + "/likedby")
          .then((response) => {
            this.likes = response.data.data;
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
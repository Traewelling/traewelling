<template>
  <div class="container">
    <div class="row justify-content-center">
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
          <Status v-for="status in statuses" :status="status" v-bind:stopovers="stopovers"
                  v-bind:key="status.id"></Status>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Status from "../components/Status";
import {StatusModel} from "../js/APImodels";

export default {
  data() {
    return {
      loading: true,
      error: null,
      statuses: [StatusModel],
      stopovers: null, //ToDo Typedef
    };
  },
  components: {
    Status
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.error = this.statuses = null;
      axios
          .get(this.$route.path)
          .then((response) => {
            this.loading = false;
            if (!Object.keys(response.data.data).length && this.$route.path === "/dashboard") {
              this.$router.push({name: "dashboard.global"}); //ToDo: Redirect if following nobody
              this.fetchData();
            }
            this.statuses = response.data.data;
            this.fetchStopovers();
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
            this.loading = false;
          });
    },
  },
};
</script>

<style scoped>

</style>

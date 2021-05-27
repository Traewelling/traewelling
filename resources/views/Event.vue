<template>
  <div>
    <div class="px-4 py-5 mt-n4"
         style="background-image: url('/images/covers/profile-background.png');background-position: center;background-color: #c5232c">
      <div class="container" id="event-header">
        <div class="row justify-content-center">
          <div class="text-white col-md-8 col-lg-7">
            <h1 class="card-title font-bold">
              <strong> {{ event.name }}
                <code class="text-white">#{{ event.hashtag }}</code>
              </strong>
            </h1>
            <h2>
              <span class="font-weight-bold">
                <i class="fa fa-route d-inline" aria-hidden="true"/>&nbsp;{{ event.trainDistance.toFixed(0) }}
              </span>
              <span class="small font-weight-lighter">km</span>
              <span class="font-weight-bold ps-sm-2">
                <i class="fa fa-stopwatch d-inline" aria-hidden="true"/>&nbsp;{{ duration }}
              </span>
              <br class="d-block d-sm-none">
              <span class="font-weight-bold ps-sm-2">
                <i class="fa fa-user" aria-hidden="true"/>&nbsp;{{ event.host }}
              </span>
              <span class="font-weight-bold ps-sm-2">
                <i class="fa fa-link" aria-hidden="true"/>&nbsp;<a :href="event.url"
                                                                   class="text-white">{{ event.url }}</a>
              </span>
            </h2>
            <h2>
              <span class="font-weight-bold"><i class="fa fa-train" aria-hidden="true"/></span>
              <span class="font-weight-bold"><a class="text-white" href="asdf">{{ event.station.name }}</a></span>
            </h2>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
      <div v-if="loading || statusesLoading">
         {{ i18n.get("_.vue.loading") }}
      </div>

      <div v-if="!statusesLoading && !loading" class="row justify-content-center mt-5">
        <div v-if="statuses.length > 0" class="col-md-8 col-lg-7">

          <div v-if="statuses">
            <Status v-for="status in statuses" :status="status"></Status>
          </div>
          <div class="mt-5">
            $statuses->links()
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Status from "../components/Status";
import moment from "moment";
import axios from "axios";

export default {
  name: "Event",
  data() {
    return {
      username: this.$route.params.username,
      loading: false,
      statusesLoading: false,
      event: {
        "id": 0,
        "name": "",
        "slug": "",
        "hashtag": "",
        "host": "",
        "url": "",
        "begin": "",
        "end": "",
        "trainDistance": 0,
        "trainDuration": 0,
        "station": {
          "id": 0,
          "name": "",
          "latitude": 0,
          "longitude": 0,
          "ibnr": 0,
          "rilIdentifier": null
        }
      },
      statuses: null
    };
  },
  components: {
    Status
  },
  computed: {
    duration() {
      const duration = moment.duration(this.event.trainDuration, "minutes").asMinutes();
      let minutes    = duration % 60;
      let hours      = Math.floor(duration / 60);

      return hours + "h " + minutes + "m";
    },
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      this.error   = null;
      this.loading = true;
      axios
          .get("/api/v1/event/" + this.$route.params.slug)
          .then((response) => {
            this.loading = false;
            this.event   = response.data.data;
            this.fetchStatuses();
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.response.data.message || error.message;
          });
    },
    fetchStatuses() {
      this.error           = null;
      this.statusesLoading = true;
      axios
          .get("/api/v1/event/" + this.$route.params.slug + "/statuses")
          .then((response) => {
            this.statusesLoading = false;
            this.statuses        = response.data.data;
          })
          .catch((error) => {
            this.statusesLoading = false;
            this.error           = error.response.data.message || error.message;
          });
    }
  }
};
</script>

<style scoped>

</style>
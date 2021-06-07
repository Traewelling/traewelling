<template>
  <div>
    <div v-if="!loading" class="px-4 py-5 mt-n4"
         style="background-image: url('/images/covers/profile-background.png');background-position: center;background-color: #c5232c">
      <div class="container">
        <img :alt="i18n.get('_.settings.picture')" :src="`/profile/${user.username}/profilepicture`" height="20%"
             width="20%" class="float-end img-thumbnail rounded-circle img-fluid"/>
        <div class="text-white px-4">
          <h2 class="card-title font-bold">
            <strong>{{ user.username }} <i v-if="user.privateProfile" class="fas fa-user-lock" aria-hidden="true"/>
            </strong> <br/>
            <small class="font-weight-light">@{{ user.username }}</small>
            <!--          ToDo: Implement Follow-Button to vue-->
            FOLLOW
          </h2>
          <h2>
          <span class="font-weight-bold">
            <i class="fa fa-route d-inline" aria-hidden="true"/>&nbsp;{{ user.trainDistance.toFixed(2) }}
          </span>
            <span class="small font-weight-lighter">km</span>
            <span class="font-weight-bold ps-sm-2">
            <i class="fa fa-stopwatch d-inline" aria-hidden="true"/>&nbsp;{{ duration }}
          </span>
            <span class="font-weight-bold ps-sm-2">
            <i class="fa fa-dice-d20 d-inline" aria-hidden="true"/>&nbsp;{{ user.points }}
          </span>
            <span class="small font-weight-lighter">{{ i18n.get("_.profile.points-abbr") }}</span>
            <span v-if="user.twitterUrl" class="font-weight-bold ps-sm-2">
            <a :href="user.twitterUrl" rel="me" class="text-white" target="_blank">
              <i class="fab fa-twitter d-inline" aria-hidden="true"/>
            </a>
          </span>
            <span v-if="user.mastodonUrl" class="font-weight-bold ps-sm-2">
            <a :href="user.mastodonUrl" rel="me" class="text-white" target="_blank">
              <i class="fab fa-mastodon d-inline" aria-hidden="true"/>
            </a>
          </span>
          </h2>
        </div>
      </div>
    </div>
    <div class="container">
      <div v-if="loading || statusesLoading">
         {{ i18n.get("_.vue.loading") }}
      </div>

      <div v-if="!statusesLoading && !loading" class="row justify-content-center mt-5">
        <div v-if="user.userInvisibleToMe" class="col-md-8 col-lg-7 text-center mb-5">
          <header><h3>{{ i18n.get("_.profile.private-profile-text") }}</h3></header>
          <h5>
             {{ i18n.choice("_.profile.private-profile-information-text", 1, {"username": user.username, "request": i18n.get("_.profile.follow_req")}) }}
          </h5>
        </div>
        <div v-else-if="statuses.length > 0" class="col-md-8 col-lg-7">
          <header><h3>{{ i18n.get("_.profile.last-journeys-of") }} {{ user.displayName }}:</h3></header>

          <div v-if="statuses">
            <Status v-for="status in statuses" :status="status"></Status>
          </div>
          <div class="mt-5">
            $statuses->links()
          </div>
        </div>
        <div v-else class="col-md-8 col-lg-7">
          <h3 class="text-danger">
            strtr({{ i18n.get("_.profile.no-statuses") }}, [':username' => $user->name])
          </h3>
        </div>
      </div>

      <!--      @include('includes.edit-modal')-->
      <!--      @include('includes.delete-modal')-->
    </div>
  </div>
</template>

<script>
import axios from "axios";
import moment from "moment";
import Status from "../components/Status";

export default {
  name: "Profile",
  data() {
    return {
      username: this.$route.params.username,
      loading: false,
      statusesLoading: false,
      user: {
        "id": 0,
        "displayName": "",
        "username": "",
        "trainDistance": 0,
        "trainDuration": 0,
        "trainSpeed": 0,
        "points": 0,
        "twitterUrl": null,
        "mastodonUrl": null,
        "privateProfile": false,
        "userInvisibleToMe": true,
      },
      statuses: null
    };
  },
  components: {
    Status
  },
  computed: {
    duration() {
      const duration = moment.duration(this.user.trainDuration, "minutes").asMinutes();
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
          .get("/api/v1/user/" + this.$route.params.username)
          .then((response) => {
            this.loading = false;
            this.user    = response.data.data;
            if (!this.user.userInvisibleToMe) {
              this.fetchStatuses();
            }
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
          .get("/api/v1/user/" + this.$route.params.username + "/statuses")
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
}
</script>

<style scoped>

</style>

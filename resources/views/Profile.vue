<template>
  <div>
    <div v-if="!loading" class="px-4 py-5 mt-n4"
         style="background-image: url('/images/covers/profile-background.png');background-position: center;background-color: #c5232c">
      <div class="container">
        <img alt="__('settings.picture')" :src="`/profile/${user.username}/profilepicture`" height="20%"
             width="20%" class="float-end img-thumbnail rounded-circle img-fluid"/>
        <div class="text-white px-4">
          <h2 class="card-title h1-responsive font-bold">
            <strong>{{ user.username }} <i v-if="user.private_profile" class="fas fa-user-lock"></i>
            </strong> <br/>
            <small class="font-weight-light">@{{ user.username }}</small>
            <!--          ToDo: Implement Follow-Button to vue-->
            FOLLOW
          </h2>
          <h2>
          <span class="font-weight-bold">
            <i class="fa fa-route d-inline"></i>&nbsp;{{ user.train_distance.toFixed(2) }}
          </span>
            <span class="small font-weight-lighter">km</span>
            <span class="font-weight-bold ps-sm-2">
            <i class="fa fa-stopwatch d-inline"></i>&nbsp;{{ duration }}
          </span>
            <span class="font-weight-bold ps-sm-2">
            <i class="fa fa-dice-d20 d-inline"></i> &nbsp{{ user.points }}
          </span>
            <span class="small font-weight-lighter">__('profile.points-abbr')</span>
            <span v-if="user.twitter_url" class="font-weight-bold ps-sm-2">
            <a :href="user.twitter_url" rel="me" class="text-white" target="_blank">
              <i class="fab fa-twitter d-inline"></i>
            </a>
          </span>
            <span v-if="user.mastodon_url" class="font-weight-bold ps-sm-2">
            <a :href="user.mastodon_url" rel="me" class="text-white" target="_blank">
              <i class="fab fa-mastodon d-inline"></i>
            </a>
          </span>
          </h2>
        </div>
      </div>
    </div>
    <div class="container">
      <div v-if="loading || statusesLoading">
        Loading...
      </div>

      <div v-if="!statusesLoading && !loading" class="row justify-content-center mt-5">
        <div v-if="user.userInvisibleToMe" class="col-md-8 col-lg-7 text-center mb-5">
          <header><h3>__('profile.private-profile-text')</h3></header>
          <h5>
            __('profile.private-profile-information-text', ["username" => $user->username, "request" =>
            __('profile.follow_req')])
          </h5>
        </div>
        <div v-else-if="statuses.length > 0" class="col-md-8 col-lg-7">
          <header><h3>__('profile.last-journeys-of') {{ user.displayname }}:</h3></header>

          <div v-if="statuses">
            <Status v-for="status in statuses" :status="status"></Status>
          </div>
          <div class="mt-5">
            $statuses->links()
          </div>
        </div>
        <div v-else class="col-md-8 col-lg-7">
          <h3 class="text-danger">
            strtr(__('profile.no-statuses'), [':username' => $user->name])
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
        "displayname": "",
        "username": "",
        "train_distance": 0,
        "train_duration": 0,
        "train_speed": 0,
        "points": 0,
        "twitter_url": null,
        "mastodon_url": null,
        "private_profile": false,
        "userInvisibleToMe": true,
      },
      statuses: null
    };
  },
  components: {
    Status
  },
  computed: {
    duration () {
      const duration = moment.duration(this.user.train_duration, "minutes").asMinutes();
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
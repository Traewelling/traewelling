<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-9">
        <div class="card" id="leaderboard">
          <div class="card-header">
            <router-link :to="{ name: 'leaderboard.month', params:{month: month} }" class="float-end">
              {{ i18n.get("_.leaderboard.month.title") }}
            </router-link>
            {{ i18n.get("_.menu.leaderboard") }}
          </div>
          <div class="card-body" v-if="!loading">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="main-tab" data-toggle="tab" href="#leaderboard-main"
                   role="tab" aria-controls="home" aria-selected="true">
                  {{ i18n.get("_.leaderboard.top") }} {{ users.length }}
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="distance-tab" data-toggle="tab" href="#leaderboard-distance"
                   role="tab" aria-controls="profile" aria-selected="false">
                  {{ i18n.get("_.leaderboard.distance") }}
                </a>
              </li>
              <li class="nav-item" v-if="$auth.check() && friends">
                <a class="nav-link" id="friends-tab" data-toggle="tab" href="#leaderboard-friends"
                   role="tab" aria-controls="contact" aria-selected="false">
                  {{ i18n.get("_.leaderboard.friends") }}
                </a>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active table-responsive" id="leaderboard-main"
                   role="tabpanel">
                <LeaderboardTable described-by="main-tab" :users="users"></LeaderboardTable>
              </div>
              <div class="tab-pane fade table-responsive" id="leaderboard-distance" role="tabpanel">
                <LeaderboardTable described-by="distance-tab" :users="distance"></LeaderboardTable>
              </div>
              <div v-if="$auth.check() && friends" class="tab-pane fade table-responsive"
                   id="leaderboard-friends" role="tabpanel">
                <LeaderboardTable described-by="distance-tab" :users="friends"></LeaderboardTable>
              </div>
            </div>
          </div>
          <div class="card-body" v-else> {{ i18n.get("_.vue.loading") }}</div>
          <div class="card-footer text-muted">
            <i class="far fa-question-circle" aria-hidden="true"></i>
            {{ i18n.get("_.leaderboard.notice")}}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
import LeaderboardTable from "../components/LeaderboardTable";
import axios from "axios";
import {LeaderboardUserModel} from "../js/APImodels";

export default {
  //ToDo format numbers correctly for languages, etc.
  name: "Leaderboard",
  data() {
    return {
      month: moment().format("YYYY-MM"),
      users: null,
      distance: null,
      friends: null,
      loading: false
    };
  },
  components: {
    LeaderboardTable
  },
  methods: {
    fetchData() {
      this.error   = null;
      this.loading = true;
      axios
          .get("/leaderboard/")
          .then((response) => {
            this.loading = false;
            this.users   = response.data.data;
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.data.message || error.message;
          });
      axios
          .get("/leaderboard/distance")
          .then((response) => {
            this.loading  = false;
            this.distance = response.data.data;
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.data.message || error.message;
          });
      if (this.$auth.check()) {
        axios
            .get("/leaderboard/friends")
            .then((response) => {
              this.loading = false;
              this.friends = response.data.data;
              if (!Object.keys(this.friends).length) {
                this.friends = null;
              }
            })
            .catch((error) => {
              this.loading = false;
              this.error   = error.data.message || error.message;
            });
      }
    },
  },
  created() {
    this.fetchData();
  }
};
</script>

<style scoped>

</style>
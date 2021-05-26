<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-9 col-lg-9">
        <div class="card" id="leaderboard">
          <div class="card-header">
            <router-link :to="{ name: 'leaderboard.month', params:{month: month} }" class="float-end">
               __('leaderboard.month.title') 
            </router-link>
             __('menu.leaderboard') 
          </div>
          <div class="card-body" v-if="!loading">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="main-tab" data-toggle="tab" href="#leaderboard-main"
                   role="tab" aria-controls="home" aria-selected="true">
                   __('leaderboard.top') {{users.length}}
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="distance-tab" data-toggle="tab" href="#leaderboard-distance"
                   role="tab" aria-controls="profile" aria-selected="false">
                   __('leaderboard.distance') 
                </a>
              </li>
<!--              ToDo: Friends-->
<!--              <li class="nav-item">-->
<!--                <a class="nav-link" id="friends-tab" data-toggle="tab" href="#leaderboard-friends"-->
<!--                   role="tab" aria-controls="contact" aria-selected="false">-->
<!--                   __('leaderboard.friends') -->
<!--                </a>-->
<!--              </li>-->
<!--              @endif-->
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active table-responsive" id="leaderboard-main"
                   role="tabpanel">
                <LeaderboardTable described-by="main-tab" :users="users"></LeaderboardTable>
              </div>
              <div class="tab-pane fade table-responsive" id="leaderboard-distance" role="tabpanel">
                <LeaderboardTable described-by="distance-tab" :users="users"></LeaderboardTable>
              </div>
<!--              ToDo: Friends-->
<!--              <div class="tab-pane fade table-responsive" id="leaderboard-friends" role="tabpanel">-->
<!--                @include('leaderboard.includes.main-table', [-->
<!--                'data' => $friends,-->
<!--                'describedBy' => 'friends-tab'-->
<!--                ])-->
<!--              </div>-->
<!--              @endisset-->
            </div>
          </div>
          <div class="card-body" v-else>__('vue.loading')</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
import LeaderboardTable from "../components/LeaderboardTable";
import axios from "axios";
export default {
  name: "Leaderboard",
  data() {
    return {
      month: moment().format("YYYY-MM"),
      users: null,
      distance: null,
      friends: null,
      loading: false
    }
  },
  components: {
    LeaderboardTable
  },
  methods: {
    fetchData() {
      this.error   = null;
      this.loading = true;
      axios
          .get("/api/v1/leaderboard/")
          .then((response) => {
            this.loading = false;
            this.users    = response.data.data;
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.response.data.message || error.message;
          });
      axios
          .get("/api/v1/leaderboard/distance")
          .then((response) => {
            this.loading = false;
            this.distance    = response.data.data;
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.response.data.message || error.message;
          });
    },
  },
  created() {
    this.fetchData();
  }
}
</script>

<style scoped>

</style>
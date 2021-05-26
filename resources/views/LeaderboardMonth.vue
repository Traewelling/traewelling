<template>
  <div class="container" v-if="!loading">
    <div class="row">
      <div class="col-md-12">
        <h4>__('leaderboard.month') <strong>{{ month.format("MMMM YYYY") }}</strong></h4>
        <hr/>
        <router-link :to="{ name: 'leaderboard.month', params:{month: lastMonth} }"
                     class="btn btn-sm btn-primary float-left">
          <i class="fas fa-arrow-left" aria-hidden="true"/> {{ moment(lastMonth).format("MMMM YYYY") }}
        </router-link>

        <router-link v-if="moment(nextMonth).isBefore()" :to="{ name: 'leaderboard.month', params:{month: nextMonth} }"
                     class="btn btn-sm btn-primary float-end">
          {{ moment(nextMonth).format("MMMM YYYY") }} <i class="fas fa-arrow-right" aria-hidden="true"/>
        </router-link>
        <div class="clearfix"></div>
        <hr/>
      </div>


      <div v-if="users.length == 0" class="col-md-12">
        <div class="card">
          <div class="card-body text-center text-danger text-bold">
            __('leaderboard.no_data')
          </div>
        </div>
      </div>


      <div v-for="(place, index) in users.slice(0, 3)" class="col-md-4">
        <div class="card mb-2">
          <div class="card-header"> __('leaderboard.rank') {{ index + 1 }}</div>
          <div class="card-body text-center">
            <div class="image-box pe-0 d-lg-flex">
              <router-link to="{ name: 'profile', params: {username: place.username}}">
                <img :src="`/profile/${place.username}/profilepicture`" :alt="place.username" style="width: 50%;">
              </router-link>
            </div>
            <router-link to="{ name: 'profile', params: {username: place.username}}" style="font-size: 1.3em;">
              {{ place.username }}
            </router-link>
            <table class="table text-muted">
              <tbody>
                <tr>
                  <td>
                    <i class="fas fa-dice-d20" aria-hidden="true"/>
                    {{ place.points.toFixed(0) }}
                  </td>
                  <td>
                    <i class="fas fa-clock" aria-hidden="true"/>
                    {{ place.trainDuration }}min
                  </td>
                  <td>
                    <i class="fas fa-route" aria-hidden="true"/>
                    {{ place.trainDistance.toFixed(0) }}km
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <hr/>
    <div v-if="users.length > 3" class="row justify-content-center">
      <div class="col-md-8 col-lg-7">
        <div class="card">
          <div class="card-body table-responsive">
            <div class="card-body table-responsive">
              <table class="table table-vertical-center">
                <thead>
                  <tr>
                    <th scope="col"> __('leaderboard.rank')</th>
                    <th scope="col" colspan="2"> __('leaderboard.user')</th>
                    <th scope="col"> __('leaderboard.duration')</th>
                    <th scope="col"> __('leaderboard.distance')</th>
                    <th scope="col"> __('leaderboard.points')</th>
                  </tr>
                </thead>
                <tr v-for="(place, index) in users.slice(3, 100)">
                  <td>{{ index + 4}}</td>
                  <td>
                    <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                      <router-link to="{ name: 'profile', params: {username: place.username}}">
                        <img :src="`/profile/${place.username}/profilepicture`" :alt="place.username" style="width: 50%;">
                      </router-link>
                    </div>
                  </td>
                  <td>
                    <router-link to="{ name: 'profile', params: {username: place.username}}" style="font-size: 1.3em;">
                      {{ place.username }}
                    </router-link>
                  </td>
                  <td>
                    {{ place.points.toFixed(0) }}
                  </td>
                  <td>
                    {{ place.trainDuration }}min
                  </td>
                  <td>
                    {{ place.trainDistance.toFixed(0) }}km
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
      <hr/>
    </div>
  </div>
  <div v-else>__('vue.loading')</div>
</template>

<script>
import moment from "moment";
import LeaderboardTable from "../components/LeaderboardTable";
import axios from "axios";

export default {
  name: "LeaderboardMonth",
  data() {
    return {
      moment: moment,
      users: null,
      loading: false
    }
  },
  computed: {
    lastMonth() {
      return moment(this.$route.params.month).subtract(1, 'months').format("YYYY-MM");
    },
    month() {
      return moment(this.$route.params.month);
    },
    nextMonth() {
      return moment(this.$route.params.month).add(1, 'months').format("YYYY-MM");
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
          .get("/api/v1/leaderboard/" + this.$route.params.month)
          .then((response) => {
            this.loading = false;
            this.users   = response.data.data;
          })
          .catch((error) => {
            this.loading = false;
            this.error   = error.response.data.message || error.message;
          });
    },
  },
  watch: {
    month () {
      this.fetchData();
    }
  },
  created() {
    this.fetchData();
  }
}
</script>

<style scoped>

</style>
<template>
  <table class="table table-striped table-hover" aria-describedby="describedBy">
    <thead>
      <tr>
        <th scope="col"> {{ i18n.get("_.leaderboard.rank") }}</th>
        <th scope="col"> {{ i18n.get("_.leaderboard.user") }}</th>
        <th scope="col"> {{ i18n.get("_.leaderboard.duration") }}</th>
        <th scope="col"> {{ i18n.get("_.leaderboard.distance") }}</th>
        <th scope="col"> {{ i18n.get("_.leaderboard.averagespeed") }}</th>
        <th scope="col"> {{ i18n.get("_.leaderboard.points") }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(row, index) in users">
        <td> {{ index + 1 }}</td>
        <td>
          <router-link :to="{ name: 'profile', params: {username: row.username}}">
            {{ row.username }}
          </router-link>
        </td>
        <td data-mdb-toggle="tooltip" :title="fullTime(row.trainDuration)">
            {{ hoursAndMinutes(row.trainDuration) }}
        </td>
        <td>{{ localizeDistance(row.trainDistance) }} <small>km</small></td>
        <td>{{ localizeDistance(row.trainSpeed.toFixed(0)) }} <small>km/h</small></td>
        <td>{{ localizeThousands(row.points) }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script>
export default {
  name: "LeaderboardTable",
  props: {
    describedBy: null,
    users: null, //Idk, it doesn't want the model...
  },
};
</script>

<style scoped>

</style>

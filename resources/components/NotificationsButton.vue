<template>
  <a href="#" :class="{'nav-link':!toggler, 'navbar-toggler':toggler}" @click="$emit('click')">
    <span class="fa-bell" :class="{fas: notificationsCount > 0, far: notificationsCount <= 0}"></span>
    <span class="notifications-pill badge rounded-pill badge-notification" v-if="notificationsCount > 0">
      {{ notificationsCount }}
    </span>
  </a>
</template>

<script>
export default {
  name: "NotificationsButton",
  props: {
    notificationsCount: 0,
    toggler: false
  }
};
</script>

<style scoped>

</style>

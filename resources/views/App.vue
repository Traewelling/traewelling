<template>
  <div>
    <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
      <div class="container">
        <router-link class="navbar-brand" :to="{ name: 'base' }">
          Träwelling <!-- ToDo: get name from config -->
        </router-link>
        <div class="navbar-toggler">
          <button v-if="$auth.check()" class="navbar-toggler" type="button"
                  data-mdb-target="#notifications-board" aria-controls="navbarSupportedContent"
                  :aria-label="i18n.get('_.Show notifications')">
            <span class="far fa-bell"></span>
            <span class="notifications-pill badge rounded-pill badge-notification" hidden>0</span>
          </button>
          <button class="navbar-toggler" type="button" data-mdb-toggle="collapse"
                  data-mdb-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                  aria-expanded="false" :aria-label="i18n.get('_.Toggle navigation')">
            <i class="fas fa-bars" aria-hidden="true"></i>
          </button>
        </div>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto">
            <li class="nav-item" v-if="$auth.check()">
              <router-link :to="{ name: 'dashboard' }" class="nav-link">{{ i18n.get('_.menu.dashboard') }}</router-link>
            </li>
            <li class="nav-item">
              <router-link :to="{ name: 'leaderboard' }" class="nav-link">
                {{ i18n.get('_.menu.leaderboard') }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link :to="{ name: 'statuses.active'}" class="nav-link">
                {{ i18n.get('_.menu.active') }}
              </router-link>
            </li>
          </ul>
          <ul class="navbar-nav w-auto" v-if="!$auth.check()">
            <li class="nav-item">
              <router-link :to="{ name: 'auth.login'}"
                           class="nav-link">{{ i18n.get('_.menu.login') }}
              </router-link>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">{{ i18n.get('_.menu.register') }}</a>
            </li>
          </ul>
          <ul class="navbar-nav w-auto" v-else>
            <form class="form-inline" action="#">
              <div class="input-group ps-0 m-0">
                <input name="searchQuery" type="text"
                       class="border border-white rounded-left form-control my-0 py-1"
                       :placeholder="i18n.get('_.stationboard.submit-search')"
                       aria-label="User suchen"/>
                <button class="input-group-text btn-primary" type="submit">
                  <i class="fas fa-search" aria-hidden="true"></i>
                </button>
              </div>
            </form>
            <li class="nav-item d-none d-md-inline-block">
              <a href="#" class="nav-link">
                <span
                    class="notifications-bell fa-bell"
                    :class="{fas: notificationsCount, far: !notificationsCount}"
                ></span>
                <span class="notifications-pill badge rounded-pill badge-notification" v-if="notificationsCount > 0">
                  {{ notificationsCount }}
                </span>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a id="navbarDropdown" href="#" class="nav-link dropdown-toggle mdb-select"
                 role="button" data-mdb-toggle="dropdown" aria-haspopup="true"
                 aria-expanded="false">
                {{$auth.user().displayName}} <span class="caret"></span>
              </a>

              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                <router-link :to="{ name: 'profile', params: {username: $auth.user().username}}"
                             class="dropdown-item">
                  <i class="fas fa-user" aria-hidden="true"></i> {{ i18n.get('_.menu.profile') }}
                </router-link>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-save" aria-hidden="true"></i> {{ i18n.get('_.menu.export') }}
                </a>
                <a class="dropdown-item" href="#">
                  <i class="fas fa-cog" aria-hidden="true"></i> {{ i18n.get('_.menu.settings') }}
                </a>
                <!--                {{ &#45;&#45;@if(Auth::user()->role >= 5)&#45;&#45; }}-->
                <!--                {{-->
                <!--                  &#45;&#45; <a class="dropdown-item" href="#">&#45;&#45;}}-->
                <!--                {{ &#45;&#45; <i class="fas fa-tools" aria-hidden="true"></i> {{ i18n.get('_.menu.admin') }}&#45;&#45;}}-->
                <!--                {{ &#45;&#45;                                        </a>&#45;&#45;}}-->
                <!--                {{ &#45;&#45;@endif&#45;&#45; }}-->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" @click.prevent="$auth.logout()">
                  <i class="fas fa-sign-out-alt" aria-hidden="true"></i> {{ i18n.get('_.menu.logout') }}
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <main class="py-4">
      <router-view></router-view>
      <!--      <NotificationsModal></NotificationsModal>-->
    </main>
  </div>
</template>

<script>
import NotificationsModal from "../components/NotificationsModal";

export default {
  data() {
    return {
      notificationsCount: 1
    };
  },
  components: {
    NotificationsModal
  },
  mounted() {
    this.$auth.load().then(() => {
      if (this.$auth.check()) {
        this.$auth.fetch()
            .then((res) => {
              this.$auth.user(res.data);
            });
      }
    });
    this.fetchNotificationsCount();
  },
  methods: {
    fetchNotificationsCount() {
      axios
      .get('/notifications/count')
      .then((response) => {
        this.notificationsCount = response.data.data;
      })
      .catch((error) => {
        console.error(error);
      });
    }
  }
};
</script>

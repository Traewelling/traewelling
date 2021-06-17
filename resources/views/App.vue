<template>
  <div>
    <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
      <div class="container">
        <router-link class="navbar-brand" :to="{ name: 'base' }">
          Träwelling <!-- ToDo: get name from config -->
        </router-link>
        <div class="navbar-toggler">
          <NotificationsButton
              v-if="$auth.check()"
              toggler="true"
              @click="showNotifications"
              :notifications-count="notificationsCount"
          ></NotificationsButton>
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
              <NotificationsButton
                  :notifications-count="notificationsCount"
                  @click="showNotifications"
              ></NotificationsButton>
            </li>
            <li class="nav-item dropdown">
              <a href="#" class="nav-link dropdown-toggle"
                 role="button" data-mdb-toggle="dropdown" aria-haspopup="true"
                 aria-expanded="false">
                {{ $auth.user().displayName }}
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
      <NotificationsModal ref="notifModal"></NotificationsModal>
    </main>
    <footer class="footer mt-auto py-3">
      <div class="container">
        <div class="btn-group dropup float-end">
          <button type="button" class="btn btn-primary dropdown-toggle" data-mdb-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-globe-europe" aria-hidden="true"></i> {{ i18n.get("_.settings.language.set") }}
          </button>
          <div class="dropdown-menu">
            <a v-for="(lang, key) in langs" class="dropdown-item" href="?language=$key" @click.prevent="setLang(key)">
              {{ lang }}
            </a>
          </div>
        </div>
        <p class="text-muted mb-0">
                <span class="footer-nav-link">
                    <a href="route('static.about')">{{ i18n.get("_.menu.about") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(globaldashboard) ">{{ i18n.get("_.menu.globaldashboard") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(events) ">{{ i18n.get("_.events") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(static.privacy) ">{{ i18n.get("_.menu.privacy") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(static.imprint) ">{{ i18n.get("_.menu.imprint") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(blog.all) ">{{ i18n.get("_.menu.blog") }}</a>
                </span>
        </p>
        <p class="mb-0" v-html="i18n.get('_.menu.developed')"></p>
        <p class="mb-0">&copy; {{ moment().format('Y') }} Tr&auml;welling</p>
        <p class="mb-0 text-muted small">commit:
          <!--          ToDo: get current commit -->
          <a href="https://github.com/Traewelling/traewelling/commit/get_current_git_commit()"
             class="text-muted">
            get_current_git_commit()
          </a>
        </p>
      </div>
    </footer>
  </div>
</template>

<script>
import NotificationsModal from "../components/NotificationsModal";
import NotificationsButton from "../components/NotificationsButton";
import Vue from "vue";
import {languages} from "../js/translations";

export default {
  data() {
    return {
      notificationsCount: 1,
      langs: languages
    };
  },
  components: {
    NotificationsButton,
    NotificationsModal
  },
  mounted() {
    this.$auth.load().then(() => {
      if (this.$auth.check()) {
        this.$auth.fetch()
            .then((res) => {
              this.$auth.user(res.data.data);
              if (this.$auth.user().language) {
                this.setLang(this.$auth.user().language);
              }
            });
      }
    });
    this.fetchNotificationsCount();
  },
  methods: {
    showNotifications() {
      this.$refs.notifModal.show();
    },
    fetchNotificationsCount() {
      axios
          .get("/notifications/count")
          .then((response) => {
            this.notificationsCount = response.data.data;
          })
          .catch((error) => {
            console.error(error);
          });
    },
    setLang(language) {
      if (typeof language === "string" && languages.hasOwnProperty(language)) {
        Vue.localStorage.set("language", language);
        this.i18n.setLocale(language);
        this.moment.locale(language.substr(0, 2));
        this.$forceUpdate();
      }
    }
  }
};
</script>

<template>
  <div>
    <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
      <div class="container">
        <router-link :to="{ name: 'base' }" class="navbar-brand">
          Träwelling <!-- ToDo: get name from config -->
        </router-link>
        <div class="navbar-toggler">
          <NotificationsButton
              v-if="$auth.check()"
              :notifications-count="notificationsCount"
              toggler="true"
              @click="showNotifications"
          ></NotificationsButton>
          <button :aria-label="i18n.get('_.Toggle navigation')" aria-controls="navbarCollapse" aria-expanded="false"
                  class="navbar-toggler" data-mdb-target="#navbarCollapse"
                  data-mdb-toggle="collapse" type="button">
            <i aria-hidden="true" class="fas fa-bars"></i>
          </button>
        </div>
        <div id="navbarCollapse" ref="navbar" class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto">
            <li v-if="$auth.check()" class="nav-item">
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
          <ul v-if="!$auth.check()" class="navbar-nav w-auto">
            <li class="nav-item">
              <router-link :to="{ name: 'auth.login'}"
                           class="nav-link">{{ i18n.get('_.menu.login') }}
              </router-link>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">{{ i18n.get('_.menu.register') }}</a>
            </li>
          </ul>
          <ul v-else class="navbar-nav w-auto">
            <form action="#" class="form-inline">
              <div class="input-group ps-0 m-0" hidden>
                <input :placeholder="i18n.get('_.stationboard.submit-search')" aria-label="User suchen"
                       class="border border-white rounded-left form-control my-0 py-1"
                       name="searchQuery"
                       type="text"/>
                <button class="input-group-text btn-primary" type="submit">
                  <i aria-hidden="true" class="fas fa-search"></i>
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
              <a aria-expanded="false" aria-haspopup="true"
                 class="nav-link dropdown-toggle" data-mdb-toggle="dropdown" href="#"
                 role="button">
                {{ $auth.user().displayName }}
              </a>

              <div aria-labelledby="navbarDropdown" class="dropdown-menu dropdown-menu-right">
                <router-link :to="{ name: 'profile', params: {username: $auth.user().username}}"
                             class="dropdown-item">
                  <i aria-hidden="true" class="fas fa-user"></i> {{ i18n.get('_.menu.profile') }}
                </router-link>
                <a class="dropdown-item" href="#">
                  <i aria-hidden="true" class="fas fa-save"></i> {{ i18n.get('_.menu.export') }}
                </a>
                <a class="dropdown-item" href="#">
                  <i aria-hidden="true" class="fas fa-cog"></i> {{ i18n.get('_.menu.settings') }}
                </a>
                <!--                {{ &#45;&#45;@if(Auth::user()->role >= 5)&#45;&#45; }}-->
                <!--                {{-->
                <!--                  &#45;&#45; <a class="dropdown-item" href="#">&#45;&#45;}}-->
                <!--                {{ &#45;&#45; <i class="fas fa-tools" aria-hidden="true"></i> {{ i18n.get('_.menu.admin') }}&#45;&#45;}}-->
                <!--                {{ &#45;&#45;                                        </a>&#45;&#45;}}-->
                <!--                {{ &#45;&#45;@endif&#45;&#45; }}-->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" @click.prevent="$auth.logout()">
                  <i aria-hidden="true" class="fas fa-sign-out-alt"></i> {{ i18n.get('_.menu.logout') }}
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
          <button aria-expanded="false" aria-haspopup="true" class="btn btn-primary dropdown-toggle"
                  data-mdb-toggle="dropdown" type="button">
            <i aria-hidden="true" class="fas fa-globe-europe"></i> {{ i18n.get("_.settings.language.set") }}
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
                    / <router-link :to="{name: 'dashboard.global'}">{{
              i18n.get("_.menu.globaldashboard")
            }}</router-link>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(events) ">{{ i18n.get("_.events") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(legal.privacy) ">{{ i18n.get("_.menu.privacy") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(legal.notice) ">{{ i18n.get("_.menu.imprint") }}</a>
                </span>
          <span class="footer-nav-link">
                    / <a href=" route(blog.all) ">{{ i18n.get("_.menu.blog") }}</a>
                </span>
        </p>
        <p class="mb-0" v-html="i18n.get('_.menu.developed')"></p>
        <p class="mb-0">&copy; {{ moment().format('Y') }} Tr&auml;welling</p>
        <p class="mb-0 text-muted small">commit:
          <!--          ToDo: get current commit -->
          <a class="text-muted"
             href="https://github.com/Traewelling/traewelling/commit/get_current_git_commit()">
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
  watch: {
    '$route'() {
      $("#navbarCollapse").collapse("hide");
    }
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

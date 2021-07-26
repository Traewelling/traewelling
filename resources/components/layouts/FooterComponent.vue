<template>
    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="btn-group dropup float-end">
                <button :class="{'btn-sm btn-light': dashboard, 'btn-primary': !dashboard}" aria-expanded="false"
                        aria-haspopup="true"
                        class="btn dropdown-toggle"
                        data-mdb-toggle="dropdown" type="button">
                    <i aria-hidden="true" class="fas fa-globe-europe"></i> {{ i18n.get("_.settings.language.set") }}
                </button>
                <div class="dropdown-menu">
                    <a v-for="(lang, key) in langs" class="dropdown-item" href="?language=$key"
                       @click.prevent="setLang(key)">
                        {{ lang }}
                    </a>
                </div>
            </div>
            <p class="text-muted mb-0">
                <span class="footer-nav-link">
                    <a :class="{'text-white-50': dashboard}" href="route('static.about')">{{
                            i18n.get("_.menu.about")
                        }}</a>
                </span>
                <span v-if="$auth.check()" class="footer-nav-link">
                    / <router-link :class="{'text-white-50': dashboard}" :to="{name: 'dashboard.global'}">{{
                        i18n.get("_.menu.globaldashboard")
                    }}</router-link>
                </span>
                <span class="footer-nav-link">
                    / <a :class="{'text-white-50': dashboard}" href=" route(events) ">{{ i18n.get("_.events") }}</a>
                </span>
                <span class="footer-nav-link">
                    / <a :class="{'text-white-50': dashboard}"
                         href=" route(static.privacy) ">{{ i18n.get("_.menu.privacy") }}</a>
                </span>
                <span class="footer-nav-link">
                    / <a :class="{'text-white-50': dashboard}"
                         href=" route(static.imprint) ">{{ i18n.get("_.menu.imprint") }}</a>
                </span>
                <span class="footer-nav-link">
                    / <a :class="{'text-white-50': dashboard}" href=" route(blog.all) ">{{
                        i18n.get("_.menu.blog")
                    }}</a>
                </span>
            </p>
            <p v-if="!dashboard" class="mb-0" v-html="i18n.get('_.menu.developed')"></p>
            <p class="mb-0">&copy; {{ moment().format('Y') }} Tr&auml;welling</p>
            <p v-if="!dashboard" class="mb-0 text-muted small">commit:
                <!--          ToDo: get current commit -->
                <a class="text-muted"
                   href="https://github.com/Traewelling/traewelling/commit/get_current_git_commit()">
                    get_current_git_commit()
                </a>
            </p>
        </div>
        <NotificationsModal ref="notifModal"></NotificationsModal>
    </footer>
</template>

<script>
import NotificationsModal from "../NotificationsModal";
import {languages} from "../../js/translations";
import Vue from "vue";

export default {
    name: "FooterComponent",
    data() {
        return {
            langs: languages
        };
    },
    components: {NotificationsModal},
    props: {
        dashboard: false
    },
    methods: {
        setLang(language) {
            if (typeof language === "string" && languages.hasOwnProperty(language)) {
                Vue.localStorage.set("language", language);
                this.i18n.setLocale(language);
                this.moment.locale(language.substr(0, 2));
                this.$forceUpdate();
                window.location.reload(); //ToDo change this to a better concept, so that the whole page doesn't need to be reloaded
            }
        }
    }
}
</script>

<style scoped>

</style>

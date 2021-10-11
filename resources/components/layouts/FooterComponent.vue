<template>
    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="btn-group dropup float-end">
                <button :class="{'btn-sm btn-light': dashboard, 'btn-primary': !dashboard}" aria-expanded="false"
                        aria-haspopup="true"
                        class="btn dropdown-toggle"
                        :aria-label="i18n.get('_.settings.language.set')" data-mdb-toggle="dropdown" type="button">
                    <i aria-hidden="true" class="fas fa-globe-europe"></i>
                    <span aria-hidden="true" class="d-none d-md-inline">
                        {{ i18n.get("_.settings.language.set") }}
                    </span>
                </button>
                <div class="dropdown-menu">
                    <a v-for="(lang, key) in langs" class="dropdown-item" href="?language=$key"
                       @click.prevent="setLang(key)">
                        {{ lang }}
                    </a>
                </div>
            </div>
            <nav class="text-muted mb-0">
                <router-link :class="{'text-white': dashboard}" :to="{name: 'about'}" class="footer-link">
                    {{ i18n.get("_.menu.about") }}
                </router-link>
                <router-link v-if="$auth.check()" :class="{'text-white': dashboard}" :to="{name: 'dashboard.global'}"
                             class="footer-link">
                    {{ i18n.get("_.menu.globaldashboard") }}
                </router-link>
                <a :class="{'text-white': dashboard}" class="footer-link" href=" route(events) ">
                    {{ i18n.get("_.events") }}
                </a>
                <a :class="{'text-white': dashboard}" class="footer-link" href=" route(static.privacy) ">
                    {{ i18n.get("_.menu.privacy") }}
                </a>
                <a :class="{'text-white': dashboard}" class="footer-link" href=" route(static.imprint) ">
                    {{ i18n.get("_.menu.imprint") }}
                </a>
                <a :class="{'text-white': dashboard}" class="footer-link" href="https://blog.traewelling.de">
                    {{ i18n.get("_.menu.blog") }}
                </a>
            </nav>
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
};
</script>

<style scoped>
.footer-link {
    text-decoration: none;
}

.footer-link:hover {
    text-decoration: underline;
}
</style>

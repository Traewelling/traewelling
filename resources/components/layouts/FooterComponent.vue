<template>
    <footer class="footer mt-auto py-3">
        <div class="container">
            <div class="btn-group dropup float-end">
                <button aria-expanded="false" aria-haspopup="true" class="btn btn-primary dropdown-toggle"
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

export default {
    name: "FooterComponent",
    components: {NotificationsModal}
}
</script>

<style scoped>

</style>

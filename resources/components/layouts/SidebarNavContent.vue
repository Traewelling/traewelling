<template>
    <div>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <router-link :to="{ name: 'profile', params: {username: $auth.user().username}}"
                             class="nav-link bg-transparent">
                    <i aria-hidden="true" class="fas fa-user-alt me-2"></i>
                    {{ i18n.get("_.menu.profile") }}
                </router-link>
            </li>
            <li class="nav-item">
                <a class="nav-link bg-transparent" href="#"><!-- ToDo: Link -->
                    <i aria-hidden="true" class="fas fa-calendar me-2"></i>
                    {{ i18n.get("_.events") }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link bg-transparent" href="#"><!-- ToDo: Link -->
                    <i aria-hidden="true" class="fas fa-search me-2"></i>
                    {{ i18n.get("_.stationboard.submit-search") }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link bg-transparent" href="#"><!-- ToDo: Link -->
                    <i aria-hidden="true" class="fas fa-cog me-2"></i>
                    {{ i18n.get("_.menu.settings") }}
                </a>
            </li>
        </ul>
        <hr>
        <ul class="nav flex-column text-dark">
            <li class="nav-item">
                <router-link :to="{name: 'about'}" class="nav-link text-black-50">
                    {{ i18n.get("_.menu.about") }}
                </router-link>
            </li>
            <li class="nav-item">
                <a class="nav-link text-black-50" href="https://blog.traewelling.de">
                    {{ i18n.get("_.menu.blog") }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-black-50" href="#"><!-- ToDo: Link -->
                    {{ i18n.get("_.menu.privacy") }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-black-50" href="#"><!-- ToDo: Link -->
                    {{ i18n.get("_.menu.legal-notice") }}
                </a>
            </li>
        </ul>
        <hr>
        <ul class="nav nav-pills flex-column mb-0">
            <li class="nav-item">
                <a class="nav-link bg-transparent" href="#" @click.prevent="$auth.logout()">
                    <i aria-hidden="true" class="fas fa-sign-out-alt me-2"></i>
                    {{ i18n.get('_.menu.logout') }}
                </a>
            </li>
        </ul>
        <hr>
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
</template>

<script>
export default {
    name: "SidebarNavContent"
}
</script>

<style scoped>

</style>

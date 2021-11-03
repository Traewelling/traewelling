<template>
    <div>
        <router-link v-if="$auth.check()" :to="{ name: 'profile', params: {username: $auth.user().username}}"
                     class="d-flex align-items-center link-dark text-decoration-none">
            <img :alt="i18n.get('_.settings.picture')" :src="`/profile/${$auth.user().username}/profilepicture`"
                 class="rounded-circle me-2" height="32" width="32">
            <strong>{{ $auth.user().displayName }}</strong>&nbsp;
            <small class="text-muted">@{{ $auth.user().username }}</small>
        </router-link>
        <div v-if="$auth.check()" class="row text-black-50 mt-1 justify">
            <div class="col">
                <i aria-hidden="true" class="fas fa-dice-d20"/>
                <span class="sr-only">{{ i18n.get("_.leaderboard.points") }}</span>
                {{ $auth.user().points.toFixed(0) }}
            </div>
            <div class="col">
                <i aria-hidden="true" class="fas fa-clock"/>
                <span class="sr-only">{{ i18n.get("_.leaderboard.duration") }}</span>
                {{ $auth.user().trainDuration.toFixed(0) }}min
                <!-- ToDo: trainDuration in hours & minutes -->
            </div>
            <div class="col">
                <i aria-hidden="true" class="fas fa-route"/>
                <span class="sr-only">{{ i18n.get("_.leaderboard.distance") }}</span>
                {{ ($auth.user().trainDistance / 1000).toFixed(1) }}km
            </div>
        </div>
        <hr>
        <ul v-if="!$auth.check()" class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a class="nav-link bg-transparent" href="#"><!-- ToDo: Link -->
                    <i aria-hidden="true" class="fas fa-calendar me-2"></i>
                    {{ i18n.get("_.events") }}
                </a>
            </li>
        </ul>
        <ul v-if="$auth.check()" class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <form autocomplete="off" @submit.prevent="searchRedirect">
                    <div class="input-group nav-link bg-transparent d-flex py-1">
                        <div class="form-outline w-75">
                            <input id="search-focus" type="search" class="form-control border-bottom rounded-0"
                                   v-model="searchInput" :class="{active: isSearchPage}">
                            <label class="form-label" for="search-focus">{{
                                    i18n.get("_.stationboard.submit-search")
                                }}</label>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </li>
            <li v-if="desktop && dashboard" class="nav-item">
                <router-link :to="{ name: 'dashboard.global' }" class="nav-link bg-transparent" role="tab">
                    <i aria-hidden="true" class="fas fa-globe me-2"></i>
                    {{ i18n.get("_.menu.globaldashboard") }}
                </router-link>
            </li>
            <li v-else-if="desktop" class="nav-item">
                <router-link :to="{ name: 'dashboard' }" active-class="" class="nav-link bg-transparent"
                             role="tab">
                    <i aria-hidden="true" class="fas fa-user-friends me-2"></i>
                    {{ i18n.get("_.menu.dashboard") }}
                </router-link>
            </li>
            <li class="nav-item">
                <router-link :to="{ name: 'profile', params: {username: $auth.user().username}}"
                             active-class="bg-primary text-light" class="nav-link bg-transparent">
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
                <router-link :to="{name: 'settings'}" class="nav-link bg-transparent">
                    <i aria-hidden="true" class="fas fa-cog me-2"></i>
                    {{ i18n.get("_.menu.settings") }}
                </router-link>
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
                <ChangeLanguageButton navbar="true"/>
            </li>
            <li v-if="$auth.check()" class="nav-item">
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
import ChangeLanguageButton from "../ChangeLanguageButton";

export default {
    name: "SidebarNavContent",
    data() {
        return {
            searchInput: ""
        };
    },
    components: {ChangeLanguageButton},
    props: {desktop: false},
    computed: {
        dashboard() {
            return this.$route.name === "dashboard";
        },
        isSearchPage() {
            return this.$route.name === "search";
        }
    },
    methods: {
        searchRedirect() {
            if (this.searchInput) {
                this.$router.push({name: "search", query: {query: this.searchInput}})
                    .then(() => {
                        this.$emit("refresh");
                    })
                    .catch(() => {
                        this.$emit("refresh");
                    });
            }
        },
        searchQuery() {
            if (this.isSearchPage) {
                this.searchInput = this.$route.query.query;
            }
        }
    },
    mounted() {
        this.searchQuery();
    }
};
</script>

<style scoped>

</style>

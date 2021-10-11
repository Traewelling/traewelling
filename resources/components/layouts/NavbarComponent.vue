<template>
    <nav class="navbar navbar-expand-md navbar-dark bg-trwl">
        <div class="container">
            <router-link :to="{ name: 'base' }" class="navbar-brand d-none d-md-block">
                Träwelling <!-- ToDo: get name from config -->
            </router-link>

            <button :aria-label="i18n.get('_.Toggle navigation')"
                    aria-controls="offcanvasNavigation"
                    class="navbar-toggler float-start" data-mdb-target="#offcanvasNavigation"
                    data-mdb-toggle="offcanvas"
                    type="button">
                <i aria-hidden="true" class="fas fa-bars"></i>
            </button>
            <div class="navbar-toggler">
                <NotificationsButton
                    v-if="$auth.check()"
                    :notifications-count="notificationsCount"
                    toggler="true"
                    @click="showNotifications"
                ></NotificationsButton>
            </div>
            <div id="navbarCollapse" ref="navbar" class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li v-if="$auth.check()" class="nav-item">
                        <router-link :to="{ name: 'dashboard' }" class="nav-link">{{
                                i18n.get('_.menu.dashboard')
                            }}
                        </router-link>
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
                    <li class="nav-item">
                        <router-link :to="{ name: 'statistics'}" class="nav-link">
                            {{ i18n.get('_.stats') }}
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
                                <i aria-hidden="true" class="fas fa-sign-out-alt"></i> {{
                                    i18n.get('_.menu.logout')
                                }}
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
import NotificationsButton from "../NotificationsButton";

export default {
    name: "NavbarComponent",
    components: {NotificationsButton}
};
</script>

<style scoped>

</style>

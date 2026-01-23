<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { ConfigurationFeatureEnum } from '../../../types/Api.gen';
import { PrideService } from '../../services/PrideService';
import { useConfigurationStore } from '../../stores/configuration';
import { useUserStore } from '../../stores/user';
import NotificationBell from '../NotificationBell.vue';
import NavLink from './NavLink.vue';
import UserDropdown from './UserDropdown.vue';

const user = useUserStore();
const config = useConfigurationStore();
config.fetchData();

const searchQuery = computed(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('searchQuery') || '';
});

const prideClass = computed(() => {
    return PrideService.getCssClassesForPrideFlag();
});
</script>

<template>
    <nav id="nav-main" class="navbar navbar-expand-md navbar-dark bg-trwl">
        <div class="container">
            <a class="navbar-brand" :class="prideClass" href="/">
                {{ config.appName }}
            </a>

            <div class="navbar-toggler">
                <NotificationBell v-if="user.authenticated"></NotificationBell>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    :aria-label="trans('toggle-navigation')"
                >
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div id="navbarSupportedContent" class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li v-if="user.authenticated" class="nav-item">
                        <NavLink href="/dashboard">
                            {{ trans('menu.dashboard') }}
                        </NavLink>
                    </li>
                    <li v-if="!user.authenticated || user.user?.pointsEnabled" class="nav-item">
                        <NavLink href="/leaderboard">
                            {{ trans('menu.leaderboard') }}
                        </NavLink>
                    </li>
                    <li class="nav-item">
                        <NavLink href="/statuses/active">
                            {{ trans('menu.active') }}
                        </NavLink>
                    </li>
                    <li v-if="user.authenticated" class="nav-item">
                        <NavLink hre-f="/statistics">
                            {{ trans('stats') }}
                        </NavLink>
                    </li>
                    <li v-if="config.isFeatureEnabled(ConfigurationFeatureEnum.YearInReview)" class="nav-item">
                        <a class="nav-link" href="/year-in-review">
                            <i class="fa-solid fa-champagne-glasses"></i>
                            {{ trans('year-review') }}
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav w-auto">
                    <template v-if="!user.authenticated">
                        <li class="nav-item">
                            <a class="nav-link" href="/login">
                                {{ trans('menu.login') }}
                            </a>
                        </li>
                        <li v-if="config.isFeatureEnabled(ConfigurationFeatureEnum.UserRegistration)" class="nav-item">
                            <a class="nav-link" href="/register">
                                {{ trans('menu.register') }}
                            </a>
                        </li>
                    </template>
                    <template v-else>
                        <form class="form-inline" action="/search">
                            <div class="input-group md-form form-sm form-2 ps-0 m-0">
                                <input
                                    name="searchQuery"
                                    type="text"
                                    :value="searchQuery"
                                    class="border border-white rounded-left form-control my-0 py-1"
                                    :placeholder="trans('stationboard.submit-search')"
                                    :aria-label="trans('stationboard.submit-search')"
                                    required
                                />
                                <button
                                    class="btn btn-primary"
                                    type="submit"
                                    :aria-label="trans('stationboard.submit-search')"
                                >
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        <li class="nav-item d-none d-md-inline-block">
                            <notification-bell :link="true" :allow-fetch="false"></notification-bell>
                        </li>

                        <UserDropdown />
                    </template>
                </ul>
            </div>
        </div>
    </nav>
</template>

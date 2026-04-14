<template>
    <div class="min-h-screen flex flex-col bg-base-300 drawer drawer-end">
        <input id="my-drawer-5" type="checkbox" class="drawer-toggle" />
        <!-- Navigation -->
        <div class="navbar bg-primary shadow-lg drawer-content">
            <div class="navbar-start">
                <a href="/" class="btn btn-ghost text-xl text-white">
                    <img src="/images/icons/logo.svg" alt="Träwelling Logo" class="w-10 h-10 mr-2" />
                    Träwelling
                </a>
            </div>

            <div class="navbar-center hidden text-white lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li v-for="link in links" v-show="link.condition === undefined || link.condition" :key="link.name">
                        <a v-if="link.legacy" :href="link.route as string">
                            <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                            {{ trans(link.name) }}
                        </a>
                        <router-link v-else :to="link.route">
                            <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                            {{ trans(link.name) }}
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="navbar-end">
                <div v-if="user.authenticated && user.user" class="dropdown dropdown-end hidden lg:flex">
                    <div tabindex="0" role="button" class="btn btn-sm m-1">
                        <User class="inline-block w-6 h-6" />
                        {{ user.user.username }}
                    </div>
                    <ul tabindex="-1" class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm">
                        <li v-for="link in userLinks" :key="link.name">
                            <a
                                v-if="link.legacy"
                                v-show="link.condition === undefined || link.condition"
                                :href="link.route as string"
                            >
                                <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                                {{ trans(link.name) }}
                            </a>
                            <router-link
                                v-else
                                v-show="link.condition === undefined || link.condition"
                                :to="link.route"
                            >
                                <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                                {{ trans(link.name) }}
                            </router-link>
                        </li>
                        <li class="border-t border-base-300 mt-1 pt-1">
                            <button @click="logout">
                                <LogOut class="inline-block w-6 h-6 mr-2" />
                                {{ trans('menu.logout') }}
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="lg:hidden">
                    <label for="my-drawer-5" class="drawer-button btn btn-ghost">
                        <Menu class="inline-block w-6 h-6 text-white" />
                    </label>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <main
            class="flex-1 w-full min-h-screen"
            :class="{ 'px-4 sm:px-6 lg:px-8': !legacy, 'max-w-7xl mx-auto py-8': !fullscreen }"
        >
            <slot></slot>
        </main>

        <footer class="footer sm:footer-horizontal bg-primary text-white p-10">
            <aside>
                <div class="flex items-center space-x-2 mb-4">
                    <img src="/images/icons/logo.svg" class="h-12 w-12" alt="Träwelling Logo" />
                    <h1 class="text-2xl text-bold">#Träwelling</h1>
                </div>
                <p>
                    {{ trans('footer.developed') }}
                    <br />
                    {{ trans('footer.source') }}
                    <br />

                    Version
                    <router-link :to="{ name: 'changelog' }" class="link link-hover">
                        {{ config.appVersion?.substring(0, 11) }}
                    </router-link>
                </p>
            </aside>
            <nav>
                <h6 class="footer-title">{{ trans('footer.services') }}</h6>
                <router-link :to="{ name: 'event-list' }" class="link link-hover">{{ trans('events') }}</router-link>
                <a href="https://help.traewelling.de/faq" target="_blank" class="link link-hover">About</a>
            </nav>
            <nav>
                <h6 class="footer-title">{{ trans('footer.elsewhere') }}</h6>
                <a href="https://blog.traewelling.de" target="_blank" class="link link-hover">Blog</a>
                <a href="https://chaos.social/@traewelling" target="_blank" class="link link-hover">Mastodon</a>
                <a href="https://github.com/traewelling/traewelling" target="_blank" class="link link-hover">GitHub</a>
            </nav>
            <nav>
                <h6 class="footer-title">{{ trans('footer.legal') }}</h6>
                <a href="/legal/privacy-policy" class="link link-hover">
                    {{ trans('menu.privacy') }}
                </a>
                <a href="/legal" class="link link-hover">
                    {{ trans('menu.legal-notice') }}
                </a>
            </nav>
            <nav>
                <DarkModeSelector />
                <LanguageSelector />
            </nav>
        </footer>
        <div class="drawer-side">
            <label for="my-drawer-5" aria-label="close sidebar" class="drawer-overlay"></label>
            <ul class="menu bg-base-200 min-h-full w-80 p-4">
                <!-- Sidebar content here -->
                <li v-for="link in links" v-show="link.condition === undefined || link.condition" :key="link.name">
                    <a v-if="link.legacy" :href="link.route as string">
                        <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                        {{ trans(link.name) }}
                    </a>
                    <router-link v-else :to="link.route">
                        <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                        {{ trans(link.name) }}
                    </router-link>
                </li>
                <li class="border-1"></li>
                <li v-for="link in userLinks" :key="link.route">
                    <a v-if="link.legacy" v-show="link.condition === undefined || link.condition" :href="link.route">
                        <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                        {{ trans(link.name) }}
                    </a>
                    <router-link v-else v-show="link.condition === undefined || link.condition" :to="link.route">
                        <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                        {{ trans(link.name) }}
                    </router-link>
                </li>
                <li class="border-t border-base-300 mt-1 pt-1">
                    <button @click="logout">
                        <LogOut class="inline-block w-6 h-6 mr-2" />
                        {{ trans('menu.logout') }}
                    </button>
                </li>
                <li class="p-0 mt-auto">
                    <DarkModeSelector />
                </li>
                <li class="p-0 bottom-0">
                    <LanguageSelector />
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import {
    ChartNoAxesCombined,
    House,
    LifeBuoy,
    LogOut,
    Map,
    Medal,
    Menu,
    MonitorCog,
    Save,
    Search,
    Settings,
    Ticket,
    User,
} from 'lucide-vue-next';
import { computed, FunctionalComponent } from 'vue';
import { RouteLocationRaw } from 'vue-router';
import { useConfigurationStore } from '../../vue/stores/configuration';
import { useUserStore } from '../../vue/stores/user';
import DarkModeSelector from './Footer/DarkModeSelector.vue';
import LanguageSelector from './Footer/LanguageSelector.vue';

defineProps({
    legacy: {
        type: Boolean,
        default: false,
    },
    fullscreen: {
        type: Boolean,
        default: false,
    },
});

const user = useUserStore();
user.fetchSettings();

const config = useConfigurationStore();
config.fetchData();

const links: {
    name: string;
    icon: FunctionalComponent;
    route: string | RouteLocationRaw;
    legacy: boolean;
    condition?: boolean;
}[] = [
    { name: 'menu.dashboard', icon: House, route: { name: 'dashboard' }, legacy: false },
    {
        name: 'menu.leaderboard',
        icon: Medal,
        route: { name: 'leaderboard' },
        legacy: false,
        condition: user.user?.pointsEnabled || false,
    },
    { name: 'menu.active', icon: Map, route: { name: 'active-journeys' }, legacy: false },
    { name: 'stats', icon: ChartNoAxesCombined, route: { name: 'statistics' }, legacy: false },
];

function logout() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        return;
    }

    user.invalidateUser();
    fetch('/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({}),
    })
        .then(() => {
            window.location.href = '/';
        })
        .catch((error) => {
            console.error('Error logging out:', error);
        });
}

const userLinks = computed<
    {
        name: string;
        icon: FunctionalComponent;
        route: string | RouteLocationRaw;
        legacy: boolean;
        condition?: boolean;
    }[]
>(() => [
    {
        name: 'menu.profile',
        icon: User,
        route: { name: 'user-profile', params: { username: user.getUsername } },
        legacy: false,
    },
    { name: 'menu.export', icon: Save, route: { name: 'export' }, legacy: false },
    { name: 'menu.settings', icon: Settings, route: { name: 'settings-profile' }, legacy: false },
    { name: 'menu.about', icon: LifeBuoy, route: 'https://help.traewelling.de/faq/', legacy: true },
    { name: 'tickets.title', icon: Ticket, route: { name: 'tickets' }, legacy: false },
    { name: 'stationboard.submit-search', icon: Search, route: { name: 'search' }, legacy: false },
    {
        name: 'menu.backend',
        icon: MonitorCog,
        route: '/admin',
        condition: user.isEventModerator || user.isAdmin,
        legacy: true,
    },
]);
</script>

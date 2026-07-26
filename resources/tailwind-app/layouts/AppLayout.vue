<template>
    <div class="min-h-screen flex flex-col bg-base-300 drawer drawer-end">
        <input id="my-drawer-5" type="checkbox" class="drawer-toggle" />
        <!-- Navigation -->
        <div class="navbar bg-primary shadow-lg drawer-content">
            <div class="navbar-start">
                <router-link :to="{ name: 'dashboard' }" class="btn btn-ghost text-xl text-white" :class="prideClass">
                    <img src="/images/icons/logo.svg" alt="Träwelling Logo" class="w-8 h-8 mr-2" />
                    {{ config.appName }}
                </router-link>
            </div>

            <div class="navbar-center hidden text-white lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li
                        v-for="(link, number) in links"
                        v-show="link.condition === undefined || link.condition"
                        :key="number"
                    >
                        <router-link :to="link.route">
                            <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                            {{ trans(link.name) }}
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="navbar-end">
                <router-link
                    v-if="user.authenticated"
                    :to="{ name: 'search' }"
                    class="btn btn-ghost btn-sm text-white flex mr-1"
                >
                    <Search class="size-5" />
                </router-link>
                <button
                    v-if="user.authenticated"
                    class="btn btn-ghost btn-sm text-white flex mr-1"
                    @click="notificationsModal?.open()"
                >
                    <div class="indicator">
                        <Bell class="size-5" />
                        <span v-if="notificationsStore.count > 0" class="badge indicator-item badge-info badge-xs">
                            {{ notificationsStore.count < 99 ? notificationsStore.count : '99+' }}
                        </span>
                    </div>
                </button>
                <div v-if="user.authenticated && user.user" class="dropdown dropdown-end hidden lg:flex">
                    <div tabindex="0" role="button" class="btn btn-sm m-1">
                        <User class="inline-block w-6 h-6" />
                        {{ user.user.username }}
                    </div>
                    <ul tabindex="-1" class="dropdown-content menu bg-base-100 rounded-box z-50 w-52 p-2 shadow-sm">
                        <li v-for="link in userLinks" :key="link.name">
                            <router-link v-show="link.condition === undefined || link.condition" :to="link.route">
                                <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                                {{ trans(link.name) }}
                            </router-link>
                        </li>
                        <li>
                            <a href="https://help.traewelling.de/faq/" target="_blank">
                                <LifeBuoy class="inline-block w-6 h-6 mr-2" />
                                {{ trans('menu.about') }}
                            </a>
                        </li>
                        <li v-if="user.isEventModerator || user.isAdmin">
                            <a href="/admin">
                                <ShieldCogCorner class="inline-block w-6 h-6 mr-2" />
                                {{ trans('menu.backend') }}
                            </a>
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

        <!-- Beta banner -->
        <div
            v-if="showBetaBanner"
            role="alert"
            class="alert alert-warning rounded-none py-2 px-4 flex items-center justify-between gap-2"
        >
            <span class="text-sm">
                {{ trans('beta.banner.text') }}
                <a href="/settings/account" class="link link-hover font-semibold">{{
                    trans('beta.banner.settings')
                }}</a>
            </span>
            <button class="btn btn-ghost btn-xs" @click="dismissBanner">
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Main content -->
        <main class="flex-1 w-full" :class="{ 'px-4 sm:px-6 lg:px-8': !legacy, 'max-w-7xl mx-auto py-4': !fullscreen }">
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
                    <a href="https://github.com/Traewelling/traewelling" target="_blank" class="link link-hover">
                        {{ trans('footer.sourcecode') }}
                    </a>
                    {{ trans('footer.licensed-under') }}
                    <a href="https://www.gnu.org/licenses/agpl-3.0.html" target="_blank" class="link link-hover"
                        >AGPLv3</a
                    >.
                    <br />

                    Version
                    <router-link :to="{ name: 'changelog' }" class="link link-hover">
                        {{ config.appVersion }}
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
                    <router-link :to="link.route">
                        <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                        {{ trans(link.name) }}
                    </router-link>
                </li>
                <template v-if="user.authenticated">
                    <li class="border-1"></li>
                    <li v-for="(link, number) in userLinks" :key="number">
                        <router-link v-show="link.condition === undefined || link.condition" :to="link.route">
                            <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                            {{ trans(link.name) }}
                        </router-link>
                    </li>
                    <li>
                        <a href="https://help.traewelling.de/faq/" target="_blank">
                            <LifeBuoy class="inline-block w-6 h-6 mr-2" />
                            {{ trans('menu.about') }}
                        </a>
                    </li>
                    <li v-if="user.isEventModerator || user.isAdmin">
                        <a href="/admin">
                            <ShieldCogCorner class="inline-block w-6 h-6 mr-2" />
                            {{ trans('menu.backend') }}
                        </a>
                    </li>
                </template>
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
        <ActiveStatusCard />
        <NotificationsModal ref="notificationsModal" />
    </div>
</template>

<script setup lang="ts">
import {
    Bell,
    ChartNoAxesCombined,
    House,
    LifeBuoy,
    LogOut,
    Map,
    Medal,
    Menu,
    Save,
    Search,
    Settings,
    ShieldCogCorner,
    Ticket,
    User,
    X,
} from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, FunctionalComponent, onMounted, onUnmounted, ref } from 'vue';
import { RouteLocationRaw } from 'vue-router';
import { PrideService } from '../../vue/services/PrideService';
import { useConfigurationStore } from '../../vue/stores/configuration';
import { useNotificationsStore } from '../../vue/stores/notifications';
import { useUserStore } from '../../vue/stores/user';
import ActiveStatusCard from '../components/ActiveStatusCard.vue';
import NotificationsModal from '../components/Notifications/NotificationsModal.vue';
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

const showBetaBanner = ref(localStorage.getItem('trwl:beta-banner-dismissed') !== '1');

const prideClass = computed(() => {
    return PrideService.getCssClassesForPrideFlag();
});

function dismissBanner() {
    localStorage.setItem('trwl:beta-banner-dismissed', '1');
    showBetaBanner.value = false;
}

const notificationsStore = useNotificationsStore();
const notificationsModal = ref<typeof NotificationsModal | null>(null);
let pollInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    notificationsStore.fetchCount();
    pollInterval = setInterval(() => notificationsStore.fetchCount(), 30000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});

const links: {
    name: string;
    icon: FunctionalComponent;
    route: RouteLocationRaw;
    condition?: boolean;
}[] = [
    { name: 'menu.dashboard', icon: House, route: { name: 'dashboard' } },
    {
        name: 'menu.leaderboard',
        icon: Medal,
        route: { name: 'leaderboard' },
        condition: user.user?.pointsEnabled || false,
    },
    { name: 'menu.active', icon: Map, route: { name: 'active-journeys' } },
    { name: 'stats', icon: ChartNoAxesCombined, route: { name: 'statistics' } },
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
        route: RouteLocationRaw;
        condition?: boolean;
    }[]
>(() => [
    {
        name: 'menu.profile',
        icon: User,
        route: { name: 'user-profile', params: { username: user.getUsername } },
        legacy: false,
    },
    { name: 'menu.export', icon: Save, route: { name: 'export' } },
    { name: 'menu.settings', icon: Settings, route: { name: 'settings-profile' } },
    { name: 'tickets.title', icon: Ticket, route: { name: 'tickets' }, condition: user.isClosedBeta },
    { name: 'stationboard.submit-search', icon: Search, route: { name: 'search' } },
]);
</script>

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
                    <li v-for="link in links" :key="link.route">
                        <a :href="link.route">
                            <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                            {{ trans(link.name) }}
                        </a>
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
                        <li v-for="link in userLinks" :key="link.route">
                            <a v-show="link.condition === undefined || link.condition" :href="link.route">
                                <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                                {{ trans(link.name) }}
                            </a>
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
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                    <a href="/changelog" class="link link-hover">
                        {{ config.appVersion.substring(0, 11) }}
                    </a>
                </p>
            </aside>
            <nav>
                <h6 class="footer-title">{{ trans('footer.services') }}</h6>
                <a href="/events" class="link link-hover">{{ trans('events') }}</a>
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
                <li v-for="link in links" :key="link.route">
                    <a :href="link.route">
                        <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                        {{ trans(link.name) }}
                    </a>
                </li>
                <li class="border-1"></li>
                <li v-for="link in userLinks" :key="link.route">
                    <a v-show="link.condition === undefined || link.condition" :href="link.route">
                        <component :is="link.icon" class="inline-block w-6 h-6 mr-2" />
                        {{ trans(link.name) }}
                    </a>
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
    Map,
    Medal,
    Menu,
    MonitorCog,
    Save,
    Settings,
    User,
} from 'lucide-vue-next';
import { FunctionalComponent } from 'vue';
import { useConfigurationStore } from '../../vue/stores/configuration';
import { useUserStore } from '../../vue/stores/user';
import DarkModeSelector from './Footer/DarkModeSelector.vue';
import LanguageSelector from './Footer/LanguageSelector.vue';

const user = useUserStore();

const config = useConfigurationStore();
config.fetchData();

const links: { name: string; icon: FunctionalComponent; route: string }[] = [
    { name: 'menu.dashboard', icon: House, route: '/dashboard' },
    { name: 'menu.leaderboard', icon: Medal, route: '/leaderboard' },
    { name: 'menu.active', icon: Map, route: '/statuses/active' },
    { name: 'stats', icon: ChartNoAxesCombined, route: '/statistics' },
];

const userLinks = [
    { name: 'menu.profile', icon: User, route: '/profile' },
    { name: 'menu.export', icon: Save, route: '/export' },
    { name: 'menu.settings', icon: Settings, route: '/settings' },
    { name: 'menu.about', icon: LifeBuoy, route: 'https://help.traewelling.de/faq/' },
    { name: 'menu.backend', icon: MonitorCog, route: '/admin', condition: user.isEventModerator || user.isAdmin },
];
</script>

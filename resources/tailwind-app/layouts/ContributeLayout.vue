<template>
    <div class="min-h-screen flex flex-col bg-base-300 drawer drawer-end">
        <input id="my-drawer-contribute" type="checkbox" class="drawer-toggle" />
        <!-- Navigation -->
        <div class="navbar bg-info shadow-lg drawer-content">
            <div class="navbar-start">
                <a href="/" class="btn btn-sm hidden lg:flex">
                    <ArrowLeft class="inline-block w-4 h-4 mr-2" />
                    {{ trans('contribute.nav.back_to_traewelling') }}
                </a>
                <router-link to="/contribute/" class="btn btn-ghost text-xl text-info-content flex lg:hidden">
                    <PencilRuler class="inline-block w-6 h-6 mr-2" />{{ trans('contribute') }}
                </router-link>
            </div>

            <div class="navbar-center hidden text-info-content lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li v-for="link in links" :key="link.route">
                        <router-link :to="link.route">
                            <component :is="link.icon" class="w-6 h-6 mr-2" />
                            {{ trans(link.name) }}
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="navbar-end">
                <router-link to="/contribute/" class="btn btn-ghost text-xl text-white hidden lg:flex">
                    <PencilRuler class="inline-block w-6 h-6 mr-2" />{{ trans('contribute') }}
                </router-link>
                <div class="lg:hidden">
                    <label for="my-drawer-contribute" class="drawer-button btn btn-ghost">
                        <Menu class="inline-block w-6 h-6 text-white" />
                    </label>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot></slot>
        </main>

        <!-- Footer -->
        <footer class="footer footer-horizontal footer-center bg-info text-info-content rounded p-10">
            <nav class="grid grid-flow-col gap-4">
                <a href="https://help.traewelling.de/features/" target="_blank" class="link link-hover">
                    {{ trans('menu.about') }}
                </a>
                <a href="/legal/privacy-policy" class="link link-hover">
                    {{ trans('menu.privacy') }}
                </a>
                <a href="/legal" class="link link-hover">
                    {{ trans('menu.legal-notice') }}
                </a>
            </nav>
        </footer>
        <div class="drawer-side">
            <label for="my-drawer-contribute" aria-label="close sidebar" class="drawer-overlay"></label>
            <ul class="menu bg-base-200 min-h-full w-80 p-4">
                <li class="mb-2">
                    <a href="/" class="btn btn-outline btn-xs">
                        <ArrowLeft class="inline-block w-4 h-4 mr-2" />
                        {{ trans('contribute.nav.back_to_traewelling') }}
                    </a>
                </li>
                <li v-for="link in links" :key="link.route">
                    <a :href="link.route">
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
import { ArrowLeft, CalendarPlus, House, Menu, PencilRuler, User } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { FunctionalComponent } from 'vue';
import DarkModeSelector from './partials/DarkModeSelector.vue';
import LanguageSelector from './partials/LanguageSelector.vue';

const links: { name: string; icon: FunctionalComponent; route: string }[] = [
    { name: 'contribute.nav.overview', icon: House, route: '/contribute' },
    { name: 'contribute.nav.profile', icon: User, route: '/contribute/profile' },
    { name: 'contribute.nav.suggest_event', icon: CalendarPlus, route: '/contribute/event-proposal' },
];
</script>

<template>
    <div class="min-h-screen flex flex-col bg-base-300">
        <!-- Navigation -->
        <div class="navbar bg-primary shadow-lg">
            <div class="navbar-start">
                <a href="/" class="btn btn-sm hidden lg:flex">
                    <ArrowLeft class="inline-block w-4 h-4 mr-2" />
                    {{ trans('contribute.nav.back_to_traewelling') }}
                </a>
                <router-link to="/contribute/" class="btn btn-ghost text-xl text-white flex lg:hidden">
                    <PencilRuler class="inline-block w-6 h-6 mr-2" />{{ trans('contribute') }}
                </router-link>
            </div>

            <div class="navbar-center hidden text-white lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li>
                        <router-link to="/contribute/">
                            <House class="inline-block w-6 h-6 mr-2" />
                            {{ trans('contribute.nav.overview') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/contribute/profile">
                            <User class="inline-block w-6 h-6 mr-2" />
                            {{ trans('contribute.nav.profile') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/contribute/event-proposal">
                            <CalendarPlus class="inline-block w-6 h-6 mr-2" />
                            {{ trans('contribute.nav.suggest_event') }}
                        </router-link>
                    </li>
                </ul>
            </div>

            <div class="navbar-end">
                <router-link to="/contribute/" class="btn btn-ghost text-xl text-white hidden lg:flex">
                    <PencilRuler class="inline-block w-6 h-6 mr-2" />{{ trans('contribute') }}
                </router-link>
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                        <Menu class="inline-block w-6 h-6 text-white" />
                    </div>
                    <ul
                        tabindex="-1"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow"
                    >
                        <li class="mb-2">
                            <a href="/" class="btn btn-outline btn-xs">
                                <ArrowLeft class="inline-block w-4 h-4 mr-2" />
                                {{ trans('contribute.nav.back_to_traewelling') }}
                            </a>
                        </li>
                        <li>
                            <router-link to="/contribute/">
                                <House class="inline-block w-6 h-6 mr-2" />
                                {{ trans('contribute.nav.overview') }}
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/contribute/profile">
                                <User class="inline-block w-6 h-6 mr-2" />
                                {{ trans('contribute.nav.profile') }}
                            </router-link>
                        </li>
                        <li>
                            <router-link to="/contribute/event-proposal">
                                <CalendarPlus class="inline-block w-6 h-6 mr-2" />
                                {{ trans('contribute.nav.suggest_event') }}
                            </router-link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot></slot>
        </main>

        <!-- Footer -->
        <footer class="footer footer-horizontal footer-center bg-primary text-white rounded p-10">
            <nav class="grid grid-flow-col gap-4">
                <a href="https://help.traewelling.de/faq/" target="_blank" class="link link-hover">
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
    </div>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ArrowLeft, CalendarPlus, House, Menu, PencilRuler, User } from 'lucide-vue-next';
import '../../css/contribute-theme.css';
</script>

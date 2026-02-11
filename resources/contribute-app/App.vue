<template>
    <div class="min-h-screen flex flex-col bg-base-300">
        <!-- Navigation -->
        <div class="navbar bg-primary shadow-lg">
            <div class="navbar-start">
                <div class="dropdown">
                    <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            color="white"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h8m-8 6h16"
                            />
                        </svg>
                    </div>
                    <ul
                        tabindex="-1"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow"
                    >
                        <li class="mb-2">
                            <a href="/" class="btn btn-outline btn-xs">
                                <i class="fa fa-arrow-left mr-2"></i>{{ trans('contribute.nav.back_to_traewelling') }}
                            </a>
                        </li>
                        <li>
                            <router-link to="/"
                            ><i class="fa fa-home mr-2"></i>{{ trans('contribute.nav.overview') }}
                            </router-link
                            >
                        </li>
                        <li>
                            <router-link to="/profile"
                            ><i class="fa fa-user mr-2"></i>{{ trans('contribute.nav.profile') }}
                            </router-link
                            >
                        </li>
                    </ul>
                </div>
                <router-link to="/" class="btn btn-ghost text-xl text-white">
                    <i class="fa fa-users mr-2"></i>{{ trans('contribute.title') }}
                </router-link>
            </div>
            <div class="navbar-center hidden text-white lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li>
                        <router-link to="/"
                        ><i class="fa fa-home mr-2"></i>{{ trans('contribute.nav.overview') }}
                        </router-link
                        >
                    </li>
                    <li>
                        <router-link to="/profile"
                        ><i class="fa fa-user mr-2"></i>{{ trans('contribute.nav.profile') }}
                        </router-link
                        >
                    </li>
                </ul>
            </div>
            <div class="navbar-end hidden lg:flex">
                <a href="/" class="btn btn-sm">
                    <i class="fa fa-arrow-left mr-2"></i>{{ trans('contribute.nav.back_to_traewelling') }}
                </a>
            </div>
        </div>

        <!-- Main content -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <router-view></router-view>
        </main>

        <!-- Footer -->
        <footer class="footer footer-horizontal footer-center bg-primary text-white rounded p-10">
            <nav class="grid grid-flow-col gap-4">
                <a
                    href="https://help.traewelling.de/faq/"
                    target="_blank"
                    class="link link-hover"
                >
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
import {trans} from 'laravel-vue-i18n';
</script>

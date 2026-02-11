<template>
    <div class="min-h-screen flex flex-col bg-base-100">
        <!-- Navigation -->
        <div class="navbar traewelling-nav shadow-lg">
            <div class="navbar-start">
                <div class="dropdown">
                    <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
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
                        <li>
                            <router-link to="/"
                                ><i class="fa fa-home mr-2"></i>{{ trans('contribute.nav.overview') }}</router-link
                            >
                        </li>
                        <li>
                            <router-link to="/profile"
                                ><i class="fa fa-user mr-2"></i>{{ trans('contribute.nav.profile') }}</router-link
                            >
                        </li>
                    </ul>
                </div>
                <router-link to="/" class="btn btn-ghost text-xl">
                    <i class="fa fa-users mr-2"></i>{{ trans('contribute.title') }}
                </router-link>
            </div>
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1">
                    <li>
                        <router-link to="/"
                            ><i class="fa fa-home mr-2"></i>{{ trans('contribute.nav.overview') }}</router-link
                        >
                    </li>
                    <li>
                        <router-link to="/profile"
                            ><i class="fa fa-user mr-2"></i>{{ trans('contribute.nav.profile') }}</router-link
                        >
                    </li>
                </ul>
            </div>
            <div class="navbar-end">
                <a href="/public" class="btn">
                    <i class="fa fa-arrow-left mr-2"></i>{{ trans('contribute.nav.back_to_traewelling') }}
                </a>
            </div>
        </div>

        <!-- Main content -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <router-view></router-view>
        </main>

        <!-- Footer -->
        <footer class="traewelling-footer"></footer>
    </div>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
</script>

<style scoped>
/* Träwelling Red Theme */
.traewelling-nav {
    background: linear-gradient(135deg, hsl(6, 67%, 45%) 0%, hsl(6, 67%, 35%) 100%);
    border-bottom: 3px solid hsl(6, 100%, 65%);
}

.traewelling-footer {
    background: linear-gradient(135deg, hsl(6, 67%, 35%) 0%, hsl(6, 67%, 25%) 100%);
    border-top: 3px solid hsl(6, 100%, 65%);
    height: 3rem;
}

html:not(.dark) .traewelling-nav {
    background: linear-gradient(135deg, rgb(199, 39, 48) 0%, rgb(179, 29, 38) 100%);
    border-bottom: 3px solid rgb(219, 49, 58);
}

html:not(.dark) .traewelling-footer {
    background: linear-gradient(135deg, rgb(179, 29, 38) 0%, rgb(159, 19, 28) 100%);
    border-top: 3px solid rgb(219, 49, 58);
}

/* Navigation link colors */
.traewelling-nav :deep(.btn) {
    color: white;
}

.traewelling-nav :deep(.menu a) {
    color: rgba(255, 255, 255, 0.9);
}

.traewelling-nav :deep(.btn:hover),
.traewelling-nav :deep(.menu a:hover) {
    color: white;
    background-color: rgba(255, 255, 255, 0.15);
}

.traewelling-nav :deep(.btn-ghost:hover) {
    background-color: rgba(255, 255, 255, 0.1);
}

.traewelling-nav :deep(svg) {
    stroke: white;
}
</style>

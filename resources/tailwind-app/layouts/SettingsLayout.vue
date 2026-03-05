<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { LayoutGrid, Lock, ShieldUser, TriangleAlert, User, UserRoundKey, Users } from 'lucide-vue-next';
import AppLayout from './AppLayout.vue';

const tabs = [
    { name: 'menu.profile', route: '/settings/profile', icon: User },
    {
        name: 'settings.follower.manage',
        route: '/settings/followers',
        icon: Users,
        activeRoutes: ['/settings/followers', '/settings/follow-requests', '/settings/followings'],
    },
    { name: 'settings.tab.account', route: '/settings/account', icon: ShieldUser },
    { name: 'menu.privacy', route: '/settings/privacy', icon: Lock },
    { name: 'settings.title-security', route: '/settings/security', icon: UserRoundKey },
];

const legacyTabs = [{ name: 'your-apps', route: '/settings/applications', icon: LayoutGrid }];

function isActiveTab(route: { route: string; activeRoutes?: string[] }): boolean {
    if (route.activeRoutes) {
        return route.activeRoutes.some((r) => window?.location?.pathname.startsWith(r));
    }

    return window?.location?.pathname.startsWith(route.route);
}
</script>
<template>
    <AppLayout>
        <div>
            <h1 class="text-2xl lg:text-4xl font-bold mb-4">Settings</h1>
            <div role="alert" class="alert alert-warning mt-3">
                <TriangleAlert class="size-5" />
                <span>
                    This page is still experimental. Please disable experimental features if you want to change your
                    password or delete your account.
                </span>
            </div>
            <div role="tablist" class="tabs tabs-border">
                <router-link
                    v-for="tab in tabs"
                    :key="tab.route"
                    role="tab"
                    class="tab"
                    :to="tab.route"
                    :class="{ 'tab-active': isActiveTab(tab) }"
                >
                    <component :is="tab.icon" class="size-5 md:me-1"></component>
                    <span class="hidden md:inline">
                        {{ trans(tab.name) }}
                    </span>
                </router-link>
                <a
                    v-for="tab in legacyTabs"
                    :key="tab.route"
                    role="tab"
                    class="tab"
                    :href="tab.route"
                    :class="{ 'tab-active': isActiveTab(tab) }"
                >
                    <component :is="tab.icon" class="size-5 md:me-1"></component>
                    <span class="hidden md:inline">
                        {{ trans(tab.name) }}
                    </span>
                </a>
            </div>
            <div class="mt-4 min-h-100">
                <slot />
            </div>
        </div>
    </AppLayout>
</template>

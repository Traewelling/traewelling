<script setup lang="ts">
import { Activity, LayoutGrid, Lock, ShieldUser, User, UserRoundKey, Users } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import AppLayout from './AppLayout.vue';

const tabs = [
    { name: 'menu.profile', route: '/settings/profile', icon: User },
    { name: 'settings.tab.account', route: '/settings/account', icon: ShieldUser },
    { name: 'settings.tab.wellbeing', route: '/settings/wellbeing', icon: Activity },
    { name: 'menu.privacy', route: '/settings/privacy', icon: Lock },
    {
        name: 'settings.social.manage',
        route: '/settings/followers',
        icon: Users,
        activeRoutes: [
            '/settings/followers',
            '/settings/follow-requests',
            '/settings/followings',
            '/settings/blocks',
            '/settings/mutes',
        ],
    },
    { name: 'settings.title-services-and-security', route: '/settings/security', icon: UserRoundKey },
    {
        name: 'your-apps',
        route: '/settings/applications',
        icon: LayoutGrid,
        activeRoutes: ['/settings/applications'],
    },
];

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
            </div>
            <div class="mt-4 min-h-100">
                <slot />
            </div>
        </div>
    </AppLayout>
</template>

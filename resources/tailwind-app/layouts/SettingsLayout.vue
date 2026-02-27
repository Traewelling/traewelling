<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { TriangleAlert } from 'lucide-vue-next';
import AppLayout from './AppLayout.vue';

const tabs = [
    { name: 'menu.profile', route: '/settings/profile' },
    { name: 'settings.tab.account', route: '/settings/account' },
    { name: 'menu.privacy', route: '/settings/privacy' },
    { name: 'settings.title-loginservices', route: '/settings/security/login-providers' },
    { name: 'your-apps', route: '/settings/applications' },
];

function isActiveTab(route: string) {
    return window?.location?.pathname.startsWith(route);
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
                <a
                    role="tab"
                    class="tab"
                    v-for="tab in tabs"
                    :href="tab.route"
                    :class="{ 'tab-active': isActiveTab(tab.route) }"
                >
                    {{ trans(tab.name) }}
                </a>
            </div>
            <div class="mt-4">
                <slot />
            </div>
        </div>
    </AppLayout>
</template>

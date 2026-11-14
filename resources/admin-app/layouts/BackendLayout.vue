<script setup lang="ts">
import {
    Activity,
    Bell,
    BriefcaseBusiness,
    CalendarDays,
    Flag,
    House,
    MapPin,
    Menu,
    Radio,
    Train,
    Users,
} from '@lucide/vue';
import { type FunctionalComponent } from 'vue';
import { useUserStore } from '../../vue/stores/user';

const user = useUserStore();
user.fetchSettings();

// |update-events|delete-events
const navLinks: {
    label: string;
    icon: FunctionalComponent;
    href: string;
    roles: string[];
    permissions?: string[];
}[] = [
    { label: 'Trips', icon: Train, href: '/admin/trips', roles: ['admin'] },
    { label: 'Users', icon: Users, href: '/admin/users', roles: ['admin'] },
    {
        label: 'Events',
        icon: CalendarDays,
        href: '/admin/events',
        roles: ['admin', 'event-moderator'],
        permissions: ['view-events', 'deny-events', 'update-events', 'delete-events', 'create-events', 'accept-events'],
    },
    { label: 'Status', icon: Radio, href: '/admin/statuses', roles: ['admin'] },
    { label: 'Stations', icon: MapPin, href: '/admin/stations', roles: ['admin'] },
    { label: 'Operators', icon: BriefcaseBusiness, href: '/admin/operators', roles: ['admin'] },
    { label: 'Activity', icon: Activity, href: '/admin/activity', roles: ['admin'] },
    { label: 'Reports', icon: Flag, href: '/admin/reports', roles: ['admin'] },
    { label: 'Alerts', icon: Bell, href: '/admin/alerts', roles: ['admin'] },
];

function isActive(href: string): boolean {
    const path = window.location.pathname;
    if (href === '/admin') {
        return path === '/admin' || path === '/admin/';
    }
    return path.startsWith(href);
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-base-200 drawer drawer-end">
        <input id="backend-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex flex-col min-h-screen">
            <!-- Navbar -->
            <div class="navbar bg-primary shadow-lg shrink-0">
                <div class="navbar-start">
                    <RouterLink v-slot="{ href, navigate }" to="/admin" custom>
                        <a :href="href" class="btn btn-ghost text-xl text-white" @click="navigate">
                            <img src="/images/icons/logo.svg" alt="Träwelling Logo" class="w-8 h-8 mr-2" />
                            TRWL Backend
                        </a>
                    </RouterLink>
                </div>

                <div class="navbar-center hidden text-white xl:flex">
                    <ul class="menu menu-horizontal gap-0.5 px-1 text-sm">
                        <li
                            v-for="link in navLinks"
                            v-show="
                                link.roles.some((role) => user.user?.roles.includes(role)) ||
                                link.roles.some((role) => user.user?.roles.includes(role))
                            "
                            :key="link.href"
                        >
                            <RouterLink
                                :to="link.href"
                                class="gap-1.5 px-2 py-1.5"
                                :class="{ 'menu-active': isActive(link.href) }"
                            >
                                <component :is="link.icon" class="w-4 h-4" />
                                {{ link.label }}
                            </RouterLink>
                        </li>
                    </ul>
                </div>

                <div class="navbar-end gap-2">
                    <a href="/dashboard" class="btn btn-ghost btn-sm text-white hidden xl:flex gap-1">
                        <House class="w-4 h-4" />
                        Back to Träwelling
                    </a>
                    <label for="backend-drawer" class="btn btn-ghost xl:hidden">
                        <Menu class="inline-block w-6 h-6 text-white" />
                    </label>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 p-4 lg:p-6">
                <slot />
            </main>
        </div>

        <!-- Mobile drawer -->
        <div class="drawer-side z-50">
            <label for="backend-drawer" aria-label="close sidebar" class="drawer-overlay" />
            <ul class="menu bg-base-100 min-h-full w-72 p-4 gap-1">
                <li class="menu-title text-xs">Navigation</li>
                <li
                    v-for="link in navLinks"
                    v-show="
                        link.roles.some((role) => user.user?.roles.includes(role)) ||
                        link.roles.some((role) => user.user?.roles.includes(role))
                    "
                    :key="link.href"
                >
                    <RouterLink :to="link.href" :class="{ 'menu-active': isActive(link.href) }">
                        <component :is="link.icon" class="w-4 h-4" />
                        {{ link.label }}
                    </RouterLink>
                </li>
                <li class="mt-auto">
                    <a href="/dashboard" class="gap-2">
                        <House class="w-4 h-4" />
                        Back to Träwelling
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

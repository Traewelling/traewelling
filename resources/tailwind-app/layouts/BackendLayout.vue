<script setup lang="ts">
import {
    Activity,
    Bell,
    BriefcaseBusiness,
    CalendarDays,
    Flag,
    House,
    MapPin,
    Radio,
    Scale,
    Train,
    Users,
} from 'lucide-vue-next';
import { type FunctionalComponent } from 'vue';

const navLinks: { label: string; icon: FunctionalComponent; href: string }[] = [
    { label: 'Trips', icon: Train, href: '/admin' },
    { label: 'Users', icon: Users, href: '/admin/users' },
    { label: 'Events', icon: CalendarDays, href: '/admin/events' },
    { label: 'Status', icon: Radio, href: '/admin/statuses' },
    { label: 'Stations', icon: MapPin, href: '/admin/stations' },
    { label: 'Operators', icon: BriefcaseBusiness, href: '/admin/operators' },
    { label: 'Activity', icon: Activity, href: '/admin/activity' },
    { label: 'Reports', icon: Flag, href: '/admin/reports' },
    { label: 'Licenses', icon: Scale, href: '/admin/sources' },
    { label: 'Alerts', icon: Bell, href: '/admin/alerts' },
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
            <div class="navbar bg-primary text-primary-content shadow-lg shrink-0">
                <div class="navbar-start">
                    <a href="/admin" class="btn btn-ghost text-lg font-bold">
                        <img src="/images/icons/logo.svg" alt="Träwelling" class="w-7 h-7" />
                        TRWL Backend
                    </a>
                </div>

                <div class="navbar-center hidden xl:flex">
                    <ul class="menu menu-horizontal gap-0.5 px-1 text-sm">
                        <li v-for="link in navLinks" :key="link.href">
                            <a
                                :href="link.href"
                                class="gap-1.5 px-2 py-1.5"
                                :class="isActive(link.href) ? 'active' : ''"
                            >
                                <component :is="link.icon" class="w-4 h-4" />
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="navbar-end gap-2">
                    <a href="/dashboard" class="btn btn-ghost btn-sm hidden xl:flex gap-1">
                        <House class="w-4 h-4" />
                        Back to Träwelling
                    </a>
                    <label for="backend-drawer" class="btn btn-ghost btn-sm xl:hidden">
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
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
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
                <li v-for="link in navLinks" :key="link.href">
                    <a :href="link.href" :class="isActive(link.href) ? 'active' : ''">
                        <component :is="link.icon" class="w-4 h-4" />
                        {{ link.label }}
                    </a>
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

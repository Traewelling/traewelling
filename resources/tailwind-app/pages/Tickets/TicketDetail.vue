<script setup lang="ts">
import { ChevronRight, Pencil, Ticket } from '@lucide/vue';
import { trans, transChoice } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { Api, TicketResource, TicketStatisticsResource } from '../../../types/Api.gen';
import TicketFormModal from '../../components/Tickets/TicketFormModal.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;

const ticketId = route.params.id as string;

const ticket = ref<TicketResource | null>(null);
const stats = ref<TicketStatisticsResource | null>(null);
const loading = ref(true);
const formOpen = ref(false);

const categoryColors: Record<string, string> = {
    nationalExpress: '#003DA5',
    regional: '#009D57',
    regionalExp: '#009D57',
    suburban: '#960041',
    subway: '#005282',
    tram: '#BE1414',
    bus: '#A7117A',
    ferry: '#009DCC',
    taxi: '#FFCC00',
};

const purposeLabels: Record<string, string> = {
    '0': 'export.reason.private',
    '1': 'export.reason.business',
    '2': 'export.reason.commute',
};

async function fetchData(): Promise<void> {
    try {
        const [ticketRes, statsRes] = await Promise.all([
            api.tickets.getTicket(ticketId),
            api.tickets.getTicketStatistics(ticketId),
        ]);
        ticket.value = ticketRes.data.data ?? null;
        stats.value = statsRes.data.data ?? null;
    } catch {
        notyf.error(trans('generic.error'));
    } finally {
        loading.value = false;
    }
}

function onUpdated(updated: TicketResource): void {
    ticket.value = updated;
    notyf.success(trans('tickets.updated'));
}

function formatDate(date: string | null | undefined): string {
    if (!date) return '?';
    return new Date(date).toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatKm(meters: number): string {
    return (meters / 1000).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 1 });
}

function formatCost(value: number): string {
    return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const purposeData = computed(() =>
    (stats.value?.purposes ?? []).map((p) => ({
        label: trans(purposeLabels[p.reason ?? ''] ?? p.reason ?? '?'),
        count: p.count ?? 0,
        distance: p.distance ?? 0,
    })),
);

const hasCostStats = computed(() => ticket.value?.price && stats.value?.tripCount);

onMounted(fetchData);
</script>

<template>
    <AppLayout>
        <div v-if="loading" class="flex justify-center items-center py-16">
            <span class="loading loading-spinner loading-lg"></span>
        </div>

        <template v-else-if="ticket">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="breadcrumbs text-sm mb-1">
                        <ul>
                            <li>
                                <router-link :to="{ name: 'tickets' }" class="flex items-center gap-1">
                                    <Ticket class="w-4 h-4" />
                                    {{ trans('tickets.title') }}
                                </router-link>
                            </li>
                            <li>{{ ticket.name }}</li>
                        </ul>
                    </div>
                    <h1 class="text-2xl font-bold">{{ ticket.name }}</h1>
                    <p v-if="ticket.validFrom || ticket.validUntil" class="text-base-content/60 text-sm mt-1">
                        {{ formatDate(ticket.validFrom) }}
                        <ChevronRight class="inline w-3 h-3" />
                        {{ formatDate(ticket.validUntil) }}
                        <span v-if="ticket.price !== null && ticket.price !== undefined" class="ml-2">
                            · {{ ticket.price }} {{ ticket.currency }}
                        </span>
                    </p>
                </div>
                <button class="btn btn-outline btn-sm" @click="formOpen = true">
                    <Pencil class="w-4 h-4" />
                    {{ trans('tickets.edit') }}
                </button>
            </div>

            <!-- Primary stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <span class="text-3xl font-bold text-primary">{{ formatKm(stats?.distance ?? 0) }}</span>
                        <span class="text-sm text-base-content/60">km</span>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <span class="text-3xl font-bold text-primary">
                            {{
                                ((stats?.duration ?? 0) / 60).toLocaleString(undefined, {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 1,
                                })
                            }}
                        </span>
                        <span class="text-sm text-base-content/60">{{ trans('time.hours') }}</span>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <span class="text-3xl font-bold text-primary">{{ stats?.tripCount ?? 0 }}</span>
                        <span class="text-sm text-base-content/60">
                            {{ transChoice('stats.trips', stats?.tripCount ?? 0) }}
                        </span>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <template v-if="stats?.costPerTrip !== null && stats?.costPerTrip !== undefined">
                            <span class="text-3xl font-bold text-primary">
                                {{ formatCost(stats.costPerTrip) }} {{ ticket.currency }}
                            </span>
                            <span class="text-sm text-base-content/60">/ {{ trans('tickets.per-trip') }}</span>
                        </template>
                        <template v-else>
                            <span class="text-3xl font-bold text-base-content/30">–</span>
                            <span class="text-sm text-base-content/60">{{ trans('tickets.price') }}</span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Secondary cost stats -->
            <div v-if="hasCostStats" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <template v-if="stats?.costPerKm !== null && stats?.costPerKm !== undefined">
                            <span class="text-xl font-semibold"
                                >{{ formatCost(stats.costPerKm) }} {{ ticket.currency }}</span
                            >
                        </template>
                        <template v-else>
                            <span class="text-xl font-semibold text-base-content/30">–</span>
                        </template>
                        <span class="text-sm text-base-content/60">/ km</span>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <template v-if="stats?.costPerHour !== null && stats?.costPerHour !== undefined">
                            <span class="text-xl font-semibold"
                                >{{ formatCost(stats.costPerHour) }} {{ ticket.currency }}</span
                            >
                        </template>
                        <template v-else>
                            <span class="text-xl font-semibold text-base-content/30">–</span>
                        </template>
                        <span class="text-sm text-base-content/60">/ {{ trans('tickets.per-hour') }}</span>
                    </div>
                </div>
                <div v-if="stats?.firstUsed" class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <span class="text-xl font-semibold">{{ formatDate(stats.firstUsed) }}</span>
                        <span class="text-sm text-base-content/60">{{ trans('tickets.first-used') }}</span>
                    </div>
                </div>
                <div v-if="stats?.lastUsed" class="card bg-base-100">
                    <div class="card-body items-center text-center py-4">
                        <span class="text-xl font-semibold">{{ formatDate(stats.lastUsed) }}</span>
                        <span class="text-sm text-base-content/60">{{ trans('tickets.last-used') }}</span>
                    </div>
                </div>
            </div>

            <!-- Breakdowns -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-if="purposeData.length > 0" class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-sm uppercase tracking-wide text-base-content/50 font-semibold">
                            {{ trans('tickets.travel-purposes') }}
                        </h2>
                        <div v-for="p in purposeData" :key="p.label" class="mt-2">
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ p.label }}</span>
                                <span class="text-base-content/50">{{ p.count }}</span>
                            </div>
                            <progress
                                class="progress progress-primary w-full"
                                :value="Math.round((p.count / (stats?.tripCount ?? 1)) * 100)"
                                max="100"
                            ></progress>
                        </div>
                    </div>
                </div>

                <div v-if="stats?.categories?.length" class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-sm uppercase tracking-wide text-base-content/50 font-semibold">
                            {{ trans('tickets.transport-type') }}
                        </h2>
                        <div v-for="c in stats.categories" :key="c.name" class="mt-2">
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ trans(`transport_types.${c.name}`, {}, c.name ?? '') }}</span>
                                <span class="text-base-content/50">{{ c.count }}</span>
                            </div>
                            <progress
                                class="progress w-full"
                                :style="{ '--progress-color': categoryColors[c.name ?? ''] ?? 'oklch(var(--p))' }"
                                :value="Math.round(((c.count ?? 0) / (stats?.tripCount ?? 1)) * 100)"
                                max="100"
                            ></progress>
                        </div>
                    </div>
                </div>

                <div v-if="stats?.operators?.length" class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-sm uppercase tracking-wide text-base-content/50 font-semibold">
                            {{ trans('tickets.operators') }}
                        </h2>
                        <div v-for="o in stats.operators" :key="o.name" class="mt-2">
                            <div class="flex justify-between text-sm mb-1">
                                <span>{{ o.name ?? trans('generic.unknown') }}</span>
                                <span class="text-base-content/50">{{ formatKm(o.distance ?? 0) }} km</span>
                            </div>
                            <progress
                                class="progress progress-secondary w-full"
                                :value="Math.round(((o.distance ?? 0) / (stats?.distance ?? 1)) * 100)"
                                max="100"
                            ></progress>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div v-else class="py-16 text-center text-base-content/50">
            {{ trans('tickets.none') }}
        </div>

        <TicketFormModal :open="formOpen" :ticket="ticket" @close="formOpen = false" @updated="onUpdated" />
    </AppLayout>
</template>

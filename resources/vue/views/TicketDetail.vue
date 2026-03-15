<script setup lang="ts">
import { trans, transChoice } from 'laravel-vue-i18n';
import { computed, onMounted, ref } from 'vue';
import { Api, TicketResource, TicketStatisticsResource } from '../../types/Api.gen';
import TicketFormModal from '../components/Tickets/TicketFormModal.vue';
import { Dtm } from '../helpers/DateTime';

const props = defineProps({
    ticketId: {
        type: String,
        required: true,
    },
});

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const ticket = ref<TicketResource | null>(null);
const stats = ref<TicketStatisticsResource | null>(null);
const loading = ref(true);
const formModal = ref<InstanceType<typeof TicketFormModal> | null>(null);

async function fetchData(): Promise<void> {
    try {
        const [ticketRes, statsRes] = await Promise.all([
            api.tickets.getTicket(props.ticketId),
            api.tickets.getTicketStatistics(props.ticketId),
        ]);
        ticket.value = ticketRes.data.data ?? null;
        stats.value = statsRes.data.data ?? null;
    } catch (e) {
        console.error('Error fetching ticket details:', e);
    } finally {
        loading.value = false;
    }
}

function onUpdated(updated: TicketResource): void {
    ticket.value = updated;
}

function formatDate(date: string | null | undefined): string {
    if (!date) return '?';
    return Dtm.fromISO(date).toLocaleString({ day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatKm(meters: number): string {
    return (meters / 1000).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 1 });
}

function formatCost(value: number): string {
    return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const purposeLabels: Record<string, string> = {
    '0': 'export.reason.private',
    '1': 'export.reason.business',
    '2': 'export.reason.commute',
};

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

const purposeData = computed(() =>
    (stats.value?.purposes ?? []).map((p) => ({
        label: trans(purposeLabels[p.reason ?? ''] ?? p.reason ?? '?'),
        count: p.count ?? 0,
        distance: p.distance ?? 0,
    })),
);

onMounted(fetchData);
</script>

<template>
    <div v-if="loading" class="py-5 text-center text-muted">
        <div class="spinner-border" role="status" />
    </div>

    <template v-else-if="ticket">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item">
                            <a href="/tickets">{{ trans('tickets.title') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ticket.name }}</li>
                    </ol>
                </nav>
                <h1 class="mb-0">
                    <i class="fa-solid fa-ticket me-2" />
                    {{ ticket.name }}
                </h1>
                <p v-if="ticket.validFrom || ticket.validUntil" class="text-muted mb-0 mt-1">
                    {{ formatDate(ticket.validFrom) }} – {{ formatDate(ticket.validUntil) }}
                    <span v-if="ticket.price !== null && ticket.price !== undefined" class="ms-2">
                        · {{ ticket.price }} {{ ticket.currency }}
                    </span>
                </p>
            </div>
            <button class="btn btn-outline-secondary" @click="formModal?.openEdit(ticket!)">
                <i class="fa-solid fa-pencil" />
                {{ trans('tickets.edit') }}
            </button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-primary">{{ formatKm(stats?.distance ?? 0) }}</div>
                        <div class="text-muted small">km</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-primary">
                            {{
                                ((stats?.duration ?? 0) / 60).toLocaleString(undefined, {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 1,
                                })
                            }}
                        </div>
                        <div class="text-muted small">{{ trans('time.hours') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-2 fw-bold text-primary">{{ stats?.tripCount ?? 0 }}</div>
                        <div class="text-muted small">{{ transChoice('stats.trips', stats?.tripCount ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <template v-if="stats?.costPerTrip !== null && stats?.costPerTrip !== undefined">
                            <div class="fs-2 fw-bold text-primary">
                                {{ formatCost(stats.costPerTrip) }} {{ ticket.currency }}
                            </div>
                            <div class="text-muted small">/ {{ trans('tickets.per-trip') }}</div>
                        </template>
                        <template v-else>
                            <div class="fs-2 fw-bold text-muted">–</div>
                            <div class="text-muted small">{{ trans('tickets.price') }}</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="ticket.price && stats?.tripCount" class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <template v-if="stats.costPerKm !== null && stats.costPerKm !== undefined">
                            <div class="fs-4 fw-semibold">{{ formatCost(stats.costPerKm) }} {{ ticket.currency }}</div>
                            <div class="text-muted small">/ km</div>
                        </template>
                        <template v-else>
                            <div class="fs-4 fw-semibold text-muted">–</div>
                            <div class="text-muted small">/ km</div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <template v-if="stats.costPerHour !== null && stats.costPerHour !== undefined">
                            <div class="fs-4 fw-semibold">
                                {{ formatCost(stats.costPerHour) }} {{ ticket.currency }}
                            </div>
                            <div class="text-muted small">/ {{ trans('tickets.per-hour') }}</div>
                        </template>
                        <template v-else>
                            <div class="fs-4 fw-semibold text-muted">–</div>
                            <div class="text-muted small">/ {{ trans('tickets.per-hour') }}</div>
                        </template>
                    </div>
                </div>
            </div>
            <div v-if="stats.firstUsed" class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-5 fw-semibold">{{ formatDate(stats.firstUsed) }}</div>
                        <div class="text-muted small">{{ trans('tickets.first-used') }}</div>
                    </div>
                </div>
            </div>
            <div v-if="stats.lastUsed" class="col-6 col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-5 fw-semibold">{{ formatDate(stats.lastUsed) }}</div>
                        <div class="text-muted small">{{ trans('tickets.last-used') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdowns -->
        <div class="row g-3">
            <!-- Purposes -->
            <div v-if="purposeData.length > 0" class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase small fw-semibold mb-3">
                            {{ trans('tickets.travel-purposes') }}
                        </h6>
                        <div v-for="p in purposeData" :key="p.label" class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">{{ p.label }}</span>
                                <span class="small text-muted">{{ p.count }}</span>
                            </div>
                            <div class="progress" style="height: 6px">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    :style="{
                                        width: `${Math.round((p.count / (stats?.tripCount ?? 1)) * 100)}%`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div v-if="stats?.categories?.length" class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase small fw-semibold mb-3">
                            {{ trans('tickets.transport-type') }}
                        </h6>
                        <div v-for="c in stats.categories" :key="c.name" class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">{{ trans(`transport_types.${c.name}`, {}, c.name ?? '') }}</span>
                                <span class="small text-muted">{{ c.count }}</span>
                            </div>
                            <div class="progress" style="height: 6px">
                                <div
                                    class="progress-bar"
                                    role="progressbar"
                                    :style="{
                                        width: `${Math.round(((c.count ?? 0) / (stats?.tripCount ?? 1)) * 100)}%`,
                                        backgroundColor: categoryColors[c.name ?? ''] ?? undefined,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="stats?.operators?.length" class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted text-uppercase small fw-semibold mb-3">
                            {{ trans('tickets.operators') }}
                        </h6>
                        <div v-for="o in stats.operators" :key="o.name" class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">{{ o.name ?? trans('generic.unknown') }}</span>
                                <span class="small text-muted">{{ formatKm(o.distance ?? 0) }} km</span>
                            </div>
                            <div class="progress" style="height: 6px">
                                <div
                                    class="progress-bar bg-secondary"
                                    role="progressbar"
                                    :style="{
                                        width: `${Math.round(((o.distance ?? 0) / (stats?.distance ?? 1)) * 100)}%`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div v-else class="py-5 text-center text-muted">
        {{ trans('tickets.none') }}
    </div>

    <TicketFormModal ref="formModal" @updated="onUpdated" />
</template>

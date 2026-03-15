<script setup lang="ts">
import { Tooltip } from 'bootstrap';
import { trans } from 'laravel-vue-i18n';
import { computed, onBeforeUnmount, onMounted, PropType, ref } from 'vue';
import { Api, TicketResource } from '../../../../types/Api.gen';
import { Dtm } from '../../../helpers/DateTime';
import { useUserStore } from '../../../stores/user';

const props = defineProps({
    statusId: {
        type: Number,
        required: true,
    },
    ticket: {
        type: Object as PropType<TicketResource | null>,
        default: null,
    },
    departurePlanned: {
        type: String as PropType<string | null>,
        default: null,
    },
    tripDistance: {
        type: Number as PropType<number | null>,
        default: null,
    },
    tripDuration: {
        type: Number as PropType<number | null>,
        default: null,
    },
});

const emit = defineEmits<{
    (e: 'ticket-changed', ticket: TicketResource | null): void;
}>();

const userStore = useUserStore();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const availableTickets = ref<TicketResource[]>([]);
const selectedTicketId = ref<string | null>(null);
const saving = ref(false);

const rootEl = ref<HTMLElement | null>(null);
let tooltip: Tooltip | null = null;

onMounted(() => {
    if (!rootEl.value) return;
    tooltip = new Tooltip(rootEl.value, {
        selector: '[data-bs-toggle="tooltip"]',
    });
    if (userStore.isClosedBeta) {
        fetchTickets();
    }
});

onBeforeUnmount(() => {
    tooltip?.dispose();
});

async function fetchTickets(): Promise<void> {
    try {
        const validOn = props.departurePlanned ? props.departurePlanned.substring(0, 10) : undefined;
        const response = await api.tickets.getTickets({ validOn });
        availableTickets.value = response.data.data ?? [];
    } catch (e) {
        console.error('Error fetching tickets:', e);
    }
}

async function assign(): Promise<void> {
    saving.value = true;
    try {
        const response = await api.statuses.assignTicketToStatus(
            { ticketId: selectedTicketId.value ?? null },
            props.statusId,
        );
        emit('ticket-changed', response.data.data?.ticket ?? null);
        selectedTicketId.value = null;
    } catch (e) {
        console.error('Error assigning ticket:', e);
    } finally {
        saving.value = false;
    }
}

const costByTrip = computed<number | null>(() => {
    if (!props.ticket?.price || !props.ticket.tripCount) return null;
    return props.ticket.price / props.ticket.tripCount;
});

const costByDistance = computed<number | null>(() => {
    if (!props.ticket?.price || !props.ticket.totalDistance || !props.tripDistance) return null;
    return props.ticket.price * (props.tripDistance / props.ticket.totalDistance);
});

const costByDuration = computed<number | null>(() => {
    if (!props.ticket?.price || !props.ticket.totalDuration || !props.tripDuration) return null;
    return props.ticket.price * (props.tripDuration / props.ticket.totalDuration);
});

function formatDate(date: string | null | undefined): string {
    if (!date) return '?';
    return Dtm.fromISO(date).toLocaleString({ day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatCost(value: number): string {
    return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function remove(): Promise<void> {
    saving.value = true;
    try {
        await api.statuses.assignTicketToStatus({ ticketId: null }, props.statusId);
        emit('ticket-changed', null);
    } catch (e) {
        console.error('Error removing ticket:', e);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div ref="rootEl" class="card mb-3">
        <div class="card-body py-2 px-3">
            <template v-if="ticket">
                <small class="text-muted d-flex align-items-center mb-1">
                    <span>
                        <i class="fa-solid fa-ticket me-1" />
                        {{ trans('tickets.used-ticket') }}
                    </span>
                    <i
                        class="fa-solid fa-eye-slash ms-auto"
                        :title="trans('tickets.only-visible-to-you')"
                        data-bs-toggle="tooltip"
                    />
                </small>
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div>
                            {{ ticket.name }}
                            <span v-if="ticket.validFrom || ticket.validUntil" class="text-muted ms-2 small">
                                {{ formatDate(ticket.validFrom) }} – {{ formatDate(ticket.validUntil) }}
                            </span>
                            <span
                                v-if="ticket.price !== null && ticket.price !== undefined"
                                class="text-muted ms-2 small"
                            >
                                · {{ ticket.price }} {{ ticket.currency }}
                            </span>
                        </div>
                        <div v-if="costByTrip !== null" class="d-flex flex-wrap align-items-center gap-2 mt-1">
                            <span
                                class="badge text-bg-light border small fw-normal"
                                :title="trans('tickets.cost-by-trip-hint', { count: ticket.tripCount })"
                                data-bs-toggle="tooltip"
                            >
                                {{ formatCost(costByTrip) }} {{ ticket.currency }}
                                <i class="fa-solid fa-divide fa-xs ms-1 opacity-50" />
                                {{ trans('tickets.per-trip') }}
                            </span>
                            <span
                                v-if="costByDistance !== null"
                                class="badge text-bg-light border small fw-normal"
                                :title="trans('tickets.cost-by-distance-hint')"
                                data-bs-toggle="tooltip"
                            >
                                {{ formatCost(costByDistance) }} {{ ticket.currency }}
                                <i class="fa-solid fa-divide fa-xs ms-1 opacity-50" />
                                {{ trans('tickets.per-distance') }}
                            </span>
                            <span
                                v-if="costByDuration !== null"
                                class="badge text-bg-light border small fw-normal"
                                :title="trans('tickets.cost-by-duration-hint')"
                                data-bs-toggle="tooltip"
                            >
                                {{ formatCost(costByDuration) }} {{ ticket.currency }}
                                <i class="fa-solid fa-divide fa-xs ms-1 opacity-50" />
                                {{ trans('tickets.per-duration') }}
                            </span>
                        </div>
                    </div>
                    <button class="btn btn-link btn-sm text-muted p-0 ms-2" :disabled="saving" @click="remove()">
                        <i class="fa-solid fa-xmark" />
                    </button>
                </div>
            </template>

            <template v-else-if="userStore.isClosedBeta">
                <small class="text-muted d-flex align-items-center mb-2">
                    <i class="fa-solid fa-ticket me-1" />
                    {{ trans('tickets.assign-ticket') }}
                </small>
                <div class="d-flex gap-2">
                    <select v-model="selectedTicketId" class="form-select form-select-sm">
                        <option :value="null">{{ trans('tickets.none-selected') }}</option>
                        <option v-for="t in availableTickets" :key="t.id" :value="t.id">
                            {{ t.name }}
                            <template v-if="t.validFrom || t.validUntil">
                                ({{ formatDate(t.validFrom) }} – {{ formatDate(t.validUntil) }})
                            </template>
                        </option>
                    </select>
                    <button class="btn btn-sm btn-primary" :disabled="saving || !selectedTicketId" @click="assign()">
                        <span v-if="saving" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
                        {{ trans('tickets.assign') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

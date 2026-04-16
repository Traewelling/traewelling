<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { EyeOff, Ticket, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Api, TicketResource } from '../../../types/Api.gen';
import { Dtm } from '../../../vue/helpers/DateTime';
import { useUserStore } from '../../../vue/stores/user';

const props = defineProps<{
    statusId: number;
    ticket: TicketResource | null;
    departurePlanned: string | null;
    tripDistance: number | null;
    tripDuration: number | null;
}>();

const emit = defineEmits<{
    'ticket-changed': [ticket: TicketResource | null];
}>();

const userStore = useUserStore();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const availableTickets = ref<TicketResource[]>([]);
const selectedTicketId = ref<string | null>(null);
const saving = ref(false);

onMounted(() => {
    if (userStore.isClosedBeta) {
        fetchTickets();
    }
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
</script>

<template>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body py-3 px-4">
            <!-- Assigned ticket -->
            <template v-if="ticket">
                <div class="flex items-center justify-between text-xs text-base-content/50 mb-2">
                    <span class="flex items-center gap-1">
                        <Ticket class="w-3.5 h-3.5" />
                        {{ trans('tickets.used-ticket') }}
                    </span>
                    <span class="flex items-center gap-1" :title="trans('tickets.only-visible-to-you')">
                        <EyeOff class="w-3.5 h-3.5" />
                    </span>
                </div>

                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-medium leading-tight">
                            {{ ticket.name }}
                            <span
                                v-if="ticket.validFrom || ticket.validUntil"
                                class="text-base-content/50 font-normal ml-1 text-xs"
                            >
                                {{ formatDate(ticket.validFrom) }} – {{ formatDate(ticket.validUntil) }}
                            </span>
                            <span
                                v-if="ticket.price !== null && ticket.price !== undefined"
                                class="text-base-content/50 font-normal ml-1 text-xs"
                            >
                                · {{ ticket.price }} {{ ticket.currency }}
                            </span>
                        </p>

                        <div v-if="costByTrip !== null" class="flex flex-wrap gap-1.5 mt-2">
                            <span
                                class="badge badge-ghost badge-sm text-xs font-normal"
                                :title="trans('tickets.cost-by-trip-hint', { count: ticket.tripCount })"
                            >
                                {{ formatCost(costByTrip) }} {{ ticket.currency }} / {{ trans('tickets.per-trip') }}
                            </span>
                            <span
                                v-if="costByDistance !== null"
                                class="badge badge-ghost badge-sm text-xs font-normal"
                                :title="trans('tickets.cost-by-distance-hint')"
                            >
                                {{ formatCost(costByDistance) }} {{ ticket.currency }} /
                                {{ trans('tickets.per-distance') }}
                            </span>
                            <span
                                v-if="costByDuration !== null"
                                class="badge badge-ghost badge-sm text-xs font-normal"
                                :title="trans('tickets.cost-by-duration-hint')"
                            >
                                {{ formatCost(costByDuration) }} {{ ticket.currency }} /
                                {{ trans('tickets.per-duration') }}
                            </span>
                        </div>
                    </div>

                    <button
                        class="btn btn-ghost btn-xs btn-circle shrink-0 text-base-content/40"
                        :disabled="saving"
                        @click="remove()"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </template>

            <!-- Assign ticket -->
            <template v-else-if="userStore.isClosedBeta">
                <p class="text-xs text-base-content/50 flex items-center gap-1 mb-2">
                    <Ticket class="w-3.5 h-3.5" />
                    {{ trans('tickets.assign-ticket') }}
                </p>
                <div class="flex gap-2">
                    <select v-model="selectedTicketId" class="select select-sm select-bordered flex-1 min-w-0">
                        <option :value="null">{{ trans('tickets.none-selected') }}</option>
                        <option v-for="t in availableTickets" :key="t.id" :value="t.id">
                            {{ t.name }}
                            <template v-if="t.validFrom || t.validUntil">
                                ({{ formatDate(t.validFrom) }} – {{ formatDate(t.validUntil) }})
                            </template>
                        </option>
                    </select>
                    <button
                        class="btn btn-sm btn-primary shrink-0"
                        :disabled="saving || !selectedTicketId"
                        @click="assign()"
                    >
                        <span v-if="saving" class="loading loading-spinner loading-xs" />
                        {{ trans('tickets.assign') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

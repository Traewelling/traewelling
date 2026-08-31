<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { onMounted, ref } from 'vue';
import { Api, TicketResource } from '../../types/Api.gen';
import TicketFormModal from '../components/Tickets/TicketFormModal.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const tickets = ref<TicketResource[]>([]);
const formModal = ref<InstanceType<typeof TicketFormModal> | null>(null);

async function fetchTickets(): Promise<void> {
    try {
        const response = await api.tickets.getTickets();
        tickets.value = response.data.data ?? [];
    } catch (e) {
        console.error('Error fetching tickets:', e);
    }
}

function onCreated(ticket: TicketResource): void {
    tickets.value.push(ticket);
}

function onUpdated(ticket: TicketResource): void {
    tickets.value = tickets.value.map((t) => (t.id === ticket.id ? ticket : t));
}

async function deleteTicket(ticket: TicketResource): Promise<void> {
    if (!window.confirm(trans('tickets.delete-confirm-title'))) return;
    try {
        await api.tickets.deleteTicket(ticket.id);
        tickets.value = tickets.value.filter((t) => t.id !== ticket.id);
        notyf.success(trans('successfully-deleted'));
    } catch (e) {
        console.error('Error deleting ticket:', e);
        notyf.error(trans('generic.error'));
    }
}

onMounted(fetchTickets);
</script>

<template>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="mb-0">
            <i class="fa-solid fa-ticket" />
            {{ trans('tickets.title') }}
        </h1>
        <button class="btn btn-primary" @click="formModal?.openCreate()">
            <i class="fa-solid fa-plus" />
            {{ trans('tickets.add') }}
        </button>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card overflow-hidden">
                <div class="card-body p-0">
                    <p v-if="tickets.length === 0" class="text-muted mx-4 my-3">
                        {{ trans('tickets.none') }}
                    </p>
                    <div v-else class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ trans('tickets.name') }}</th>
                                    <th>{{ trans('tickets.valid-from') }}</th>
                                    <th>{{ trans('tickets.valid-until') }}</th>
                                    <th>{{ trans('tickets.price') }}</th>
                                    <th />
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ticket in tickets" :key="ticket.id">
                                    <td>
                                        <a
                                            :href="`/tickets/${ticket.id}`"
                                            class="text-body text-decoration-none fw-semibold"
                                        >
                                            {{ ticket.name }}
                                        </a>
                                    </td>
                                    <td>{{ ticket.validFrom ?? '–' }}</td>
                                    <td>{{ ticket.validUntil ?? '–' }}</td>
                                    <td>
                                        <span v-if="ticket.price !== null && ticket.price !== undefined">
                                            {{ ticket.price }} {{ ticket.currency }}
                                        </span>
                                        <span v-else>–</span>
                                    </td>
                                    <td class="text-end">
                                        <a
                                            :href="`/tickets/${ticket.id}`"
                                            class="btn btn-sm btn-outline-secondary me-1"
                                            :title="trans('profile.statistics')"
                                        >
                                            <i class="fa-solid fa-chart-bar" />
                                        </a>
                                        <button
                                            class="btn btn-sm btn-outline-secondary me-1"
                                            @click="formModal?.openEdit(ticket)"
                                        >
                                            <i class="fa-solid fa-pencil" />
                                        </button>
                                        <button class="btn btn-sm btn-danger" @click="deleteTicket(ticket)">
                                            <i class="fa-solid fa-trash" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fa-solid fa-circle-info me-1" />
                        {{ trans('tickets.info-title') }}
                    </h5>
                    <p class="card-text text-muted small mb-0">
                        {{ trans('tickets.info-text') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <TicketFormModal ref="formModal" @created="onCreated" @updated="onUpdated" />
</template>

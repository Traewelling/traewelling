<script setup lang="ts">
import { Pencil, Plus, Ticket, Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, onMounted, ref } from 'vue';
import { Api, TicketResource } from '../../../types/Api.gen';
import TicketFormModal from '../../components/Tickets/TicketFormModal.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;

const tickets = ref<TicketResource[]>([]);
const loading = ref(true);
const formOpen = ref(false);
const editingTicket = ref<TicketResource | null>(null);

const deleteConfirmOpen = ref(false);
const deletingTicket = ref<TicketResource | null>(null);
const deleting = ref(false);

async function fetchTickets(): Promise<void> {
    loading.value = true;
    try {
        const response = await api.tickets.getTickets();
        tickets.value = response.data.data ?? [];
    } finally {
        loading.value = false;
    }
}

function openCreate(): void {
    editingTicket.value = null;
    formOpen.value = true;
}

function openEdit(ticket: TicketResource): void {
    editingTicket.value = ticket;
    formOpen.value = true;
}

function openDelete(ticket: TicketResource): void {
    deletingTicket.value = ticket;
    deleteConfirmOpen.value = true;
}

function onCreated(ticket: TicketResource): void {
    tickets.value.push(ticket);
    notyf.success(trans('tickets.created'));
}

function onUpdated(ticket: TicketResource): void {
    tickets.value = tickets.value.map((t) => (t.id === ticket.id ? ticket : t));
    notyf.success(trans('tickets.updated'));
}

async function confirmDelete(): Promise<void> {
    if (!deletingTicket.value) return;
    deleting.value = true;
    try {
        await api.tickets.deleteTicket(deletingTicket.value.id);
        tickets.value = tickets.value.filter((t) => t.id !== deletingTicket.value!.id);
        notyf.success(trans('successfully-deleted'));
        deleteConfirmOpen.value = false;
        deletingTicket.value = null;
    } catch {
        notyf.error(trans('generic.error'));
    } finally {
        deleting.value = false;
    }
}

onMounted(fetchTickets);
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <Ticket class="w-6 h-6" />
                {{ trans('tickets.title') }}
            </h1>
            <button class="btn btn-primary btn-sm" @click="openCreate">
                <Plus class="w-4 h-4" />
                {{ trans('tickets.add') }}
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-3">
                <div class="card bg-base-100">
                    <div class="card-body p-0">
                        <div v-if="loading" class="flex justify-center items-center py-12">
                            <span class="loading loading-spinner loading-lg"></span>
                        </div>

                        <p v-else-if="tickets.length === 0" class="text-base-content/50 px-6 py-4">
                            {{ trans('tickets.none') }}
                        </p>

                        <div v-else class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ trans('tickets.name') }}</th>
                                        <th>{{ trans('tickets.valid-from') }}</th>
                                        <th>{{ trans('tickets.valid-until') }}</th>
                                        <th>{{ trans('tickets.price') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ticket in tickets" :key="ticket.id" class="hover">
                                        <td>
                                            <router-link
                                                :to="{ name: 'ticket-detail', params: { id: ticket.id } }"
                                                class="font-semibold link link-hover"
                                            >
                                                {{ ticket.name }}
                                            </router-link>
                                        </td>
                                        <td class="text-base-content/60">{{ ticket.validFrom ?? '–' }}</td>
                                        <td class="text-base-content/60">{{ ticket.validUntil ?? '–' }}</td>
                                        <td class="text-base-content/60">
                                            <span v-if="ticket.price !== null && ticket.price !== undefined">
                                                {{ ticket.price }} {{ ticket.currency }}
                                            </span>
                                            <span v-else>–</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-1">
                                                <router-link
                                                    :to="{ name: 'ticket-detail', params: { id: ticket.id } }"
                                                    class="btn btn-ghost btn-xs"
                                                    :title="trans('profile.statistics')"
                                                >
                                                    <Ticket class="w-4 h-4" />
                                                </router-link>
                                                <button class="btn btn-ghost btn-xs" @click="openEdit(ticket)">
                                                    <Pencil class="w-4 h-4" />
                                                </button>
                                                <button
                                                    class="btn btn-ghost btn-xs text-error"
                                                    @click="openDelete(ticket)"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h2 class="card-title text-base">{{ trans('tickets.info-title') }}</h2>
                        <p class="text-sm text-base-content/60">{{ trans('tickets.info-text') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <TicketFormModal
            :open="formOpen"
            :ticket="editingTicket"
            @close="formOpen = false"
            @created="onCreated"
            @updated="onUpdated"
        />

        <!-- Delete confirm dialog -->
        <dialog :open="deleteConfirmOpen" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <h3 class="text-lg font-bold">{{ trans('tickets.delete-confirm-title') }}</h3>
                <p class="py-4 text-base-content/70">{{ deletingTicket?.name }}</p>
                <div class="modal-action">
                    <button class="btn btn-ghost" @click="deleteConfirmOpen = false">
                        {{ trans('cancel') }}
                    </button>
                    <button class="btn btn-error" :disabled="deleting" @click="confirmDelete">
                        <span v-if="deleting" class="loading loading-spinner loading-sm"></span>
                        {{ trans('delete') }}
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="deleteConfirmOpen = false"></div>
        </dialog>
    </AppLayout>
</template>

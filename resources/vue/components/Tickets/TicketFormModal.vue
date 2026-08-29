<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, TicketResource } from '../../../types/Api.gen';
import ModalComponent from '../ModalComponent.vue';

const emit = defineEmits<{
    (e: 'created', ticket: TicketResource): void;
    (e: 'updated', ticket: TicketResource): void;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });
const modal = ref<InstanceType<typeof ModalComponent> | null>(null);
const editingTicket = ref<TicketResource | null>(null);
const loading = ref(false);

const form = ref({
    name: '',
    valid_from: '',
    valid_until: '',
    price: '',
    currency: '',
});

function openCreate(): void {
    editingTicket.value = null;
    form.value = { name: '', valid_from: '', valid_until: '', price: '', currency: '' };
    modal.value?.show();
}

function openEdit(ticket: TicketResource): void {
    editingTicket.value = ticket;
    form.value = {
        name: ticket.name,
        valid_from: ticket.validFrom ?? '',
        valid_until: ticket.validUntil ?? '',
        price: ticket.price !== null && ticket.price !== undefined ? String(ticket.price) : '',
        currency: ticket.currency ?? '',
    };
    modal.value?.show();
}

async function save(): Promise<void> {
    loading.value = true;
    const payload = {
        name: form.value.name,
        valid_from: form.value.valid_from || undefined,
        valid_until: form.value.valid_until || undefined,
        price: form.value.price !== '' ? Number(form.value.price) : undefined,
        currency: form.value.currency || undefined,
    };

    try {
        if (editingTicket.value) {
            const response = await api.tickets.updateTicket(editingTicket.value.id, payload);
            emit('updated', response.data.data);
            notyf.success(trans('tickets.updated'));
        } else {
            const response = await api.tickets.createTicket(payload);
            emit('created', response.data.data);
            notyf.success(trans('tickets.created'));
        }
        modal.value?.hide();
    } catch (e) {
        console.error('Error saving ticket:', e);
        notyf.error(trans('generic.error'));
    } finally {
        loading.value = false;
    }
}

defineExpose({ openCreate, openEdit });
</script>

<template>
    <ModalComponent ref="modal" :title="editingTicket ? trans('tickets.edit') : trans('tickets.create')">
        <template #body>
            <div class="mb-3">
                <label class="form-label" for="ticket-name">{{ trans('tickets.name') }}</label>
                <input
                    id="ticket-name"
                    v-model="form.name"
                    type="text"
                    class="form-control"
                    :placeholder="trans('tickets.name-placeholder')"
                    required
                />
            </div>
            <div class="row mb-3">
                <div class="col-sm-6">
                    <label class="form-label" for="ticket-valid-from">{{ trans('tickets.valid-from') }}</label>
                    <input id="ticket-valid-from" v-model="form.valid_from" type="date" class="form-control" />
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="ticket-valid-until">{{ trans('tickets.valid-until') }}</label>
                    <input id="ticket-valid-until" v-model="form.valid_until" type="date" class="form-control" />
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <label class="form-label" for="ticket-price">{{ trans('tickets.price') }}</label>
                    <input
                        id="ticket-price"
                        v-model="form.price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                    />
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="ticket-currency">{{ trans('tickets.currency') }}</label>
                    <input
                        id="ticket-currency"
                        v-model="form.currency"
                        type="text"
                        class="form-control"
                        maxlength="10"
                        placeholder="EUR"
                    />
                </div>
            </div>
        </template>

        <template #footer>
            <button class="btn btn-primary" :disabled="loading || !form.name" @click="save">
                <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
                {{ trans('save') }}
            </button>
        </template>
    </ModalComponent>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';
import { Api, TicketResource } from '../../../types/Api.gen';

const props = defineProps<{
    open: boolean;
    ticket: TicketResource | null;
}>();

const emit = defineEmits<{
    close: [];
    created: [ticket: TicketResource];
    updated: [ticket: TicketResource];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const form = ref({
    name: '',
    valid_from: '',
    valid_until: '',
    price: '',
    currency: '',
});
const loading = ref(false);

watch(
    () => props.open,
    (open) => {
        if (!open) return;
        if (props.ticket) {
            form.value = {
                name: props.ticket.name,
                valid_from: props.ticket.validFrom ?? '',
                valid_until: props.ticket.validUntil ?? '',
                price:
                    props.ticket.price !== null && props.ticket.price !== undefined ? String(props.ticket.price) : '',
                currency: props.ticket.currency ?? '',
            };
        } else {
            form.value = { name: '', valid_from: '', valid_until: '', price: '', currency: '' };
        }
    },
);

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
        if (props.ticket) {
            const response = await api.tickets.updateTicket(props.ticket.id, payload);
            emit('updated', response.data.data);
        } else {
            const response = await api.tickets.createTicket(payload);
            emit('created', response.data.data);
        }
        emit('close');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <dialog :open="open" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="text-lg font-bold mb-4">
                {{ ticket ? trans('tickets.edit') : trans('tickets.create') }}
            </h3>

            <form class="flex flex-col gap-4" @submit.prevent="save">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ trans('tickets.name') }}</legend>
                    <input
                        v-model="form.name"
                        type="text"
                        class="input w-full"
                        :placeholder="trans('tickets.name-placeholder')"
                        required
                    />
                </fieldset>

                <div class="grid grid-cols-2 gap-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ trans('tickets.valid-from') }}</legend>
                        <input v-model="form.valid_from" type="date" class="input w-full" />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ trans('tickets.valid-until') }}</legend>
                        <input v-model="form.valid_until" type="date" class="input w-full" />
                    </fieldset>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ trans('tickets.price') }}</legend>
                        <input v-model="form.price" type="number" step="0.01" min="0" class="input w-full" />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ trans('tickets.currency') }}</legend>
                        <input
                            v-model="form.currency"
                            type="text"
                            class="input w-full"
                            maxlength="10"
                            placeholder="EUR"
                        />
                    </fieldset>
                </div>

                <div class="modal-action mt-0">
                    <button type="button" class="btn btn-ghost" @click="emit('close')">
                        {{ trans('cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="loading || !form.name">
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        {{ trans('save') }}
                    </button>
                </div>
            </form>
        </div>
        <div class="modal-backdrop" @click="emit('close')"></div>
    </dialog>
</template>

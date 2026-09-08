<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api } from '../../../types/Api.gen';
import { useActiveCheckin } from '../../../vue/stores/activeCheckin';

const props = defineProps<{
    open: boolean;
    statusId: number;
}>();

const emit = defineEmits<{
    close: [];
    deleted: [id: number];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;
const activeCheckin = useActiveCheckin();
const deleting = ref(false);

async function confirm() {
    deleting.value = true;
    try {
        await api.status.destroySingleStatus(props.statusId);
        if (activeCheckin.status?.id === props.statusId) {
            activeCheckin.reset();
        }
        emit('deleted', props.statusId);
    } catch (e) {
        notyf?.error(String(e));
    } finally {
        deleting.value = false;
    }
}
</script>

<template>
    <dialog class="modal" :class="{ 'modal-open': open }">
        <div class="modal-box">
            <h3 class="font-bold text-lg">{{ trans('modals.deleteStatus-title') }}</h3>
            <div class="modal-action">
                <button class="btn btn-ghost" :disabled="deleting" @click="emit('close')">
                    {{ trans('cancel') }}
                </button>
                <button class="btn btn-error" :disabled="deleting" @click="confirm">
                    <span v-if="deleting" class="loading loading-spinner loading-xs" />
                    {{ trans('delete') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="emit('close')">
            <button>close</button>
        </form>
    </dialog>
</template>

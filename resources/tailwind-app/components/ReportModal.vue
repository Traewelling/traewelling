<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, ref, watch } from 'vue';
import { Api } from '../../types/Api.gen';

type SubjectType = 'Event' | 'Status' | 'Trip' | 'User';
type Reason = 'inappropriate' | 'implausible' | 'spam' | 'illegal' | 'other';

const props = defineProps<{
    open: boolean;
    subjectType: SubjectType;
    subjectId: number;
}>();

const emit = defineEmits<{
    close: [];
    submitted: [];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;

const reasons: Reason[] = ['inappropriate', 'implausible', 'spam', 'illegal', 'other'];

const titleKeys: Record<SubjectType, string> = {
    Status: 'status.report',
    User: 'user.report',
    Event: 'report-something',
    Trip: 'report-something',
};
const title = computed(() => trans(titleKeys[props.subjectType]));

const reason = ref<Reason | ''>('');
const description = ref('');
const loading = ref(false);

watch(
    () => props.open,
    (open) => {
        if (open) {
            reason.value = '';
            description.value = '';
            loading.value = false;
        }
    },
);

const canSubmit = computed(() => !!reason.value && description.value.length >= 10 && !loading.value);

async function submit() {
    if (!canSubmit.value) return;

    loading.value = true;
    try {
        await api.reports.createReport({
            subjectType: props.subjectType,
            subjectId: props.subjectId,
            reason: reason.value as Reason,
            description: description.value,
        });
        notyf?.success(trans('report.success'));
        emit('submitted');
        emit('close');
    } catch {
        notyf?.error(trans('report.error'));
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <dialog class="modal" :class="{ 'modal-open': open }">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">{{ title }}</h3>
            <div class="form-control mb-3">
                <label class="label" for="report-reason">
                    <span class="label-text">{{ trans('report.reason') }}</span>
                </label>
                <select id="report-reason" v-model="reason" class="select select-bordered w-full" required>
                    <option value="" disabled>{{ trans('report.reason') }}</option>
                    <option v-for="r in reasons" :key="r" :value="r">
                        {{ trans(`report-reason.${r}`) }}
                    </option>
                </select>
            </div>
            <div class="form-control mb-4">
                <label class="label" for="report-description">
                    <span class="label-text">{{ trans('report.description') }}</span>
                </label>
                <textarea
                    id="report-description"
                    v-model="description"
                    class="textarea textarea-bordered w-full"
                    rows="3"
                    minlength="10"
                    required
                />
                <label class="label" for="report-description">
                    <span class="label-text-alt text-base-content/50">{{ trans('report.min-length') }}</span>
                </label>
            </div>
            <div class="modal-action">
                <button class="btn btn-ghost" :disabled="loading" @click="emit('close')">
                    {{ trans('cancel') }}
                </button>
                <button class="btn btn-primary" :disabled="!canSubmit" @click="submit">
                    <span v-if="loading" class="loading loading-spinner loading-xs" />
                    {{ trans('report.submit') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @submit.prevent="emit('close')">
            <button>close</button>
        </form>
    </dialog>
</template>

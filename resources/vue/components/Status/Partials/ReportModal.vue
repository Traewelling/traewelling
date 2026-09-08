<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, ref } from 'vue';
import { Api } from '../../../../types/Api.gen';
import ModalComponent from '../../ModalComponent.vue';

const props = defineProps<{
    subjectType: 'Status' | 'User' | 'Event' | 'Trip';
    subjectId: number;
}>();

const modal = ref<InstanceType<typeof ModalComponent> | null>(null);
const notyf = new Notyf({ position: { x: 'right', y: 'bottom' } });
const api = new Api({ baseUrl: window.location.origin + '/api' });

const titleKey: Record<string, string> = {
    Status: 'status.report',
    User: 'user.report',
    Event: 'report-something',
    Trip: 'report-something',
};
const modalTitle = computed(() => trans(titleKey[props.subjectType] ?? 'report-something'));

const reasons = ['inappropriate', 'implausible', 'spam', 'illegal', 'other'] as const;
type Reason = (typeof reasons)[number];

const reason = ref<Reason | ''>('');
const description = ref('');
const loading = ref(false);
const descriptionTouched = ref(false);

const descriptionInvalid = () => descriptionTouched.value && description.value.length < 10;

function show() {
    reason.value = '';
    description.value = '';
    descriptionTouched.value = false;
    loading.value = false;
    modal.value?.show();
}

async function submit() {
    descriptionTouched.value = true;
    if (!reason.value || description.value.length < 10) return;

    loading.value = true;
    try {
        await api.reports.createReport({
            subjectType: props.subjectType,
            subjectId: props.subjectId,
            reason: reason.value,
            description: description.value,
        });
        notyf.success(trans('report.success'));
        modal.value?.hide();
    } catch {
        notyf.error(trans('report.error'));
    } finally {
        loading.value = false;
    }
}

defineExpose({ show });
</script>

<template>
    <ModalComponent ref="modal" :title="modalTitle">
        <template #body>
            <div class="form-floating mb-3">
                <select id="report-reason" v-model="reason" class="form-select" required>
                    <option value="" disabled selected />
                    <option v-for="r in reasons" :key="r" :value="r">
                        {{ trans(`report-reason.${r}`) }}
                    </option>
                </select>
                <label for="report-reason">{{ trans('report.reason') }}</label>
            </div>

            <div class="form-floating">
                <textarea
                    id="report-description"
                    v-model="description"
                    class="form-control"
                    :class="{ 'is-invalid': descriptionInvalid() }"
                    style="min-height: 100px"
                    required
                    @blur="descriptionTouched = true"
                />
                <label for="report-description">{{ trans('report.description') }}</label>
                <div v-if="descriptionInvalid()" class="invalid-feedback">
                    {{ trans('report.min-length') }}
                </div>
            </div>
        </template>

        <template #footer>
            <button type="button" class="btn btn-danger" :disabled="loading" @click="submit">
                <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
                {{ trans('report.submit') }}
            </button>
        </template>
    </ModalComponent>
</template>

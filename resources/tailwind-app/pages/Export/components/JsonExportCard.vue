<script setup lang="ts">
import { CalendarDays, FileCode } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { ExportableFileType } from '../../../../types/Api.gen';
import { useExportDownload } from '../composables/useExportDownload';
import ExportDateRange from './ExportDateRange.vue';

const notyf = inject('notyf') as Notyf;
const { loading, download } = useExportDownload();

function firstOfMonth(): string {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
}

function lastOfMonth(): string {
    const now = new Date();
    const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    return `${last.getFullYear()}-${String(last.getMonth() + 1).padStart(2, '0')}-${String(last.getDate()).padStart(2, '0')}`;
}

const from = ref(firstOfMonth());
const until = ref(lastOfMonth());

async function submit() {
    const result = await download({ from: from.value, until: until.value, filetype: ExportableFileType.Json });
    if (!result.ok) {
        notyf.error(result.errorMessage ?? trans('generic.error'));
    }
}
</script>

<template>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body gap-4">
            <h2 class="card-title text-base">
                <FileCode class="w-5 h-5" />
                {{ trans('export.submit') }} JSON
            </h2>

            <p class="text-sm text-base-content/70">{{ trans('export.json.description2') }}</p>
            <p class="text-sm text-base-content/70">{{ trans('export.json.description3') }}</p>

            <div class="divider my-0"></div>

            <div>
                <p class="font-semibold text-sm mb-2 flex items-center gap-2">
                    <CalendarDays class="w-4 h-4" />
                    {{ trans('export.period') }}
                </p>
                <ExportDateRange v-model:from="from" v-model:until="until" />
            </div>

            <div class="card-actions justify-end">
                <button class="btn btn-primary btn-sm" :disabled="loading" @click="submit">
                    <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                    {{ trans('export.generate') }}
                </button>
            </div>
        </div>
    </div>
</template>

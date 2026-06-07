<script setup lang="ts">
import { CalendarDays, Download, FileCode, FileText, TableIcon } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { ExportableColumn, ExportableFileType } from '../../../../types/Api.gen';
import { useExportDownload } from '../composables/useExportDownload';
import ExportColumnSelector from './ExportColumnSelector.vue';
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

const columns = ref<ExportableColumn[]>([]);
const from = ref(firstOfMonth());
const until = ref(lastOfMonth());

async function submit(filetype: ExportableFileType) {
    if (columns.value.length === 0) {
        notyf.error(trans('export.columns'));
        return;
    }

    const result = await download({ from: from.value, until: until.value, columns: columns.value, filetype });
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
                {{ trans('export.submit') }} PDF / CSV
            </h2>

            <div>
                <p class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <TableIcon class="w-4 h-4" />
                    {{ trans('export.columns') }}
                </p>
                <ExportColumnSelector v-model="columns" />
            </div>

            <div class="divider my-0"></div>

            <div>
                <p class="font-semibold text-sm mb-2 flex items-center gap-2">
                    <CalendarDays class="w-4 h-4" />
                    {{ trans('export.period') }}
                </p>
                <ExportDateRange v-model:from="from" v-model:until="until" />
            </div>

            <div class="divider my-0"></div>

            <div>
                <p class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <Download class="w-4 h-4" />
                    {{ trans('export.format') }}
                </p>
                <div class="flex flex-col gap-2">
                    <button class="btn btn-primary btn-sm" :disabled="loading" @click="submit(ExportableFileType.Pdf)">
                        <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                        <FileText v-else class="w-4 h-4" />
                        PDF
                    </button>
                    <button
                        class="btn btn-primary btn-sm"
                        :disabled="loading"
                        @click="submit(ExportableFileType.CsvHuman)"
                    >
                        <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                        <TableIcon v-else class="w-4 h-4" />
                        CSV ({{ trans('human-readable-headings') }})
                    </button>
                    <button
                        class="btn btn-primary btn-sm"
                        :disabled="loading"
                        @click="submit(ExportableFileType.CsvMachine)"
                    >
                        <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                        <TableIcon v-else class="w-4 h-4" />
                        CSV ({{ trans('machine-readable-headings') }})
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

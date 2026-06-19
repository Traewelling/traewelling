<script setup lang="ts">
import { Save } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { useUserStore } from '../../../vue/stores/user';
import AppLayout from '../../layouts/AppLayout.vue';
import ExportPdfCsvCard from './components/ExportPdfCsvCard.vue';
import GdprExportCard from './components/GdprExportCard.vue';
import JsonExportCard from './components/JsonExportCard.vue';

const user = useUserStore();

const showGdprExport = computed(() => user.getRoles.includes('test-gdpr-export'));
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <Save class="w-6 h-6" />
                {{ trans('export.title') }}
            </h1>
            <p class="text-base-content/70 mt-1">{{ trans('export.lead') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <ExportPdfCsvCard />
            </div>

            <div class="flex flex-col gap-6">
                <JsonExportCard />
                <GdprExportCard v-if="showGdprExport" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ChartBar, TriangleAlert } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { Api, WebhookStatsResource } from '../../../../types/Api.gen';
import SettingsLayout from '../../../layouts/SettingsLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const route = useRoute();

const stats = ref<WebhookStatsResource | null>(null);
const loading = ref(true);
const notFound = ref(false);

const clientId = Number(route.params.clientId);

function successRate(): string {
    if (!stats.value || stats.value.total === 0) return '0';
    const successes = stats.value.by_response_code
        .filter((r) => r.response_code !== null && r.response_code >= 200 && r.response_code < 300)
        .reduce((sum, r) => sum + r.total, 0);
    return ((successes / stats.value.total) * 100).toFixed(1);
}

function responseCodeLabel(code: number | null): string {
    if (code === null) return trans('webhook-stats.timeout');
    return String(code);
}

function responseCodeClass(code: number | null): string {
    if (code === null) return 'badge-error';
    if (code >= 200 && code < 300) return 'badge-success';
    if (code >= 500) return 'badge-error';
    return 'badge-warning';
}

onMounted(async () => {
    const response = await api.applications.getApplicationWebhookStats(clientId);

    if (response.status === 404) {
        notFound.value = true;
        loading.value = false;
        return;
    }

    const data = await response.json();
    stats.value = data.data;
    loading.value = false;
});
</script>

<template>
    <SettingsLayout>
        <div class="flex items-center gap-2 mb-4">
            <ChartBar class="size-6" />
            <h2 class="text-xl font-bold">{{ trans('webhook-stats.title') }}</h2>
        </div>

        <div v-if="notFound" role="alert" class="alert alert-warning">
            <TriangleAlert class="size-5" />
            <span>{{ trans('webhook-stats.not-found') }}</span>
        </div>

        <div v-else-if="loading" class="flex justify-center py-12">
            <span class="loading loading-spinner loading-lg"></span>
        </div>

        <template v-else-if="stats">
            <p class="text-base-content/60 text-sm mb-6">
                {{ trans('webhook-stats.subtitle', { name: stats.client_name }) }}
            </p>

            <!-- Summary cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                <div class="stat bg-base-100 rounded-box shadow">
                    <div class="stat-title">{{ trans('webhook-stats.total-calls') }}</div>
                    <div class="stat-value text-2xl">{{ stats.total }}</div>
                </div>
                <div class="stat bg-base-100 rounded-box shadow">
                    <div class="stat-title">{{ trans('webhook-stats.success-rate') }}</div>
                    <div class="stat-value text-2xl text-success">{{ successRate() }}%</div>
                </div>
                <div class="stat bg-base-100 rounded-box shadow col-span-2 md:col-span-1">
                    <div class="stat-title">{{ trans('webhook-stats.active-webhooks') }}</div>
                    <div class="stat-value text-2xl">{{ stats.by_event.length }}</div>
                    <div class="stat-desc">{{ trans('webhook-stats.event-types') }}</div>
                </div>
            </div>

            <!-- By day -->
            <h3 class="font-semibold mb-2">{{ trans('webhook-stats.by-day') }}</h3>
            <div class="overflow-x-auto rounded-box shadow mb-6">
                <table class="table table-sm bg-base-100">
                    <thead>
                        <tr>
                            <th>{{ trans('webhook-stats.date') }}</th>
                            <th class="text-right">{{ trans('webhook-stats.total') }}</th>
                            <th class="text-right text-success">{{ trans('webhook-stats.success') }}</th>
                            <th class="text-right text-error">{{ trans('webhook-stats.failed') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="stats.by_day.length === 0">
                            <td colspan="4" class="text-center text-base-content/50">
                                {{ trans('webhook-stats.no-data') }}
                            </td>
                        </tr>
                        <tr v-for="day in stats.by_day" :key="day.date">
                            <td>{{ day.date }}</td>
                            <td class="text-right">{{ day.total }}</td>
                            <td class="text-right text-success">{{ day.success }}</td>
                            <td class="text-right text-error">{{ day.failed }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- By event -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-2">{{ trans('webhook-stats.by-event') }}</h3>
                    <div class="overflow-x-auto rounded-box shadow">
                        <table class="table table-sm bg-base-100">
                            <thead>
                                <tr>
                                    <th>{{ trans('webhook-stats.event') }}</th>
                                    <th class="text-right">{{ trans('webhook-stats.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="stats.by_event.length === 0">
                                    <td colspan="2" class="text-center text-base-content/50">
                                        {{ trans('webhook-stats.no-data') }}
                                    </td>
                                </tr>
                                <tr v-for="e in stats.by_event" :key="e.event">
                                    <td>
                                        <code class="text-xs">{{ e.event }}</code>
                                    </td>
                                    <td class="text-right">{{ e.total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- By response code -->
                <div>
                    <h3 class="font-semibold mb-2">{{ trans('webhook-stats.by-response-code') }}</h3>
                    <div class="overflow-x-auto rounded-box shadow">
                        <table class="table table-sm bg-base-100">
                            <thead>
                                <tr>
                                    <th>{{ trans('webhook-stats.response-code') }}</th>
                                    <th class="text-right">{{ trans('webhook-stats.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="stats.by_response_code.length === 0">
                                    <td colspan="2" class="text-center text-base-content/50">
                                        {{ trans('webhook-stats.no-data') }}
                                    </td>
                                </tr>
                                <tr v-for="r in stats.by_response_code" :key="r.response_code ?? 'timeout'">
                                    <td>
                                        <span :class="['badge badge-sm', responseCodeClass(r.response_code)]">
                                            {{ responseCodeLabel(r.response_code) }}
                                        </span>
                                    </td>
                                    <td class="text-right">{{ r.total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </SettingsLayout>
</template>

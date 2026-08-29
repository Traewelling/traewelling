<script setup lang="ts">
import { TriangleAlert, Waypoints } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { onUnmounted, ref, watch } from 'vue';
import { Api, RouteMapEntryResource } from '../../../types/Api.gen';
import Loading from '../../components/Loading.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import { defaultFilterState, RouteMapFilterState, toQuery } from './filters';
import RouteMapCanvas from './partials/RouteMapCanvas.vue';
import RouteMapFilters from './partials/RouteMapFilters.vue';

const DEBOUNCE_MS = 400;

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const filters = ref<RouteMapFilterState>(defaultFilterState());
const entries = ref<RouteMapEntryResource[]>([]);
const loading = ref(true);
const failed = ref(false);

let latestRequest = 0;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

function fetchRouteMap(): void {
    const requestId = ++latestRequest;
    loading.value = true;
    failed.value = false;

    // an empty selection means the user deselected every mode of transport, so there is nothing to draw
    if (filters.value.travelTypes.length === 0) {
        entries.value = [];
        loading.value = false;
        return;
    }

    api.routeMap
        .getRouteMap(toQuery(filters.value))
        .then((response) => {
            if (requestId !== latestRequest) {
                return;
            }
            entries.value = response.data.data ?? [];
            loading.value = false;
        })
        .catch(() => {
            if (requestId !== latestRequest) {
                return;
            }
            entries.value = [];
            failed.value = true;
            loading.value = false;
        });
}

watch(
    filters,
    () => {
        if (debounceTimer !== null) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(fetchRouteMap, DEBOUNCE_MS);
    },
    { deep: true },
);

onUnmounted(() => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
});

fetchRouteMap();
</script>

<template>
    <AppLayout>
        <div class="flex items-center gap-2 mb-1">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <Waypoints class="size-6" />
                {{ trans('route-map.title') }}
            </h1>
            <Loading v-if="loading" />
        </div>
        <p class="text-sm text-base-content/60 mb-6">{{ trans('route-map.description') }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-1">
                <RouteMapFilters v-model="filters" />
            </div>

            <div class="lg:col-span-3 flex flex-col gap-4">
                <div v-if="failed" class="alert alert-error">
                    <TriangleAlert class="size-5" />
                    <span>{{ trans('error.500') }}</span>
                </div>

                <div class="relative rounded-box overflow-hidden h-[55vh] lg:h-[70vh] isolate bg-base-100">
                    <RouteMapCanvas :entries="entries" />

                    <div
                        v-if="!loading && entries.length === 0 && !failed"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-base-100/80 pointer-events-none"
                    >
                        <span class="alert alert-warning w-auto">{{ trans('route-map.empty') }}</span>
                    </div>
                    <div
                        v-if="loading"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-base-100/60 pointer-events-none"
                    >
                        <span class="loading loading-spinner loading-lg" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

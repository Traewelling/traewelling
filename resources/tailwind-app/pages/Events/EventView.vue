<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { forEach } from 'lodash';
import { DateTime } from 'luxon';
import { LngLat, LngLatBounds } from 'maplibre-gl';
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { Api, EventResource, LivePointDto, MapProvider, StatusResource } from '../../../types/Api.gen';
import EventMarker from '../../../vue/components/Map/EventMarker.vue';
import GenericMap from '../../../vue/components/Map/GenericMap.vue';
import { useUserStore } from '../../../vue/stores/user';
import Loading from '../../components/Loading.vue';
import StatusCard from '../../components/Status/StatusCard.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import EventDetail from './partials/EventDetail.vue';

const route = useRoute();
const slug = route.params.slug as string;
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const user = useUserStore();

const event = ref<EventResource | null>(null);
const statuses = ref<StatusResource[]>([]);
const loading = ref(true);
const loadingMore = ref(false);
const errorMsg = ref<string | null>(null);
const currentPage = ref(1);
const showMore = ref(false);
const livePositions = ref<LivePointDto[]>([]);
const bounds = ref<LngLatBounds>(LngLatBounds.fromLngLat(new LngLat(9.902056, 49.843), 1000000));

async function fetchEvent(): Promise<void> {
    try {
        const res = await api.event.getEvent(slug);
        event.value = res.data?.data ?? null;

        if (event.value?.station) {
            const newBounds = LngLatBounds.fromLngLat(
                new LngLat(event.value.station.longitude, event.value.station.latitude),
                1000000,
            );
            bounds.value = newBounds;
        }
    } catch {
        // event info is best-effort
    }
}

async function fetchStatuses(page = 1, append = false): Promise<void> {
    if (append) {
        loadingMore.value = true;
    } else {
        loading.value = true;
        errorMsg.value = null;
    }

    try {
        const res = await api.event.getEventStatuses(slug, { page });
        const items: StatusResource[] = res.data?.data ?? [];

        if (append) {
            statuses.value.push(...items);
        } else {
            statuses.value = items;
        }

        showMore.value = !!res.data?.links?.next;
        currentPage.value = res.data?.meta?.current_page ?? page;
    } catch (e: unknown) {
        errorMsg.value = e instanceof Error ? e.message : trans('generic.error');
    } finally {
        loading.value = false;
        loadingMore.value = false;
        fetchPolyLines();
    }
}

async function fetchPolyLines() {
    const polylineTripIds: string[] = [];
    forEach(statuses.value, (status) => {
        if (DateTime.fromISO(status.checkin.destination.arrivalPlanned ?? '') > DateTime.now()) {
            polylineTripIds.push(status.id.toString());
        }
    });

    if (polylineTripIds.length == 0) {
        return;
    }

    api.positions.getLivePositionsForStatuses(polylineTripIds.join(',')).then((response) => {
        livePositions.value = response.data.data || [];
        const newBounds = bounds.value;
        for (const position of livePositions.value) {
            position.polyline?.features?.forEach((feature) => {
                const coord = feature.geometry?.coordinates;
                if (coord && coord[0] && coord[1]) {
                    newBounds.extend([coord[0], coord[1]]);
                }
            });
        }
        bounds.value = newBounds;
    });
}

onMounted(() => {
    fetchEvent();
    fetchStatuses();
});
</script>

<template>
    <AppLayout>
        <div class="mx-auto grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="event" class="col md:sticky md:top-4 md:self-start">
                <div class="card bg-base-100 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div
                            v-if="event.station"
                            class="[&_canvas]:[border-top-left-radius:var(--rounded-box)] [&_canvas]:[border-top-right-radius:var(--rounded-box)]"
                        >
                            <GenericMap
                                :live-positions="livePositions"
                                :map-provider="
                                    user.user?.mapProvider
                                        ? MapProvider[user.user!.mapProvider as keyof typeof MapProvider]
                                        : MapProvider.Cargo
                                "
                                :bounds="bounds"
                            >
                                <EventMarker :event="event" />
                            </GenericMap>
                        </div>
                        <div class="p-4">
                            <EventDetail :event="event" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <!-- Error -->
                <div v-if="errorMsg" class="alert alert-error mb-4">
                    {{ errorMsg }}
                </div>

                <template v-if="loading">
                    <div v-for="n in 3" :key="n" class="card bg-base-100 mb-3">
                        <div class="card-body gap-3">
                            <div class="skeleton h-4 w-32 rounded" />
                            <div class="skeleton h-24 w-full rounded" />
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div v-for="status in statuses" :key="status.id" class="mb-3">
                        <StatusCard
                            :status="status"
                            @status-deleted="statuses = statuses.filter((s) => s.id !== $event)"
                            @status-updated="statuses = statuses.map((s) => (s.id === $event.id ? $event : s))"
                        />
                    </div>

                    <!-- Load more -->
                    <div v-if="showMore" class="text-center my-4">
                        <button
                            class="btn btn-primary btn-sm"
                            :disabled="loadingMore"
                            @click="fetchStatuses(currentPage + 1, true)"
                        >
                            <Loading v-if="loadingMore" />
                            <ChevronDown v-else class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- End of feed -->
                    <div v-if="!showMore && statuses.length" class="text-center py-8 text-base-content/30 text-sm">
                        Final stop. All change, please!
                    </div>
                </template>

                <!-- Loading more skeletons -->
                <template v-if="loadingMore">
                    <div v-for="n in 2" :key="n" class="card bg-base-100 mb-3">
                        <div class="card-body gap-3">
                            <div class="skeleton h-4 w-32 rounded" />
                            <div class="skeleton h-24 w-full rounded" />
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <!-- Disclaimer -->
        <div class="text-center mt-8 mb-4">
            <p class="text-xs text-base-content/40">
                {{ trans('events.disclaimer.organizer') }}
            </p>
            <p class="text-xs text-base-content/40">{{ trans('events.disclaimer.source') }}</p>
            <p class="text-xs text-base-content/40">
                {{ trans('events.disclaimer.warranty') }}
            </p>
        </div>
    </AppLayout>
</template>

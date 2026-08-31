<script setup lang="ts">
import { ChevronDown, Route } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import profileBg from '../../../images/covers/profile-background.png';
import { Api, StatusResource, StopoverResource, UserResource, ViewUserForbiddenReason } from '../../../types/Api.gen';
import { getDepartureForStatus } from '../../../vue/helpers/DateTimeHelper';
import { useUserStore } from '../../../vue/stores/user';
import StatusCard from '../../components/Status/StatusCard.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import BioCard from './partials/BioCard.vue';
import Header from './partials/Header.vue';
import InvisibleReasons from './partials/InvisibleReasons.vue';
import StatsCard from './partials/StatsCard.vue';

const route = useRoute();
const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;
const authUser = useUserStore();

const username = computed(() => route.params.username as string);

const userData = ref<UserResource | null>(null);
const userInvisibleReason = ref<ViewUserForbiddenReason | null>(null);
const statuses = ref<StatusResource[]>([]);
const stopovers = ref<Record<string, StopoverResource[]>>({});
const loadingUser = ref(true);
const loadingStatuses = ref(true);
const showMore = ref(false);
const currentPage = ref(1);

const showPoints = computed(() => !!(userData.value?.pointsEnabled || authUser.user?.pointsEnabled));

const isOwnProfile = computed(() => authUser.authenticated && authUser.getId === userData.value?.id);

async function fetchUser() {
    loadingUser.value = true;
    try {
        const res = await api.user.showUser(username.value);
        userData.value = res.data.data;
    } catch (err: unknown) {
        if (err?.status === 403) {
            userInvisibleReason.value = err.error?.meta?.reason ?? null;
            userData.value = err.error?.meta?.user ?? null;
        } else {
            notyf?.error(trans('generic.error'));
        }
    } finally {
        loadingUser.value = false;
    }
}

async function fetchStopovers() {
    if (!statuses.value.length) return;
    const tripIds = [...new Set(statuses.value.map((s) => s.checkin.trip.toString()))];
    try {
        const res = await api.stopovers.getStopOvers(tripIds.join(','));
        stopovers.value = { ...stopovers.value, ...(res.data?.data ?? {}) };
    } catch {
        // stopovers are best-effort
    }
}

async function fetchStatuses(append = false) {
    loadingStatuses.value = true;
    const nextPage = append ? currentPage.value + 1 : 1;
    try {
        const res = await api.user.getStatusesForUser(username.value, { page: nextPage });
        const list: StatusResource[] = res.data?.data ?? [];
        if (append) {
            statuses.value.push(...list);
        } else {
            statuses.value = list;
        }
        currentPage.value = res.data?.meta?.current_page ?? nextPage;
        const next = res.data?.links?.next ?? null;
        showMore.value = next !== null;
        await fetchStopovers();
    } catch (err: unknown) {
        if (err?.status !== 403) {
            notyf?.error(trans('generic.error'));
        }
    } finally {
        loadingStatuses.value = false;
    }
}

function isNewDay(index: number): boolean {
    if (index === 0) return true;
    const prev = getDepartureForStatus(statuses.value[index - 1]).dateTime;
    const curr = getDepartureForStatus(statuses.value[index]).dateTime;
    return !curr.hasSame(prev, 'day');
}

function dayLabel(s: StatusResource): string {
    return getDepartureForStatus(s).dateTime.toLocaleString(DateTime.DATE_HUGE);
}

function statsDailyHref(s: StatusResource): string {
    return `/statistics/daily/${getDepartureForStatus(s).dateTime.toISODate()}`;
}

watch(username, () => {
    userData.value = null;
    userInvisibleReason.value = null;
    statuses.value = [];
    stopovers.value = {};
    currentPage.value = 1;
    fetchUser();
    fetchStatuses();
});

onMounted(() => {
    fetchUser();
    fetchStatuses();
});
</script>

<template>
    <AppLayout :legacy="true" :fullscreen="true">
        <div
            class="w-full bg-primary text-primary-content bg-center bg-cover px-4 sm:px-6 lg:px-8 py-6"
            :style="{ backgroundImage: `url(${profileBg})` }"
        >
            <div class="max-w-7xl mx-auto">
                <!-- Loading skeleton -->
                <template v-if="loadingUser">
                    <div class="flex gap-4 items-end">
                        <div class="skeleton w-20 h-20 rounded-full shrink-0 opacity-40" />
                        <div class="flex flex-col gap-2 flex-1 pb-1">
                            <div class="skeleton h-6 w-40 rounded opacity-40" />
                            <div class="skeleton h-4 w-24 rounded opacity-40" />
                        </div>
                    </div>
                </template>

                <Header v-else-if="userData" :user-data="userData" @update:user-data="userData = $event" />
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-[280px_1fr] gap-6">
                <aside class="flex flex-col gap-4 min-w-0">
                    <StatsCard
                        v-if="userData && userInvisibleReason !== ViewUserForbiddenReason.YOU_ARE_BLOCKED"
                        :user-data="userData"
                        :show-points="showPoints"
                    />

                    <BioCard
                        v-if="userData && userInvisibleReason !== ViewUserForbiddenReason.YOU_ARE_BLOCKED"
                        :user-data="userData"
                    />
                </aside>

                <section class="min-w-0">
                    <InvisibleReasons
                        v-if="!loadingUser && userData?.userInvisibleToMe"
                        :user-data="userData"
                        :user-invisible-reason="userInvisibleReason"
                    />

                    <template v-else-if="userData">
                        <!-- Loading skeletons -->
                        <template v-if="loadingStatuses && !statuses.length">
                            <div v-for="n in 3" :key="n" class="card bg-base-100 mb-3">
                                <div class="card-body gap-3">
                                    <div class="skeleton h-4 w-32 rounded" />
                                    <div class="skeleton h-20 w-full rounded" />
                                </div>
                            </div>
                        </template>

                        <!-- Empty state -->
                        <div v-else-if="!loadingStatuses && !statuses.length" class="card bg-base-100 shadow-sm">
                            <div class="card-body text-center py-10 text-base-content/50">
                                <template v-if="(userData.totalDistance ?? 0) > 0">
                                    {{ trans('profile.no-visible-statuses', { username: userData.displayName }) }}
                                </template>
                                <template v-else>
                                    {{ trans('profile.no-statuses', { username: userData.displayName }) }}
                                </template>
                            </div>
                        </div>

                        <!-- Status list -->
                        <template v-else>
                            <template v-for="(s, i) in statuses" :key="s.id">
                                <p
                                    v-if="isNewDay(i)"
                                    class="text-sm font-medium text-base-content/50 mt-4 mb-2 first:mt-0 flex items-center gap-2"
                                >
                                    {{ dayLabel(s) }}
                                    <a
                                        v-if="isOwnProfile"
                                        :href="statsDailyHref(s)"
                                        class="text-primary hover:text-primary/70"
                                        :aria-label="trans('stats')"
                                    >
                                        <Route class="w-4 h-4 inline" />
                                    </a>
                                </p>
                                <div class="mb-3">
                                    <StatusCard
                                        :status="s"
                                        :stopovers="stopovers[s.checkin.trip.toString()]"
                                        @status-deleted="statuses = statuses.filter((x) => x.id !== $event)"
                                        @status-updated="
                                            statuses = statuses.map((x) => (x.id === $event.id ? $event : x))
                                        "
                                    />
                                </div>
                            </template>

                            <!-- Load more -->
                            <div v-if="showMore" class="text-center my-4">
                                <button
                                    class="btn btn-primary btn-sm"
                                    :class="{ loading: loadingStatuses }"
                                    :disabled="loadingStatuses"
                                    @click="fetchStatuses(true)"
                                >
                                    <ChevronDown v-if="!loadingStatuses" class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- End of feed -->
                            <div
                                v-if="!loadingStatuses && !showMore && statuses.length"
                                class="text-center py-8 text-base-content/30 text-sm"
                            >
                                <p>Final stop. All change, please!</p>
                            </div>
                        </template>
                    </template>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

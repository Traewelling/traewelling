<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Duration } from 'luxon';
import { Notyf } from 'notyf';
import { computed, ref } from 'vue';
import { Api, StatusResource, StopoverResource, UserResource } from '../../../types/Api.gen';
import LoadingSkeletonRows from '../../components/Loader/LoadingSkeletonRows.vue';
import { IconHelper } from '../../helpers/IconHelper';
import { useUserStore } from '../../stores/user';
import Statuses from './partials/Statuses.vue';

const props = defineProps<{ username: string }>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = new Notyf({ position: { x: 'right', y: 'bottom' } });

// -------------------------
// State
// -------------------------
const userData = ref<UserResource | null>(null);
const statuses = ref<StatusResource[]>([]);
const stopovers = ref<Record<string, StopoverResource[]>>({});
const loadingUser = ref(true);
const loadingStatuses = ref(true);

const showMore = ref(false);
const currentPage = ref(1);
const lastPage = ref<number | null>(null);

const authUser = useUserStore();

function fetchUser() {
    loadingUser.value = true;
    try {
        api.user.showUser(props.username).then((res) => {
            if (!res.ok) throw new Error(`HTTP ${res.status} - ${res.statusText}`);
            res.json().then((user) => {
                userData.value = user.data;
            });
        });
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
    } catch (err: any) {
        notyf.error('Error fetching user: ' + err.message);
    } finally {
        loadingUser.value = false;
    }
}

function fetchStatuses(append = false) {
    loadingStatuses.value = true;

    const nextPage = append ? currentPage.value + 1 : 1;

    try {
        api.user.getStatusesForUser(props.username).then((res) => {
            if (!res.ok) throw new Error(`HTTP ${res.status} - ${res.statusText}`);
            res.json().then((json) => {
                const list: StatusResource[] = json.data ?? [];
                if (append) statuses.value.push(...list);
                else statuses.value = list;

                const meta = json.meta ?? {};
                currentPage.value = meta.current_page ?? nextPage;
                lastPage.value = meta.last_page ?? null;

                if (lastPage.value === null) {
                    showMore.value = !!json.links?.next;
                } else {
                    showMore.value = currentPage.value < lastPage.value;
                }

                fetchStopovers();
            });
        });

        // eslint-disable-next-line @typescript-eslint/no-explicit-any
    } catch (err: any) {
        notyf.error('Error fetching statuses: ' + err.message);
    } finally {
        loadingStatuses.value = false;
    }
}

async function fetchStopovers() {
    if (!statuses.value.length) return;
    const tripIds = [...new Set(statuses.value.map((s) => s.checkin.trip.toString()))];
    if (!tripIds.length) return;

    try {
        const res = await api.stopovers.getStopOvers(tripIds.join(','));
        const json = await res.json();
        stopovers.value = json.data ?? {};
    } catch (err) {
        console.error('Stopovers error', err);
    }
}

// Metrics
const kmDisplay = computed(() => {
    const meters = userData.value?.trainDistance ?? 0;
    const km = meters / 1000;
    return km.toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 });
});
const durationParts = computed(() => {
    const minutes = userData.value?.trainDuration ?? 0;
    const dur = Duration.fromObject({ minutes }).shiftTo('days', 'hours', 'minutes');
    return { d: dur.days ?? 0, h: dur.hours ?? 0, m: Math.round(dur.minutes ?? 0) };
});
const showPoints = computed(() => !!(userData.value?.pointsEnabled || authUser.user?.pointsEnabled));

const mergedLinks = computed(() => {
    const links = [...(userData.value?.profileLinks ?? [])];
    const hasMastodon = links.some((l) => (l.name || '').toUpperCase() === 'MASTODON');
    if (userData.value?.mastodonUrl && !hasMastodon) {
        links.push({ name: 'mastodon', url: userData.value.mastodonUrl });
    }
    return links;
});

fetchUser();
fetchStatuses(false);
</script>

<template>
    <div class="row mt-4">
        <!-- LEFT COLUMN -->
        <div class="col">
            <!-- Stats card -->
            <div class="card mb-3 shadow-sm rounded">
                <div class="card-body">
                    <LoadingSkeletonRows v-if="loadingUser" :columns="3" :rows="1" />
                    <div v-else class="row text-center gx-2 gy-3">
                        <div class="col">
                            <i class="fa fa-route fa-2x text-trwl" />
                            <div class="h5 mb-0">
                                {{ kmDisplay }}
                                <small class="text-muted">km</small>
                            </div>
                        </div>
                        <div class="col">
                            <i class="fa fa-stopwatch fa-2x text-trwl" />
                            <div class="h5 mb-0">
                                {{ durationParts.d }}<small class="text-muted">d</small>&nbsp; {{ durationParts.h
                                }}<small class="text-muted">h</small>&nbsp; {{ durationParts.m
                                }}<small class="text-muted">min</small>
                            </div>
                        </div>
                        <div v-if="showPoints" class="col">
                            <i class="fa fa-dice-d20 fa-2x text-trwl" />
                            <div class="h5 mb-0">
                                {{ userData?.points ?? 0 }}
                                <small class="text-muted">{{ trans('profile.points-abbr') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bio & links -->
            <div v-if="userData?.bio || mergedLinks.length" class="card mb-3 shadow-sm rounded">
                <div class="card-body">
                    <p v-if="userData?.bio" class="text-muted fst-italic m-0">
                        <i class="fa fa-quote-left me-2" />
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <span class="profile-bio" v-html="userData.bio" />
                    </p>
                    <div v-if="mergedLinks.length" class="d-flex justify-content-center flex-wrap gap-3 mt-4">
                        <a
                            v-for="(link, i) in mergedLinks"
                            :key="i"
                            :href="link.url"
                            class="text-muted fs-4"
                            :aria-label="link.name"
                            target="_blank"
                            rel="me"
                        >
                            <i :class="IconHelper.getLinkIcon(link.name) || 'fa-link'" class="fa-solid" />
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-lg-7">
            <Statuses
                v-if="userData"
                :statuses="statuses"
                :stopovers="stopovers"
                :user-data="userData"
                :loading-statuses="loadingStatuses"
                :show-more="showMore"
                :last-page="lastPage"
                :current-page="currentPage"
                @fetch-more-statuses="fetchStatuses(true)"
            />
        </div>
    </div>
</template>

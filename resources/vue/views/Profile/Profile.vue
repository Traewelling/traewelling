<script setup lang="ts">
import { Notyf } from 'notyf';
import { ref } from 'vue';
import { Api, StatusResource, StopoverResource, UserResource, ViewUserForbiddenReason } from '../../../types/Api.gen';
import LoadingSkeletonRows from '../../components/Loader/LoadingSkeletonRows.vue';
import BioCard from './partials/BioCard.vue';
import Header from './partials/Header.vue';
import ProfileNotVisibleInfo from './partials/ProfileNotVisibleInfo.vue';
import StatisticsCard from './partials/StatisticsCard.vue';
import Statuses from './partials/Statuses.vue';

const props = defineProps<{ username: string }>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = new Notyf({ position: { x: 'right', y: 'bottom' } });

// -------------------------
// State
// -------------------------
const userData = ref<UserResource | null>(null);
const userInvisibleReason = ref<ViewUserForbiddenReason | null>(null);
const statuses = ref<StatusResource[]>([]);
const stopovers = ref<Record<string, StopoverResource[]>>({});
const loadingUser = ref(true);
const loadingStatuses = ref(true);

const showMore = ref(false);
const currentPage = ref(1);
const lastPage = ref<number | null>(null);

function fetchUser() {
    loadingUser.value = true;
    api.user
        .showUser(props.username)
        .then((res) => {
            res.json().then((user) => {
                userData.value = user.data;
            });
            loadingUser.value = false;
        })
        .catch((err) => {
            if (err.status === 403) {
                userInvisibleReason.value = err.error?.meta?.reason || null;
                userData.value = err.error?.meta?.user || null;
            } else {
                notyf.error('Error fetching user: ' + err.message);
            }
            loadingUser.value = false;
        });
}

function fetchStatuses(append = false) {
    loadingStatuses.value = true;

    const nextPage = append ? currentPage.value + 1 : 1;

    api.user
        .getStatusesForUser(props.username)
        .then((res) => {
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
                loadingStatuses.value = false;
            });
        })
        .catch((err) => {
            notyf.error('Error fetching statuses: ' + err.error?.message);
            loadingStatuses.value = false;
        });
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
fetchUser();
fetchStatuses(false);
</script>

<template>
    <Header v-if="userData" :user-data="userData" />
    <div class="container">
        <div class="row mt-4">
            <!-- LEFT COLUMN -->
            <LoadingSkeletonRows v-if="loadingUser" :count="3" :row-height="90" class="mb-3 col" />
            <div v-else class="col">
                <StatisticsCard v-if="userData" :user-data="userData" :loading-user="loadingUser" />

                <BioCard v-if="userData" :user-data="userData" />
            </div>

            <div class="col-md-8 col-lg-7">
                <ProfileNotVisibleInfo
                    v-if="!loadingUser && userData !== null && userData.userInvisibleToMe"
                    :user-data="userData"
                />
                <Statuses
                    v-else-if="userData"
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
    </div>
</template>

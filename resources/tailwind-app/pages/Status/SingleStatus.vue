<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime as LuxonDateTime } from 'luxon';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    Api,
    CheckinSuccessResource,
    StatusResource,
    StopoverResource,
    TicketResource,
    UserAuthResource,
    UserResource,
} from '../../../types/Api.gen';
import { checkinSuccessStore } from '../../../vue/stores/checkinSuccess';
import { useUserStore } from '../../../vue/stores/user';
import CheckinSuccessBanner from '../../components/Checkin/CheckinSuccessBanner.vue';
import StatusCard from '../../components/Status/StatusCard.vue';
import StatusTags from '../../components/Status/StatusTags.vue';
import StatusTicket from '../../components/Status/StatusTicket.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const checkinSuccess = checkinSuccessStore();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const statusId = computed(() => Number(route.params.id));

const loading = ref(true);
const status = ref<StatusResource | null>(null);
const stopovers = ref<StopoverResource[]>([]);
const likedBy = ref<UserResource[]>([]);
const pageError = ref<'403' | '404' | null>(null);
const checkinResult = ref<CheckinSuccessResource | null>(null);

const formattedDate = computed(() => {
    if (!status.value) return '';
    const dep = status.value.train.origin.departurePlanned ?? status.value.train.origin.departure;
    if (!dep) return '';
    return LuxonDateTime.fromISO(dep).toLocaleString({
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

async function fetchStatus() {
    loading.value = true;
    pageError.value = null;
    try {
        const res = await api.status.getSingleStatus(statusId.value);
        const json = await res.json();
        status.value = json.data as StatusResource;
        const stored = checkinSuccess.checkinResponse;
        if (stored?.status?.id === status.value.id) {
            checkinResult.value = stored;
            checkinSuccess.reset();
        }
        await fetchStopovers();
    } catch (e: unknown) {
        const err = e as { status?: number };
        if (err?.status === 404) pageError.value = '404';
        else if (err?.status === 403) pageError.value = '403';
        else pageError.value = '404';
    } finally {
        loading.value = false;
    }
}

async function fetchStopovers() {
    if (!status.value) return;
    try {
        const res = await api.stopovers.getStopOvers(status.value.train.trip.toString());
        const json = await res.json();
        stopovers.value = json.data?.[status.value.train.trip] ?? [];
    } catch {
        // stopovers are best-effort
    }
}

async function fetchLikes() {
    try {
        const res = await api.status.getLikesForStatus(statusId.value);
        likedBy.value = res.data.data ?? [];
    } catch {
        // likes best-effort
    }
}

function userAuthToUserResource(u: UserAuthResource): UserResource {
    return { id: u.id, username: u.username, profilePicture: u.profilePicture } as UserResource;
}

function onStatusLiked() {
    if (!status.value || !userStore.user) return;
    status.value.likes++;
    status.value.liked = true;
    likedBy.value.push(userAuthToUserResource(userStore.user));
}

function onStatusUnliked() {
    if (!status.value || !userStore.user) return;
    status.value.likes--;
    status.value.liked = false;
    likedBy.value = likedBy.value.filter((l) => l.id !== userStore.user?.id);
}

function onStatusDeleted() {
    router.push('/dashboard');
}

onMounted(() => {
    fetchStatus();
    fetchLikes();
});
</script>

<template>
    <AppLayout>
        <!-- Loading skeleton -->
        <template v-if="loading">
            <div class="max-w-2xl mx-auto space-y-4">
                <div class="skeleton h-6 w-40 rounded" />
                <div class="skeleton h-64 w-full rounded-xl" />
            </div>
        </template>

        <!-- Error states -->
        <template v-else-if="pageError">
            <div class="max-w-2xl mx-auto text-center py-16">
                <template v-if="pageError === '403'">
                    <p class="text-4xl font-bold text-base-content/20 mb-4">403</p>
                    <p class="text-base-content/60">{{ trans('error.403.description') }}</p>
                </template>
                <template v-else>
                    <p class="text-4xl font-bold text-base-content/20 mb-4">404</p>
                    <p class="text-base-content/60">{{ trans('error.404.description') }}</p>
                </template>
            </div>
        </template>

        <!-- Status page -->
        <template v-else-if="status">
            <!-- Checkin success banner -->
            <CheckinSuccessBanner v-if="checkinResult" :data="checkinResult" class="max-w-5xl mx-auto" />

            <!-- Date heading -->
            <h2 class="text-base font-semibold text-base-content/60 mb-3 max-w-5xl mx-auto">
                {{ formattedDate }}
            </h2>

            <div class="max-w-5xl mx-auto flex flex-col lg:flex-row gap-4 items-start">
                <!-- Left: status card + attribution + likes -->
                <div
                    class="w-full min-w-0"
                    :class="
                        userStore.isClosedBeta && status.userDetails.id === userStore.user?.id
                            ? 'lg:flex-1'
                            : 'lg:max-w-2xl lg:mx-auto'
                    "
                >
                    <StatusCard
                        :status="status"
                        :stopovers="stopovers"
                        :show-map="true"
                        @status-liked="onStatusLiked"
                        @status-unliked="onStatusUnliked"
                        @status-deleted="onStatusDeleted"
                        @status-updated="status = $event"
                    />

                    <!-- Tags -->
                    <div v-if="status.tags?.length || status.userDetails.id === userStore.user?.id" class="mt-3">
                        <StatusTags
                            :status-id="status.id"
                            :tags="status.tags ?? []"
                            :editable="status.userDetails.id === userStore.user?.id"
                        />
                    </div>

                    <!-- Data attribution -->
                    <p
                        v-if="status.checkin?.dataSource?.attribution"
                        class="text-xs text-base-content/40 mt-2 text-center"
                    >
                        {{ status.checkin.dataSource.attribution }}
                    </p>

                    <!-- Likes panel -->
                    <div v-if="likedBy.length" class="card bg-base-100 mt-4">
                        <div class="card-body p-0">
                            <div
                                v-for="user in likedBy"
                                :key="user.id"
                                class="flex items-center gap-3 px-4 py-2 border-b border-base-200 last:border-0"
                            >
                                <a :href="`/@${user.username}`" class="shrink-0">
                                    <img
                                        :src="user.profilePicture"
                                        :alt="user.username"
                                        class="w-8 h-8 rounded-full object-cover"
                                        loading="lazy"
                                    />
                                </a>
                                <div class="text-sm">
                                    <a :href="`/@${user.username}`" class="link link-hover font-medium">
                                        {{ user.username }}
                                    </a>
                                    <span class="text-base-content/50 ml-1">
                                        {{
                                            user.id === status.userDetails.id
                                                ? trans('user.liked-own-status')
                                                : trans('user.liked-status')
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: ticket card (closed-beta only, own status only) -->
                <div
                    v-if="userStore.isClosedBeta && status.userDetails.id === userStore.user?.id"
                    class="w-full lg:w-80 shrink-0"
                >
                    <StatusTicket
                        :status-id="status.id"
                        :ticket="(status.ticket as TicketResource | null) ?? null"
                        :departure-planned="status.checkin.origin.departurePlanned"
                        :trip-distance="status.checkin.distance"
                        :trip-duration="status.checkin.duration"
                        @ticket-changed="status.ticket = $event"
                    />
                </div>
            </div>
        </template>
    </AppLayout>
</template>

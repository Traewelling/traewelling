<script setup lang="ts">
import { ref } from 'vue';
import { Api, StatusResource, StopoverResource, UserAuthResource, UserResource } from '../../types/Api.gen';
import StatusCard from '../components/Status/StatusCard.vue';
import CheckinSuccessHelper from '../components/CheckinSuccessHelper.vue';
import { trans } from 'laravel-vue-i18n';
import { getDepartureForStatus } from '../helpers/DateTimeHelper';
import { DateTime } from 'luxon';
import { useUserStore } from '../stores/user';
import TagHelper from '../components/TagHelper.vue';
import LoadingSkeletonRows from '../components/Loader/LoadingSkeletonRows.vue';
import Error403 from '../components/Errors/403.vue';
import Error404 from '../components/Errors/404.vue';

const loading = ref(true);
const status = ref<StatusResource | null>(null);
const likedBy = ref<UserResource[]>([]);
const statusId = parseInt(window.location.pathname.split('/').pop() || 0);
const user = useUserStore();
const pageError = ref<'403' | '404' | null>(null);
const stopovers = ref<StopoverResource[]>([]);

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

function fetchLikes() {
    if (!statusId) {
        console.error('Status ID not found in URL');
        return;
    }
    api.status.getLikesForStatus(statusId).then((response) => {
        likedBy.value = response.data.data ?? [];
    }).catch((error) => {
        console.error('Error fetching likes:', error);
    });
}

function fetchStopovers() {
    if (!status.value) {
        console.error('Status not loaded yet');
        return;
    }
    api.stopovers.getStopOvers(status.value!.train.trip.toString()).then((response) => {
        stopovers.value = response.data.data?.[status.value!.train.trip] ?? [];
    }).catch((error) => {
        console.error('Error fetching stopovers:', error);
    });
}

function fetchStatus() {
    if (!statusId) {
        console.error('Status ID not found in URL');
        loading.value = false;
        pageError.value = '404';
        return;
    }

    api.status.getSingleStatus(statusId).then((response) => {
        response.json().then((data) => {
            loading.value = false;
            status.value = data.data;
            fetchStopovers();
        });
    }).catch((error) => {
        loading.value = false;
        if (error.status === 404) {
            pageError.value = '404';
        } else if (error.status === 403) {
            pageError.value = '403';
        }
        console.error('Error fetching status:', error);
    });
}

function addSelfToLikes() {
    if (!status.value || !user.user) return;
    status.value.likes++;
    status.value.liked = true;
    likedBy.value.push(userAuthToUserResource(user.user));
}

function removeSelfFromLikes() {
    if (!status.value || !user.user) return;
    status.value.likes--;
    status.value.liked = false;
    likedBy.value = likedBy.value.filter(like => like.id !== user.user?.id);
}

function userAuthToUserResource(user: UserAuthResource): UserResource {
    return {
        id: user.id,
        username: user.username,
        profilePicture: user.profilePicture,
    } as UserResource;
}

fetchStatus();
fetchLikes();
</script>

<template>
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <template v-if="loading">
                <LoadingSkeletonRows :row-height="30" :rows="1" />
                <LoadingSkeletonRows :row-height="600" :rows="1" />
            </template>

            <template v-else>
                <template v-if="status">
                    <CheckinSuccessHelper v-if="user.user && status.userDetails.id === user.user.id" />
                    <h2 class="fs-5">
                        {{ getDepartureForStatus(status).toLocaleString(DateTime.DATE_HUGE) }}
                    </h2>
                    <StatusCard
                        :status
                        :show-map="true"
                        :authenticated-user="user.user"
                        :stopovers
                        @status-liked="addSelfToLikes()"
                        @status-unliked="removeSelfFromLikes()"
                    />
                    <TagHelper :status-id="status.id" :editable="status.userDetails.id === user.user?.id" class="mb-3" />

                    <div v-show="likedBy.length" class="card">
                        <div v-for="like in likedBy" :key="like.id" class="card-footer text-muted clearfix">
                            <a :href="`/@${like.username}`" class="float-start">
                                <img
                                    loading="lazy"
                                    :src="like.profilePicture"
                                    class="profile-image float-start me-2"
                                    :alt="trans('settings.picture')"
                                >
                            </a>
                            <span class="like-text pl-2 d-table-cell">
                                <a :href="`/@${like.username}`">
                                    {{ like.username }}
                                </a>
                                <span v-if="like.id === status.userDetails.id">
                                    &thinsp;{{ trans('user.liked-own-status') }}
                                </span>
                                <span v-else>
                                    &thinsp;{{ trans('user.liked-status') }}
                                </span>
                            </span>
                        </div>
                    </div>
                </template>

                <template v-else-if="pageError === '403'">
                    <Error403 :status-id="statusId" />
                </template>
                <template v-else-if="pageError === '404'">
                    <Error404 />
                </template>
            </template>
        </div>
    </div>
</template>

<style scoped lang="scss">
.profile-image {
  height: 2em;
  border-radius: 50%;
}
</style>

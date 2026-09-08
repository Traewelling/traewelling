<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { computed, PropType, ref } from 'vue';
import { Api, StatusResource } from '../../../../types/Api.gen';
import { Dtm } from '../../../helpers/DateTime';
import { IconHelper } from '../../../helpers/IconHelper';
import { useUserStore } from '../../../stores/user';
import StatusContextMenu from './StatusContextMenu.vue';

const props = defineProps({
    status: {
        type: Object as PropType<StatusResource>,
        required: true,
    },
});
const emit = defineEmits(['status-liked', 'status-unliked', 'status-deleted', 'status-deleting', 'status-updated']);
const api = new Api({ baseUrl: window.location.origin + '/api' });
const user = useUserStore();
const likes = ref(0);

function like() {
    if (!user.user) {
        return;
    }

    if (props.status.liked) {
        api.status.removeLikeFromStatus(props.status.id).then(() => {
            // eslint-disable-next-line vue/no-mutating-props
            props.status.liked = false;
            likes.value--;
        });

        emit('status-unliked', props.status.id);
    } else {
        api.status.addLikeToStatus(props.status.id).then(() => {
            // eslint-disable-next-line vue/no-mutating-props
            props.status.liked = true;
            likes.value++;
        });

        emit('status-liked', props.status.id);
    }
}

const createdAt = computed(() => {
    const date = Dtm.fromISO(props.status.createdAt);
    if (date.dateTime.diffNow().as('days') < -1) {
        return trans('dates.-on-') + ' ' + date.toLocaleString(DateTime.TIME_SIMPLE);
    } else {
        return date.toRelative();
    }
});

function deleteStatus() {
    emit('status-deleting', true);
    api.status
        .destroySingleStatus(props.status.id)
        .then(() => {
            emit('status-deleted', props.status.id);
        })
        .catch((error) => {
            emit('status-deleting', false);
            console.error('Error deleting status:', error);
        });
}

likes.value = props.status.likes || 0;
</script>

<template>
    <div class="card-footer text-muted px-3 px-md-4">
        <ul class="list-inline float-end">
            <template v-if="status.isLikable">
                <li class="like-text list-inline-item me-1">
                    <a
                        href="#"
                        class="like-heart"
                        :class="{
                            'fas fa-heart': status.liked,
                            'far fa-heart': !status.liked,
                            peach: status.userDetails.id === 18574,
                        }"
                        @click.prevent="like()"
                    >
                        <span class="sr-only">{{ trans('action.like') }}</span>
                    </a>
                </li>
                <li class="like-text list-inline-item">
                    <span class="pl-1" :class="{ 'd-none': likes <= 0 }">
                        {{ likes }}
                    </span>
                </li>
            </template>
            <li class="like-text list-inline-item">
                <i
                    class="fas"
                    :class="IconHelper.getVisibilityIcon(status.visibility)"
                    aria-hidden="true"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    :title="IconHelper.getVisibilityTooltip(status.visibility)"
                />
            </li>
            <li class="like-text list-inline-item">
                <StatusContextMenu
                    :status
                    @confirm-delete="deleteStatus()"
                    @status-updated="emit('status-updated', $event)"
                />
            </li>
        </ul>

        <ul class="list-inline">
            <li class="d-lg-none list-inline-item">
                <a :href="`/@${status.userDetails.username}`">
                    <img
                        loading="lazy"
                        :src="status.userDetails.profilePicture"
                        class="profile-image"
                        :alt="status.userDetails.username"
                    />
                </a>
            </li>
            <li class="list-inline-item me-1">
                <a :href="`/@${status.userDetails.username}`" class="username">
                    {{ user.user?.id !== status.userDetails.id ? status.userDetails.username : trans('user.you') }}
                </a>
            </li>
            <li class="list-inline-item">
                <a :href="`/status/${status.id}`" class="status-link">
                    {{ createdAt }}
                </a>
            </li>
        </ul>
    </div>
</template>
<style scoped>
.like-heart {
    color: #e74c3c;
    cursor: pointer;
}

:root.dark {
    .like-heart {
        color: #e74c3c;
    }
}
</style>

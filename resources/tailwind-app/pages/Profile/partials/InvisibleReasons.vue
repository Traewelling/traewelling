<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { UserResource, ViewUserForbiddenReason } from '../../../../types/Api.gen';

defineProps({
    userData: {
        type: Object as () => UserResource,
        required: true,
    },
    userInvisibleReason: {
        type: Object as () => ViewUserForbiddenReason | null,
        required: false,
        default: null,
    },
});
</script>

<template>
    <div v-if="userData.muted" class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-10">
            <h3 class="text-lg font-semibold mb-1">{{ trans('user.muted.heading') }}</h3>
            <p class="text-base-content/60">
                {{ trans('user.muted.text', { username: userData.username }) }}
            </p>
        </div>
    </div>

    <div v-else-if="userData.blocked" class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-10">
            <p class="text-lg font-semibold">
                {{ trans('profile.youre-blocking-text', { username: userData.username }) }}
            </p>
            <p class="text-base-content/60 text-sm mt-1">
                {{ trans('profile.youre-blocking-information-text') }}
            </p>
        </div>
    </div>

    <div v-else-if="userInvisibleReason === ViewUserForbiddenReason.YOU_ARE_BLOCKED" class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-10">
            <p class="text-lg font-semibold">{{ trans('profile.private-profile-text') }}</p>
            <p class="text-base-content/60 text-sm mt-1">
                {{ trans('profile.no-visible-statuses', { username: userData.username }) }}
            </p>
        </div>
    </div>

    <div v-else-if="userData.privateProfile && !userData.following" class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-10">
            <p class="text-lg font-semibold">{{ trans('profile.private-profile-text') }}</p>
            <p class="text-base-content/60 text-sm mt-1">
                {{
                    trans('profile.private-profile-information-text', {
                        username: userData.username,
                        request: trans('profile.follow_req'),
                    })
                }}
            </p>
        </div>
    </div>

    <div v-else class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-10">
            <p class="text-lg font-semibold">{{ trans('profile.private-profile-text') }}</p>
            <p class="text-base-content/60 text-sm mt-1">
                {{ trans('profile.no-visible-statuses', { username: userData.username }) }}
            </p>
        </div>
    </div>
</template>

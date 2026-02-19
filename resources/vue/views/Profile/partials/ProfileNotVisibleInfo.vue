<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { UserResource } from '../../../../types/Api.gen';

defineProps<{
    userData: UserResource;
}>();
</script>

<template>
    <template v-if="userData.muted">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-7 text-center mb-5">
                <header>
                    <h3>{{ trans('user.muted.heading') }}</h3>
                </header>
                <h5>{{ trans('user.muted.text', { username: userData.username }) }}</h5>

                <!-- todo: mute-button -->
            </div>
        </div>
    </template>
    <template v-else-if="userData.blocked">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-7 text-center mb-5">
                <span class="fs-3">{{ trans('profile.youre-blocking-text', { username: userData.username }) }}</span>
                <br />
                <span class="fs-5">
                    {{ trans('profile.youre-blocking-information-text') }}
                </span>
            </div>
        </div>
    </template>
    <template v-else-if="userData.privateProfile && !userData.following">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-7 text-center mb-5">
                <span class="fs-3">{{ trans('profile.private-profile-text') }}</span>
                <br />
                <span class="fs-5">
                    {{
                        trans('profile.private-profile-information-text', {
                            username: userData.username,
                            request: trans('profile.follow_req'),
                        })
                    }}
                </span>
            </div>
        </div>
    </template>
    <template v-else>
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-7 text-center mb-5">
                <span class="fs-3">{{ trans('profile.private-profile-text') }}</span>
                <br />
                <span class="fs-5">
                    {{ trans('profile.no-visible-statuses', { username: userData.username }) }}
                </span>
            </div>
        </div>
    </template>
</template>

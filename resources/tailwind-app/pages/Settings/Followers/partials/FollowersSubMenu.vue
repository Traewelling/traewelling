<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
</script>

<template>
    <h2 class="text-xl font-bold mb-2">{{ trans('settings.social.manage') }}</h2>
    <div role="tablist" class="tabs tabs-box flex-wrap">
        <router-link to="/settings/followers" class="tab" active-class="tab-active">
            {{ trans('menu.settings.myFollower') }}
        </router-link>
        <router-link to="/settings/follow-requests" class="tab" active-class="tab-active">
            {{ trans('menu.settings.follower-requests') }}
        </router-link>
        <router-link to="/settings/followings" class="tab" active-class="tab-active">
            {{ trans('menu.settings.followings') }}
        </router-link>
        <router-link to="/settings/blocks" class="tab" active-class="tab-active">
            {{ trans('user.blocked.heading2') }}
        </router-link>
        <router-link to="/settings/mutes" class="tab" active-class="tab-active">
            {{ trans('user.muted.heading2') }}
        </router-link>
    </div>
</template>

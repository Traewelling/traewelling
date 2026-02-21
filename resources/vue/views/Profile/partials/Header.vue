<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { UserResource, ViewUserForbiddenReason } from '../../../../types/Api.gen';
import BlockButton from '../../../components/BlockButton.vue';
import FollowButton from '../../../components/FollowButton.vue';
import MuteButton from '../../../components/MuteButton.vue';
import { useUserStore } from '../../../stores/user';

const authUser = useUserStore();

defineProps<{
    userData: UserResource;
    userInvisibleReason?: ViewUserForbiddenReason | null;
}>();
</script>

<template>
    <div class="px-md-4 py-md-5 pt-2 pb-0 mt-n4 profile-banner">
        <div class="container">
            <img
                :alt="trans('settings.picture')"
                :src="userData.profilePicture"
                class="float-end img-thumbnail rounded-circle img-fluid profile-picture"
            />
            <div class="text-white px-md-4">
                <h1 class="card-title h1-responsive font-bold mb-0 profile-name">
                    <strong>
                        {{ userData.displayName }}
                        <i v-if="userData.privateProfile" class="fas fa-user-lock"></i>
                    </strong>
                </h1>
                <span
                    class="d-flex flex-column flex-md-row justify-content-md-start align-items-md-center gap-md-2 gap-1 pt-1 pb-2 pb-md-0 small"
                >
                    <small class="font-weight-light profile-tag">
                        {{ '@' + userData.username }}
                        <span v-if="userData.followedBy" class="badge text-bg-success">
                            {{ trans('profile.follows-you') }}
                        </span>
                    </small>
                </span>
                <div
                    v-if="authUser.authenticated && userInvisibleReason !== ViewUserForbiddenReason.YOU_ARE_BLOCKED"
                    class="d-flex py-3 flex-row justify-content-md-start align-items-md-center gap-1"
                >
                    <FollowButton :user-data="userData" />
                    <template v-if="userData.id !== authUser.getId">
                        <MuteButton :user-data="userData" />
                        <BlockButton :user-data="userData" />
                    </template>
                    <a
                        v-if="authUser?.isAdmin"
                        :href="`/admin/users/${userData.id}`"
                        class="btn btn-sm btn-outline-light"
                    >
                        <i class="fa fa-tools"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

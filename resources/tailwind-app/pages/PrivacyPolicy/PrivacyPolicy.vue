<script setup lang="ts">
import { ShieldCheck } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { usePrivacyPolicy } from '../../../vue/helpers/usePrivacyPolicy';
import { useUserStore } from '../../../vue/stores/user';
import Loading from '../../components/Loading.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import DeleteAccountDialog from './partials/DeleteAccountDialog.vue';
import MarkdownContent from './partials/MarkdownContent.vue';
import PolicyActionButtons from './partials/PolicyActionButtons.vue';

const user = useUserStore();

const isLoggedIn = user.isAuthenticated;

const {
    loading,
    policyMarkdown,
    upcomingPolicyMarkdown,
    upcomingValidFrom,
    policyChanged,
    hasUserSigned,
    showUpcoming,
    showActions,
    loadingAccept,
    formatDate,
    acceptPolicy,
} = usePrivacyPolicy(isLoggedIn);

const deleteDialog = ref<InstanceType<typeof DeleteAccountDialog>>();
</script>

<template>
    <AppLayout>
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <ShieldCheck class="w-6 h-6" />
                {{ trans('menu.privacy') }}
            </h1>

            <Loading v-if="loading" />
            <template v-else>
                <div v-if="!isLoggedIn && upcomingValidFrom" class="alert alert-error" role="alert">
                    <span>{{ trans('privacy.upcoming.public-notice', { date: formatDate(upcomingValidFrom) }) }}</span>
                    <a href="#upcoming-policy" class="btn btn-sm text-nowrap">
                        {{ trans('privacy.upcoming.public-notice-link') }}
                    </a>
                </div>

                <div v-if="showUpcoming" class="alert alert-info alert-outline" role="alert">
                    {{
                        trans('privacy.upcoming-alert.content', {
                            date: upcomingValidFrom ? formatDate(upcomingValidFrom) : '',
                        })
                    }}
                </div>
                <div v-else-if="isLoggedIn && policyChanged" class="alert alert-warning" role="alert">
                    <p>
                        <b>{{ trans('privacy.we-changed.title') }}</b>
                        {{ trans('privacy.we-changed.body') }}
                    </p>
                </div>
                <div v-else-if="isLoggedIn && !hasUserSigned" class="alert alert-info" role="alert">
                    <p>
                        <b>{{ trans('privacy.not-signed-yet.title') }}</b>
                        {{ trans('privacy.not-signed-yet.body') }}
                    </p>
                </div>

                <PolicyActionButtons
                    v-if="showActions"
                    :loading-accept="loadingAccept"
                    @accept="acceptPolicy"
                    @open-delete="deleteDialog?.open()"
                />

                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <MarkdownContent :markdown="policyMarkdown" />
                    </div>
                </div>

                <div
                    v-if="!isLoggedIn && upcomingPolicyMarkdown"
                    id="upcoming-policy"
                    class="card bg-base-100 shadow-sm"
                >
                    <div class="card-body">
                        <h2 class="card-title">
                            {{
                                trans('privacy.upcoming.section-heading', {
                                    date: upcomingValidFrom ? formatDate(upcomingValidFrom) : '',
                                })
                            }}
                        </h2>
                        <MarkdownContent :markdown="upcomingPolicyMarkdown" />
                    </div>
                </div>

                <PolicyActionButtons
                    v-if="showActions"
                    :loading-accept="loadingAccept"
                    @accept="acceptPolicy"
                    @open-delete="deleteDialog?.open()"
                />
            </template>

            <DeleteAccountDialog ref="deleteDialog" :username="user.getUsername" />
        </div>
    </AppLayout>
</template>

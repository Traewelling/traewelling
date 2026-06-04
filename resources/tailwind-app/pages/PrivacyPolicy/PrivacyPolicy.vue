<script setup lang="ts">
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
        <div class="container mx-auto md:px-4 py-2 md:py-24">
            <Loading v-if="loading" />
            <template v-else>
                <!-- Notice banner: upcoming policy (public view) -->
                <div
                    v-if="!isLoggedIn && upcomingValidFrom"
                    class="alert alert-error d-flex justify-content-between align-items-center mb-4"
                    role="alert"
                >
                    <span>{{ trans('privacy.upcoming.public-notice', { date: formatDate(upcomingValidFrom) }) }}</span>
                    <a href="#upcoming-policy" class="btn btn-sm btn-danger ms-3 text-nowrap">
                        {{ trans('privacy.upcoming.public-notice-link') }}
                    </a>
                </div>

                <!-- Info card: upcoming policy (authenticated) -->
                <div v-if="showUpcoming" class="alert alert-info mb-3 alert-outline" role="alert">
                    {{
                        trans('privacy.upcoming-alert.content', {
                            date: upcomingValidFrom ? formatDate(upcomingValidFrom) : '',
                        })
                    }}
                </div>
                <!-- Info card: policy changed -->
                <div v-else-if="isLoggedIn && policyChanged" class="alert alert-warning mb-3" role="alert">
                    <p class="card-body mb-0">
                        <b>{{ trans('privacy.we-changed.title') }}</b>
                        {{ trans('privacy.we-changed.body') }}
                    </p>
                </div>
                <!-- Info card: not signed yet -->
                <div v-else-if="isLoggedIn && !hasUserSigned" class="alert alert-info mb-3" role="alert">
                    <p class="card-body mb-0">
                        <b>{{ trans('privacy.not-signed-yet.title') }}</b>
                        {{ trans('privacy.not-signed-yet.body') }}
                    </p>
                </div>

                <!-- Action buttons (top) -->
                <PolicyActionButtons
                    v-if="showActions"
                    class="mb-4"
                    :loading-accept="loadingAccept"
                    @accept="acceptPolicy"
                    @open-delete="deleteDialog?.open()"
                />

                <MarkdownContent class="w-full" :markdown="policyMarkdown" />

                <!-- Upcoming policy section (public view) -->
                <template v-if="!isLoggedIn && upcomingPolicyMarkdown">
                    <hr class="my-5" />
                    <h3 id="upcoming-policy" class="mb-3">
                        {{
                            trans('privacy.upcoming.section-heading', {
                                date: upcomingValidFrom ? formatDate(upcomingValidFrom) : '',
                            })
                        }}
                    </h3>
                    <MarkdownContent class="privacy w-full" :markdown="upcomingPolicyMarkdown" />
                </template>

                <!-- Action buttons (bottom) -->
                <PolicyActionButtons
                    v-if="showActions"
                    class="mt-4 mb-5"
                    :loading-accept="loadingAccept"
                    @accept="acceptPolicy"
                    @open-delete="deleteDialog?.open()"
                />
            </template>

            <DeleteAccountDialog ref="deleteDialog" :username="user.getUsername" />
        </div>
    </AppLayout>
</template>

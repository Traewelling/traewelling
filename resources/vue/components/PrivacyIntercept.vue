<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';
import { usePrivacyPolicy } from '../helpers/usePrivacyPolicy';
import DeleteAccountDialog from './Privacy/DeleteAccountDialog.vue';
import MarkdownContent from './Privacy/MarkdownContent.vue';
import PolicyActionButtons from './Privacy/PolicyActionButtons.vue';

const props = defineProps({
    username: {
        type: String,
        required: false,
        default: null,
    },
});

const isLoggedIn = computed(() => !!props.username);

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
} = usePrivacyPolicy(isLoggedIn.value);

const deleteDialog = ref<InstanceType<typeof DeleteAccountDialog>>();
</script>

<template>
    <!-- Loading skeleton -->
    <div v-if="loading" class="text-center my-5">
        <div class="spinner-border text-secondary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <template v-else>
        <!-- Notice banner: upcoming policy (public view) -->
        <div
            v-if="!isLoggedIn && upcomingValidFrom"
            class="alert alert-danger d-flex justify-content-between align-items-center mb-4"
            role="alert"
        >
            <span>{{ trans('privacy.upcoming.public-notice', { date: formatDate(upcomingValidFrom) }) }}</span>
            <a href="#upcoming-policy" class="btn btn-sm btn-danger ms-3 text-nowrap">
                {{ trans('privacy.upcoming.public-notice-link') }}
            </a>
        </div>

        <!-- Info card: upcoming policy (authenticated) -->
        <div v-if="showUpcoming" class="card mb-3 border-danger">
            <p class="card-body mb-0">
                {{
                    trans('privacy.upcoming-alert.content', {
                        date: upcomingValidFrom ? formatDate(upcomingValidFrom) : '',
                    })
                }}
            </p>
        </div>
        <!-- Info card: policy changed -->
        <div v-else-if="isLoggedIn && policyChanged" class="card mb-3">
            <p class="card-body mb-0">
                <b>{{ trans('privacy.we-changed.title') }}</b>
                {{ trans('privacy.we-changed.body') }}
            </p>
        </div>
        <!-- Info card: not signed yet -->
        <div v-else-if="isLoggedIn && !hasUserSigned" class="card mb-3">
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

        <MarkdownContent class="privacy" :markdown="policyMarkdown" />

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
            <MarkdownContent class="privacy" :markdown="upcomingPolicyMarkdown" />
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

    <DeleteAccountDialog v-if="props.username" ref="deleteDialog" :username="props.username" />
</template>

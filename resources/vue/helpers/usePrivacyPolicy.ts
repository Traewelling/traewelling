import { trans } from 'laravel-vue-i18n';
import type { Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import { Api } from '../../types/Api.gen';

export function usePrivacyPolicy(isLoggedIn: Ref<boolean>) {
    const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
    const locale = document.documentElement.lang ?? 'en';

    const loading = ref(true);
    const policyId = ref<string | null>(null);
    const policyChanged = ref(false);
    const hasUserSigned = ref(false);
    const policyMarkdown = ref('');
    const upcomingPolicyMarkdown = ref('');
    const upcomingPolicyId = ref<string | null>(null);
    const upcomingValidFrom = ref<string | null>(null);
    const hasUserSignedUpcoming = ref(false);
    const loadingAccept = ref(false);

    function formatDate(isoString: string): string {
        return new Date(isoString).toLocaleDateString(locale, {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }

    const showUpcoming = computed(
        () =>
            isLoggedIn.value && hasUserSigned.value && upcomingPolicyId.value !== null && !hasUserSignedUpcoming.value,
    );

    const showActions = computed(() => isLoggedIn.value && (!hasUserSigned.value || showUpcoming.value));

    const activeAcceptId = computed(() => (showUpcoming.value ? upcomingPolicyId.value : policyId.value));

    onMounted(async () => {
        const policyResponse = await api.privacyPolicies.appHttpControllersApiV1PrivacyPolicyController();

        if (policyResponse.ok) {
            const policy = policyResponse.data.data;
            const md = locale.startsWith('de') ? policy.de : policy.en;

            if (!isLoggedIn.value) {
                policyMarkdown.value = md;
                if (policy.upcoming) {
                    upcomingValidFrom.value = policy.upcoming.validFrom ?? null;
                    upcomingPolicyMarkdown.value =
                        (locale.startsWith('de') ? policy.upcoming.de : policy.upcoming.en) ?? '';
                }
            } else {
                policyId.value = policy.id;
                hasUserSigned.value = policy.acceptedAt !== null;
                policyChanged.value = !hasUserSigned.value && policy.hasOldAcceptance;

                if (policy.upcoming) {
                    upcomingPolicyId.value = policy.upcoming.id ?? null;
                    upcomingValidFrom.value = policy.upcoming.validFrom ?? null;
                    hasUserSignedUpcoming.value = policy.upcoming.acceptedAt !== null;
                }

                const displayPolicy = showUpcoming.value && policy.upcoming ? policy.upcoming : policy;
                policyMarkdown.value = (locale.startsWith('de') ? displayPolicy.de : displayPolicy.en) ?? '';
            }
        }

        loading.value = false;
    });

    async function acceptPolicy(): Promise<void> {
        if (!activeAcceptId.value) {
            return;
        }
        loadingAccept.value = true;
        try {
            const response = await api.privacyPolicies.acceptPrivacyPolicy(activeAcceptId.value);
            if (response.ok) {
                window.location.href = '/dashboard';
            } else {
                window.notyf.error(trans('settings.something-wrong'));
            }
        } catch {
            window.notyf.error(trans('settings.something-wrong'));
        } finally {
            loadingAccept.value = false;
        }
    }

    return {
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
    };
}

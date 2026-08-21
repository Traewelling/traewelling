import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useMapConsentStore = defineStore(
    'mapConsent',
    () => {
        const mapConsentPersisted = ref(false);
        const mapConsentSession = ref(false);

        const mapConsentGiven = computed(() => mapConsentPersisted.value || mapConsentSession.value);

        function giveMapConsent(persist: boolean) {
            mapConsentSession.value = true;
            if (persist) {
                mapConsentPersisted.value = true;
            }
        }

        function revokeMapConsent() {
            mapConsentPersisted.value = false;
            mapConsentSession.value = false;
        }

        return {
            mapConsentPersisted,
            mapConsentSession,
            mapConsentGiven,
            giveMapConsent,
            revokeMapConsent,
        };
    },
    {
        persist: {
            pick: ['mapConsentPersisted'],
        },
    },
);

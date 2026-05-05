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

        // Vector tiles via OpenFreeMap requires separate explicit consent
        // because OpenFreeMap is not yet covered by the privacy policy (ToDo for later)
        const vectorTilesConsented = ref(false);
        const useVectorTiles = ref(false);

        function acceptVectorTiles() {
            vectorTilesConsented.value = true;
            useVectorTiles.value = true;
        }

        function toggleVectorTiles() {
            useVectorTiles.value = !useVectorTiles.value;
        }

        return {
            mapConsentPersisted,
            mapConsentSession,
            mapConsentGiven,
            giveMapConsent,
            revokeMapConsent,
            vectorTilesConsented,
            useVectorTiles,
            acceptVectorTiles,
            toggleVectorTiles,
        };
    },
    {
        persist: {
            pick: ['mapConsentPersisted', 'vectorTilesConsented', 'useVectorTiles'],
        },
    },
);

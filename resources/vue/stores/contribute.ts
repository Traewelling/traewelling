import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { Api, CommunityProfile } from '../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });

export const useContributeStore = defineStore(
    'contribute',
    () => {
        const profile = ref<CommunityProfile>({
            xp: 0,
            level: 0,
            nextLevelXP: 50,
            progressPercent: 0,
        });
        const loading = ref<boolean>(false);

        const progressToNextLevel = computed<number>(() => profile.value.progressPercent);

        async function fetchProfile(): Promise<void> {
            loading.value = true;
            try {
                const response = await api.community.getCommunityProfile();
                profile.value = response.data.data;
            } catch (error) {
                console.error('Failed to fetch contribution profile:', error);
            } finally {
                loading.value = false;
            }
        }

        const xp = computed<number>(() => profile.value.xp);
        const level = computed<number>(() => profile.value.level);
        const nextLevelXP = computed<number>(() => profile.value.nextLevelXP);
        const progressPercent = computed<number>(() => profile.value.progressPercent);

        return {
            xp,
            level,
            nextLevelXP,
            progressPercent,
            loading,
            progressToNextLevel,
            fetchProfile,
        };
    },
    { persist: true },
);

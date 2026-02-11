import { defineStore } from 'pinia';
import API from '../../js/api/api';

interface ContributeState {
    xp: number;
    level: number;
    nextLevelXP: number;
    progressPercent: number;
    loading: boolean;
}

export const useContributeStore = defineStore('contribute', {
    state: (): ContributeState => ({
        xp: 0,
        level: 0,
        nextLevelXP: 50,
        progressPercent: 0,
        loading: false,
    }),

    getters: {
        progressToNextLevel: (state) => state.progressPercent,
    },

    actions: {
        async fetchProfile() {
            this.loading = true;
            try {
                const response = await API.request('/community/profile');
                const data = await response.json();
                this.updateProfile(data.data);
            } catch (error) {
                console.error('Failed to fetch contribution profile:', error);
            } finally {
                this.loading = false;
            }
        },

        updateProfile(data: { xp: number; level: number; nextLevelXP: number; progressPercent: number }) {
            this.xp = data.xp;
            this.level = data.level;
            this.nextLevelXP = data.nextLevelXP;
            this.progressPercent = data.progressPercent;
        },
    },

    persist: true,
});

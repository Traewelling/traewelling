import {defineStore} from "pinia";
import { ProfileSettings } from "../../types/ProfileSettings";

export const useProfileSettingsStore = defineStore('profileSettings', {
    // because of the persist option. This option is defined in the pinia persisted state plugin
    // @types-ignore
    persist: true,
    state: () => ({
        settings: null as ProfileSettings | null,
        loading: false,
        error: null as unknown | null,
        refreshed: "2021-08-01T12:00:00Z"
    }),
    getters: {
        getDisplayName(): string {
            return this.settings ? this.settings.displayName : '';
        },
        getUsername(): string {
            return this.settings ? this.settings.username : '';
        },
        getProfilePicture(): string | null {
            return this.settings ? this.settings.profilePicture : '';
        },
        isPrivateProfile(): boolean {
            return this.settings ? this.settings.privateProfile : false;
        },
        isPreventIndex(): boolean {
            return this.settings ? this.settings.preventIndex : false;
        },
        getDefaultStatusVisibility(): number {
            return this.settings ? this.settings.defaultStatusVisibility : 0;
        },
        getPrivacyHideDays(): number {
            return this.settings ? this.settings.privacyHideDays : 0;
        },
        getEmail(): string | null {
            return this.settings ? this.settings.email : null;
        },
        isEmailVerified(): boolean {
            return this.settings ? this.settings.emailVerified : false;
        },
        isProfilePictureSet(): boolean {
            return this.settings ? this.settings.profilePictureSet : false;
        },
        getTwitter(): string | null {
            return this.settings ? this.settings.twitter : null;
        },
        getMastodon(): string | null {
            return this.settings ? this.settings.mastodon : null;
        },
        getMastodonVisibility(): number {
            return this.settings ? this.settings.mastodonVisibility : 0;
        }
    },
    actions: {
        async fetchSettings() : Promise<void>{
            // Fetch Data every 15 Minutes
            // ToDo: reduce interval
            // ToDo: refresh with settings update
            // ToDo: invalidate when logging out
            if (this.refreshed && (new Date().getTime() - new Date(this.refreshed).getTime()) < 60 * 15 * 1000) {
                return;
            }
            this.loading = true;
            try {
                this.settings  = await fetch('/api/v1/settings/profile')
                    .then((response: { json: () => any; }) => response.json())
                    .then((data: { data: any; }) => data.data);
                this.refreshed = new Date().toString();
            } catch (error) {
                this.error = error;
            } finally {
                this.loading = false;
            }
        }
    }
});

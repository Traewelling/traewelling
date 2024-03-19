import {defineStore} from "pinia";

export const useProfileSettingsStore = defineStore('profileSettings', {
    persist: true,
    state: () => ({
        settings: {
            "username": "Gertrud123",
            "displayName": "Gertrud",
            "profilePicture": null,
            "privateProfile": false,
            "preventIndex": false,
            "defaultStatusVisibility": 0,
            "privacyHideDays": 239,
            "password": true,
            "email": "gertrud@traewelling.de",
            "emailVerified": true,
            "profilePictureSet": true,
            "twitter": null,
            "mastodon": null,
            "mastodonVisibility": 1
        },
        loading: false,
        error: null,
        refreshed: "2021-08-01T12:00:00Z"
    }),
    getters: {
        getDisplayName() {
            return this.settings ? this.settings.displayName : '';
        },
        getUsername() {
            return this.settings ? this.settings.username : '';
        },
        getProfilePicture() {
            return this.settings ? this.settings.profilePicture : '';
        },
        isPrivateProfile() {
            return this.settings ? this.settings.privateProfile : false;
        },
        isPreventIndex() {
            return this.settings ? this.settings.preventIndex : false;
        },
        getDefaultStatusVisibility() {
            return this.settings ? this.settings.defaultStatusVisibility : 0;
        },
        getPrivacyHideDays() {
            return this.settings ? this.settings.privacyHideDays : 0;
        },
        getEmail() {
            return this.settings ? this.settings.email : null;
        },
        isEmailVerified() {
            return this.settings ? this.settings.emailVerified : false;
        },
        isProfilePictureSet() {
            return this.settings ? this.settings.profilePictureSet : false;
        },
        getTwitter() {
            return this.settings ? this.settings.twitter : null;
        },
        getMastodon() {
            return this.settings ? this.settings.mastodon : null;
        },
        getMastodonVisibility() {
            return this.settings ? this.settings.mastodonVisibility : 0;
        }
    },
    actions: {
        async fetchSettings() {
            // Fetch Data every 15 Minutes
            // ToDo: reduce interval
            // ToDo: refresh with settings update
            // ToDo: invalidate when logging out
            if (this.refreshed && (new Date() - new Date(this.refreshed)) < 60 * 15 * 1000) {
                return;
            }
            this.loading = true;
            try {
                this.settings  = await fetch('/api/v1/settings/profile')
                    .then(response => response.json())
                    .then(data => data.data);
                this.refreshed = new Date();
            } catch (error) {
                this.error = error;
            } finally {
                this.loading = false;
            }
        }
    }
});

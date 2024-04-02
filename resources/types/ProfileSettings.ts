export type ProfileSettings = {
    username: string;
    displayName: string;
    profilePicture: string | null;
    privateProfile: boolean;
    preventIndex: boolean;
    defaultStatusVisibility: number;
    privacyHideDays: number;
    password: boolean;
    email: string | null;
    emailVerified: boolean;
    profilePictureSet: boolean;
    twitter: string | null;
    mastodon: string | null;
    mastodonVisibility: number;
};

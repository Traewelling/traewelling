import {ShortStation} from "./Station";

export type User = {
    displayName: string,
    username: string,
    profilePicture: string,
    trainDistance: number,
    trainDuration: number,
    points: number,
    mastodonUrl: string|null,
    privateProfile: boolean,
    privacyHideDays: number,
    preventIndex: boolean,
    role: number,
    home: ShortStation,
    language: string
};

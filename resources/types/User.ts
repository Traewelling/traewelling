import {ShortStation} from "./Station";

export type User = {
    displayName: string,
    username: string,
    profilePicture: string,
    trainDistance: number, // @todo: rename key - we have more than just trains
    trainDuration: number, // @todo: rename key - we have more than just trains
    points: number,
    mastodonUrl: string | null,
    privateProfile: boolean,
    privacyHideDays: number,
    preventIndex: boolean,
    role: number,
    home: ShortStation,
    language: string
};

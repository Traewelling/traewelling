import {ShortStation} from "./Station";

export type TrwlEvent = {
    id: number;
    begin: string;
    end: string;
    hashtag: string;
    host: string;
    name: string;
    slug: string;
    url: string;
    station: ShortStation;
}

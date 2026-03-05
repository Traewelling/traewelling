export type StationIdentifier = {
    type: string;
    identifier: string;
};

export type ShortStation = {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    areas: Area[];
    identifiers?: StationIdentifier[];
};

export type Area = {
    name: string;
    default: boolean;
    adminLevel: number;
};

export type ShortStation = {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    ibnr: number;
    rilIdentifier: string;
    areas: Area[];
};

export type Area = {
    name: string;
    default: boolean;
    adminLevel: number;
};

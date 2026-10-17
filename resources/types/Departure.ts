export type departureEntry = {
    tripId: string;
    stop: HafasStop;
    when: string | null;
    plannedWhen: string | null;
    platform: string | null;
    prognosisType: string;
    direction: string;
    line: HafasLine;
    cancelled: boolean | null | undefined;
    station: {
        id: number;
        wikidata_id: null | any; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_a: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_b: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_c: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_d: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_e: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        name: string;
        latitude: number | null;
        longitude: number | null;
        ifopt: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
    };
};

export type HafasLine = {
    type: string;
    id: string;
    fahrtNr: string;
    name: string;
    mode: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    product: any; // eslint-disable-line @typescript-eslint/no-explicit-any
};

export type HafasStop = {
    type: string;
    id: string;
    name: string;
    location: HafasLocation;
    station: HafasStation;
};
export type HafasStation = {
    type: string;
    id: string;
    name: string;
    location: HafasLocation;
    products: {
        [key: string]: boolean;
    };
};

export type HafasLocation = {
    type: string;
    id: string;
    latitude: number;
    longitude: number;
};

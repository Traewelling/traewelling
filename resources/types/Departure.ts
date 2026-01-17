export type departureEntry = {
    tripId: string;
    stop: HafasStop;
    when: string | null;
    plannedWhen: string | null;
    delay: number | null;
    platform: string | null;
    prognosisType: string;
    direction: string;
    provenance: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
    line: HafasLine;
    remarks: any[]; // eslint-disable-line @typescript-eslint/no-explicit-any
    origin: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    destination: HafasDestination;
    currentTripPosition: {
        type: string;
        latitude: number;
        longitude: number;
    };
    cancelled: boolean | null | undefined;
    station: {
        id: number;
        ibnr: number;
        wikidata_id: null | any; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_a: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_b: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_c: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_d: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        ifopt_e: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
        rilIdentifier: string | null;
        name: string;
        latitude: number | null;
        longitude: number | null;
        ifopt: any | null; // eslint-disable-line @typescript-eslint/no-explicit-any
    };
};

export type HafasDestination = {
    type: string;
    id: string;
    name: string;
    location: HafasLocation;
    products: {
        [key: string]: boolean;
    };
    station: HafasStation;
};

export type HafasLine = {
    type: string;
    id: string;
    fahrtNr: string;
    name: string;
    public: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    adminCode: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    productName: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    mode: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    product: any; // eslint-disable-line @typescript-eslint/no-explicit-any
    operator: any; // eslint-disable-line @typescript-eslint/no-explicit-any
};

export type HafasStop = {
    type: string;
    id: string;
    name: string;
    location: HafasLocation;
    products: {
        [key: string]: boolean;
    };
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

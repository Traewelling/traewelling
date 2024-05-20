export type departureEntry = {
    tripId: string;
    stop: HafasStop;
    when: string | null;
    plannedWhen: string | null;
    delay: number | null;
    platform: string | null;
    prognosisType: string;
    direction: string;
    provenance: any | null;
    line: HafasLine;
    remarks: any[];
    origin: any;
    destination: HafasDestination;
    currentTripPosition: {
        type: string;
        latitude: number;
        longitude: number
    }
    cancelled: boolean|null|undefined;
    station: {
        id: number;
        ibnr: number;
        wikidata_id: null|any;
        ifopt_a: any|null;
        ifopt_b: any|null;
        ifopt_c: any|null;
        ifopt_d: any|null;
        ifopt_e: any|null;
        rilIdentifier: string|null;
        name: string;
        latitude: number|null;
        longitude: number|null;
        ifopt: any|null;
    }
}

export type HafasDestination = {
    type: string;
    id: string;
    name: string;
    location: HafasLocation;
    products: {
        [key: string]: boolean;
    }
    station: HafasStation;
}

export type HafasLine = {
    type: string;
    id: string;
    fahrtNr: string;
    name: string;
    public: any;
    adminCode: any;
    productName: any;
    mode: any;
    product: any;
    operator: any;
}

export type HafasStop = {
    type: string;
    id: string;
    name: string;
    location: HafasLocation;
    products: {
        [key: string]: boolean;
    }
    station: HafasStation
}
export type HafasStation = {
    type: string;
    id: string;
    name: string;
    location: HafasLocation;
    products: {
        [key: string]: boolean;
    }
}

export type HafasLocation = {
            type: string;
            id: string;
            latitude: number;
            longitude: number;
        }

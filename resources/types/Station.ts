import { StationIdentifierResource } from './Api.gen';

export type ShortStation = {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    identifiers?: StationIdentifierResource[];
    areas: Area[];
};

export type Area = {
    name: string;
    default: boolean;
    adminLevel: number;
};

/**
 * Helper: Get identifier from identifiers array
 */
export function getStationIdentifier(station: ShortStation | null | undefined, type: string): string | null {
    if (!station) return null;

    return station.identifiers?.find((i) => i.type === type)?.identifier ?? null;
}

/**
 * Get IBNR identifier (EVA number)
 */
export function getStationIBNR(station: ShortStation | null | undefined): string | null {
    return getStationIdentifier(station, 'de_db_ibnr');
}

/**
 * Get RIL100 identifier
 */
export function getStationRIL100(station: ShortStation | null | undefined): string | null {
    return getStationIdentifier(station, 'de_db_ril100');
}

/**
 * Get IFOPT identifier
 */
export function getStationIFOPT(station: ShortStation | null | undefined): string | null {
    return getStationIdentifier(station, 'de_db_ifopt');
}

/**
 * Get Wikidata ID
 */
export function getStationWikidataId(station: ShortStation | null | undefined): string | null {
    return getStationIdentifier(station, 'wikidata_id');
}

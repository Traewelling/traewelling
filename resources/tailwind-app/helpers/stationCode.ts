import type { StationIdentifierResource } from '../../types/Api.gen';

/**
 * The short code shown next to a station name: the RIL100 code where there is one, otherwise a local code.
 * Same order as Station::getShortCode() in the backend.
 */
export function stationShortCode(station: { identifiers?: StationIdentifierResource[] | null }): string | undefined {
    const identifiers = station.identifiers ?? [];
    return (
        identifiers.find((i) => i.type === 'de_db_ril100')?.identifier ??
        identifiers.find((i) => i.type === 'local_code')?.identifier ??
        undefined
    );
}

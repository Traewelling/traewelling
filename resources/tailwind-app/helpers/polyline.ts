/**
 * Decodes a Google Encoded Polyline into GeoJSON coordinate pairs.
 */
export function decodePolyline(encoded: string, precision = 5): [number, number][] {
    const factor = 10 ** precision;
    const coordinates: [number, number][] = [];
    let index = 0;
    let latitude = 0;
    let longitude = 0;

    const nextDelta = (): number => {
        let result = 0;
        let shift = 0;
        let byte: number;

        do {
            byte = encoded.charCodeAt(index++) - 63;
            result |= (byte & 0x1f) << shift;
            shift += 5;
        } while (byte >= 0x20);

        return result & 1 ? ~(result >> 1) : result >> 1;
    };

    while (index < encoded.length) {
        latitude += nextDelta();
        longitude += nextDelta();
        coordinates.push([longitude / factor, latitude / factor]);
    }

    return coordinates;
}

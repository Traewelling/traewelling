import { StopoverResource } from '../../types/Api.gen';

export class NextStation {
    public static getNextStation(stations: StopoverResource[]): StopoverResource | null {
        stations = stations.filter((stopover: StopoverResource) => {
            const time =
                stopover.departureReal ??
                stopover.departurePlanned ??
                stopover.arrivalReal ??
                stopover.arrivalPlanned ??
                null;
            return time && Date.parse(time).toFixed() >= Date.now().toFixed();
        });

        return stations.shift() ?? null;
    }
}

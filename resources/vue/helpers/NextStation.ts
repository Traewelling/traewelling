import {StopoverResource} from "../../types/Api";

export class NextStation {
    public static getNextStation(stations: StopoverResource[]): StopoverResource|null {
        stations = stations.filter((stopover: StopoverResource) => {
            const time = stopover.departure ?? stopover.arrival ?? null;
            return time && Date.parse(time).toFixed() >= Date.now().toFixed();
        });

        return stations.shift() ?? null;
    }
}

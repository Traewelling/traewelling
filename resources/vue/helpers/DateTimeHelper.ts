import {StatusResource, StopoverResource} from "../../types/Api.gen";
import {DateTime} from "luxon";
import {Dtm} from "./DateTime";

export function getDepartureForStatus(status: StatusResource): Dtm {
    let departure = status.train.manualDeparture;
    if (departure) {
        return Dtm.fromISO(departure);
    }

    return getDepartureForStopover(status.train.origin);
}

export function getArrivalForStatus(status: StatusResource): Dtm {
    let arrival = status.train.manualArrival;
    if (arrival) {
        return Dtm.fromISO(arrival);
    }

    return getArrivalForStopover(status.train.destination);
}

export function getDepartureForStopover(stopover: StopoverResource): Dtm {
    let departure = getDepartureString(stopover);
    if (departure) {
        return Dtm.fromISO(departure);
    }

    departure = getArrivalString(stopover);
    if (departure) {
        return Dtm.fromISO(departure);
    }

    // wtf, no departure or arrival? Use now.
    return new Dtm(DateTime.now().toISO());
}

export function getArrivalForStopover(stopover: StopoverResource): Dtm {
    let arrival = getArrivalString(stopover);
    if (arrival) {
        return Dtm.fromISO(arrival);
    }

    arrival = getDepartureString(stopover);
    if (arrival) {
        return Dtm.fromISO(arrival);
    }

    // wtf, no departure or arrival? Use now.
    return new Dtm(DateTime.now().toISO());
}

function getDepartureString(stopover: StopoverResource): string | null {
    if (stopover.departureReal) {
        return stopover.departureReal;
    }

    if (stopover.departurePlanned) {
        return stopover.departurePlanned;
    }

    if (stopover.departure) {
        return stopover.departure;
    }

    return null;
}

function getArrivalString(stopover: StopoverResource): string | null {
    if (stopover.arrivalReal) {
        return stopover.arrivalReal;
    }

    if (stopover.arrivalPlanned) {
        return stopover.arrivalPlanned;
    }

    if (stopover.arrival) {
        return stopover.arrival;
    }

    return null;
}


export function getDepartureAttribute(status: StatusResource): StopoverTime {
    let planned = status.train.origin.departurePlanned
        ?? status.train.origin.departure
    let real = status.train.origin.departureReal;
    let manual = status.train.manualDeparture;
    return prepareStopoverTime(planned, real, manual);
}

export function getArrivalAttribute(status: StatusResource): StopoverTime {
    let planned = status.train.destination.arrivalPlanned
        ?? status.train.destination.arrival
    let real = status.train.destination.arrivalReal;
    let manual = status.train.manualArrival;
    return prepareStopoverTime(planned, real, manual);
}


function prepareStopoverTime(
    planned: string | null,
    real: string | null,
    manual: string | null
): StopoverTime {
    let time: Dtm | null = null;
    let type: StopoverTimeType = StopoverTimeType.Planned;
    let plannedTime: Dtm | null = null;

    if (planned) {
        plannedTime = Dtm.fromISO(planned);
    }

    if (manual) {
        time = Dtm.fromISO(manual);
        type = StopoverTimeType.Manual;
    } else if (real) {
        time = Dtm.fromISO(real);
        type = StopoverTimeType.Realtime;
    } else if (planned) {
        time = Dtm.fromISO(planned);
    }

    return {
        time: time,
        originalTime: plannedTime?.toISO() !== time?.toISO() ? plannedTime : null,
        type: type
    };
}

export function timeTypeTooltip(type: StopoverTimeType): string {
    switch (type) {
        case StopoverTimeType.Manual:
            return 'time-is-planned';
        case StopoverTimeType.Realtime:
            return 'time-is-real'
        case StopoverTimeType.Planned:
            return 'time-is-manual'
        default:
            return 'time-is-unknown';
    }
}

export interface StopoverTime {
    time: Dtm | null;
    originalTime: Dtm | null;
    type: StopoverTimeType;
}

export enum StopoverTimeType {
    Manual = 'manual',
    Realtime = 'realtime',
    Planned = 'planned',
};

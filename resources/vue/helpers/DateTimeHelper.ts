import { StatusResource, StopoverResource } from '../../types/Api.gen';
import { DateTime } from 'luxon';
import { Dtm } from './DateTime';

export function getDepartureForStatus(status: StatusResource): Dtm {
    const departure = status.train.manualDeparture;
    if (departure) {
        return Dtm.fromISO(departure);
    }

    return getDepartureForStopover(status.train.origin);
}

export function getArrivalForStatus(status: StatusResource): Dtm {
    const arrival = status.train.manualArrival;
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
    const planned = status.train.origin.departurePlanned ?? status.train.origin.departure;
    const real = status.train.origin.departureReal;
    const manual = status.train.manualDeparture;
    return prepareStopoverTime(planned, real, manual);
}

export function getArrivalAttribute(status: StatusResource): StopoverTime {
    const planned = status.train.destination.arrivalPlanned ?? status.train.destination.arrival;
    const real = status.train.destination.arrivalReal;
    const manual = status.train.manualArrival;
    return prepareStopoverTime(planned, real, manual);
}

function prepareStopoverTime(planned: string | null, real: string | null, manual: string | null): StopoverTime {
    let time: Dtm | null = null;
    let type: StopoverTimeType = StopoverTimeType.Planned;
    let plannedTime: Dtm | null = null;

    if (planned) {
        plannedTime = Dtm.fromISO(planned);
        plannedTime.dateTime = plannedTime.dateTime.set({ second: 0 }); // remove seconds for consistency
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

    if (!plannedTime && !time) {
        return {
            time: plannedTime,
            originalTime: null,
            type: StopoverTimeType.Manual, // fallback to manual if no time is available
        };
    }

    let originalTime = time;
    if (time && plannedTime) {
        originalTime =
            Math.abs(plannedTime.dateTime.toSeconds() - time.dateTime.toSeconds()) >= 60 ? plannedTime : null;
    }

    return {
        time: time,
        originalTime: originalTime,
        type: type,
    };
}

export function timeTypeTooltip(type: StopoverTimeType): string {
    switch (type) {
        case StopoverTimeType.Manual:
            return 'time-is-manual';
        case StopoverTimeType.Realtime:
            return 'time-is-real';
        case StopoverTimeType.Planned:
            return 'time-is-planned';
        default:
            return 'time-is-unknown';
    }
}

export function secondsToDuration(seconds: number): TimeDuration {
    const duration: TimeDuration = {};

    duration.years = Math.floor(seconds / (365 * 24 * 60 * 60));
    duration.days = Math.floor((seconds % (365 * 24 * 60 * 60)) / (24 * 60 * 60));
    duration.hours = Math.floor((seconds % (24 * 60 * 60)) / (60 * 60));
    duration.minutes = Math.floor((seconds % (60 * 60)) / 60);

    return duration;
}

export interface TimeDuration {
    years?: number;
    days?: number;
    hours?: number;
    minutes?: number;
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
}

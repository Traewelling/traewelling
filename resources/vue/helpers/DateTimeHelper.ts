import { DateTime } from 'luxon';
import { StatusResource, StopoverResource } from '../../types/Api.gen';
import { Dtm } from './DateTime';

export function getDepartureForStatus(status: StatusResource): Dtm {
    const departure = status.checkin.manualDeparture;
    if (departure) {
        return Dtm.fromISO(departure);
    }

    return getDepartureForStopover(status.checkin.origin);
}

export function getArrivalForStatus(status: StatusResource): Dtm {
    const arrival = status.checkin.manualArrival;
    if (arrival) {
        return Dtm.fromISO(arrival);
    }

    return getArrivalForStopover(status.checkin.destination);
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

    return null;
}

function getArrivalString(stopover: StopoverResource): string | null {
    if (stopover.arrivalReal) {
        return stopover.arrivalReal;
    }

    if (stopover.arrivalPlanned) {
        return stopover.arrivalPlanned;
    }

    return null;
}

export function getDepartureAttribute(status: StatusResource): StopoverTime {
    const planned = status.checkin.origin.departurePlanned;
    const real = status.checkin.origin.departureReal;
    const manual = status.checkin.manualDeparture;
    return prepareStopoverTime(planned, real, manual);
}

export function getArrivalAttribute(status: StatusResource): StopoverTime {
    const planned = status.checkin.destination.arrivalPlanned;
    const real = status.checkin.destination.arrivalReal;
    const manual = status.checkin.manualArrival;
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

export function minutesToDuration(minutes: number): TimeDuration {
    const duration: TimeDuration = {};

    duration.years = Math.floor(minutes / (365 * 24 * 60));
    duration.days = Math.floor((minutes % (365 * 24 * 60)) / (24 * 60));
    duration.hours = Math.floor((minutes % (24 * 60)) / 60);
    duration.minutes = Math.floor(minutes % 60);

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

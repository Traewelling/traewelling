import {DateTime, DateTimeFormatOptions} from "luxon";
import {DateTimeOptions, LocaleOptions} from "luxon/src/datetime";
import {Dtm} from "./DateTime";

export class DtmRange {
    dateTimeStart: Dtm;
    dateTimeEnd: Dtm;
    isSameDay: boolean = false;

    constructor(start: Dtm, end: Dtm) {
        this.dateTimeStart = start;
        this.dateTimeEnd = end;
    }

    static fromISO(start: string, end: string, opts?: DateTimeOptions): DtmRange {
        const startDtm: Dtm = new Dtm(start, opts);
        const endDtm: Dtm = new Dtm(end, opts);
        const startDate: string = start.substring(0, 10);

        const range = new DtmRange(startDtm, endDtm);
        range.isSameDay = end.startsWith(startDate);

        return range;
    }

    toLocaleDateString(
        formatOpts?: DateTimeFormatOptions,
        opts?: LocaleOptions,
    ): string {
        if (this.isSameDay) {
            return this.dateTimeStart.toLocaleString(formatOpts, opts);
        }

        return `${this.dateTimeStart.toLocaleString(formatOpts, opts)} - ${this.dateTimeEnd.toLocaleString(formatOpts, opts)}`;
    }

    toLocaleDateTimeString(
        formatOpts?: DateTimeFormatOptions,
        opts?: LocaleOptions,
    ): string {
        formatOpts = formatOpts || DateTime.DATETIME_FULL;

        return `${this.dateTimeStart.toLocaleString(formatOpts, opts)} - ${this.dateTimeEnd.toLocaleString(formatOpts, opts)}`;
    }
}

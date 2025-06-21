import {DateTime, DateTimeFormatOptions, ToISOTimeOptions} from "luxon";
import {DateTimeOptions, LocaleOptions, ToRelativeOptions} from "luxon/src/datetime";
import {getActiveLanguage} from "laravel-vue-i18n";

// todo: rewrite to extend DateTime instead of wrapping it
export class Dtm {
    dateTime: DateTime;

    constructor(date: string, opts?: DateTimeOptions) {
        const defaultOpts: DateTimeOptions = {
            locale: this.getLocale(),
        }
        opts = {...defaultOpts, ...opts};

        this.dateTime = DateTime.fromISO(date, opts);
    }

    private getLocale(): string {
        let locale: string = getActiveLanguage();

        if (locale.startsWith('de')) {
            return 'de';
        }

        if (locale === '') {
            return 'en';
        }

        return locale;
    }

    static fromISO(date: string, opts?: DateTimeOptions): Dtm {
        return new Dtm(date, opts);
    }

    toLocaleString(
        formatOpts?: DateTimeFormatOptions,
        opts?: LocaleOptions,
    ): string {
        return this.dateTime.toLocaleString(formatOpts, opts);
    }

    toFormat(
        format: string,
        opts?: LocaleOptions
    ): string {
        return this.dateTime.toFormat(format, opts);
    }

    toISO(
        opts?: ToISOTimeOptions
    ): string | null {
        return this.dateTime.toISO(opts);
    }

    toMillis(): number {
        return this.dateTime.toMillis();
    }

    toRelative(options?: ToRelativeOptions): string | null {
        return this.dateTime.toRelative(options);
    }
}

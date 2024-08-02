import {DateTime, DateTimeFormatOptions} from "luxon";
import {DateTimeOptions, LocaleOptions} from "luxon/src/datetime";
import {getActiveLanguage} from "laravel-vue-i18n";

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
}

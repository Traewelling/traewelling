import { DateTime, DateTimeFormatOptions, ToISOTimeOptions } from 'luxon';
import { DateTimeOptions, LocaleOptions, ToRelativeOptions } from 'luxon/src/datetime';
import { getActiveLanguage } from 'laravel-vue-i18n';

// todo: rewrite to extend DateTime instead of wrapping it
export class Dtm {
    dateTime: DateTime;

    constructor(date: string, opts?: DateTimeOptions) {
        const defaultOpts: DateTimeOptions = {
            locale: this.getLocale(),
        };
        opts = { ...defaultOpts, ...opts };

        this.dateTime = DateTime.fromISO(date, opts);
    }

    private getLocale(): string {
        const locale: string = getActiveLanguage();

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
        const locale = this.dateTime.locale || this.getLocale();
        // If TIME_SIMPLE and English, force 24h
        if (
            formatOpts === DateTime.TIME_SIMPLE &&
            (locale === 'en' || locale.startsWith('en'))
        ) {
            // Use 24h format for English
            return this.dateTime.toLocaleString({ hour: '2-digit', minute: '2-digit', hour12: false }, opts);
        }
        return this.dateTime.toLocaleString(formatOpts, opts);
    }

    toFormat(
        format: string,
        opts?: LocaleOptions,
    ): string {
        return this.dateTime.toFormat(format, opts);
    }

    toISO(
        opts?: ToISOTimeOptions,
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

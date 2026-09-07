import { getActiveLanguage, isLoaded, loadLanguageAsync, trans } from 'laravel-vue-i18n';
import { useConfigurationStore } from '../../vue/stores/configuration';

function delay(ms: number): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

// Guards against a hung loadLanguageAsync() call (e.g. a dynamic import() that never
// settles) so a single stuck attempt can't block the whole retry loop forever.
function withTimeout(promise: Promise<unknown>, ms: number): Promise<unknown> {
    return Promise.race([promise, delay(ms)]);
}

export class PageTitleService {
    private static readonly FALLBACK_APP_NAME = 'Träwelling';
    private static readonly MAX_ATTEMPTS = 5;
    private static readonly ATTEMPT_TIMEOUT_MS = 2000;

    public static getAppName(): string {
        return useConfigurationStore().appName || this.FALLBACK_APP_NAME;
    }

    /**
     * Resets document.title to just the app name, e.g. for routes without a title.
     */
    public static reset(): void {
        document.title = this.getAppName();
    }

    /**
     * Sets document.title to an already human-readable, non-translatable value
     * (e.g. a username or display name).
     */
    public static setRawTitle(title: string): void {
        document.title = `${title} – ${this.getAppName()}`;
    }

    /**
     * Sets document.title from an i18n translation key, waiting (bounded) for the
     * active language to finish loading first so the title isn't shown untranslated.
     */
    public static async setTranslatedTitle(titleKey: string): Promise<void> {
        const lang = getActiveLanguage();
        for (let attempt = 0; !isLoaded(lang) && attempt < this.MAX_ATTEMPTS; attempt++) {
            if (attempt > 0) {
                await delay(100 * attempt);
            }
            await withTimeout(loadLanguageAsync(lang), this.ATTEMPT_TIMEOUT_MS);
        }
        this.setRawTitle(trans(titleKey));
    }
}

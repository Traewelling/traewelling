import { getActiveLanguage, isLoaded, loadLanguageAsync, trans } from 'laravel-vue-i18n';
import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from '../../vue/stores/user';
import routes from './routes';

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

const appName = document.title || 'Träwelling';

function delay(ms: number): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

// Guards against a hung loadLanguageAsync() call (e.g. a dynamic import() that never
// settles) so a single stuck attempt can't block the whole retry loop forever.
function withTimeout(promise: Promise<unknown>, ms: number): Promise<unknown> {
    return Promise.race([promise, delay(ms)]);
}

router.beforeEach((to) => {
    if (to.meta?.requiresClosedBeta) {
        const user = useUserStore();
        if (!user.isClosedBeta) {
            return { name: 'dashboard' };
        }
    }
});

router.afterEach(async (to) => {
    const titleKey = to.meta?.title as string | undefined;
    if (!titleKey) {
        document.title = appName;
        return;
    }
    // loadLanguageAsync can resolve early (via AbortController) when the i18n plugin's
    // loadFallbackLanguage() triggers a competing load() call, without marking the
    // language as loaded. Retry a bounded number of times with incremental backoff
    // (so a slow competing call has time to settle instead of getting re-aborted
    // immediately) and a per-attempt timeout (so a hung dynamic import can't block
    // the loop forever). Without both bounds, this can spin/hang on slow devices and
    // get the tab killed or repeatedly reloaded by the browser.
    const lang = getActiveLanguage();
    const MAX_ATTEMPTS = 5;
    const ATTEMPT_TIMEOUT_MS = 2000;
    for (let attempt = 0; !isLoaded(lang) && attempt < MAX_ATTEMPTS; attempt++) {
        if (attempt > 0) {
            await delay(100 * attempt);
        }
        await withTimeout(loadLanguageAsync(lang), ATTEMPT_TIMEOUT_MS);
    }
    document.title = `${trans(titleKey)} – ${appName}`;
});

export default router;

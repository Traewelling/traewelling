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
    // loadFallbackLanguage() triggers a competing load() call. Loop until the language
    // is genuinely present in I18n.loaded before calling trans().
    const lang = getActiveLanguage();
    while (!isLoaded(lang)) {
        await loadLanguageAsync(lang);
    }
    document.title = `${trans(titleKey)} – ${appName}`;
});

export default router;

import { getActiveLanguage, isLoaded, loadLanguageAsync, trans } from 'laravel-vue-i18n';
import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';

const router = createRouter({
    history: createWebHistory(),
    routes,
});

const appName = document.title || 'Träwelling';

router.afterEach(async (to) => {
    const titleKey = to.meta?.title as string | undefined;
    if (!titleKey) {
        document.title = appName;
        return;
    }
    if (!isLoaded()) {
        await loadLanguageAsync(getActiveLanguage());
    }
    document.title = `${trans(titleKey)} – ${appName}`;
});

export default router;

import { getActiveLanguage, isLoaded, loadLanguageAsync, trans } from 'laravel-vue-i18n';
import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from '../../vue/stores/user';
import routes from './routes';

const router = createRouter({
    history: createWebHistory(),
    routes,
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
    if (!isLoaded()) {
        await loadLanguageAsync(getActiveLanguage());
    }
    document.title = `${trans(titleKey)} – ${appName}`;
});

export default router;

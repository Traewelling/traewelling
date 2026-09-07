import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from '../../vue/stores/user';
import { PageTitleService } from '../services/PageTitleService';
import routes from './routes';

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

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
        PageTitleService.reset();
        return;
    }
    await PageTitleService.setTranslatedTitle(titleKey);
});

export default router;

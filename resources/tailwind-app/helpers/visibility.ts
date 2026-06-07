import { Eye, Globe, Lock, LogIn, Shield, Users } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import type { Component } from 'vue';

export const VISIBILITY_ICONS: Record<number, Component> = {
    0: Globe,
    1: Eye,
    2: Users,
    3: Lock,
    4: LogIn,
    5: Shield,
};

export const ALL_VISIBILITIES = [0, 1, 2, 3, 4, 5] as const;

export const VISIBILITY_ITEMS = ALL_VISIBILITIES.map((v) => ({
    value: v,
    labelKey: `status.visibility.${v}` as const,
    detailKey: `status.visibility.${v}.detail` as const,
}));

export function getVisibilityOptions(): { value: number; label: string }[] {
    return ALL_VISIBILITIES.map((v) => ({
        value: v,
        label: trans(`status.visibility.${v}`),
    }));
}

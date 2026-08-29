import { HafasTravelType } from '../../../types/Api.gen';

const CATEGORY_COLORS: Record<HafasTravelType, string> = {
    [HafasTravelType.NationalExpress]: '#c72730',
    [HafasTravelType.National]: '#f97316',
    [HafasTravelType.RegionalExp]: '#eab308',
    [HafasTravelType.Regional]: '#22c55e',
    [HafasTravelType.Suburban]: '#14b8a6',
    [HafasTravelType.Subway]: '#3b82f6',
    [HafasTravelType.Tram]: '#a855f7',
    [HafasTravelType.Bus]: '#ec4899',
    [HafasTravelType.Ferry]: '#06b6d4',
    [HafasTravelType.Plane]: '#64748b',
    [HafasTravelType.Taxi]: '#84cc16',
    [HafasTravelType.FreightTrain]: '#78716c',
};

const FALLBACK_COLOR = '#9ca3af';

export const CATEGORY_ORDER: HafasTravelType[] = [
    HafasTravelType.NationalExpress,
    HafasTravelType.National,
    HafasTravelType.RegionalExp,
    HafasTravelType.Regional,
    HafasTravelType.Suburban,
    HafasTravelType.Subway,
    HafasTravelType.Tram,
    HafasTravelType.Bus,
    HafasTravelType.Ferry,
    HafasTravelType.Plane,
    HafasTravelType.Taxi,
    HafasTravelType.FreightTrain,
];

export function categoryColor(category?: HafasTravelType | null): string {
    return (category && CATEGORY_COLORS[category]) || FALLBACK_COLOR;
}

/**
 * multiple categories = fastes wins
 */
export function primaryCategory(categories: HafasTravelType[]): HafasTravelType | null {
    for (const category of CATEGORY_ORDER) {
        if (categories.includes(category)) {
            return category;
        }
    }

    return null;
}

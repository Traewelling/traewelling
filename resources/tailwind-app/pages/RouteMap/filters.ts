import { Business, HafasTravelType } from '../../../types/Api.gen';
import { CATEGORY_ORDER } from './categoryColors';

export interface RouteMapFilterState {
    from: string;
    until: string;
    travelTypes: HafasTravelType[];
    travelPurposes: Business[];
    includeApproximated: boolean;
}

export const TRAVEL_PURPOSES: { value: Business; label: string }[] = [
    { value: Business.Value0, label: 'stationboard.business.private' },
    { value: Business.Value1, label: 'stationboard.business.business' },
    { value: Business.Value2, label: 'stationboard.business.commute' },
];

/**
 * Everything but the plane: air travel dwarfs the rest of the map and says little about the route taken.
 */
export const DEFAULT_TRAVEL_TYPES: HafasTravelType[] = CATEGORY_ORDER.filter(
    (category) => category !== HafasTravelType.Plane,
);

export function defaultFilterState(): RouteMapFilterState {
    return {
        from: '',
        until: '',
        travelTypes: [...DEFAULT_TRAVEL_TYPES],
        travelPurposes: [],
        includeApproximated: false,
    };
}

function sameTravelTypes(a: HafasTravelType[], b: HafasTravelType[]): boolean {
    return a.length === b.length && a.every((type) => b.includes(type));
}

export function isFilterActive(state: RouteMapFilterState): boolean {
    const defaults = defaultFilterState();

    return (
        state.from !== defaults.from ||
        state.until !== defaults.until ||
        !sameTravelTypes(state.travelTypes, defaults.travelTypes) ||
        state.travelPurposes.length > 0 ||
        state.includeApproximated !== defaults.includeApproximated
    );
}

export interface RouteMapQuery {
    from?: string;
    until?: string;
    'travelTypes[]'?: HafasTravelType[];
    'travelPurposes[]'?: Business[];
    includeApproximated?: boolean;
}

export function toQuery(state: RouteMapFilterState): RouteMapQuery {
    return {
        from: state.from || undefined,
        until: state.until || undefined,
        'travelTypes[]': sameTravelTypes(state.travelTypes, CATEGORY_ORDER) ? undefined : state.travelTypes,
        'travelPurposes[]': state.travelPurposes.length > 0 ? state.travelPurposes : undefined,
        includeApproximated: state.includeApproximated ? undefined : false,
    };
}

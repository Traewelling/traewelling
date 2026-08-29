import { Business, HafasTravelType } from '../../../types/Api.gen';

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

export function defaultFilterState(): RouteMapFilterState {
    return {
        from: '',
        until: '',
        travelTypes: [],
        travelPurposes: [],
        includeApproximated: true,
    };
}

export function isFilterActive(state: RouteMapFilterState): boolean {
    return (
        state.from !== '' ||
        state.until !== '' ||
        state.travelTypes.length > 0 ||
        state.travelPurposes.length > 0 ||
        !state.includeApproximated
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
        'travelTypes[]': state.travelTypes.length > 0 ? state.travelTypes : undefined,
        'travelPurposes[]': state.travelPurposes.length > 0 ? state.travelPurposes : undefined,
        includeApproximated: state.includeApproximated ? undefined : false,
    };
}

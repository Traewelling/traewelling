export function metersToDistance(meters: number): SplitDistance {
    const distance: SplitDistance = { kilometers: 0, meters: 0 };

    distance.kilometers = Math.floor(meters / 1000);
    distance.meters = Math.floor(meters % 1000);

    return distance;
}

export interface SplitDistance {
    kilometers: number;
    meters: number;
}

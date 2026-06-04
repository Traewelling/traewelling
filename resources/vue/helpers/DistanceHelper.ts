export function metersToDistance(meters: number): SplitDistance {
    const distance: SplitDistance = { gigameters: 0, megameters: 0, kilometers: 0, meters: 0 };

    distance.gigameters = Math.floor(meters / (1000 * 1000 * 1000));
    distance.megameters = Math.floor((meters % (1000 * 1000 * 1000)) / (1000 * 1000));
    distance.kilometers = Math.floor((meters % (1000 * 1000)) / 1000);
    distance.meters = Math.floor(meters % 1000);

    return distance;
}

export interface SplitDistance {
    gigameters: number;
    megameters: number;
    kilometers: number;
    meters: number;
}

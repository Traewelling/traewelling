import type {
    DataDrivenPropertyValueSpecification,
    ExpressionSpecification,
    LayerSpecification,
    StyleSpecification,
} from 'maplibre-gl';

/**
 * Custom OpenFreeMap vector basemap for Träwelling.
 *
 * The hosted OpenFreeMap styles (positron/dark) are general purpose maps: roads dominate and
 * railways are barely visible. Träwelling only cares about public transport, so this style renders
 * the OpenMapTiles schema with rails, tram/metro lines, their tunnels and the corresponding stops
 * as the primary content while everything else (roads, buildings, landuse, POIs) is toned down to
 * a faint orientation layer.
 *
 * Schema reference: https://openmaptiles.org/schema/
 *
 * Current / open problem: Trams for example are only visible at very near zoom level.
 * So maybe we need to switch to an self generated and hosted vector tile solution.
 */

export type MapTheme = 'light' | 'dark';

interface TransitPalette {
    background: string;
    water: string;
    waterway: string;
    green: string;
    landuse: string;
    building: string;
    roadMinor: string;
    roadMajor: string;
    roadMotorway: string;
    boundary: string;
    rail: string;
    railCasing: string;
    railHatch: string;
    transit: string;
    railService: string;
    ferry: string;
    stopFill: string;
    stopStroke: string;
    text: string;
    textHalo: string;
    placeText: string;
}

const PALETTES: Record<MapTheme, TransitPalette> = {
    light: {
        background: '#f5f4f0',
        water: '#d5e2ea',
        waterway: '#c8d9e3',
        green: '#e7ebe1',
        landuse: '#efeeea',
        building: '#e7e6e0',
        roadMinor: '#e9e8e3',
        roadMajor: '#e2e0d8',
        roadMotorway: '#dbd8cf',
        boundary: '#cdcac2',
        rail: '#333840',
        railCasing: 'rgba(245,244,240,0.85)',
        railHatch: '#f5f4f0',
        transit: '#5f6670',
        railService: '#9aa0a8',
        ferry: '#a9b0b8',
        stopFill: '#ffffff',
        stopStroke: '#333840',
        text: '#343a42',
        textHalo: 'rgba(245,244,240,0.9)',
        placeText: '#8b909a',
    },
    dark: {
        background: '#11141a',
        water: '#0d1a24',
        waterway: '#132330',
        green: '#161a19',
        landuse: '#171a21',
        building: '#1c2028',
        roadMinor: '#20242c',
        roadMajor: '#272c35',
        roadMotorway: '#2d333d',
        boundary: '#30363f',
        rail: '#d7dde5',
        railCasing: 'rgba(17,20,26,0.85)',
        railHatch: '#11141a',
        transit: '#98a1ad',
        railService: '#666d77',
        ferry: '#525a64',
        stopFill: '#11141a',
        stopStroke: '#d7dde5',
        text: '#dce2e9',
        textHalo: 'rgba(17,20,26,0.9)',
        placeText: '#7a818b',
    },
};

const LINE_GEOMETRY: ExpressionSpecification = [
    'match',
    ['geometry-type'],
    ['LineString', 'MultiLineString'],
    true,
    false,
];
const POLYGON_GEOMETRY: ExpressionSpecification = [
    'match',
    ['geometry-type'],
    ['MultiPolygon', 'Polygon'],
    true,
    false,
];

const RAIL_CLASS: ExpressionSpecification = ['==', ['get', 'class'], 'rail'];
const TRANSIT_CLASS: ExpressionSpecification = ['==', ['get', 'class'], 'transit'];
const IS_TUNNEL: ExpressionSpecification = ['==', ['get', 'brunnel'], 'tunnel'];
const IS_NOT_TUNNEL: ExpressionSpecification = ['!=', ['get', 'brunnel'], 'tunnel'];
const HAS_SERVICE: ExpressionSpecification = ['has', 'service'];

/** Stops served by heavy rail: these carry a station name people actually check into. */
const RAIL_STOPS: ExpressionSpecification = [
    'all',
    ['==', ['get', 'class'], 'railway'],
    ['match', ['get', 'subclass'], ['station', 'halt'], true, false],
];

/** Tram stops and metro stations: dense, so they appear later and stay smaller. */
const TRANSIT_STOPS: ExpressionSpecification = [
    'all',
    ['==', ['get', 'class'], 'railway'],
    ['match', ['get', 'subclass'], ['tram_stop', 'subway'], true, false],
];

const LOCAL_NAME: ExpressionSpecification = ['coalesce', ['get', 'name:latin'], ['get', 'name']];

function zoomWidth(stops: [number, number][]): DataDrivenPropertyValueSpecification<number> {
    return ['interpolate', ['linear'], ['zoom'], ...stops.flat()] as DataDrivenPropertyValueSpecification<number>;
}

const RAIL_WIDTH = zoomWidth([
    [5, 0.5],
    [8, 1.1],
    [11, 1.9],
    [14, 3],
    [18, 6],
]);

const RAIL_CASING_WIDTH = zoomWidth([
    [11, 3.6],
    [14, 5.6],
    [18, 10],
]);

const RAIL_HATCH_WIDTH = zoomWidth([
    [12, 1.1],
    [14, 1.8],
    [18, 3.4],
]);

const TRANSIT_WIDTH = zoomWidth([
    [8, 0.6],
    [11, 1.4],
    [14, 2.8],
    [18, 5.2],
]);

const SERVICE_WIDTH = zoomWidth([
    [13, 0.6],
    [16, 1.2],
    [18, 2],
]);

const ROAD_WIDTH = zoomWidth([
    [8, 0.4],
    [12, 1],
    [16, 3],
    [18, 6],
]);

const MAJOR_ROAD_WIDTH = zoomWidth([
    [7, 0.5],
    [10, 1],
    [14, 2],
    [18, 4],
]);

const MOTORWAY_WIDTH = zoomWidth([
    [5, 0.6],
    [10, 1.4],
    [14, 2.6],
    [18, 5],
]);

function baseLayers(palette: TransitPalette): LayerSpecification[] {
    return [
        {
            id: 'background',
            type: 'background',
            paint: { 'background-color': palette.background },
        },
        {
            id: 'water',
            type: 'fill',
            source: 'openmaptiles',
            'source-layer': 'water',
            filter: ['all', POLYGON_GEOMETRY, ['!=', ['get', 'brunnel'], 'tunnel']],
            paint: { 'fill-color': palette.water },
        },
        {
            id: 'landcover-green',
            type: 'fill',
            source: 'openmaptiles',
            'source-layer': 'landcover',
            filter: ['all', POLYGON_GEOMETRY, ['match', ['get', 'class'], ['wood', 'grass'], true, false]],
            paint: { 'fill-color': palette.green },
        },
        {
            id: 'park',
            type: 'fill',
            source: 'openmaptiles',
            'source-layer': 'park',
            filter: POLYGON_GEOMETRY,
            paint: { 'fill-color': palette.green, 'fill-opacity': 0.7 },
        },
        {
            id: 'landuse-residential',
            type: 'fill',
            source: 'openmaptiles',
            'source-layer': 'landuse',
            filter: ['all', POLYGON_GEOMETRY, ['==', ['get', 'class'], 'residential']],
            paint: { 'fill-color': palette.landuse },
        },
        {
            id: 'waterway',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'waterway',
            filter: LINE_GEOMETRY,
            minzoom: 8,
            paint: {
                'line-color': palette.waterway,
                'line-width': zoomWidth([
                    [8, 0.5],
                    [14, 1.4],
                    [18, 3],
                ]),
            },
        },
        {
            id: 'building',
            type: 'fill',
            source: 'openmaptiles',
            'source-layer': 'building',
            minzoom: 14,
            paint: {
                'fill-color': palette.building,
                'fill-opacity': ['interpolate', ['linear'], ['zoom'], 14, 0, 16, 1],
            },
        },
    ];
}

function roadLayers(palette: TransitPalette): LayerSpecification[] {
    return [
        {
            id: 'road-minor',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, ['match', ['get', 'class'], ['minor', 'service', 'track'], true, false]],
            minzoom: 12,
            paint: {
                'line-color': palette.roadMinor,
                'line-width': ROAD_WIDTH,
            },
        },
        {
            id: 'road-major',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: [
                'all',
                LINE_GEOMETRY,
                ['match', ['get', 'class'], ['primary', 'secondary', 'tertiary', 'trunk'], true, false],
            ],
            minzoom: 7,
            paint: {
                'line-color': palette.roadMajor,
                'line-width': MAJOR_ROAD_WIDTH,
            },
        },
        {
            id: 'road-motorway',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, ['==', ['get', 'class'], 'motorway']],
            minzoom: 5,
            paint: {
                'line-color': palette.roadMotorway,
                'line-width': MOTORWAY_WIDTH,
            },
        },
        {
            id: 'boundary',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'boundary',
            filter: ['all', ['<=', ['get', 'admin_level'], 2], ['!=', ['get', 'maritime'], 1]],
            paint: {
                'line-color': palette.boundary,
                'line-width': zoomWidth([
                    [3, 0.6],
                    [8, 1.2],
                    [14, 2],
                ]),
                'line-dasharray': [4, 2],
            },
        },
    ];
}

/**
 * The actual point of this style: railways, tram/metro lines and their tunnels.
 */
function railLayers(palette: TransitPalette): LayerSpecification[] {
    return [
        {
            id: 'ferry',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, ['==', ['get', 'class'], 'ferry']],
            minzoom: 7,
            paint: {
                'line-color': palette.ferry,
                'line-width': zoomWidth([
                    [7, 0.6],
                    [12, 1.2],
                    [16, 2],
                ]),
                'line-dasharray': [3, 3],
            },
        },
        {
            id: 'rail-service',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, RAIL_CLASS, HAS_SERVICE],
            minzoom: 13,
            paint: {
                'line-color': palette.railService,
                'line-width': SERVICE_WIDTH,
            },
        },
        {
            id: 'transit-tunnel',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, TRANSIT_CLASS, IS_TUNNEL],
            minzoom: 9,
            layout: { 'line-cap': 'butt', 'line-join': 'round' },
            paint: {
                'line-color': palette.transit,
                'line-width': TRANSIT_WIDTH,
                'line-opacity': 0.75,
                'line-dasharray': [2.2, 1.6],
            },
        },
        {
            id: 'rail-tunnel',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, RAIL_CLASS, ['!', HAS_SERVICE], IS_TUNNEL],
            minzoom: 7,
            layout: { 'line-cap': 'butt', 'line-join': 'round' },
            paint: {
                'line-color': palette.rail,
                'line-width': RAIL_WIDTH,
                'line-opacity': 0.75,
                'line-dasharray': [2.2, 1.6],
            },
        },
        {
            id: 'transit-line',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, TRANSIT_CLASS, IS_NOT_TUNNEL],
            minzoom: 8,
            layout: { 'line-cap': 'round', 'line-join': 'round' },
            paint: {
                'line-color': palette.transit,
                'line-width': TRANSIT_WIDTH,
            },
        },
        {
            id: 'rail-casing',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, RAIL_CLASS, ['!', HAS_SERVICE], IS_NOT_TUNNEL],
            minzoom: 11,
            layout: { 'line-cap': 'round', 'line-join': 'round' },
            paint: {
                'line-color': palette.railCasing,
                'line-width': RAIL_CASING_WIDTH,
            },
        },
        {
            id: 'rail-line',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, RAIL_CLASS, ['!', HAS_SERVICE], IS_NOT_TUNNEL],
            layout: { 'line-cap': 'round', 'line-join': 'round' },
            paint: {
                'line-color': palette.rail,
                'line-width': RAIL_WIDTH,
            },
        },
        {
            // Sleeper hatching on top of the rail line, the classic railway signature
            id: 'rail-hatch',
            type: 'line',
            source: 'openmaptiles',
            'source-layer': 'transportation',
            filter: ['all', LINE_GEOMETRY, RAIL_CLASS, ['!', HAS_SERVICE], IS_NOT_TUNNEL],
            minzoom: 12,
            layout: { 'line-cap': 'butt', 'line-join': 'round' },
            paint: {
                'line-color': palette.railHatch,
                'line-width': RAIL_HATCH_WIDTH,
                'line-dasharray': [0.35, 2.2],
            },
        },
    ];
}

function stopLayers(palette: TransitPalette): LayerSpecification[] {
    return [
        {
            id: 'transit-stop-circle',
            type: 'circle',
            source: 'openmaptiles',
            'source-layer': 'poi',
            filter: TRANSIT_STOPS,
            minzoom: 13,
            paint: {
                'circle-color': palette.stopFill,
                'circle-stroke-color': palette.transit,
                'circle-stroke-width': 1.5,
                'circle-radius': zoomWidth([
                    [13, 1.8],
                    [15, 3.2],
                    [18, 5],
                ]),
            },
        },
        {
            id: 'rail-stop-circle',
            type: 'circle',
            source: 'openmaptiles',
            'source-layer': 'poi',
            filter: RAIL_STOPS,
            minzoom: 12,
            paint: {
                'circle-color': palette.stopFill,
                'circle-stroke-color': palette.stopStroke,
                'circle-stroke-width': 2,
                'circle-radius': zoomWidth([
                    [11, 2.6],
                    [14, 4.5],
                    [18, 7],
                ]),
            },
        },
    ];
}

function labelLayers(palette: TransitPalette): LayerSpecification[] {
    return [
        {
            id: 'place-label',
            type: 'symbol',
            source: 'openmaptiles',
            'source-layer': 'place',
            filter: ['match', ['get', 'class'], ['city', 'town', 'village'], true, false],
            minzoom: 5,
            layout: {
                'text-field': LOCAL_NAME,
                'text-font': ['Noto Sans Regular'],
                'text-size': ['interpolate', ['linear'], ['zoom'], 5, 10, 12, 14],
                'text-max-width': 8,
            },
            paint: {
                'text-color': palette.placeText,
                'text-halo-color': palette.textHalo,
                'text-halo-width': 1.2,
            },
        },
        {
            id: 'transit-stop-label',
            type: 'symbol',
            source: 'openmaptiles',
            'source-layer': 'poi',
            filter: TRANSIT_STOPS,
            minzoom: 13,
            layout: {
                'text-field': LOCAL_NAME,
                'text-font': ['Noto Sans Regular'],
                'text-size': ['interpolate', ['linear'], ['zoom'], 13, 10, 18, 12],
                'text-anchor': 'top',
                'text-offset': [0, 0.7],
                'text-max-width': 9,
                'symbol-sort-key': ['match', ['get', 'subclass'], 'subway', 0, 1],
            },
            paint: {
                'text-color': palette.transit,
                'text-halo-color': palette.textHalo,
                'text-halo-width': 1.4,
            },
        },
        {
            id: 'rail-stop-label',
            type: 'symbol',
            source: 'openmaptiles',
            'source-layer': 'poi',
            filter: RAIL_STOPS,
            minzoom: 12,
            layout: {
                'text-field': LOCAL_NAME,
                'text-font': ['Noto Sans Bold'],
                'text-size': ['interpolate', ['linear'], ['zoom'], 12, 11, 16, 14],
                'text-anchor': 'top',
                'text-offset': [0, 0.8],
                'text-max-width': 9,
                'symbol-sort-key': ['match', ['get', 'subclass'], 'station', 0, 1],
            },
            paint: {
                'text-color': palette.text,
                'text-halo-color': palette.textHalo,
                'text-halo-width': 1.6,
            },
        },
    ];
}

/**
 * Builds the transit focused OpenFreeMap vector style for the given theme.
 */
export function buildTransitBasemapStyle(theme: MapTheme): StyleSpecification {
    const palette = PALETTES[theme];

    return {
        version: 8,
        name: `Träwelling Transit (${theme})`,
        projection: { type: 'globe' },
        glyphs: 'https://tiles.openfreemap.org/fonts/{fontstack}/{range}.pbf',
        sprite: 'https://tiles.openfreemap.org/sprites/ofm_f384/ofm',
        sources: {
            openmaptiles: {
                type: 'vector',
                url: 'https://tiles.openfreemap.org/planet',
            },
        },
        layers: [
            ...baseLayers(palette),
            ...roadLayers(palette),
            ...railLayers(palette),
            ...stopLayers(palette),
            ...labelLayers(palette),
        ],
    };
}

/// <reference types="vite/client" />
import { setWorkerUrl } from 'maplibre-gl';
import workerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';
import 'maplibre-gl/dist/maplibre-gl.css';

/**
 * MapLibre 6 resolves its worker relative to import.meta.url, which does not survive bundling.
 * Import this module once per entry point that renders a map, before the first map is created.
 */
setWorkerUrl(workerUrl);

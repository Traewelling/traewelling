<script>
import { trans } from 'laravel-vue-i18n';
import 'leaflet';
import { DtmRange } from '../helpers/DateRange';
import { useUserStore } from '../stores/user';

import('Leaflet-MovingMaker/MovingMarker');

const eventIcon = L.divIcon({
    html: '<i class="fa fa-calendar-day" style="line-height: 48px; font-size: 36px;"></i>',
    iconSize: [48, 48],
    className: 'text-trwl text-center',
});

export default {
    name: 'ActiveJourneyMap',
    props: {
        mapProvider: { type: String, default: 'default' },
        statusId: { type: Number, default: null },
        departure: { type: Number, default: null },
        arrival: { type: Number, default: null },
        lineColor: { type: String, default: null },
    },
    setup() {
        const user = useUserStore();
        return { user };
    },
    data() {
        return {
            map: null,
            points: [],
            routeLayer: null,
        };
    },
    computed: {
        provider() {
            if (this.user.user) {
                return this.user.user?.mapProvider || 'default';
            }
            return this.$props.mapProvider;
        },
        mapStyle() {
            return this.$props.statusId ? '' : 'min-height: 600px;';
        },
        parsedLineColor() {
            const hex = this.$props.lineColor;
            if (!hex) return null;
            const clean = String(hex).replace(/[^0-9a-fA-F]/g, '');
            if (clean.length === 6) return `#${clean}`;
            if (/^#[0-9a-fA-F]{6}$/.test(String(hex))) return hex;
            return null;
        },
    },
    mounted() {
        this.renderMap();
        if (this.$props.statusId) {
            this.fetchStatusPolyline();
        }
        this.initializeMap();
        this.fetchEvents();

        setInterval(() => {
            this.refreshMarkers();
        }, 20000);

        if (!this.$props.statusId) {
            setInterval(() => {
                this.initializeMap();
            }, 30000);
        }
    },
    methods: {
        trans,
        renderMap() {
            this.map = L.map(this.$refs.map, {
                center: [50.3, 10.47],
                zoom: 5,
            });
            setTilingLayer(this.provider, this.map);

            this.map.createPane('routes');
            this.map.getPane('routes').style.zIndex = 450;

            // LayerGroup for routes and border
            this.routeLayer = L.layerGroup([], { pane: 'routes' }).addTo(this.map);
        },
        canShowMarkers() {
            if (this.$props.arrival && this.$props.departure) {
                return this.$props.departure * 1000 <= Date.now() && this.$props.arrival * 1000 >= Date.now();
            }
            return true;
        },
        clearMarkersOnly() {
            this.points.forEach((point) => {
                if (point && point.marker) {
                    point.marker.remove();
                }
            });
            this.points = [];
        },
        clearRoute() {
            if (this.routeLayer) this.routeLayer.clearLayers();
        },

        fetchStatusPolyline() {
            this.clearRoute();

            fetch('/api/v1/polyline/' + this.$props.statusId).then((response) => {
                response.json().then((results) => {
                    const strokeColor = this.parsedLineColor || '#C0392B';

                    // casing in grey (for better visibility)
                    L.geoJSON(results.data, {
                        pane: 'routes',
                        style: {
                            color: '#181818',
                            weight: 7,
                            opacity: 0.9,
                            lineCap: 'round',
                            lineJoin: 'round',
                        },
                    }).addTo(this.routeLayer);

                    // main route
                    const main = L.geoJSON(results.data, {
                        pane: 'routes',
                        style: {
                            color: strokeColor,
                            weight: 5,
                            opacity: 1,
                            lineCap: 'round',
                            lineJoin: 'round',
                        },
                    }).addTo(this.routeLayer);

                    this.map.fitBounds(main.getBounds());
                });
            });
        },

        initializeMap() {
            let url = '/api/v1/positions';
            if (this.$props.statusId) url = url + '/' + this.$props.statusId;

            fetch(url)
                .then((response) => response.json())
                .then((results) => {
                    this.clearMarkersOnly();

                    results.data.forEach((result) => {
                        let entry = null;

                        if (result.point) {
                            const icon = this.getIconForStatus(result);
                            const markerLayer = this.canShowMarkers()
                                ? L.geoJSON(result.point, {
                                      pointToLayer: function (point, latlng) {
                                          return L.marker(latlng, { icon });
                                      },
                                  }).addTo(this.map)
                                : null;

                            entry = this.createPointObject(result, markerLayer);
                            this.points.push(entry);
                        }

                        if (result.polyline) {
                            const m = this.addMarker(result);
                            this.points.push(m);
                        }
                    });
                });
        },

        fetchEvents() {
            fetch('/api/v1/events')
                .then((response) => response.json())
                .then((results) => {
                    results.data.forEach(this.addEventMarker);
                });
        },
        addEventMarker(event) {
            if (!event.station) return;

            let marker = L.marker([event.station.latitude, event.station.longitude], {
                title: event.name,
                icon: eventIcon,
            }).addTo(this.map);

            const range = DtmRange.fromISO(event.begin, event.end);

            marker.bindPopup(`
        <strong><a href="${event.url}">${event.name}</a></strong><br />
        <i class="fa fa-user-clock"></i> ${event.host}<br />
        <i class="fa fa-calendar-day"></i> ${range.toLocaleDateString()}<br />
        <a href="/event/${event.slug}">${trans('events.show-all-for-event')}</a>
      `);
        },

        getIconForStatus(response) {
            return L.divIcon({
                className: 'custom-div-icon',
                html:
                    '<img class="img-thumbnail rounded-circle img-fluid" style="width: 20px;" src="' +
                    response.status.user.profilePictureUrl +
                    '" />',
                iconSize: [20, 20],
                iconAnchor: [9, 18],
            });
        },

        addMarker(data, oldMarker = null) {
            if (oldMarker) oldMarker.remove();
            if (!this.canShowMarkers()) return this.createPointObject(data);

            const line = [];
            data.polyline.features.forEach((point) => {
                line.push([point.geometry.coordinates[1], point.geometry.coordinates[0]]);
            });

            const marker = L.Marker.movingMarker(line, data.arrival * 1000 - Date.now(), {
                icon: this.getIconForStatus(data),
                autostart: true,
            }).addTo(this.map);
            marker.start();

            return this.createPointObject(data, marker);
        },

        createPointObject(point, marker = null) {
            return {
                statusId: point.statusId,
                arrival: point.arrival,
                departure: point.departure,
                lineName: point.lineName,
                marker: marker ?? null,
            };
        },

        refreshMarkers() {
            let refreshIds = [];
            this.points.forEach((point) => {
                if (point && point.departure * 1000 <= Date.now()) {
                    refreshIds.push(point.statusId);
                }
            });

            if (refreshIds.length) this.fetchPositions(refreshIds);
        },

        fetchPositions(refreshIds) {
            fetch('/api/v1/positions/' + refreshIds.join(','))
                .then((response) => response.json())
                .then((result) => {
                    let tmpResult = [];
                    let updatedIds = [];

                    result.data.forEach((stop) => {
                        tmpResult.push(stop);
                        let removeIdx = refreshIds.indexOf(stop.statusId);
                        if (removeIdx > -1) {
                            refreshIds.splice(removeIdx, 1);
                            updatedIds.push(stop.statusId);
                        }
                    });

                    this.points = this.points
                        .map((entry) => {
                            if (!entry) return entry;

                            if (refreshIds.indexOf(entry.statusId) > -1) {
                                if (entry.marker) entry.marker.remove();
                                return false;
                            }
                            if (updatedIds.indexOf(entry.statusId) > -1) {
                                tmpResult.forEach((result) => {
                                    if (result.polyline && result.statusId === entry.statusId) {
                                        entry = this.addMarker(result, entry.marker);
                                    }
                                });
                            }
                            return entry;
                        })
                        .filter(Boolean);
                });
        },
    },
};
</script>

<template>
    <div ref="map" class="map" :style="mapStyle" />
</template>

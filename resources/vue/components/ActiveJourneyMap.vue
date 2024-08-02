<template>
    <div
        class="map"
        :style="mapStyle"
        ref="map"
    ></div>
</template>

<script>
import 'leaflet';
import {trans} from "laravel-vue-i18n";
import {DtmRange} from "../helpers/DateRange";

import('Leaflet-MovingMaker/MovingMarker');

const eventIcon = L.divIcon({
    html: '<i class="fa fa-calendar-day" style="line-height: 48px; font-size: 36px;"></i>',
    iconSize: [48, 48],
    className: 'text-trwl text-center'
});

export default {
    props: {
        mapProvider: {
            type: String,
            default: 'default'
        },
        statusId: {
            type: Number,
            default: null
        },
        departure: {
            type: Number,
            default: null
        },
        arrival: {
            type: Number,
            default: null
        },
    },
    data() {
        return {
            map: null,
            points: [],
        }
    },
    computed: {
        mapStyle() {
            return this.$props.statusId ? '' : 'min-height: 600px;';
        }
    },
    mounted() {
        this.renderMap();
        if (this.$props.statusId) {
            this.fetchStatusPolyline();
        }
        this.initializeMap();
        this.fetchEvents();
        let temp = this;
        setInterval(function () {
            temp.refreshMarkers();
        }, 20000);

        if (!this.$props.statusId) {
            setInterval(function () {
                temp.initializeMap();
            }, 30000);
        }
    },
    methods: {
        trans,
        renderMap() {
            this.map = L.map(this.$refs.map, {
                center: [50.3, 10.47],
                zoom: 5
            });
            setTilingLayer(this.$props.mapProvider, this.map);
        },
        canShowMarkers() {
            if (this.$props.arrival && this.$props.departure) {
                return this.$props.departure * 1000 <= Date.now() && this.$props.arrival * 1000 >= Date.now();
            }

            return true;
        },
        clearAllElements() {
            this.points.forEach(point => {
                if (point.marker) {
                    point.marker.remove()
                }
            });
            this.points = [];
        },
        fetchStatusPolyline() {
            fetch('/api/v1/polyline/' + this.$props.statusId).then((response) => {
                response.json().then((results) => {
                    let polyline = L.geoJSON(results.data)
                        .setStyle({color: "rgb(192, 57, 43)", weight: 5})
                        .addTo(this.map);
                    this.map.fitBounds(polyline.getBounds());
                });
            });
        },
        initializeMap() {
            let url = '/api/v1/positions';
            if (this.$props.statusId) {
                url = url + '/' + this.$props.statusId;
            }
            fetch(url)
                .then((response) => response.json())
                .then((results) => {
                    this.clearAllElements();

                    results.data.forEach((result) => {
                        let marker = null;
                        if (result.point) {
                            const icon = this.getIconForStatus(result);

                            // If we can't show markers (yet), we just create the point object, so we can refresh it later
                            marker = this.canShowMarkers() ? this.createPointObject(
                                result,
                                L.geoJSON(result.point, {
                                    pointToLayer: function (point, latlng) {
                                        return L.marker(latlng, {icon: icon});
                                    }
                                }).addTo(this.map)
                            ) : this.createPointObject(result);
                        }

                        if (result.polyline) {
                            marker = this.addMarker(result);
                        }

                        this.points.push(marker);
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
            if (!event.station) {
                return;
            }
            let marker = L.marker([event.station.latitude, event.station.longitude], {
                title: event.name,
                icon: eventIcon
            }).addTo(this.map);

            const range = DtmRange.fromISO(event.begin, event.end);

            marker.bindPopup(`
                <strong><a href="${event.url}">${event.name}</a></strong><br />
                <i class="fa fa-user-clock"></i> ${event.host}<br />
                <i class="fa fa-calendar-day"></i> ${range.toLocaleDateString()}<br />
                <a href="/event/${event.slug}">${trans('events.show-all-for-event')}</a>`
            );
        },
        getIconForStatus(response) {
            return L.divIcon({
                className: 'custom-div-icon',
                html: '<img class="img-thumbnail rounded-circle img-fluid" style="width: 20px;" src="' + response.status.user.profilePictureUrl + '" />',
                iconSize: [20, 20],
                iconAnchor: [9, 18]
            });
        },
        addMarker(data, oldMarker = null) {
            if (oldMarker) {
                oldMarker.remove();
            }
            if (!this.canShowMarkers()) {
                return this.createPointObject(data);
            }

            let line = [];
            data.polyline.features.forEach(point => {
                line.push([point.geometry.coordinates[1], point.geometry.coordinates[0]]);
            });

            let marker = L.Marker.movingMarker(
                line,
                data.arrival * 1000 - Date.now(),
                {icon: this.getIconForStatus(data), autostart: true}
            ).addTo(this.map);
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
            }
        },
        refreshMarkers() {
            let refreshIds = [];
            this.points.forEach((point) => {
                if (point.departure * 1000 <= Date.now()) {
                    refreshIds.push(point.statusId);
                }
            })

            if (refreshIds.length) {
                this.fetchPositions(refreshIds);
            }
        },
        fetchPositions(refreshIds) {
            fetch('/api/v1/positions/' + refreshIds.join(','))
                .then((response) => response.json())
                .then((result) => {
                    let tmpResult  = [];
                    let updatedIds = [];
                    result.data.forEach((stop) => {
                        tmpResult.push(stop);

                        let removeIdx = refreshIds.indexOf(stop.statusId);
                        if (removeIdx > -1) {
                            refreshIds.splice(removeIdx, 1);
                            updatedIds.push(stop.statusId);
                        }
                    })

                    this.points = this.points.map((entry) => {
                        if (refreshIds.indexOf(entry.statusId) > -1) {
                            entry.marker.remove();
                            return false;
                        }
                        if (updatedIds.indexOf(entry.statusId) > -1) {
                            tmpResult.forEach((result) => {
                                if (result.polyline && result.statusId === entry.statusId) {
                                    entry = this.addMarker(result, entry.marker);
                                }
                            })
                        }
                        return entry;
                    });
                });
        }
    }
}
</script>

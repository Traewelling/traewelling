<template>
    <div
        id="active-map"
        class="embed-responsive embed-responsive-1by1"
        style="min-height: 600px;"
        ref="map"
    ></div>
</template>

<script>
require("leaflet/dist/leaflet.js");
require('Leaflet-MovingMaker/MovingMarker');

const trainIcon = L.divIcon({
    className: 'custom-div-icon',
    html: '<div style="background-color:#c30b82;" class="marker-pin">&nbsp;</div>',
    iconSize: [30, 30],
    iconAnchor: [15, 30]
});

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
        }
    },
    data() {
        return {
            map: null,
            points: []
        }
    },
    mounted() {
        this.renderMap();

        let temp = this;
        setInterval(function () {
            temp.refreshMarkers();
        }, 20000);
        setInterval(function () {
            temp.initializeMap();
        }, 30000);
    },
    methods: {
        renderMap() {
            this.map = L.map(this.$refs.map, {
                center: [50.3, 10.47],
                zoom: 5
            });
            setTilingLayer(this.$props.mapProvider, this.map);
            this.initializeMap();
            this.fetchEvents();
        },
        clearAllElements() {
            this.points.forEach(point => {
                if (point.marker) {
                    point.marker.remove()
                }
            });
            this.points = [];
        },
        initializeMap() {
            fetch('/api/v1/positions?' + Date.now()).then((response) => {
                response.json().then((results) => {
                    this.clearAllElements();

                    results.forEach((result) => {
                        let marker = null;
                        if (result.point) {
                            marker = this.createPointObject(
                                result,
                                L.geoJSON(result.point, {
                                    pointToLayer: function (point, latlng) {
                                        return L.marker(latlng, {icon: trainIcon});
                                    }
                                }).addTo(this.map)
                            );
                        }

                        if (result.polyline) {
                            marker = this.addMarker(result);
                        }

                        this.points.push(marker);
                    });
                });
            });
        },
        fetchEvents() {
            fetch('/api/v1/activeEvents').then((response) => {
                response.json().then((results) => {
                    results.data.forEach(this.addEventMarker);
                });
            });
        },
        addEventMarker(event) {
            let marker = L.marker([event.station.latitude, event.station.longitude], {
                title: event.name,
                icon: eventIcon
            }).addTo(this.map);

            marker.bindPopup(`
                <strong><a href="${event.url}">${event.name}</a></strong><br />
                <i class="fa fa-user-clock"></i> ${event.host}<br />
                <i class="fa fa-calendar-day"></i> ${event.begin} - ${event.end}<br />
                <a href="${event.url}">Alle Reisen zum Event anzeigen</a>`
            );
        },
        addMarker(data, oldMarker = null) {
            if (oldMarker) {
                oldMarker.remove();
            }
            let line = [];
            data.polyline.features.forEach(point => {
                line.push([point.geometry.coordinates[1], point.geometry.coordinates[0]]);
            });

            let marker = L.Marker.movingMarker(
                line,
                data.arrival * 1000 - Date.now(),
                {icon: trainIcon, autostart: true}
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
            fetch('/api/v1/positions/' + refreshIds.join(',')).then((response) => {
                response.json().then((result) => {
                    let tmpResult  = [];
                    let updatedIds = [];
                    result.forEach((stop) => {
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
            });
        }
    }
}
</script>

<style>
.marker-pin {
    width: 30px;
    height: 30px;
    border-radius: 50% 50% 50% 0;
    border-color: #830b62;
    border-width: 1px;
    background: #c30b82;
    position: absolute;
    transform: rotate(-45deg);
    left: 50%;
    top: 50%;
    margin: -15px 0 0 -15px;
}

// to draw white circle
.marker-pin::after {
    content: '';
    width: 24px;
    height: 24px;
    margin: 3px 0 0 3px;
    background: #fff;
    position: absolute;
    border-radius: 50%;
}

// to align icon
.custom-div-icon i {
    position: absolute;
    width: 22px;
    font-size: 22px;
    left: 0;
    right: 0;
    margin: 10px auto;
    text-align: center;
}
</style>

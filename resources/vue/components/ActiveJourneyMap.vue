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

let myIcon = L.divIcon({
    className: 'custom-div-icon',
    html: '<div style="background-color:#c30b82;" class="marker-pin">&nbsp;</div>',
    iconSize: [30, 30],
    iconAnchor: [15, 30]
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
            lines: [], //ToDo: Remove after development
            points: []
        }
    },
    mounted() {
        this.renderMap();

        let temp = this;
        setInterval(function () {
            temp.refreshMarkers();
        }, 5000)
    },
    methods: {
        renderMap() {
            console.info("moin")
            this.map = L.map(this.$refs.map, {
                center: [50.3, 10.47],
                zoom: 5
            });
            setTilingLayer(this.$props.mapProvider, this.map);
            this.drawShit();
        },
        clearAllElements() {
            this.points.forEach(point => {
                point.remove()
            });
            this.points = [];
            this.lines.forEach(line => {
                line.remove()
            });
            this.lines = [];

        },
        drawShit() {
            fetch('http://localhost:8000/api/v1/positions?' + Date.now()).then((response) => {
                response.json().then((results) => {
                    this.clearAllElements();

                    results.forEach((result) => {
                        let marker = null;
                        if (result.point) {
                            marker = this.createPointObject(
                                result,
                                L.geoJSON(result.point, {
                                pointToLayer: function(point, latlng) {
                                    return L.marker(latlng, {icon: myIcon});
                                }
                            }).addTo(this.map)
                            );
                        }

                        if (result.polyline) {
                            marker = this.addMarker(result);
                        }

                        this.points.push(marker);
                    })
                });
            });
        },
        addMarker(data, oldMarker = null) {
            if (oldMarker) {
                oldMarker.remove();
            }
            let line = [];
            data.polyline.features.forEach(point => {
                line.push([point.geometry.coordinates[1], point.geometry.coordinates[0]]);
            });

            this.lines.push(L.polyline(line).addTo(this.map)); //ToDo: Remove after development

            let marker = L.Marker.movingMarker(
                line,
                data.arrival * 1000 - Date.now(),
                {icon: myIcon, autostart: true}
            ).addTo(this.map);
            marker.start();

            return this.createPointObject(data, marker);
        },
        createPointObject(point, marker=null) {
            return {
                statusId: point.statusId,
                arrival: point.arrival,
                departure: point.departure,
                lineName: point.lineName,
                marker: marker ?? null,
            }
        },
        refreshMarkers() {
            console.info("REFRESH MARKERS");
            let refreshIds = [];
            this.points.forEach((point) => {
                if (point.departure * 1000 <= Date.now()) {
                    refreshIds.push(point.statusId);
                    console.log('refresh:', point.statusId);
                }
            })

            if (refreshIds.length) {
                this.fetchPositions(refreshIds);
            }
        },
        fetchPositions(refreshIds) {
            fetch('/api/v1/positions/' + refreshIds.join(',')).then((response) => {
                response.json().then((result) => {
                    let tmpResult = [];
                    let updatedIds = [];
                    result.forEach((stop) => {
                        tmpResult.push(stop);

                        let removeIdx = refreshIds.indexOf(stop.statusId);
                        if (removeIdx > -1) {
                            refreshIds.splice(removeIdx, 1);
                            updatedIds.push(stop.statusId);
                        }
                    })

                    let interim = this.points.map((entry) => {
                        if (refreshIds.indexOf(entry.statusId) > -1) {
                            return false;
                        }
                        if (updatedIds.indexOf(entry.statusId) > -1) {
                            tmpResult.forEach((result) => {
                                if (result.statusId === entry.statusId) {
                                    entry = this.addMarker(result, entry.marker);
                                }
                            })
                        }
                        return entry;
                    });
                    this.points = interim;
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

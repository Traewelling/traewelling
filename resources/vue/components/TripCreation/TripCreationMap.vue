<script>
import {defineComponent} from 'vue'
import 'leaflet';
import {trans} from "laravel-vue-i18n";

const trainIcon = L.divIcon({
  className: 'custom-div-icon',
  html: '<div style="background-color:#c30b82;" class="marker-pin">&nbsp;</div>',
  iconSize: [20, 20],
  iconAnchor: [9, 18]
});

export default defineComponent({
  name: "TripCreationMap",
  props: {
    mapProvider: {
      type: String,
      default: 'default'
    },
  },
  data() {
    return {
      map: null,
      points: [],
      origin: null,
      destination: null,
    }
  },
  computed: {
    mapStyle() {
      return '';
    }
  },
  mounted() {
    this.renderMap();
    this.initializeMap();
    let temp = this;
  },
  methods: {
    trans,
    invalidateSize() {
      setTimeout(() => {
        this.map.invalidateSize();
      }, 100);
    },
    renderMap() {
      this.map = L.map(this.$refs.map, {
        center: [50.3, 10.47],
        zoom: 5
      });
      setTilingLayer(this.$props.mapProvider, this.map);
    },
    clearAllElements() {
      this.points.forEach(point => {
        if (point.marker) {
          point.marker.remove()
        }
      });
      this.points = [];
    },
    addMarker(data, index, length) {
      let marker = L.marker(
          [data.latitude, data.longitude],
          {icon: trainIcon}
      ).addTo(this.map);

      marker.bindPopup(`<strong>${data.name}</strong> <i>${data.rilIdentifier || ''}</i>`);

      if (index === "origin") {
        if (this.origin) {
          this.origin.marker.remove();
        }
        this.origin = this.createPointObject(data, marker);
      } else if (index === "destination") {
        if (this.destination) {
          this.destination.marker.remove();
        }
        this.destination = this.createPointObject(data, marker);
      } else {

        if (length === this.points.length) {
          this.removeMarker(index);
        }

        if (index === 0 || index === this.points.length) {
          this.points.push(this.createPointObject(data, marker));
        } else {
          this.points.splice(index, 0, this.createPointObject(data, marker));
        }
      }

      this.zoomToMarkers();
    },
    zoomToMarkers() {
      let points = this.points;

      if (this.origin) {
        points = [this.origin, ...points];
      }

      if (this.destination) {
        points = [...points, this.destination];
      }

      let bounds = new L.featureGroup(
          points.map(point => point.marker)
      );
      this.map.fitBounds(bounds.getBounds());
    },
    removeMarker(index) {
      this.points[index].marker.remove();
      this.points.splice(index, 1);
    },
    initializeMap() {
      this.clearAllElements();
    },
    createPointObject(point, marker = null) {
      return {
        marker: marker ?? null,
      }
    },
  }
})
</script>

<template>

  <div
      class="map h-100"
      ref="map"
  ></div>
</template>

<style>
.marker-pin {
  width: 20px;
  height: 20px;
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
</style>

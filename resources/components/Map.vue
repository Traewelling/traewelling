<template>
  <div id="mapContainer"></div>
</template>

<script>
import "leaflet/dist/leaflet.js";
import L from "leaflet";

export default {
  name: "Map",
  data() {
    return {
      map: null
    };
  },
  props: {
    polyLines: null
  },
  methods: {
    setupLeafletMap() {
      const map = L.map("mapContainer", {
        center: [50.3, 10.47],
        zoom: 5, zoomControl: 1, dragging: 1, tap: 1
      });
      this.map  = map;
      L.tileLayer(
          "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
          {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: "abcd",
            maxZoom: 19
          }
      ).addTo(map);
      this.updatePolylines();
    },
    updatePolylines() {
      /**
       * First of all: Delete all polylines that already exist on the map
       */
      for (let i in this.map._layers) {
        if (this.map._layers[i]._path !== undefined) {
          try {
            this.map.removeLayer(this.map._layers[i]);
          } catch (e) {
              console.error("problem with " + e + this.map._layers[i]);
          }
        }
      }
      if (this.$props.polyLines) {
          let layer = L.geoJSON(this.$props.polyLines, {
              style: {color: "rgb(192, 57, 43)", weight: 5}
          }).addTo(this.map);
          this.map.fitBounds(layer.getBounds());
      }
    }
  },
  mounted() {
    this.setupLeafletMap();
  },
  watch: {
    polyLines() {
      if (this.$props.polyLines) {
        this.updatePolylines();
      }
    }
  }
}
</script>

<style scoped>

</style>

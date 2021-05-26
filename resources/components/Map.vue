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

      let lines = L.featureGroup();
      this.$props.polyLines.forEach((polyline) => {
        Object.values(polyline).forEach((coordinates) => {
          let line = L.polyline(coordinates).setStyle({color: "rgb(192, 57, 43)", weight: 5});
          lines.addLayer(line);
          //ToDo make the line more like the original
        });
      });

      lines.addTo(this.map);
      this.map.fitBounds(lines.getBounds());
    }
  },
  mounted() {
    this.setupLeafletMap();
  },
  watch: {
    polyLines() {
      this.updatePolylines();
    }
  }
}
</script>

<style scoped>

</style>

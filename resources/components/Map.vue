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
    };
  },
  props: {
    polyLines: null
  },
  methods: {
    setupLeafletMap () {
      const map = L.map("mapContainer", {zoomControl: 1, dragging: 1, tap: 1});
      L.tileLayer(
          "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
          {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: "abcd",
            maxZoom: 19
          }
      ).addTo(map);
      this.$props.polyLines.forEach((coordinates) => {
        let polyline = L.polyline(coordinates).setStyle({color: "rgb(192, 57, 43)", weight: 5}).addTo(map);

        map.fitBounds(polyline.getBounds());
      })
    },
  },
  mounted() {
    this.setupLeafletMap();
  }
}
</script>

<style scoped>

</style>

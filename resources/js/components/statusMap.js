require("leaflet/dist/leaflet.js");

Array.from(document.getElementsByClassName("statusMap")).forEach(elem => {
    var map = L.map(elem, {
        zoomControl: false,
        dragging: false,
        tap: false
    });
    console.log(elem);

    L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
        {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: "abcd",
            maxZoom: 19
        }
    ).addTo(map);

    const latlngs = JSON.parse(elem.dataset.polygon).map(([a, b]) => [b, a]);
    var polyline = L.polyline(latlngs)
        .setStyle({color: "rgb(192, 57, 43)", weight: 5})
        .addTo(map);

    map.fitBounds(polyline.getBounds());

    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
});

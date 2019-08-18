<div id="map-{{$status->id}}" style="width: 100%; height: 210px; overflow: hidden;"></div>

<script>
(function() {
    var map = L.map('map-{{$status->id}}', {zoomControl: false});
    
    var CartoDB_Voyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    const lnglats = {{$status->trainCheckin->getMapLines()}};
    const latlngs = lnglats.map(([a,b]) => [b,a]);
    var polyline = L.polyline(latlngs).setStyle({color: "rgb(192, 57, 43)", weight: 5}).addTo(map);

    map.fitBounds(polyline.getBounds());

    map.dragging.disable();
    map.touchZoom.disable();
    map.doubleClickZoom.disable();
    map.scrollWheelZoom.disable();
})();
</script>
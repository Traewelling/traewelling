<div id="map-{{$status->id}}" style="width: 100%; height: 200px; overflow: hidden;"></div>

<script>
(function() {
    var map = L.map('map-{{$status->id}}');
    
    // All of this does not work:
    // 
    // var CartoDB_Voyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    //     attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    //     subdomains: 'abcd',
    //     maxZoom: 19
    // }).addTo(map);
    // var OpenRailwayMap = L.tileLayer('https://{s}.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png', {
    //     maxZoom: 19,
    //     attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Map style: &copy; <a href="https://www.OpenRailwayMap.org">OpenRailwayMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
    // }).addTo(map);

    const lnglats = {{$status->trainCheckin->getMapLines()}};
    const latlngs = lnglats.map(([a,b]) => [b,a]);
    var polyline = L.polyline(latlngs).setStyle({color: "rgb(192, 57, 43)", weight: 5}).addTo(map);

    map.fitBounds(polyline.getBounds());
})();
</script>
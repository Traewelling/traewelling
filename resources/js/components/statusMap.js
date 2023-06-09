Array.from(document.getElementsByClassName("statusMap")).forEach(elem => {
    /**
     * The status map is used in dashboard and when viewing a single status.
     * Some map options like zooming should only be enabled in status view,
     * thus `includes/status.blade.php` sets data-showmapcontrols if the
     * request is `/status/{id}`.
     */
    const mapInStatusView = elem.dataset.showmapcontrols == " 1 ";

    let map = L.map(elem, {
        zoomControl: mapInStatusView,
        dragging: mapInStatusView,
        tap: mapInStatusView
    });

    setTilingLayer(mapprovider, map);

    let coordinates = JSON.parse(elem.dataset.polygon);
    let polyline    = L.polyline(coordinates)
        .setStyle({color: "rgb(192, 57, 43)", weight: 5})
        .addTo(map);

    map.fitBounds(polyline.getBounds());

    if (!mapInStatusView) {
        map.dragging.disable();
        map.touchZoom.disable();
        map.doubleClickZoom.disable();
        map.scrollWheelZoom.disable();
    }
});

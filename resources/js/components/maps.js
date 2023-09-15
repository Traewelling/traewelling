/**
 * Set the tiling layers for the current map.
 */

window.setTilingLayer = (mapprovider, map) => {
    switch (mapprovider) {
        case "open-railway-map":
            // Base map without labels
            L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}{r}.png", {
                    attribution: '&copy; <a href="https://carto.com/attributions" target="carto">CARTO</a>',
                    subdomains: "abcd",
                    maxZoom: 19
                }
            ).addTo(map);

            // Semi-transparent Open Railway Map overlay. There are additional filters on the tiles in css.
            new L.TileLayer('https://{s}.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://www.openrailwaymap.org/" target="orm">OpenRailwayMap</a>',
                minZoom: 2,
                maxZoom: 19,
                tileSize: 256
            }).addTo(map);

            //add additional copyright notice to map
            break;
        case "cargo":
        default:
            // Default voyager map
            L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png", {
                attribution: '&copy; <a href="https://carto.com/attributions" target="carto">CARTO</a>',
                subdomains: "abcd",
                maxZoom: 19
            }).addTo(map);
    }

    map.attributionControl.addAttribution('&copy; <a href="https://www.openstreetmap.org/copyright" target="osm">OpenStreetMap</a> contributors');
}

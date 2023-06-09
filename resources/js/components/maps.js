/**
 * Set the tiling layers for the current map.
 */

console.log("HELLO");

window.setTilingLayer = (mapprovider, map) => {
    switch (mapprovider) {
        case "open-railway-map":

            // Base map without labels
            L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}{r}.png", {
                    attribution:
                        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: "abcd",
                    maxZoom: 19
                }
            ).addTo(map);

            // Semi-transparent Open Railway Map overlay. There are additional filters on the tiles in css.
            new L.TileLayer('http://{s}.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png', {
                attribution: '<a href="https://www.openstreetmap.org/copyright">© OpenStreetMap contributors</a>, Style: <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA 2.0</a> <a href="http://www.openrailwaymap.org/">OpenRailwayMap</a> and OpenStreetMap',
                minZoom: 2,
                maxZoom: 19,
                tileSize: 256
            }).addTo(map);
            break;
        case "cargo":
        default:
            // Default voyager map
            L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png", {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: "abcd",
                maxZoom: 19
            }).addTo(map);
    }
}
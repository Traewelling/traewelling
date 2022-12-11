window.ActiveJourneys = class ActiveJourneys {

    static renderMap(statuses, events) {
        var map = L.map(document.getElementById('map'), {
            center: [50.3, 10.47],
            zoom: 5
        });

        L.tileLayer(
            "https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png",
            {
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: "abcd",
                maxZoom: 19
            }
        ).addTo(map);

        const swapC = ([lng, lat]) => [lat, lng];

        const updateMap = () => {

            /**
             * First of all: Delete all polylines that already exist on the map
             */
            for (i in map._layers) {
                if (map._layers[i]._path != undefined) {
                    try {
                        map.removeLayer(map._layers[i]);
                    } catch (e) {
                        console.error("problem with " + e + map._layers[i]);
                    }
                }
            }

            const tzoffset = new Date().getTimezoneOffset() * 60000; //offset in milliseconds
            const now      = new Date(Date.now() - tzoffset).toISOString();

            statuses.forEach(status => {
                try {
                    let i   = 0;
                    let j   = 0;
                    status.stops = status.stops.filter(s => !status.cancelled)
                        .map(status => {
                            status.stop.id = i++ + "_" + status.stop.id;
                            return status;
                        });

                    // Statuses with empty polylines (e.g. migrated or broken) stop right here and don't throw more errors than needed.
                    if (typeof status.polyline.features == "undefined") {
                        return;
                    }

                    status.polyline.features = status.polyline.features.map(f => {
                        if (typeof f.properties.id == "undefined") {
                            return f;
                        }
                        f.properties.id = j++ + "_" + f.properties.id;
                        return f;
                    });
                    const behindUs      = status.stops
                        .filter(
                            b =>
                                (b.departure != null && b.departure < now) ||
                                (b.arrival != null && b.arrival < now)
                        )
                        .map(b => b.stop.id);
                    const infrontofUs   = status.stops
                        .filter(
                            (b => b.arrival != null && b.arrival > now) ||
                            (b => b.departure != null && b.departure > now)
                        )
                        .map(b => b.stop.id);

                    const justInfrontofUs = status.stops[behindUs.length].stop.id;
                    // The last station is relevant for us, but we can't act with it like any other station before.
                    const justBehindUs    = behindUs.pop();

                    /**
                     * This piece calculates the distance between the last and the
                     *  upcoming train station, so we can interpolate between them.
                     */
                    let isInterestingBit   = false;
                    let stopDistLastToNext = 0;
                    for (let i = 0; i < status.polyline.features.length - 1; i++) {
                        if (status.polyline.features[i].properties.id == justBehindUs) {
                            isInterestingBit = true;
                        }
                        if (isInterestingBit) {
                            stopDistLastToNext += ActiveJourneys.distance(
                                status.polyline.features[i].geometry.coordinates[1],
                                status.polyline.features[i].geometry.coordinates[0],
                                status.polyline.features[i + 1].geometry.coordinates[1],
                                status.polyline.features[i + 1].geometry.coordinates[0]
                            );
                        }
                        if (status.polyline.features[i].properties.id == justInfrontofUs) {
                            isInterestingBit = false;
                        }
                    }

                    /**
                     * Here, we describe how far we are between the last and the upcoming stop.
                     */
                    const stationWeJustLeft = status.stops.find(b => b.stop.id == justBehindUs);
                    const leaveTime         = new Date(stationWeJustLeft.departure).getTime();
                    const stationNextUp     = status.stops.find(b => b.stop.id == justInfrontofUs);
                    const arriveTime        = new Date(stationNextUp.departure).getTime();
                    const nowTime           = new Date().getTime();
                    status.percentage            = (nowTime - leaveTime) / (arriveTime - leaveTime);

                    /**
                     * Now, let's get through all polylines.
                     */
                    let sI = -1; // ID of the last visited Station

                    // Since we traverse through all polygons, we need to check, if we're
                    // actually on the train.
                    let inTheTrain = false;

                    // This is the distance that between the last station and the polygon that
                    // we're traversing through. We just change the value, once we're in the
                    //interesting piece of the journey.
                    let polyDistSinceStop = 0;

                    for (let i = 0; i < status.polyline.features.length - 1; i++) {
                        if (
                            status.polyline.features[i].properties.id == status.stops[sI + 1].stop.id
                        ) {
                            sI += 1;
                        }

                        if (status.stops[sI].stop.id.endsWith(status.origin)) {
                            inTheTrain = true;
                        }

                        if (inTheTrain) {
                            let isSeen = true;

                            if (justBehindUs == status.stops[sI].stop.id) {
                                // The interesting part.
                                polyDistSinceStop += ActiveJourneys.distance(
                                    status.polyline.features[i].geometry.coordinates[1],
                                    status.polyline.features[i].geometry.coordinates[0],
                                    status.polyline.features[i + 1].geometry.coordinates[1],
                                    status.polyline.features[i + 1].geometry.coordinates[0]
                                );

                                isSeen = polyDistSinceStop / stopDistLastToNext < status.percentage;
                            } else if (behindUs.indexOf(status.stops[sI].stop.id) > -1) {
                                isSeen = true;
                            } else if (infrontofUs.indexOf(status.stops[sI].stop.id) > -1) {
                                isSeen = false;
                            }

                            L.polyline([
                                swapC(status.polyline.features[i].geometry.coordinates),
                                swapC(status.polyline.features[i + 1].geometry.coordinates)
                            ])
                                .setStyle({
                                    color: isSeen
                                        ? "rgb(192, 57, 43)"
                                        : "#B8B8B8",
                                    weight: 5
                                })
                                .addTo(map);
                        }

                        if (status.stops[sI].stop.id.endsWith(status.destination)) {
                            // After the last station on the trip, we don't need to traverse our polygons anymore.
                            break;
                        }
                    }
                } catch (e) {
                    console.error(e);
                }
            });

        };
        updateMap();
        setInterval(() => {
            updateMap();
        }, 5 * 1000);


        /////////// Events ///////////

        const icon = L.divIcon({
            html: '<i class="fa fa-calendar-day" style="line-height: 48px; font-size: 36px;"></i>',
            iconSize: [48, 48],
            className: 'text-trwl text-center'
        });

        events.forEach((event) => {
            var marker = L.marker([event.ts.latitude, event.ts.longitude], {
                title: event.name,
                icon: icon
            }).addTo(map);

            marker.bindPopup(`<strong><a href="${event.url}">${event.name}</a></strong><br />
<i class="fa fa-user-clock"></i> ${event.host}<br />
<i class="fa fa-calendar-day"></i> ${event.begin} - ${event.end}<br />
<a href="${event.mapLink}">Alle Reisen zum Event anzeigen</a><br />
${event.closestLink}`);
        });
    }

    /**
     * This one is stolen from https://snipplr.com/view/25479/calculate-distance-between-two-points-with-latitude-and-longitude-coordinates/
     */
    static distance(lat1, lon1, lat2, lon2) {
        var R    = 6371; // km (change this constant to get miles)
        var dLat = ((lat2 - lat1) * Math.PI) / 180;
        var dLon = ((lon2 - lon1) * Math.PI) / 180;
        var a    =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos((lat1 * Math.PI) / 180) *
                Math.cos((lat2 * Math.PI) / 180) *
                Math.sin(dLon / 2) *
                Math.sin(dLon / 2);
        var c    = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var d    = R * c;
        return d;
    }

}

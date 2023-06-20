window.ActiveJourneys = class ActiveJourneys {

    static renderMap(statuses, events) {
        var map = L.map(document.getElementById('map'), {
            center: [50.3, 10.47],
            zoom: 5
        });

        setTilingLayer(mapprovider, map);

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
                    let i            = 0;
                    let j            = 0;
                    status.stopovers = status.stopovers.filter(status => !status.cancelled)
                        .map(status => {
                            status.evaIdentifier = i++ + "_" + status.evaIdentifier;
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
                    const behindUs           = status.stopovers
                        .filter(
                            stopover =>
                                (stopover.departure != null && stopover.departure < now) ||
                                (stopover.arrival != null && stopover.arrival < now)
                        )
                        .map(stopover => stopover.evaIdentifier);
                    const infrontofUs        = status.stopovers
                        .filter(
                            (stopover => stopover.arrival != null && stopover.arrival > now) ||
                            (stopover => stopover.departure != null && stopover.departure > now)
                        )
                        .map(stopover => stopover.evaIdentifier);

                    const justInfrontofUs = status.stopovers[behindUs.length].evaIdentifier;
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
                    const stationWeJustLeft = status.stopovers.find(stopover => stopover.evaIdentifier == justBehindUs);
                    const leaveTime         = new Date(stationWeJustLeft.departure).getTime();
                    const stationNextUp     = status.stopovers.find(stopover => stopover.evaIdentifier == justInfrontofUs);
                    const arriveTime        = new Date(stationNextUp.departure).getTime();
                    const nowTime           = new Date().getTime();
                    status.percentage       = (nowTime - leaveTime) / (arriveTime - leaveTime);

                    /**
                     * Now, let's get through all polylines.
                     */
                    let sI = -1; // ID of the last visited Station

                    // Since we traverse through all polygons, we need to check, if we're
                    // actually on the train.
                    let inTheTrain = false;

                    // This is the distance that between the last station and the polygon that
                    // we're traversing through. We just change the value, once we're in the
                    // interesting piece of the journey.
                    let polyDistSinceStop = 0;

                    for (let i = 0; i < status.polyline.features.length - 1; i++) {
                        if (status.polyline.features[i].properties.id == status.stopovers[sI + 1].evaIdentifier) {
                            sI += 1;
                        }

                        if (status.stopovers[sI].evaIdentifier.endsWith(status.origin)) {
                            inTheTrain = true;
                        }

                        if (inTheTrain) {
                            let isSeen = true;

                            if (justBehindUs == status.stopovers[sI].evaIdentifier) {
                                // The interesting part.
                                polyDistSinceStop += ActiveJourneys.distance(
                                    status.polyline.features[i].geometry.coordinates[1],
                                    status.polyline.features[i].geometry.coordinates[0],
                                    status.polyline.features[i + 1].geometry.coordinates[1],
                                    status.polyline.features[i + 1].geometry.coordinates[0]
                                );

                                isSeen = polyDistSinceStop / stopDistLastToNext < status.percentage;
                            } else if (behindUs.indexOf(status.stopovers[sI].evaIdentifier) > -1) {
                                isSeen = true;
                            } else if (infrontofUs.indexOf(status.stopovers[sI].evaIdentifier) > -1) {
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

                        if (status.stopovers[sI].evaIdentifier.endsWith(status.destination)) {
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
        let R    = 6371; // km (change this constant to get miles)
        let dLat = ((lat2 - lat1) * Math.PI) / 180;
        let dLon = ((lon2 - lon1) * Math.PI) / 180;
        let a    =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos((lat1 * Math.PI) / 180) *
                Math.cos((lat2 * Math.PI) / 180) *
                Math.sin(dLon / 2) *
                Math.sin(dLon / 2);
        let c    = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

}

@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('content')
<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8" id="activeJourneys">

                <div id="map"></div>
                <script>
window.addEventListener("load", () => {
    var map = L.map(document.getElementById('map'), {
        center: [50.27264, 7.26469],
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


    // const journeys = [
    // @foreach($polylines as $p)
    //     {{$p}},
    // @endforeach
    // ];

    // journeys.forEach(j => {
    //     const latlngs = j.map(([a, b]) => [b, a]);
    //     var polyline = L.polyline(latlngs)
    //         .setStyle({
    //             color: "rgb(192, 57, 43)",
    //             weight: 5
    //         })
    //         .addTo(map);
    // });

    const statuses = [
        @foreach($statuses as $s)
            {
                id: {{$s->id}},
                origin: {{$s->trainCheckin->origin}},
                destination: {{$s->trainCheckin->destination}},
                <?php $hafas = $s->trainCheckin->getHafasTrip()->first() ?>
                polyline: <?php echo $hafas->polyline ?>,
                stopovers: <?php echo $hafas->stopovers ?>,
                percentage: 0,
            },
        @endforeach
    ];

    const swapC = ([lng, lat]) => [lat, lng];
    function distance(lat1, lon1, lat2, lon2) {
        var R = 6371; // km (change this constant to get miles)
        var dLat = ((lat2 - lat1) * Math.PI) / 180;
        var dLon = ((lon2 - lon1) * Math.PI) / 180;
        var a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos((lat1 * Math.PI) / 180) *
                Math.cos((lat2 * Math.PI) / 180) *
                Math.sin(dLon / 2) *
                Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        var d = R * c;
        return d;
    }

    const tzoffset = (new Date()).getTimezoneOffset() * 60000; //offset in milliseconds
    const now = (new Date(Date.now() - tzoffset)).toISOString();
    statuses.forEach(s => {

        const behindUs = s.stopovers.filter(b => (b.departure != null && b.departure < now) || (b.arrival != null && b.arrival < now)).map(b => b.stop.id);
        const infrontofUs = s.stopovers.filter((b => b.arrival != null && b.arrival > now) || (b => b.departure != null && b.departure > now)).map(b => b.stop.id);
        
        
        // The last station is relevant for us, but we can't act with it like any other station before.
        const justBehindUs = behindUs.pop();
        const justInfrontofUs = s.stopovers[behindUs.length +1].stop.id;

        console.log(justBehindUs, justInfrontofUs);

        console.log(behindUs);
        console.log(infrontofUs);
        
        

        /**
         * This piece calculates the distance between the last and the
         *  upcoming train station, so we can interpolate between them.
         */
        let isInterestingBit = false;
        let distanceBetweenLastAndNextTrainStation = 0;
        for (let i = 0; i < s.polyline.features.length -1; i++) {
            if(s.polyline.features[i].properties.id == justBehindUs) {
                isInterestingBit = true;
            }
            if(isInterestingBit) {
                distanceBetweenLastAndNextTrainStation += distance(
                        s.polyline.features[i].geometry.coordinates[1],
                        s.polyline.features[i].geometry.coordinates[0],
                        s.polyline.features[i+1].geometry.coordinates[1],
                        s.polyline.features[i+1].geometry.coordinates[0],);
            }
            if(s.polyline.features[i].properties.id == justInfrontofUs) {
                isInterestingBit = false;
            }
        }
        console.log(distanceBetweenLastAndNextTrainStation);
        
        

        const stationWeJustLeft = s.stopovers.find(b => b.stop.id == justBehindUs);
        const leaveTime = (new Date(stationWeJustLeft.departure)).getTime();
        const stationNextUp = s.stopovers.find(b => b.stop.id == justInfrontofUs);
        const arriveTime = (new Date(stationNextUp.departure)).getTime();
        const nowTime = (new Date()).getTime();

        s.percentage = (nowTime - leaveTime) / (arriveTime - leaveTime);

        let sIndex = -1;
        let inTheTrain = false;
        let travelledDistanceSinceLastStop = 0;        
        console.log(s.polyline.features);
        for (let i = 0; i < (s.polyline.features.length -1); i++) {
            if (s.polyline.features[i].properties.id == s.stopovers[sIndex + 1].stop.id) {
                sIndex += 1;
            }

            if (s.stopovers[sIndex].stop.id == s.origin) {
                inTheTrain = true;
            }

            if(inTheTrain) {
                let isBehindUs = true;

                if(justBehindUs == s.stopovers[sIndex].stop.id
                    // && justInfrontofUs == s.stopovers[sIndex +1].stop.id
                    ) {
                 //   console.log("hier wirds spannend");
                    travelledDistanceSinceLastStop += distance(
                        s.polyline.features[i].geometry.coordinates[1],
                        s.polyline.features[i].geometry.coordinates[0],
                        s.polyline.features[i+1].geometry.coordinates[1],
                        s.polyline.features[i+1].geometry.coordinates[0],);

                        isBehindUs = (travelledDistanceSinceLastStop / distanceBetweenLastAndNextTrainStation < s.percentage);
                } else if(behindUs.indexOf(s.stopovers[sIndex].stop.id) > -1) {
                  //  console.log("Dark red!");
                    isBehindUs = true;
                } else if (infrontofUs.indexOf(s.stopovers[sIndex].stop.id) > -1) {
                  //  console.log("lighter red..");
                    isBehindUs = false;
                }

                console.log(isBehindUs);

                var polyline = L.polyline([
                    swapC(s.polyline.features[i].geometry.coordinates),
                    swapC(s.polyline.features[i+1].geometry.coordinates)
                ]).setStyle({
                        color: isBehindUs ? "rgb(192, 57, 43)" : "rgb(43, 138, 192)",
                        weight: 5
                    })
                    .addTo(map);

            }

            if (s.stopovers[sIndex].stop.id == s.destination) {
                inTheTrain = false;
            }
        }

        // console.log(behindUs);

        // let polylineIndex = 0;
        // let stopoversIndex = -1;
        // let isOnTrip = false;

        // for (let polylineIndex = 0; polylineIndex < s.polyline.features.length; polylineIndex++) {
        //     if(!typeof s.polyline.features[polylineIndex].properties.id == 'undefined')
        //         console.log(s.polyline.features[polylineIndex].properties.id);
        //     console.log(s.stopovers[stopoversIndex + 1].stop.id);
        //     if(s.polyline.features[polylineIndex].properties.id == s.stopovers[stopoversIndex + 1].stop.id) {
        //         // The features arrived at a new stopover!
        //         stopoversIndex++;
        //     }

        //     if(s.stopovers[stopoversIndex].stop.id == s.origin) { // We found the start of the statuses trip
        //         isOnTrip = true;   
        //     }
        //     if(isOnTrip) {
        //         console.log("?");
        //         if(behindUs.filter( b => b.stop.id == s.stopovers[stopoversIndex].stop.id) > -1) {
        //             console.log("!");
                    
        //         }
        //     }


        //     if(s.stopovers[stopoversIndex].stop.id == s.checkinDestination) {// We found the end of the statuses trip
        //         isOnTrip = false;
        //     }
        // }
    });
});
                </script>

                <!-- The status cards -->
                @foreach($statuses as $status)
                    @include('includes.status')
                @endforeach
            </div>
        </div>
    </div><!--- /container -->
@endsection

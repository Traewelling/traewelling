export let travelImages = ["bus", "suburban", "subway", "tram"];

export let EventModel = {
    id: 0,
    name: "",
    slug: "",
    hashtag: "",
    host: "",
    url: "",
    begin: "",
    end: "",
    trainDistance: 0,
    trainDuration: 0,
    station: {
        id: 0,
        name: "",
        latitude: 0,
        longitude: 0,
        ibnr: 0,
        rilIdentifier: null
    }
};

export let Stopover = {
    id: 0,
    name: "",
    rilIdentifier: null,
    trainStationId: 0,
    arrival: "",
    arrivalPlanned: "",
    arrivalReal: null,
    arrivalPlatformPlanned: null,
    arrivalPlatformReal: null,
    departure: "",
    departurePlanned: "",
    departureReal: null,
    departurePlatformPlanned: null,
    departurePlatformReal: null,
    plattform: null,
    isArrivalDelayed: false,
    isDepartureDelayed: false
};

export let StatusModel = {
    id: 0,
    body: "",
    type: "",
    createdAt: "",
    user: 0,
    username: "",
    business: 0,
    visibility: 0,
    likes: 0,
    liked: null,
    train: {
        trip: 0,
        category: "",
        number: "",
        lineName: null,
        distance: 0,
        points: 0,
        delay: null,
        duration: 0,
        speed: 0,
        origin: Stopover,
        destination: Stopover,
        polyline: ""
    },
    EventModel
};

export let PolyLineModel = {};

export let ProfileModel = {
    id: 0,
    displayName: "",
    username: "",
    trainDistance: 0,
    trainDuration: 0,
    trainSpeed: 0,
    points: 0,
    twitterUrl: null,
    mastodonUrl: null,
    privateProfile: false,
    userInvisibleToMe: true,
};

export let LeaderboardUserModel = {
    username: "",
    trainDuration: 0.0,
    trainDistance: 0.0,
    trainSpeed: 0.0,
    points: 0
};

export let travelReason = [
    {
        icon: "fa fa-user",
        desc: "_.stationboard.business.private",
        detail: null
    },
    {
        icon: "fa fa-briefcase",
        desc: "_.stationboard.business.business",
        detail: "_.stationboard.business.business.detail"
    },
    {
        icon: "fa fa-building",
        desc: "_.stationboard.business.commute",
        detail: "_.stationboard.business.commute.detail"
    }
];

export let visibility = [
    {
        icon: "fa fa-globe-americas",
        desc: "_.status.visibility.0",
        detail: "_.status.visibility.0.detail"
    },
    {
        icon: "fa fa-lock-open",
        desc: "_.status.visibility.1",
        detail: "_.status.visibility.1.detail"
    },
    {
        icon: "fa fa-user-friends",
        desc: "_.status.visibility.2",
        detail: "_.status.visibility.2.detail"
    },
    {
        icon: "fa fa-lock",
        desc: "_.status.visibility.3",
        detail: "_.status.visibility.3.detail"
    },
];

/* eslint-disable */
/* tslint:disable */

/*
 * ---------------------------------------------------------------
 * ## THIS FILE WAS GENERATED VIA SWAGGER-TYPESCRIPT-API        ##
 * ##                                                           ##
 * ## AUTHOR: acacode                                           ##
 * ## SOURCE: https://github.com/acacode/swagger-typescript-api ##
 * ---------------------------------------------------------------
 */

/**
 * Coordinate
 * GeoJson Coordinates
 */
export interface Coordinate {
    /**
     * @format float
     * @example "Feature"
     */
    type?: number;
    /** @example "{}" */
    properties?: object;
    geometry?: {
        /** @example "Point" */
        type?: string;
        coordinates?: any[];
    };
}

/**
 * FeatureCollection
 * featurecollection of multiple GeoJson points
 */
export interface FeatureCollection {
    /**
     * type
     * @example "FeatureCollection"
     */
    type?: string;
    features?: Coordinate[];
}

/**
 * LivePointDto
 * All necessary information to calculate live position
 */
export interface LivePointDto {
    /** GeoJson Coordinates */
    point?: Coordinate;
    /** featurecollection of multiple GeoJson points */
    polyline?: FeatureCollection;
    /**
     * arrival
     * arrival at end of polyline in UNIX time format
     * @format integer
     * @example 1692538680
     */
    arrival?: number;
    /**
     * departure
     * departure at start of polyline in UNIX time format
     * @format integer
     * @example 1692538740
     */
    departure?: number;
    /**
     * lineName
     * name of line
     * @format string
     * @example "ICE 123"
     */
    lineName?: string;
    /**
     * statusId
     * ID of status
     * @deprecated
     * @format int
     * @example 12345
     */
    statusId?: number;
}

/**
 * Mention
 * Mentioned user and position in status body
 */
export interface MentionDto {
    /** User model */
    user?: User;
    /**
     * position
     * @format int
     * @example 0
     */
    position?: number;
    /**
     * length
     * @format integer
     * @example 4
     */
    length?: number;
}

/**
 * Station
 * train station model
 */
export interface Station {
    /**
     * id
     * id
     * @example "4711"
     */
    id?: number;
    /**
     * name
     * name of the station
     * @example "Karlsruhe Hbf"
     */
    name?: string;
    /**
     * latitude
     * latitude of the station
     * @format float
     * @example "48.991591"
     */
    latitude?: number;
    /**
     * longitude
     * longitude of the station
     * @format float
     * @example "8.400538"
     */
    longitude?: number;
    /**
     * ibnr
     * IBNR of the station
     * @example "8000191"
     */
    ibnr?: number;
    /**
     * rilIdentifier
     * Identifier specified in 'Richtline 100' of the Deutsche Bahn
     * @example "RK"
     */
    rilIdentifier?: string | null;
}

/**
 * Business
 * What type of travel (0=private, 1=business, 2=commute) did the user specify?
 * @example 0
 */
export enum Business {
    Value0 = 0,
    Value1 = 1,
    Value2 = 2,
}

/**
 * category
 * Category of transport.
 * @example "suburban"
 */
export enum HafasTravelType {
    NationalExpress = "nationalExpress",
    National = "national",
    RegionalExp = "regionalExp",
    Regional = "regional",
    Suburban = "suburban",
    Bus = "bus",
    Ferry = "ferry",
    Subway = "subway",
    Tram = "tram",
    Taxi = "taxi",
    Plane = "plane",
}

/**
 * MapProvider
 * What type of map provider (cargo, open-railway-map) did the user specify?
 * @example "cargo"
 */
export enum MapProvider {
    Cargo = "cargo",
    OpenRailwayMap = "open-railway-map",
}

/**
 * visibility
 * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private) did the user specify for
 *  *     future posts to Mastodon? Some instances such as chaos.social discourage bot posts on public timelines.
 * @example 1
 */
export enum MastodonVisibility {
    Value0 = 0,
    Value1 = 1,
    Value2 = 2,
    Value3 = 3,
}

/**
 * PointsReason
 * What is the reason for the points calculation factor? (0=in time => 100%, 1=good enough => 25%, 2=not sufficient (1 point), 3=forced => no points, 4=manual trip => no points, 5=points disabled)
 * @example 1
 */
export enum PointReason {
    Value0 = 0,
    Value1 = 1,
    Value2 = 2,
    Value3 = 3,
    Value4 = 4,
    Value5 = 5,
}

/**
 * visibility
 * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
 *  *      user specify?
 * @example 0
 */
export enum StatusVisibility {
    Value0 = 0,
    Value1 = 1,
    Value2 = 2,
    Value3 = 3,
    Value4 = 4,
}

/**
 * travelType
 * When adding a new travel type, make sure to add it to the translation file as well.
 * @example "suburban"
 */
export enum TravelType {
    Express = "express",
    Regional = "regional",
    Suburban = "suburban",
    Bus = "bus",
    Ferry = "ferry",
    Subway = "subway",
    Tram = "tram",
    Taxi = "taxi",
    Plane = "plane",
}

/**
 * FriendCheckinSetting
 * @example "forbidden"
 */
export enum FriendCheckinSetting {
    Forbidden = "forbidden",
    Friends = "friends",
    List = "list",
}

/** CheckinResponse */
export interface CheckinSuccessResource {
    status?: StatusResource;
    /** Points model */
    points?: Points;
    /** Statuses of other people on this connection */
    alsoOnThisconnection?: StatusResource[];
}

/** Client */
export interface ClientResource {
    /**
     * Model -> OAuthClient
     * @example 1
     */
    id?: number;
    /** @example "Träwelling App" */
    name?: string;
    /** @example "https://traewelling.de/privacy-policy" */
    privacyPolicyUrl?: string;
}

/** EventDetails */
export interface EventDetailsResource {
    /** @example 39 */
    id?: number;
    /** @example "9_euro_ticket" */
    slug?: string;
    /** @example 12345 */
    trainDistance?: number;
    /** @example 12345 */
    trainDuration?: number;
}

/** Event */
export interface EventResource {
    /** @example 39 */
    id?: number;
    /** @example "9-Euro-Ticket" */
    name?: string;
    /** @example "9_euro_ticket" */
    slug?: string;
    /** @example "NeunEuroTicket" */
    hashtag?: string;
    /** @example "9-Euro-Ticket GmbH" */
    host?: string;
    /** @example "https://9-euro-ticket.de" */
    url?: string;
    /**
     * @format date-time
     * @example "2022-01-01T00:00:00+00:00"
     */
    begin?: string;
    /**
     * @format date-time
     * @example "2022-01-02T00:00:00+00:00"
     */
    end?: string;
    /** train station model */
    station?: Station;
}

/** LeaderboardUserResource */
export interface LeaderboardUserResource {
    /** User model with just basic information */
    user?: LightUserResource;
    /**
     * duration travelled in minutes
     * @example 6
     */
    totalDuration?: number;
    /**
     * distance travelled in meters
     * @example 12345
     */
    totalDistance?: number;
    /** points of user */
    points?: number;
}

/**
 * LightUser
 * User model with just basic information
 */
export interface LightUserResource {
    /** @example 1 */
    id: number;
    /** @example "Gertrud" */
    displayName: string;
    /** @example "Gertrud123" */
    username: string;
    /** @example "https://traewelling.de/@Gertrud123/picture" */
    profilePicture?: string;
    /** @example "https://traewelling.social/@Gertrud123" */
    mastodonUrl?: string;
    /** @example false */
    preventIndex?: boolean;
}

export interface OperatorResource {
    /** @example 1 */
    id?: number;
    /** @example "db-regio-ag-nord" */
    identifier?: string;
    /** @example "DB Regio AG Nord" */
    name?: string;
}

/** Station */
export interface StationResource {
    /** @example "1" */
    id?: number;
    /** @example "Karlsruhe Hbf" */
    name?: string;
    /** @example "48.993207" */
    latitude?: number;
    /** @example "8.400977" */
    longitude?: number;
    /** @example "8000191" */
    ibnr?: string;
    /** @example "RK" */
    rilIdentifier?: string;
}

/** Status */
export interface StatusResource {
    /** @example 12345 */
    id?: number;
    /**
     * User defined status text
     * @example "Hello world!"
     */
    body?: any;
    /** Mentions in the status body */
    bodyMentions?: MentionDto[];
    /** What type of travel (0=private, 1=business, 2=commute) did the user specify? */
    business?: Business;
    /**
     * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
     *  *      user specify?
     */
    visibility?: StatusVisibility;
    /**
     * How many people have liked this status
     * @example 12
     */
    likes?: number;
    /**
     * Did the currently authenticated user like this status? (if unauthenticated = false)
     * @example true
     */
    liked?: boolean;
    /**
     * Do the author of this status and the currently authenticated user allow liking of statuses? Only show the like UI if set to true
     * @example true
     */
    isLikable?: boolean;
    client?: ClientResource;
    /**
     * creation date of this status
     * @format datetime
     * @example "2022-07-17T13:37:00+02:00"
     */
    createdAt?: string;
    train?: TransportResource;
    event?: EventResource | null;
    /** User model with just basic information */
    userDetails?: LightUserResource;
    tags?: StatusTagResource[];
}

/** StatusTagResource */
export interface StatusTagResource {
    /** @example "trwl:vehicle_number" */
    key?: string;
    /** @example "94 80 0450 921 D-AVG" */
    value?: string;
    /** @example "1" */
    visibility?: number;
}

/** StopoverResource */
export interface StopoverResource {
    /** @example 12345 */
    id?: number;
    /**
     * name of the station
     * @example "Karlsruhe Hbf"
     */
    name?: string;
    /**
     * Identifier specified in 'Richtline 100' of the Deutsche Bahn
     * @example "RK"
     */
    rilIdentifier?: string | null;
    /**
     * IBNR identifier of Deutsche Bahn
     * @example "8000191"
     */
    evaIdentifier?: string | null;
    /**
     * currently known arrival time. Equal to arrivalReal if known. Else equal to arrivalPlanned.
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    arrival?: string | null;
    /**
     * planned arrival according to timetable records
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    arrivalPlanned?: string | null;
    /**
     * real arrival according to live data
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    arrivalReal?: string | null;
    /**
     * planned arrival platform according to timetable records
     * @example "5"
     */
    arrivalPlatformPlanned?: string | null;
    /**
     * real arrival platform according to live data
     * @example "5 A-F"
     */
    arrivalPlatformReal?: string | null;
    /**
     * currently known departure time. Equal to departureReal if known. Else equal to departurePlanned.
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    departure?: string | null;
    /**
     * planned departure according to timetable records
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    departurePlanned?: string | null;
    /**
     * real departure according to live data
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    departureReal?: string | null;
    /**
     * planned departure platform according to timetable records
     * @example "5"
     */
    departurePlatformPlanned?: string | null;
    /**
     * real departure platform according to live data
     * @example "5 A-F"
     */
    departurePlatformReal?: string | null;
    /** @example "5 A-F" */
    platform?: string | null;
    /**
     * Is there a delay in the arrival time?
     * @example false
     */
    isArrivalDelayed?: boolean;
    /**
     * Is there a delay in the departure time?
     * @example false
     */
    isDepartureDelayed?: boolean;
    /**
     * is this stopover cancelled?
     * @example false
     */
    cancelled?: boolean;
}

/** TransportResource */
export interface TransportResource {
    /** @example "4711" */
    trip?: number;
    /** @example "1|1234|567" */
    hafasId?: string;
    /** Category of transport.  */
    category?: HafasTravelType;
    /**
     * Internal number of the journey
     * @example "4-a6s8-8"
     */
    number?: any;
    /** @example "S 1" */
    lineName?: string;
    /** @example 85639 */
    journeyNumber?: number;
    /**
     * Distance in meters
     * @example 10000
     */
    distance?: number;
    /** @example 37 */
    points?: number;
    /**
     * Duration in minutes
     * @example 30
     */
    duration?: number;
    /**
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    manualDeparture?: string | null;
    /**
     * @format date-time
     * @example "2022-07-17T13:37:00+02:00"
     */
    manualArrival?: string | null;
    origin?: StopoverResource;
    destination?: StopoverResource;
    operator?: OperatorResource;
}

/** TrustedUser */
export interface TrustedUserResource {
    /** User model with just basic information */
    user: LightUserResource;
    /**
     * @format date-time
     * @example "2024-07-28T00:00:00Z"
     */
    expiresAt?: string;
}

/** UserAuth */
export interface UserAuthResource {
    /** @example "1" */
    id?: number;
    /** @example "Gertrud" */
    displayName?: string;
    /** @example "Gertrud123" */
    username?: string;
    /** @example "https://traewelling.de/@Gertrud123/picture" */
    profilePicture?: string;
    /** @example "100" */
    totalDistance?: number;
    /** @example "100" */
    totalDuration?: number;
    /** @example "100" */
    points?: number;
    /** @example "https://mastodon.social/@Gertrud123" */
    mastodonUrl?: string | null;
    /** @example "false" */
    privateProfile?: boolean;
    /** @example "false" */
    preventIndex?: boolean;
    /** @example "true" */
    likes_enabled?: boolean;
    home?: StationResource;
    /** @example "de" */
    language?: string;
    /** @example 0 */
    defaultStatusVisibility?: number;
    /** @example ["admin","open-beta","closed-beta"] */
    roles?: string[];
}

/** UserProfileSettings */
export interface UserProfileSettingsResource {
    /** @example "Gertrud123" */
    username?: string;
    /** @example "Gertrud" */
    displayName?: string;
    /** @example "https://traewelling.de/@Gertrud123/picture" */
    profilePicture?: string;
    /** @example false */
    privateProfile?: boolean;
    /**
     * Did the user choose to prevent search engines from indexing their profile?
     * @example false
     */
    preventIndex?: boolean;
    /**
     * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
     *  *      user specify?
     */
    defaultStatusVisibility?: StatusVisibility;
    /**
     * Number of days to hide the user's location history
     * @example 1
     */
    privacyHideDays?: number;
    /** @example true */
    password?: boolean;
    /** @example "gertrud@traewelling.de" */
    email?: string;
    /** @example true */
    emailVerified?: boolean;
    /** @example true */
    profilePictureSet?: boolean;
    /** @example "https://mastodon.social/@Gertrud123" */
    mastodon?: string;
    /**
     * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private) did the user specify for
     *  *     future posts to Mastodon? Some instances such as chaos.social discourage bot posts on public timelines.
     */
    mastodonVisibility?: MastodonVisibility;
    friendCheckin?: FriendCheckinSetting;
    /** @example true */
    likesEnabled?: boolean;
    /** @example true */
    pointsEnabled?: boolean;
}

/** BearerTokenResponse */
export interface BearerTokenResponse {
    /**
     * token
     * Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)
     * @example "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZWU2ZWZiOWUxYTIwN2FmMjZjNjk4NjVkOTA5ODNmNzFjYzYyMzE5ODA3NGU1NjlhNjU1MGRiMTdhMWY5YmNhMmY4ZjNjNTQ4ZGZkZTY5ZmUiLCJpYXQiOjE2NjYxODUzMDYuOTczODU3LCJuYmYiOjE2NjYxODUzMDYuOTczODYsImV4cCI6MTY5NzcyMTMwNi45NDYyNDgsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.tiv8VeL8qw6BRwo5QZZ71Zn3WnFJjtvVciahiUJjzVNfqgofdRF6EoWrTFc_WmrgbVCdfXBjBI02fjbSrsD4....."
     */
    token?: string;
    /**
     * slug
     * end of life for this token. Lifespan is usually one year.
     * @example "2023-10-19T15:15:06+02:00"
     */
    expires_at?: string;
}

/**
 * CheckinRequestBody
 * Fields for creating a train checkin
 */
export interface CheckinRequestBody {
    /**
     * @maxLength 280
     * @example "Meine erste Fahrt nach Knuffingen!"
     */
    body?: string | null;
    /** What type of travel (0=private, 1=business, 2=commute) did the user specify? */
    business?: Business;
    /**
     * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
     *  *      user specify?
     */
    visibility?: StatusVisibility;
    /**
     * Id of an event the status should be connected to
     * @example "1"
     */
    eventId?: number | null;
    /**
     * Should this status be posted to mastodon?
     * @example "false"
     */
    toot?: boolean | null;
    /**
     * Should this status be posted to mastodon as a chained post?
     * @example "false"
     */
    chainPost?: boolean | null;
    /**
     * If true, the `start` and `destination` properties can be supplied as an ibnr. Otherwise they should be given as the Träwelling-ID. Default behavior is `false`.
     * @example "true"
     */
    ibnr?: boolean | null;
    /**
     * The tripId for the to be checked in train
     * @example "b37ff515-22e1-463c-94de-3ad7964b5cb8"
     */
    tripId?: string | null;
    /**
     * The line name for the to be checked in train
     * @example "S 4"
     */
    lineName?: string | null;
    /**
     * The Station-ID of the starting point (see `ibnr`)
     * @example "8000191"
     */
    start?: number;
    /**
     * The Station-ID of the destination point (see `ibnr`)
     * @example "8000192"
     */
    destination?: number;
    /**
     * Timestamp of the departure
     * @format date-time
     * @example "2022-12-19T20:41:00+01:00"
     */
    departure?: string;
    /**
     * Timestamp of the arrival
     * @format date-time
     * @example "2022-12-19T20:42:00+01:00"
     */
    arrival?: string;
    /**
     * If true, the checkin will be created, even if a colliding checkin exists. No points will be awarded.
     * @example "false"
     */
    force?: boolean | null;
    /**
     * If set, the checkin will be created for all given users as well. The user creating the checkin must be allowed to checkin for the other users. Max. 10 users.
     * @example "[1, 2]"
     */
    with?: number[] | null;
}

/**
 * EventSuggestion
 * Fields for suggesting an event
 */
export interface EventSuggestion {
    /**
     * name
     * name of the event
     * @maxLength 255
     * @example "Eröffnung der Nebenbahn in Knuffingen"
     */
    name?: string;
    /**
     * host
     * host of the event
     * @example "MiWuLa"
     */
    host?: string | null;
    /**
     * begin
     * Timestamp for the start of the event
     * @example "2022-06-01T00:00:00+02:00"
     */
    begin?: any;
    /**
     * end
     * Timestamp for the end of the event
     * @example "2022-08-31T23:59:00+02:00"
     */
    end?: any;
    /**
     * url
     * external URL for this event
     * @maxLength 255
     * @example "https://www.bundesregierung.de/breg-de/aktuelles/faq-9-euro-ticket-2028756"
     */
    url?: string | null;
    /**
     * hashtag
     * hashtag for this event
     * @maxLength 40
     * @example "gpn21"
     */
    hashtag?: string | null;
    /**
     * nearestStation
     * Query string for the nearest station to this event
     * @maxLength 255
     * @example "Berlin Hbf"
     */
    nearestStation?: string | null;
}

/**
 * Links
 * pagination links
 */
export interface Links {
    /**
     * first
     * URL to first page of this pagination
     * @format uri
     * @example "https://traewelling.de/api/v1/ENDPOINT?page=1"
     */
    first?: string | null;
    /**
     * last
     * URL to last page of this pagination (mostly null)
     * @format uri
     * @example null
     */
    last?: string | null;
    /**
     * prev
     * URL to previous page of this pagination (mostly null)
     * @format uri
     * @example "https://traewelling.de/api/v1/ENDPOINT?page=1"
     */
    prev?: string | null;
    /**
     * next
     * URL to next page of this pagination (mostly null)
     * @format uri
     * @example "https://traewelling.de/api/v1/ENDPOINT?page=2"
     */
    next?: string | null;
}

/**
 * Meta
 * Pagination meta data
 */
export interface PaginationMeta {
    /**
     * current_page
     * currently displayed page in this pagination
     * @example 2
     */
    current_page?: number;
    /**
     * from
     * The first element on this page is the nth element of the query
     * @example 16
     */
    from?: number;
    /**
     * path
     * The path of this pagination
     * @format url
     * @example "https://traewelling.de/api/v1/ENDPOINT"
     */
    path?: string;
    /**
     * per_page
     * the amount of items per page in this pagination
     * @example 15
     */
    per_page?: number;
    /**
     * to
     * The last element on this page is the nth element of the query
     * @example 30
     */
    to?: number;
}

/** LikeResponse */
export interface LikeResponse {
    /**
     * count
     * Amount of likes
     * @format int32
     * @example 12
     */
    count?: number;
}

/**
 * Notification
 * Notification model
 */
export interface Notification {
    /**
     * ID
     * ID
     * @format string
     * @example "bb1ba9a5-9c2b-43c3-b8c9-2f70651fc51c"
     */
    id?: string;
    /**
     * type
     * type of notification
     * @example "UserJoinedConnection"
     */
    type?: string;
    /**
     * leadFormatted
     * the title of notification in html formatted form
     * @format string
     * @example "<b>@bob</b> is in your connection!"
     */
    leadFormatted?: string;
    /**
     * lead
     * the title of notification in plain text form
     * @format string
     * @example "@bob is in your connection!"
     */
    lead?: string;
    /**
     * noticeFormatted
     * the body of notification in html formatted form
     * @format string
     * @example "@bob is on <b>S 81</b> from <b>Karlsruhe Hbf</b> to <b>Freudenstadt Hbf</b>."
     */
    noticeFormatted?: string;
    /**
     * notice
     * the body of notification in plain text form
     * @format string
     * @example "@bob is on S 81 from Karlsruhe Hbf to Freudenstadt Hbf."
     */
    notice?: string;
    /**
     * link
     * the link to the notification
     * @format string
     * @example "https://traewelling.de/status/123456"
     */
    link?: string;
    /**
     * data
     * the data of the notification
     */
    data?: any[];
    /**
     * readAt
     * the date when the notification was read, null if not read yet
     * @format string
     * @example "2023-01-01T00:00:00+00:00"
     */
    readAt?: string | null;
    /**
     * createdAt
     * the date when the notification was created
     * @format string
     * @example "2023-01-01T00:00:00+00:00"
     */
    createdAt?: string;
    /**
     * createdAtForHumans
     * DON'T USE THIS ATTRIBUTE! This Attribute will be removed in the future. The date when the notification was created, but in human readable form
     * @format string
     * @example "2 days ago"
     */
    createdAtForHumans?: string;
}

/**
 * PaginationPage
 * pagination links
 */
export type PaginationPage = any;

/**
 * Points
 * Points model
 */
export interface Points {
    /**
     * points
     * points
     * @format int
     * @example 1
     */
    points?: number;
    calculation?: PointsCalculation;
    /**
     * additional
     * extra points that can be given
     */
    additional?: any[];
}

/** PointsCalculation */
export interface PointsCalculation {
    /**
     * base
     * Basepoints for this type of vehicle
     * @format float
     * @example 0.5
     */
    base?: number;
    /**
     * distance
     * Points for the travelled distance
     * @example 0.25
     */
    distance?: any;
    /**
     * factor
     * @example 0.25
     */
    factor?: any;
    /** What is the reason for the points calculation factor? (0=in time => 100%, 1=good enough => 25%, 2=not sufficient (1 point), 3=forced => no points, 4=manual trip => no points, 5=points disabled) */
    reason?: PointReason;
}

/**
 * Polyline
 * Polyline of a single status as GeoJSON Feature
 */
export interface Polyline {
    /**
     * type
     * @example "Feature"
     */
    type?: string;
    geometry?: {
        /** @example "LineString" */
        type?: object;
        coordinates?: any[];
    };
    properties?: {
        /** @example 1337 */
        statusId?: number;
    };
}

/** CheckinForbiddenWithUsersResponse */
export interface CheckinForbiddenWithUsersResponse {
    /** @example "You are not allowed to check in the following users: 1" */
    message?: any;
    meta?: {
        invalidUsers?: number[];
    };
}

/**
 * StatusTag
 * StatusTag model
 */
export interface StatusTag {
    /**
     * key
     * Key of tag
     * @example "trwl:ticket"
     */
    key?: string;
    /**
     * value
     * Value of tag
     * @example "BahnCard 100"
     */
    value?: string;
    /**
     * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
     *  *      user specify?
     */
    visibility?: StatusVisibility;
}

/**
 * SuccessResponse
 * Success Response
 */
export interface SuccessResponse {
    /**
     * status
     * status
     * @example "success"
     */
    status?: string;
}

/**
 * User
 * User model
 */
export interface User {
    /**
     * ID
     * ID
     * @format int
     * @example 1
     */
    id?: number;
    /**
     * displayName
     * Display name of the user
     * @example "Gertrud"
     */
    displayName?: any;
    /**
     * username
     * username of user
     * @example "Gertrud123"
     */
    username?: string;
    /**
     * profilePicture
     * URL of the profile picture of the user
     * @example "https://traewelling.de/@Gertrud123/picture"
     */
    profilePicture?: number;
    /**
     * trainDistance
     * distance travelled by train in meters
     * @format int
     * @example 12345
     */
    trainDistance?: number;
    /**
     * trainDuration
     * duration travelled by train in minutes
     * @format int
     * @example 6
     */
    trainDuration?: number;
    /**
     * points
     * Current points of the last 7 days
     * @format int
     * @example 300
     */
    points?: number;
    /**
     * mastodonUrl
     * URL to the Mastodon profile of the user
     * @example "https://chaos.social/@traewelling"
     */
    mastodonUrl?: string | null;
    /**
     * privateProfile
     * is this profile set to private?
     * @example false
     */
    privateProfile?: boolean;
    /**
     * likes_enabled
     * Does this profile allow likes? Only offer the UI to like any status if this setting is set to true. If set to false, the likes API will return 403.
     * @example true
     */
    likes_enabled?: boolean;
    /**
     * userInvisibleToMe
     * Can the currently authenticated user see the statuses of this user?
     * @example false
     */
    userInvisibleToMe?: boolean;
    /**
     * muted
     * Is this user muted by the currently authenticated user?
     * @example false
     */
    muted?: boolean;
    /**
     * following
     * Does the currently authenticated user follow this user?
     * @example false
     */
    following?: boolean;
    /**
     * followPending
     * Is there a currently pending follow request?
     * @example false
     */
    followPending?: boolean;
    /**
     * preventIndex
     * Did the user choose to prevent search engines from indexing their profile?
     * @example false
     */
    preventIndex?: boolean;
}

/**
 * Webhook
 * Webhook model
 */
export interface Webhook {
    /**
     * ID
     * ID
     * @format int
     * @example 12345
     */
    id?: number;
    /**
     * ClientID
     * ID of the client which created this webhook
     * @format int
     * @example 12345
     */
    clientId?: number;
    /**
     * UserID
     * ID of the user which created this webhook
     * @format int
     * @example 12345
     */
    userId?: number;
    /**
     * url
     * URL where the webhook gets sent to
     * @example "https://example.com/webhook"
     */
    url?: any;
    /**
     * createdAt
     * creation date of this webhook
     * @format datetime
     * @example "2022-07-17T13:37:00+02:00"
     */
    createdAt?: string;
    /**
     * events
     * array of events this webhook receives
     */
    events?: any[];
}

/**
 * StatusUpdateBody
 * Status Update Body
 */
export interface StatusUpdateBody {
    /**
     * Status-Text to be displayed alongside the checkin
     * @maxLength 280
     * @example "Wow. This train is extremely crowded!"
     */
    body?: any;
    /** What type of travel (0=private, 1=business, 2=commute) did the user specify? */
    business?: Business;
    /**
     * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated) did the
     *  *      user specify?
     */
    visibility?: StatusVisibility;
    /**
     * The ID of the event this status is related to - or null
     * @example "1"
     */
    eventId?: any;
    /**
     * Manual departure time set by the user
     * @format date
     * @example "2020-01-01 12:00:00"
     */
    manualDeparture?: any;
    /**
     * Manual arrival time set by the user
     * @format date
     * @example "2020-01-01 13:00:00"
     */
    manualArrival?: any;
    /**
     * Destination station id
     * @example "1"
     */
    destinationId?: any;
    /**
     * Destination arrival time
     * @format date
     * @example "2020-01-01 13:00:00"
     */
    destinationArrivalPlanned?: any;
}

export type QueryParamsType = Record<string | number, any>;
export type ResponseFormat = keyof Omit<Body, "body" | "bodyUsed">;

export interface FullRequestParams extends Omit<RequestInit, "body"> {
    /** set parameter to `true` for call `securityWorker` for this request */
    secure?: boolean;
    /** request path */
    path: string;
    /** content type of request body */
    type?: ContentType;
    /** query params */
    query?: QueryParamsType;
    /** format of response (i.e. response.json() -> format: "json") */
    format?: ResponseFormat;
    /** request body */
    body?: unknown;
    /** base url */
    baseUrl?: string;
    /** request cancellation token */
    cancelToken?: CancelToken;
}

export type RequestParams = Omit<FullRequestParams, "body" | "method" | "query" | "path">;

export interface ApiConfig<SecurityDataType = unknown> {
    baseUrl?: string;
    baseApiParams?: Omit<RequestParams, "baseUrl" | "cancelToken" | "signal">;
    securityWorker?: (securityData: SecurityDataType | null) => Promise<RequestParams | void> | RequestParams | void;
    customFetch?: typeof fetch;
}

export interface HttpResponse<D extends unknown, E extends unknown = unknown> extends Response {
    data: D;
    error: E;
}

type CancelToken = Symbol | string | number;

export enum ContentType {
    Json = "application/json",
    FormData = "multipart/form-data",
    UrlEncoded = "application/x-www-form-urlencoded",
    Text = "text/plain",
}

export class HttpClient<SecurityDataType = unknown> {
    public baseUrl: string = "https://traewelling.de/api/v1";
    private securityData: SecurityDataType | null = null;
    private securityWorker?: ApiConfig<SecurityDataType>["securityWorker"];
    private abortControllers = new Map<CancelToken, AbortController>();
    private customFetch = (...fetchParams: Parameters<typeof fetch>) => fetch(...fetchParams);

    private baseApiParams: RequestParams = {
        credentials: "same-origin",
        headers: {},
        redirect: "follow",
        referrerPolicy: "no-referrer",
    };

    constructor(apiConfig: ApiConfig<SecurityDataType> = {}) {
        Object.assign(this, apiConfig);
    }

    public setSecurityData = (data: SecurityDataType | null) => {
        this.securityData = data;
    };

    protected encodeQueryParam(key: string, value: any) {
        const encodedKey = encodeURIComponent(key);
        return `${encodedKey}=${encodeURIComponent(typeof value === "number" ? value : `${value}`)}`;
    }

    protected addQueryParam(query: QueryParamsType, key: string) {
        return this.encodeQueryParam(key, query[key]);
    }

    protected addArrayQueryParam(query: QueryParamsType, key: string) {
        const value = query[key];
        return value.map((v: any) => this.encodeQueryParam(key, v)).join("&");
    }

    protected toQueryString(rawQuery?: QueryParamsType): string {
        const query = rawQuery || {};
        const keys = Object.keys(query).filter((key) => "undefined" !== typeof query[key]);
        return keys
            .map((key) => (Array.isArray(query[key]) ? this.addArrayQueryParam(query, key) : this.addQueryParam(query, key)))
            .join("&");
    }

    protected addQueryParams(rawQuery?: QueryParamsType): string {
        const queryString = this.toQueryString(rawQuery);
        return queryString ? `?${queryString}` : "";
    }

    private contentFormatters: Record<ContentType, (input: any) => any> = {
        [ContentType.Json]: (input: any) =>
            input !== null && (typeof input === "object" || typeof input === "string") ? JSON.stringify(input) : input,
        [ContentType.Text]: (input: any) => (input !== null && typeof input !== "string" ? JSON.stringify(input) : input),
        [ContentType.FormData]: (input: FormData) =>
            (Array.from(input.keys()) || []).reduce((formData, key) => {
                const property = input.get(key);
                formData.append(
                    key,
                    property instanceof Blob
                        ? property
                        : typeof property === "object" && property !== null
                            ? JSON.stringify(property)
                            : `${property}`,
                );
                return formData;
            }, new FormData()),
        [ContentType.UrlEncoded]: (input: any) => this.toQueryString(input),
    };

    protected mergeRequestParams(params1: RequestParams, params2?: RequestParams): RequestParams {
        return {
            ...this.baseApiParams,
            ...params1,
            ...(params2 || {}),
            headers: {
                ...(this.baseApiParams.headers || {}),
                ...(params1.headers || {}),
                ...((params2 && params2.headers) || {}),
            },
        };
    }

    protected createAbortSignal = (cancelToken: CancelToken): AbortSignal | undefined => {
        if (this.abortControllers.has(cancelToken)) {
            const abortController = this.abortControllers.get(cancelToken);
            if (abortController) {
                return abortController.signal;
            }
            return void 0;
        }

        const abortController = new AbortController();
        this.abortControllers.set(cancelToken, abortController);
        return abortController.signal;
    };

    public abortRequest = (cancelToken: CancelToken) => {
        const abortController = this.abortControllers.get(cancelToken);

        if (abortController) {
            abortController.abort();
            this.abortControllers.delete(cancelToken);
        }
    };

    public request = async <T = any, E = any>({
                                                  body,
                                                  secure,
                                                  path,
                                                  type,
                                                  query,
                                                  format,
                                                  baseUrl,
                                                  cancelToken,
                                                  ...params
                                              }: FullRequestParams): Promise<HttpResponse<T, E>> => {
        const secureParams =
            ((typeof secure === "boolean" ? secure : this.baseApiParams.secure) &&
                this.securityWorker &&
                (await this.securityWorker(this.securityData))) ||
            {};
        const requestParams = this.mergeRequestParams(params, secureParams);
        const queryString = query && this.toQueryString(query);
        const payloadFormatter = this.contentFormatters[type || ContentType.Json];
        const responseFormat = format || requestParams.format;

        return this.customFetch(`${baseUrl || this.baseUrl || ""}${path}${queryString ? `?${queryString}` : ""}`, {
            ...requestParams,
            headers: {
                ...(requestParams.headers || {}),
                ...(type && type !== ContentType.FormData ? {"Content-Type": type} : {}),
            },
            signal: (cancelToken ? this.createAbortSignal(cancelToken) : requestParams.signal) || null,
            body: typeof body === "undefined" || body === null ? null : payloadFormatter(body),
        }).then(async (response) => {
            const r = response.clone() as HttpResponse<T, E>;
            r.data = null as unknown as T;
            r.error = null as unknown as E;

            const data = !responseFormat
                ? r
                : await response[responseFormat]()
                    .then((data) => {
                        if (r.ok) {
                            r.data = data;
                        } else {
                            r.error = data;
                        }
                        return r;
                    })
                    .catch((e) => {
                        r.error = e;
                        return r;
                    });

            if (cancelToken) {
                this.abortControllers.delete(cancelToken);
            }

            if (!response.ok) throw data;
            return data;
        });
    };
}

/**
 * @title Träwelling API
 * @version 1.0.0 - alpha
 * @license Apache 2.0 (https://www.apache.org/licenses/LICENSE-2.0.html)
 * @baseUrl https://traewelling.de/api/v1
 * @contact <support@traewelling.de>
 *
 * Träwelling user API description. This is an incomplete documentation with still many errors. The API is currently not yet stable. Endpoints are still being restructured. Both the URL and the request or body can be changed. Breaking changes will be announced on GitHub: https://github.com/Traewelling/traewelling/blob/develop/API_CHANGELOG.md
 */
export class Api<SecurityDataType extends unknown> extends HttpClient<SecurityDataType> {
    auth = {
        /**
         * No description
         *
         * @tags Auth
         * @name LogoutUser
         * @summary Logout & invalidate current bearer token
         * @request POST:/auth/logout
         * @secure
         */
        logoutUser: (params: RequestParams = {}) =>
            this.request<
                {
                    /** @example "success" */
                    status?: any;
                },
                void
            >({
                path: `/auth/logout`,
                method: "POST",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Get all profile information about the authenticated user
         *
         * @tags Auth, User
         * @name GetAuthenticatedUser
         * @summary Get authenticated user information
         * @request GET:/auth/user
         * @secure
         */
        getAuthenticatedUser: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: UserAuthResource;
                },
                void
            >({
                path: `/auth/user`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description This request issues a new Bearer-Token with a new expiration date while also revoking the old *      token.
         *
         * @tags Auth
         * @name RefreshToken
         * @summary Refresh Bearer Token
         * @request POST:/auth/refresh
         * @secure
         */
        refreshToken: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: BearerTokenResponse;
                },
                void
            >({
                path: `/auth/refresh`,
                method: "POST",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    event = {
        /**
         * @description Returns slug, name and duration for an event
         *
         * @tags Events
         * @name GetEvent
         * @summary [Auth optional] Get basic information for event
         * @request GET:/event/{slug}
         * @secure
         */
        getEvent: (slug?: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: EventResource;
                },
                void
            >({
                path: `/event/${slug}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns overall travelled distance and duration for an event
         *
         * @tags Events
         * @name GetEventDetails
         * @summary [Auth optional] Get additional information for event
         * @request GET:/event/{slug}/details
         * @secure
         */
        getEventDetails: (slug?: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: EventDetailsResource;
                },
                void
            >({
                path: `/event/${slug}/details`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns all for user visible statuses for an event
         *
         * @tags Events
         * @name GetEventStatuses
         * @summary [Auth optional] Get paginated statuses for event
         * @request GET:/event/{slug}/statuses
         * @secure
         */
        getEventStatuses: (
            slug?: string,
            query?: {
                /** Page of pagination */
                page?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: StatusResource[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/event/${slug}/statuses`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Submit a possible event for our administrators to publish
         *
         * @tags Events
         * @name SuggestEvent
         * @summary Suggest a event
         * @request POST:/event
         * @secure
         */
        suggestEvent: (data: EventSuggestion, params: RequestParams = {}) =>
            this.request<void, void>({
                path: `/event`,
                method: "POST",
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),
    };
    events = {
        /**
         * @description Returns all active or upcoming events for the given timestamp. Default timestamp is now. If upcoming is set to true, all events ending after the timestamp are returned.
         *
         * @tags Events
         * @name GetEvents
         * @summary [Auth optional] Show active or upcoming events for the given timestamp
         * @request GET:/events
         * @secure
         */
        getEvents: (
            query?: {
                /**
                 * The timestamp of view. Default is now.
                 * @example "2022-08-01T12:00:00+02:00"
                 */
                timestamp?: string;
                /** Show only upcoming events */
                upcoming?: boolean;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: EventResource[];
                },
                void
            >({
                path: `/events`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),
    };
    activeEvents = {
        /**
         * @description DEPRECATED - USE /events - removed after 2024-08
         *
         * @tags Events
         * @name GetActiveEvents
         * @summary DEPRECATED - USE /events - removed after 2024-08
         * @request GET:/activeEvents
         */
        getActiveEvents: (params: RequestParams = {}) =>
            this.request<void, any>({
                path: `/activeEvents`,
                method: "GET",
                ...params,
            }),
    };
    user = {
        /**
         * No description
         *
         * @tags User/Follow
         * @name CreateFollow
         * @summary Follow a user
         * @request POST:/user/{id}/follow
         * @secure
         */
        createFollow: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    /** User model */
                    data?: User;
                },
                void
            >({
                path: `/user/${id}/follow`,
                method: "POST",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags User/Follow
         * @name DestroyFollow
         * @summary Unfollow a user
         * @request DELETE:/user/{id}/follow
         * @secure
         */
        destroyFollow: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    /** User model */
                    data?: User;
                },
                void
            >({
                path: `/user/${id}/follow`,
                method: "DELETE",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags User/Follow
         * @name RemoveFollower
         * @summary Remove a follower
         * @request DELETE:/user/removeFollower
         * @secure
         */
        removeFollower: (
            data: {
                /**
                 * userId
                 * ID of the to-be-unfollowed user
                 * @format int
                 * @example 1
                 */
                userId?: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/user/removeFollower`,
                method: "DELETE",
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),

        /**
         * No description
         *
         * @tags User/Follow
         * @name AcceptFollowRequest
         * @summary Accept a follow request
         * @request PUT:/user/acceptFollowRequest
         * @secure
         */
        acceptFollowRequest: (
            data: {
                /**
                 * userId
                 * ID of the user who sent the follow request
                 * @format int
                 * @example 1
                 */
                userId?: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/user/acceptFollowRequest`,
                method: "PUT",
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),

        /**
         * No description
         *
         * @tags User/Follow
         * @name RejectFollowRequest
         * @summary Reject a follow request
         * @request DELETE:/user/rejectFollowRequest
         * @secure
         */
        rejectFollowRequest: (
            data: {
                /**
                 * userId
                 * ID of the user who sent the follow request
                 * @format int
                 * @example 1
                 */
                userId?: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/user/rejectFollowRequest`,
                method: "DELETE",
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),

        /**
         * @description This request returns whether the currently logged-in user has an active check-in or not.
         *
         * @tags Auth
         * @name UserState
         * @summary User state
         * @request GET:/user/statuses/active
         * @secure
         */
        userState: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: StatusResource;
                },
                void
            >({
                path: `/user/statuses/active`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Get all trusted users for the current user or a specific user (admin only).
         *
         * @tags User
         * @name TrustedUserIndex
         * @summary Get all trusted users for a user
         * @request GET:/user/{user}/trusted
         */
        trustedUserIndex: (user: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: TrustedUserResource[];
                },
                void
            >({
                path: `/user/${user}/trusted`,
                method: "GET",
                format: "json",
                ...params,
            }),

        /**
         * @description Add a user to the trusted users for the current user or a specific user (admin only).
         *
         * @tags User
         * @name TrustedUserStore
         * @summary Add a user to the trusted users for a user
         * @request POST:/user/{user}/trusted
         */
        trustedUserStore: (
            user: string,
            data: {
                /** @example "1" */
                userId?: number;
                /**
                 * @format date-time
                 * @example "2024-07-28T00:00:00Z"
                 */
                expiresAt?: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/user/${user}/trusted`,
                method: "POST",
                body: data,
                type: ContentType.Json,
                ...params,
            }),

        /**
         * No description
         *
         * @tags User
         * @name TrustedByUserIndex
         * @summary Get all users who trust the current user
         * @request GET:/user/self/trusted-by
         */
        trustedByUserIndex: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: TrustedUserResource[];
                },
                void
            >({
                path: `/user/self/trusted-by`,
                method: "GET",
                format: "json",
                ...params,
            }),

        /**
         * @description Remove a user from the trusted users for the current user or a specific user (admin only).
         *
         * @tags User
         * @name TrustedUserDestroy
         * @summary Remove a user from the trusted users for a user
         * @request DELETE:/user/{user}/trusted/{trustedId}
         */
        trustedUserDestroy: (user: string, trusted: number, params: RequestParams = {}) =>
            this.request<void, void>({
                path: `/user/${user}/trusted/${trusted}`,
                method: "DELETE",
                ...params,
            }),

        /**
         * @description Returns paginated statuses of a single user specified by the username
         *
         * @tags User, Status
         * @name GetStatusesForUser
         * @summary [Auth optional] Get paginated statuses for single user
         * @request GET:/user/{username}/statuses
         * @secure
         */
        getStatusesForUser: (
            username?: any,
            query?: {
                /** Page of pagination */
                page?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: StatusResource[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/user/${username}/statuses`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns general information, metadata and statistics for a user
         *
         * @tags User
         * @name ShowUser
         * @summary [Auth optional] Get information for single user
         * @request GET:/user/{username}
         * @secure
         */
        showUser: (
            username?: any,
            query?: {
                /** Page of pagination */
                page?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    /** User model */
                    data?: User;
                },
                void
            >({
                path: `/user/${username}`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Block a specific user. That user will not be able to see your statuses or profile information, *      and cannot send you follow requests. Public statuses are still visible through the incognito mode.
         *
         * @tags User/Hide and Block
         * @name CreateBlock
         * @summary Block a user
         * @request POST:/user/{id}/block
         * @secure
         */
        createBlock: (
            id: string,
            data: {
                /**
                 * userId
                 * ID of the to-be-blocked user
                 * @format int
                 * @example 1
                 */
                userId?: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    /** User model */
                    data?: User;
                },
                void
            >({
                path: `/user/${id}/block`,
                method: "POST",
                body: data,
                secure: true,
                type: ContentType.Json,
                format: "json",
                ...params,
            }),

        /**
         * @description Unblock a specific user. They are now able to see your statuses and profile information again, *      and send you follow requests.
         *
         * @tags User/Hide and Block
         * @name DestroyBlock
         * @summary Unmute a user
         * @request DELETE:/user/{id}/block
         * @secure
         */
        destroyBlock: (
            id: string,
            data: {
                /**
                 * userId
                 * ID of the to-be-unblocked user
                 * @format int
                 * @example 1
                 */
                userId?: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    /** User model */
                    data?: User;
                },
                void
            >({
                path: `/user/${id}/block`,
                method: "DELETE",
                body: data,
                secure: true,
                type: ContentType.Json,
                format: "json",
                ...params,
            }),

        /**
         * @description Mute a specific user. That way they will not be shown on your dashboard and in the active *      journeys tab
         *
         * @tags User/Hide and Block
         * @name CreateMute
         * @summary Mute a user
         * @request POST:/user/{id}/mute
         * @secure
         */
        createMute: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    /** User model */
                    data?: User;
                },
                void
            >({
                path: `/user/${id}/mute`,
                method: "POST",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Unmute a specific user. That way they will be shown on your dashboard and in the active *      journeys tab again
         *
         * @tags User/Hide and Block
         * @name DestroyMute
         * @summary Unmute a user
         * @request DELETE:/user/{id}/mute
         * @secure
         */
        destroyMute: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    /** User model */
                    data?: User;
                },
                void
            >({
                path: `/user/${id}/mute`,
                method: "DELETE",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns paginated statuses of a single user specified by the username
         *
         * @tags User
         * @name SearchUsers
         * @summary Get paginated statuses for single user
         * @request GET:/user/search/{query}
         * @secure
         */
        searchUsers: (
            query?: any,
            queryParams?: {
                /** Page of pagination */
                page?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: User[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/user/search/${query}`,
                method: "GET",
                query: queryParams,
                secure: true,
                format: "json",
                ...params,
            }),
    };
    settings = {
        /**
         * No description
         *
         * @tags User/Follow, Settings
         * @name GetFollowers
         * @summary List all followers
         * @request GET:/settings/followers
         * @secure
         */
        getFollowers: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: User[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/settings/followers`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags User/Follow, Settings
         * @name GetFollowRequests
         * @summary List all followers
         * @request GET:/settings/follow-requests
         * @secure
         */
        getFollowRequests: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: User[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                any
            >({
                path: `/settings/follow-requests`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags User/Follow, Settings
         * @name GetFollowings
         * @summary List all users the current user is following
         * @request GET:/settings/followings
         * @secure
         */
        getFollowings: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: User[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                any
            >({
                path: `/settings/followings`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Accept the current privacy policy
         *
         * @tags Settings
         * @name AcceptPrivacyPolicy
         * @summary Accept the current privacy policy
         * @request POST:/settings/acceptPrivacy
         * @secure
         */
        acceptPrivacyPolicy: (params: RequestParams = {}) =>
            this.request<void, void>({
                path: `/settings/acceptPrivacy`,
                method: "POST",
                secure: true,
                ...params,
            }),

        /**
         * @description Get the current user's profile settings
         *
         * @tags Settings
         * @name GetProfileSettings
         * @summary Get the current user's profile settings
         * @request GET:/settings/profile
         * @secure
         */
        getProfileSettings: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: UserProfileSettingsResource;
                },
                void
            >({
                path: `/settings/profile`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Update the current user's profile settings
         *
         * @tags Settings
         * @name UpdateProfileSettings
         * @summary Update the current user's profile settings
         * @request PUT:/settings/profile
         * @secure
         */
        updateProfileSettings: (
            data: {
                /**
                 * @maxLength 25
                 * @example "gertrud123"
                 */
                username?: string;
                /**
                 * @maxLength 50
                 * @example "Gertrud"
                 */
                displayName?: string;
                /** @example false */
                privateProfile?: boolean | null;
                /** @example false */
                preventIndex?: boolean | null;
                /** @example 1 */
                privacyHideDays?: number | null;
                defaultStatusVisibility?: StatusVisibility | null;
                mastodonVisibility?: MastodonVisibility | null;
                mapProvider?: MapProvider | null;
                friendCheckin?: FriendCheckinSetting | null;
                /** @example true */
                likesEnabled?: boolean | null;
                /** @example true */
                pointsEnabled?: boolean | null;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: UserProfileSettingsResource;
                },
                void
            >({
                path: `/settings/profile`,
                method: "PUT",
                body: data,
                secure: true,
                type: ContentType.Json,
                format: "json",
                ...params,
            }),

        /**
         * @description Deletes the Account for the user and all posts created by it
         *
         * @tags Settings
         * @name DeleteUserAccount
         * @summary Delete User Account
         * @request DELETE:/settings/account
         * @secure
         */
        deleteUserAccount: (
            data: {
                /**
                 * confirmation
                 * Username of the to be deleted account (needs to match the currently logged in
                 *      *                  user)
                 * @example "Gertrud123"
                 */
                confirmation?: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/settings/account`,
                method: "DELETE",
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),
    };
    status = {
        /**
         * @description Returns array of users that liked the status. Can return an empty dataset when the status *      author or the requesting user has deactivated likes
         *
         * @tags Likes
         * @name GetLikesForStatus
         * @summary [Auth optional] Get likes for status
         * @request GET:/status/{id}/likes
         * @secure
         */
        getLikesForStatus: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    data?: User[];
                },
                void
            >({
                path: `/status/${id}/likes`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Add like to status
         *
         * @tags Likes
         * @name AddLikeToStatus
         * @summary Add like to status
         * @request POST:/status/{id}/like
         * @secure
         */
        addLikeToStatus: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    data?: LikeResponse;
                },
                void
            >({
                path: `/status/${id}/like`,
                method: "POST",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Removes like from status
         *
         * @tags Likes
         * @name RemoveLikeFromStatus
         * @summary Remove like from status
         * @request DELETE:/status/{id}/like
         * @secure
         */
        removeLikeFromStatus: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    data?: LikeResponse;
                },
                void
            >({
                path: `/status/${id}/like`,
                method: "DELETE",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns a single status Object, if user is authorized to see it
         *
         * @tags Status
         * @name GetSingleStatus
         * @summary [Auth optional] Get single statuses
         * @request GET:/status/{id}
         * @secure
         */
        getSingleStatus: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    data?: StatusResource;
                },
                void
            >({
                path: `/status/${id}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Updates a single status Object, if user is authorized to
         *
         * @tags Status
         * @name UpdateSingleStatus
         * @summary Update a status
         * @request PUT:/status/{id}
         * @secure
         */
        updateSingleStatus: (data: StatusUpdateBody, id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    data?: StatusResource;
                },
                void
            >({
                path: `/status/${id}`,
                method: "PUT",
                body: data,
                secure: true,
                type: ContentType.Json,
                format: "json",
                ...params,
            }),

        /**
         * @description Deletes a single status Object, if user is authorized to
         *
         * @tags Status
         * @name DestroySingleStatus
         * @summary Destroy a status
         * @request DELETE:/status/{id}
         * @secure
         */
        destroySingleStatus: (id?: number, params: RequestParams = {}) =>
            this.request<SuccessResponse, void>({
                path: `/status/${id}`,
                method: "DELETE",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns a collection of all visible tags for the given status, if user is authorized
         *
         * @tags Status
         * @name GetTagsForStatus
         * @summary Show all tags for a status which are visible for the current user
         * @request GET:/status/{statusId}/tags
         * @secure
         */
        getTagsForStatus: (statusId?: number, params: RequestParams = {}) =>
            this.request<
                {
                    data?: StatusTag[];
                },
                void
            >({
                path: `/status/${statusId}/tags`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Creates a single StatusTag Object, if user is authorized to. <br><br>The key of a tag is free *      text. You can choose it as you need it. However, <b>please use a namespace for tags</b> *      (<i>namespace:xxx</i>) that only affect your own application.<br><br>For tags related to standard actions *      we recommend the following tags in the trwl namespace:<br> *      <ul> *          <li>trwl:seat (i.e. 61)</li> *          <li>trwl:wagon (i.e. 25)</li> *          <li>trwl:ticket (i.e. BahnCard 100 first))</li> *          <li>trwl:travel_class (i.e. 1, 2, business, economy, ...)</li> *          <li>trwl:locomotive_class (BR424, BR450)</li> *          <li>trwl:wagon_class (i.e. Bpmz)</li> *          <li>trwl:role (i.e. Tf, Zf, Gf, Lokführer, conducteur de train, ...)</li> *          <li>trwl:vehicle_number (i.e. 425 001, Tz9001, 123, ...)</li> *          <li>trwl:passenger_rights (i.e. yes / no / ID of claim)</li> *      </ul>
         *
         * @tags Status
         * @name CreateSingleStatusTag
         * @summary Create a StatusTag
         * @request POST:/status/{statusId}/tags
         * @secure
         */
        createSingleStatusTag: (data: StatusTag, statusId?: number, params: RequestParams = {}) =>
            this.request<
                {
                    /** StatusTag model */
                    data?: StatusTag;
                },
                void
            >({
                path: `/status/${statusId}/tags`,
                method: "POST",
                body: data,
                secure: true,
                type: ContentType.Json,
                format: "json",
                ...params,
            }),

        /**
         * @description Updates a single StatusTag Object, if user is authorized to
         *
         * @tags Status
         * @name UpdateSingleStatusTag
         * @summary Update a StatusTag
         * @request PUT:/status/{statusId}/tags/{tagKey}
         * @secure
         */
        updateSingleStatusTag: (data: StatusTag, statusId?: number, tagKey?: string, params: RequestParams = {}) =>
            this.request<
                {
                    /** StatusTag model */
                    data?: StatusTag;
                },
                void
            >({
                path: `/status/${statusId}/tags/${tagKey}`,
                method: "PUT",
                body: data,
                secure: true,
                type: ContentType.Json,
                format: "json",
                ...params,
            }),

        /**
         * @description Deletes a single StatusTag Object, if user is authorized to
         *
         * @tags Status
         * @name DestroySingleStatusTag
         * @summary Destroy a StatusTag
         * @request DELETE:/status/{statusId}/tags/{tagKey}
         * @secure
         */
        destroySingleStatusTag: (statusId?: number, tagKey?: string, params: RequestParams = {}) =>
            this.request<SuccessResponse, void>({
                path: `/status/${statusId}/tags/${tagKey}`,
                method: "DELETE",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    notifications = {
        /**
         * @description Returns count of unread notifications of a authenticated user
         *
         * @tags Notifications
         * @name GetUnreadCount
         * @summary Get count of unread notifications for authenticated user
         * @request GET:/notifications/unread/count
         * @secure
         */
        getUnreadCount: (params: RequestParams = {}) =>
            this.request<
                {
                    /** @example 2 */
                    data?: number;
                },
                void
            >({
                path: `/notifications/unread/count`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns paginated notifications of a authenticated
         *
         * @tags Notifications
         * @name ListNotifications
         * @summary Get paginated notifications for authenticated user
         * @request GET:/notifications
         * @secure
         */
        listNotifications: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: Notification[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/notifications`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Notifications
         * @name MarkAsRead
         * @summary Mark notification as read
         * @request PUT:/notifications/read/{id}
         * @secure
         */
        markAsRead: (id?: string, params: RequestParams = {}) =>
            this.request<
                {
                    /** Notification model */
                    data?: Notification;
                },
                void
            >({
                path: `/notifications/read/${id}`,
                method: "PUT",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Notifications
         * @name MarkAsUnread
         * @summary Mark notification as unread
         * @request PUT:/notifications/unread/{id}
         * @secure
         */
        markAsUnread: (id?: string, params: RequestParams = {}) =>
            this.request<
                {
                    /** Notification model */
                    data?: Notification;
                },
                void
            >({
                path: `/notifications/unread/${id}`,
                method: "PUT",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Notifications
         * @name MarkAllAsRead
         * @summary Mark all notification as read
         * @request PUT:/notifications/read/all
         * @secure
         */
        markAllAsRead: (params: RequestParams = {}) =>
            this.request<SuccessResponse, void>({
                path: `/notifications/read/all`,
                method: "PUT",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    operators = {
        /**
         * No description
         *
         * @tags Checkin
         * @name Bcfcf8686980Cf0Fcdc751B2E13Fa4F7
         * @summary Get a list of all operators.
         * @request GET:/operators
         */
        bcfcf8686980Cf0Fcdc751B2E13Fa4F7: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: OperatorResource[];
                },
                void
            >({
                path: `/operators`,
                method: "GET",
                format: "json",
                ...params,
            }),
    };
    static = {
        /**
         * @description Get the current privacy policy
         *
         * @tags Settings
         * @name E649Bec35Ba50765Db023E745233Eda9
         * @summary Get the current privacy policy
         * @request GET:/static/privacy
         */
        e649Bec35Ba50765Db023E745233Eda9: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: {
                        /** @example "2022-01-05T16:26:14.000000Z" */
                        validFrom?: any;
                        /** @example "This is the english privacy policy" */
                        en?: any;
                        /** @example "Dies ist die deutsche Datenschutzerklärung" */
                        de?: any;
                    };
                },
                any
            >({
                path: `/static/privacy`,
                method: "GET",
                format: "json",
                ...params,
            }),
    };
    report = {
        /**
         * No description
         *
         * @tags Report
         * @name Report
         * @summary Report a Status, Event or User to the admins.
         * @request POST:/report
         * @secure
         */
        report: (
            data: {
                /** @example "Status" */
                subjectType: "Event" | "Status" | "User";
                /** @example 1 */
                subjectId: number;
                /** @example "inappropriate" */
                reason: "inappropriate" | "implausible" | "spam" | "illegal" | "other";
                /** @example "The status is inappropriate because..." */
                description?: string | null;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/report`,
                method: "POST",
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),
    };
    leaderboard = {
        /**
         * No description
         *
         * @tags Leaderboard
         * @name GetLeaderboard
         * @summary [Auth optional] Get array of 20 best users
         * @request GET:/leaderboard
         * @secure
         */
        getLeaderboard: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: LeaderboardUserResource[];
                },
                void
            >({
                path: `/leaderboard`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Leaderboard
         * @name GetLeaderboardByDistance
         * @summary [Auth optional] Get leaderboard array sorted by distance
         * @request GET:/leaderboard/distance
         * @secure
         */
        getLeaderboardByDistance: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: LeaderboardUserResource[];
                },
                void
            >({
                path: `/leaderboard/distance`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Leaderboard
         * @name GetLeaderboardByFriends
         * @summary Get friends-leaderboard array sorted
         * @request GET:/leaderboard/friends
         * @secure
         */
        getLeaderboardByFriends: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: LeaderboardUserResource[];
                },
                void
            >({
                path: `/leaderboard/friends`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Leaderboard
         * @name GetMonthlyLeaderboard
         * @summary [Auth optional] Get leaderboard array for a specific month
         * @request GET:/leaderboard/{month}
         * @secure
         */
        getMonthlyLeaderboard: (month?: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: LeaderboardUserResource[];
                },
                void
            >({
                path: `/leaderboard/${month}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    statistics = {
        /**
         * No description
         *
         * @tags Statistics
         * @name GetStatistics
         * @summary Get personal statistics
         * @request GET:/statistics
         * @secure
         */
        getStatistics: (
            query?: {
                /**
                 * Start date for the statistics
                 * @example "2021-01-01T00:00:00.000Z"
                 */
                from?: any;
                /**
                 * End date for the statistics
                 * @example "2021-02-01T00:00:00.000Z"
                 */
                until?: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: {
                        /** The purpose of travel */
                        purpose?: {
                            /** What type of travel (0=private, 1=business, 2=commute) did the user specify? */
                            name?: Business;
                            /** @example 11 */
                            count?: number;
                            /**
                             * Duration in
                             *      *                                                            minutes
                             * @example 425
                             */
                            duration?: number;
                        }[];
                        /** The categories of the travel */
                        categories?: {
                            /** Category of transport.  */
                            name?: HafasTravelType;
                            /** @example 11 */
                            count?: number;
                            /**
                             * Duration in minutes
                             * @example 425
                             */
                            duration?: number;
                        }[];
                        /** The operators of the means of transport */
                        operators?: {
                            /** @example "Gertruds Verkehrsgesellschaft mbH" */
                            name?: any;
                            /** @example 10 */
                            count?: number;
                            /**
                             * Duration in minutes
                             * @example 424
                             */
                            duration?: number;
                        }[];
                        /** Shows the daily travel volume */
                        time?: {
                            /** @example "2021-01-01T00:00:00.000Z" */
                            date?: string;
                            /** @example 10 */
                            count?: number;
                            /**
                             * Duration in minutes
                             * @example 424
                             */
                            duration?: number;
                        }[];
                    };
                },
                void
            >({
                path: `/statistics`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns all statuses and statistics for the requested day
         *
         * @tags Statistics
         * @name GetDailyStatistics
         * @summary Get statistics and statuses of one day
         * @request GET:/statistics/daily/{date}
         * @secure
         */
        getDailyStatistics: (
            date: string,
            query?: {
                /**
                 * Timezone for the date. If not set, the user's timezone will be used.
                 * @example "Europe/Berlin"
                 */
                timezone?: string;
                /**
                 * If this parameter is set, the polylines will be returned as well. Otherwise attribute is
                 *      *          null.
                 */
                withPolylines?: boolean;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: {
                        statuses?: StatusResource[];
                        polylines?: FeatureCollection[];
                        /** @example "74026" */
                        totalDistance?: number;
                        /** @example "4711" */
                        totalDuration?: number;
                        /** @example "42" */
                        totalPoints?: number;
                    };
                },
                void
            >({
                path: `/statistics/daily/${date}`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Statistics
         * @name GetGlobalStatistics
         * @summary Get global statistics of the last 4 weeks
         * @request GET:/statistics/global
         * @secure
         */
        getGlobalStatistics: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: {
                        /**
                         * Globally travelled distance in meters
                         * @example 1000
                         */
                        distance?: number;
                        /**
                         * Globally travelled duration in minutes
                         * @example 1000
                         */
                        duration?: number;
                        /**
                         * Number of active users
                         * @example 1000
                         */
                        activeUsers?: number;
                        meta?: {
                            /** @example "2021-01-01T00:00:00.000000Z" */
                            from?: any;
                            /** @example "2021-02-01T00:00:00.000000Z" */
                            until?: any;
                        };
                    };
                },
                any
            >({
                path: `/statistics/global`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    dashboard = {
        /**
         * @description Returns paginated statuses of personal dashboard
         *
         * @tags Dashboard
         * @name GetDashboard
         * @summary Get paginated statuses of personal dashboard
         * @request GET:/dashboard
         * @secure
         */
        getDashboard: (
            query?: {
                /** Page of pagination */
                page?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: StatusResource[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/dashboard`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns paginated statuses of global dashboard
         *
         * @tags Dashboard
         * @name GetGlobalDashboard
         * @summary Get paginated statuses of global dashboard
         * @request GET:/dashboard/global
         * @secure
         */
        getGlobalDashboard: (
            query?: {
                /** Page of pagination */
                page?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: StatusResource[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/dashboard/global`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns paginated statuses of the authenticated user, that are more than 20 minutes in the *      future
         *
         * @tags Dashboard
         * @name GetFutureDashboard
         * @summary Get paginated future statuses of current user
         * @request GET:/dashboard/future
         * @secure
         */
        getFutureDashboard: (
            query?: {
                /** Page of pagination */
                page?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: StatusResource[];
                    /** pagination links */
                    links?: Links;
                    /** Pagination meta data */
                    meta?: PaginationMeta;
                },
                void
            >({
                path: `/dashboard/future`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),
    };
    statuses = {
        /**
         * @description Returns all currently active statuses that are visible to the (un)authenticated user
         *
         * @tags Status
         * @name GetActiveStatuses
         * @summary [Auth optional] Get active statuses
         * @request GET:/statuses
         * @secure
         */
        getActiveStatuses: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: StatusResource[];
                },
                void
            >({
                path: `/statuses`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns a collection of all visible tags for the given statuses, if user is authorized
         *
         * @tags Status
         * @name GetTagsForMultipleStatuses
         * @summary Show all tags for multiple statuses which are visible for the current user
         * @request GET:/statuses/{statusIds}/tags
         * @secure
         */
        getTagsForMultipleStatuses: (statusIds?: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: {
                        "1337"?: StatusTag[];
                        "4711"?: StatusTag[];
                    };
                },
                void
            >({
                path: `/statuses/${statusIds}/tags`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    positions = {
        /**
         * @description Returns an array of live position objects for active statuses
         *
         * @tags Status
         * @name GetLivePositionsForActiveStatuses
         * @summary [Auth optional] get live positions for active statuses
         * @request GET:/positions
         * @secure
         */
        getLivePositionsForActiveStatuses: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: LivePointDto[];
                },
                void
            >({
                path: `/positions`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns an array of live position objects for given status IDs
         *
         * @tags Status
         * @name GetLivePositionsForStatuses
         * @summary [Auth optional] get live positions for given statuses
         * @request GET:/positions/{ids}
         * @secure
         */
        getLivePositionsForStatuses: (ids?: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: LivePointDto[];
                },
                void
            >({
                path: `/positions/${ids}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    polyline = {
        /**
         * @description Returns GeoJSON for all requested status IDs
         *
         * @tags Status
         * @name GetPolylines
         * @summary [Auth optional] Get GeoJSON for statuses
         * @request GET:/polyline/{ids}
         * @secure
         */
        getPolylines: (ids?: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: {
                        /** @example "FeatureCollection" */
                        type?: any;
                        features?: Polyline[];
                    };
                },
                void
            >({
                path: `/polyline/${ids}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    stopovers = {
        /**
         * @description Returns all underway-stops for stations
         *
         * @tags Status
         * @name GetStopOvers
         * @summary [Auth optional] Get stopovers for statuses
         * @request GET:/stopovers/{ids}
         * @secure
         */
        getStopOvers: (ids?: string, params: RequestParams = {}) =>
            this.request<
                {
                    data?: {
                        /** Array of stopovers. Key describes trip id */
                        "1"?: StopoverResource[];
                    };
                },
                void
            >({
                path: `/stopovers/${ids}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    station = {
        /**
         * @description Get departures from a station.
         *
         * @tags Checkin
         * @name GetDepartures
         * @summary Get departures from a station
         * @request GET:/station/{id}/departures
         * @secure
         */
        getDepartures: (
            id: any,
            query?: {
                /**
                 * When to get the departures (default: now). If you omit the timezone, the datetime is interpreted as localtime. This is especially helpful when träwelling abroad.
                 * @format date-time
                 * @example "2020-01-01T12:00:00.000Z"
                 */
                when?: string;
                /** Means of transport (default: all) */
                travelType?: TravelType;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: any[];
                    meta?: {
                        /** train station model */
                        station?: Station;
                        times?: {
                            /**
                             * @format date-time
                             * @example "2020-01-01T12:00:00.000Z"
                             */
                            now?: string;
                            /**
                             * @format date-time
                             * @example "2020-01-01T11:45:00.000Z"
                             */
                            prev?: string;
                            /**
                             * @format date-time
                             * @example "2020-01-01T12:15:00.000Z"
                             */
                            next?: string;
                        };
                    };
                },
                void
            >({
                path: `/station/${id}/departures`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Checkin
         * @name SetHomeStation
         * @summary Set a station as home station
         * @request PUT:/station/{id}/home
         * @secure
         */
        setHomeStation: (id: any, params: RequestParams = {}) =>
            this.request<
                {
                    /** train station model */
                    data?: Station;
                },
                void
            >({
                path: `/station/${id}/home`,
                method: "PUT",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    trains = {
        /**
         * No description
         *
         * @tags Checkin
         * @name GetTrainTrip
         * @summary Get the stopovers and trip information for a given train
         * @request GET:/trains/trip
         * @secure
         */
        getTrainTrip: (
            query: {
                /**
                 * HAFAS trip ID (fetched from departures)
                 * @example "1|323306|1|80|17072022"
                 */
                hafasTripId: any;
                /**
                 * line name for that train
                 * @example "S 4"
                 */
                lineName: any;
                /**
                 * start point from where the stopovers should be desplayed
                 * @example 4711
                 */
                start: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: {
                        /** @example 1 */
                        id?: number;
                        /** Category of transport.  */
                        category?: HafasTravelType;
                        /** @example "4-a6s4-4" */
                        number?: string;
                        /** @example "S 4" */
                        lineName?: string;
                        /** @example "34427" */
                        journeyNumber?: number;
                        /** train station model */
                        origin?: Station;
                        /** train station model */
                        destination?: Station;
                        stopovers?: StopoverResource[];
                    };
                },
                void
            >({
                path: `/trains/trip`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns the nearest station to the given coordinates
         *
         * @tags Checkin
         * @name TrainStationsNearby
         * @summary Location based search for stations
         * @request GET:/trains/station/nearby
         * @secure
         */
        trainStationsNearby: (
            query: {
                /**
                 * latitude
                 * @example 48.991
                 */
                latitude: any;
                /**
                 * longitude
                 * @example 8.4005
                 */
                longitude: any;
            },
            params: RequestParams = {},
        ) =>
            this.request<
                {
                    data?: Station[];
                },
                void
            >({
                path: `/trains/station/nearby`,
                method: "GET",
                query: query,
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Checkin
         * @name CreateCheckin
         * @summary Check in to a trip.
         * @request POST:/trains/checkin
         * @secure
         */
        createCheckin: (data: CheckinRequestBody, params: RequestParams = {}) =>
            this.request<CheckinSuccessResource, void | CheckinForbiddenWithUsersResponse>({
                path: `/trains/checkin`,
                method: "POST",
                body: data,
                secure: true,
                type: ContentType.Json,
                format: "json",
                ...params,
            }),

        /**
         * @description This request returns an array of max. 10 station objects matching the query. **CAUTION:** All *      slashes (as well as encoded to %2F) in {query} need to be replaced, preferrably by a space (%20)
         *
         * @tags Checkin
         * @name TrainStationAutocomplete
         * @summary Autocomplete for stations
         * @request GET:/trains/station/autocomplete/{query}
         * @secure
         */
        trainStationAutocomplete: (query?: any, params: RequestParams = {}) =>
            this.request<
                {
                    data?: StationResource[];
                },
                void
            >({
                path: `/trains/station/autocomplete/${query}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description This request returns an array of max. 10 most recent station objects that the user has arrived *      at.
         *
         * @tags Checkin
         * @name TrainStationHistory
         * @summary History for stations
         * @request GET:/trains/station/history
         * @secure
         */
        trainStationHistory: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: Station[];
                },
                void
            >({
                path: `/trains/station/history`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),
    };
    webhooks = {
        /**
         * @description Returns all webhooks which are created for the current user and which the current authorized applicaton has access to.
         *
         * @tags Webhooks
         * @name GetWebhooks
         * @summary Get webhooks for current user and current application
         * @request GET:/webhooks
         * @secure
         */
        getWebhooks: (params: RequestParams = {}) =>
            this.request<
                {
                    data?: Webhook[];
                },
                void
            >({
                path: `/webhooks`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * @description Returns a single webhook Object, if user and application is authorized to see it
         *
         * @tags Webhooks
         * @name GetSingleWebhook
         * @summary Get single webhook
         * @request GET:/webhooks/{id}
         * @secure
         */
        getSingleWebhook: (id?: number, params: RequestParams = {}) =>
            this.request<
                {
                    /** Webhook model */
                    data?: Webhook;
                },
                void
            >({
                path: `/webhooks/${id}`,
                method: "GET",
                secure: true,
                format: "json",
                ...params,
            }),

        /**
         * No description
         *
         * @tags Webhooks
         * @name DeleteWebhook
         * @summary Delete a webhook if the user and application are authorized to do
         * @request DELETE:/webhooks/{id}
         * @secure
         */
        deleteWebhook: (id?: number, params: RequestParams = {}) =>
            this.request<SuccessResponse, void>({
                path: `/webhooks/${id}`,
                method: "DELETE",
                secure: true,
                format: "json",
                ...params,
            }),
    };
}

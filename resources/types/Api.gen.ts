/* eslint-disable */
/* tslint:disable */
// @ts-nocheck
/*
 * ---------------------------------------------------------------
 * ## THIS FILE WAS GENERATED VIA SWAGGER-TYPESCRIPT-API        ##
 * ##                                                           ##
 * ## AUTHOR: acacode                                           ##
 * ## SOURCE: https://github.com/acacode/swagger-typescript-api ##
 * ---------------------------------------------------------------
 */

/**
 * ViewUserForbiddenReason
 * @example "PRIVATE_PROFILE"
 */
export enum ViewUserForbiddenReason {
  PRIVATE_PROFILE = "PRIVATE_PROFILE",
  USER_MUTED = "USER_MUTED",
  YOU_ARE_BLOCKED = "YOU_ARE_BLOCKED",
  USER_BLOCKED = "USER_BLOCKED",
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
 * visibility
 * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated, 5=trusted) did the user specify?
 * @example 0
 */
export enum StatusVisibility {
  Value0 = 0,
  Value1 = 1,
  Value2 = 2,
  Value3 = 3,
  Value4 = 4,
  Value5 = 5,
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
 * mode
 * Mode of transport
 * @example "suburban"
 */
export enum MotisCategory {
  WALK = "WALK",
  BIKE = "BIKE",
  RENTAL = "RENTAL",
  CAR = "CAR",
  CAR_PARKING = "CAR_PARKING",
  CAR_DROPOFF = "CAR_DROPOFF",
  ODM = "ODM",
  RIDE_SHARING = "RIDE_SHARING",
  FLEX = "FLEX",
  TRANSIT = "TRANSIT",
  TRAM = "TRAM",
  SUBWAY = "SUBWAY",
  FERRY = "FERRY",
  AIRPLANE = "AIRPLANE",
  SUBURBAN = "SUBURBAN",
  BUS = "BUS",
  COACH = "COACH",
  RAIL = "RAIL",
  HIGHSPEED_RAIL = "HIGHSPEED_RAIL",
  LONG_DISTANCE = "LONG_DISTANCE",
  NIGHT_RAIL = "NIGHT_RAIL",
  REGIONAL_FAST_RAIL = "REGIONAL_FAST_RAIL",
  REGIONAL_RAIL = "REGIONAL_RAIL",
  CABLE_CAR = "CABLE_CAR",
  FUNICULAR = "FUNICULAR",
  AERIAL_LIFT = "AERIAL_LIFT",
  OTHER = "OTHER",
  AERAL_LIFT = "AERAL_LIFT",
  METRO = "METRO",
}

/**
 * visibility
 * What type of visibility (0=public, 1=unlisted, 2=followers, 3=private) did the user specify for future posts to Mastodon? Some instances such as chaos.social discourage bot posts on public timelines.
 * @example 1
 */
export enum MastodonVisibility {
  Value0 = 0,
  Value1 = 1,
  Value2 = 2,
  Value3 = 3,
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
 * DataProvider
 * What type of data provider did the user specify? (users need to be in closed-beta for this to take effect)
 * @example "cargo"
 */
export enum DataProvider {
  Default = "default",
  Transitous = "transitous",
}

/** Enumeration of configuration features available in the application. */
export enum ConfigurationFeatureEnum {
  UserRegistration = "user_registration",
  YearInReview = "year_in_review",
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

/** Represents a configuration feature and its status. */
export interface Feature {
  /**
   * The name of the feature.
   * @example "user_registration"
   */
  name: string;
  /** Indicates whether the feature is enabled. */
  enabled: boolean;
}

/** Holds configuration information about the application. */
export interface ConfigurationInformation {
  /**
   * The name of the application.
   * @example "Träwelling"
   */
  appName: string;
  /**
   * Indicates whether the application is in debug mode.
   * @example false
   */
  appDebug: boolean;
  /**
   * The base URL of the application.
   * @example "https://traewelling.de"
   */
  appUrl: string;
  /**
   * The current version of the application.
   * @example "1.0.0"
   */
  version: string;
  /** A list of configuration features available in the application. */
  features: Feature[];
  /** A list of supported languages in the application. */
  languages: Language[];
}

/** Represents a language with its code and name. */
export interface Language {
  /**
   * The language code (e.g., "en", "fr").
   * @example "en"
   */
  code: string;
  /**
   * The name of the language (e.g., "English", "French").
   * @example "English"
   */
  name: string;
}

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
 * License DTO
 * Data Transfer Object for licenses
 */
export interface LicenseDto {
  /**
   * Name of the license
   * @example "CC BY 4.0"
   */
  licenseName: string;
  /**
   * Attribution string for the license
   * @example "Provided by OpenStreetMap contributors"
   */
  attributionString: string | null;
  /**
   * URL to the license text
   * @example "https://creativecommons.org/licenses/by/4.0/"
   */
  licenseUrl: string | null;
  /**
   * URL to the source of the data
   * @example "https://www.openstreetmap.org/"
   */
  sourceUrl: string | null;
}

/**
 * LivePointDto
 * All necessary information to calculate live position
 */
export interface LivePointDto {
  /**
   * point
   * current point, if stopping at a station
   */
  point?: Coordinate | null;
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
  /** user */
  user: UserResource | null;
  /**
   * position
   * @format int
   * @example 0
   */
  position: number;
  /**
   * length
   * @format integer
   * @example 4
   */
  length: number;
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
  ibnr?: number | null;
  /**
   * rilIdentifier
   * Identifier specified in 'Richtline 100' of the Deutsche Bahn
   * @example "RK"
   */
  rilIdentifier?: string | null;
}

/** BearerTokenResponse */
export interface BearerTokenResponse {
  /**
   * Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)
   * @example "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
   */
  token?: string;
  /**
   * End of life for this token.
   * @example "2023-10-19T15:15:06+02:00"
   */
  expires_at?: string;
}

/**
 * EventSuggestion
 * Fields for suggesting an event
 */
export interface EventSuggestion {
  /**
   * name of the event
   * @maxLength 255
   * @example "Eröffnung der Nebenbahn in Knuffingen"
   */
  name?: string;
  /**
   * host of the event
   * @example "MiWuLa"
   */
  host?: string | null;
  /**
   * Timestamp for the start of the event
   * @format date-time
   * @example "2022-06-01T00:00:00+02:00"
   */
  begin?: string;
  /**
   * Timestamp for the end of the event
   * @format date-time
   * @example "2022-08-31T23:59:00+02:00"
   */
  end?: string;
  /**
   * external URL for this event
   * @maxLength 255
   * @example "https://www.example.com/event"
   */
  url?: string | null;
  /**
   * hashtag for this event
   * @maxLength 40
   * @example "gpn21"
   */
  hashtag?: string | null;
  /**
   * Query string for the nearest station. Deprecated: use nearestStationId instead.
   * @deprecated
   * @maxLength 255
   * @example "Berlin Hbf"
   */
  nearestStation?: string | null;
  /**
   * ID of the nearest station to this event
   * @example 1
   */
  nearestStationId?: number | null;
}

/** LikeResponse */
export interface LikeResponse {
  /**
   * Amount of likes
   * @format int32
   * @example 12
   */
  count?: number;
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
  body?: string | null;
  /** What type of travel (0=private, 1=business, 2=commute) did the user specify? */
  business?: Business;
  /** What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated, 5=trusted) did the user specify? */
  visibility?: StatusVisibility;
  /**
   * The ID of the event this status is related to - or null
   * @example "1"
   */
  eventId?: string | null;
  /**
   * Manual departure time set by the user
   * @format date
   * @example "2020-01-01 12:00:00"
   */
  manualDeparture?: string | null;
  /**
   * Manual arrival time set by the user
   * @format date
   * @example "2020-01-01 13:00:00"
   */
  manualArrival?: string | null;
  /**
   * Destination station id
   * @example "1"
   */
  destinationId?: string | null;
  /**
   * Destination arrival time
   * @format date
   * @example "2020-01-01 13:00:00"
   */
  destinationArrivalPlanned?: string | null;
}

/**
 * Polyline
 * Polyline of a single status as GeoJSON Feature
 */
export interface Polyline {
  /** @example "Feature" */
  type?: string;
  geometry?: {
    /** @example "LineString" */
    type?: string;
    coordinates?: any[];
  };
  properties?: {
    /** @example 1337 */
    statusId?: number;
  };
}

/**
 * CheckinRequestBody
 * Fields for creating a transit checkin
 */
export interface CheckinRequestBody {
  /**
   * @maxLength 280
   * @example "Meine erste Fahrt nach Knuffingen!"
   */
  body?: string | null;
  /** What type of travel (0=private, 1=business, 2=commute) did the user specify? */
  business?: Business;
  /** What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated, 5=trusted) did the user specify? */
  visibility?: StatusVisibility;
  /**
   * Id of an event the status should be connected to
   * @example 1
   */
  eventId?: number | null;
  /**
   * Should this status be posted to mastodon?
   * @example false
   */
  toot?: boolean | null;
  /**
   * Should this status be posted to mastodon as a chained post?
   * @example false
   */
  chainPost?: boolean | null;
  /**
   * If true, `start` and `destination` can be supplied as IBNR. Otherwise Träwelling-ID. Default: false.
   * @example true
   */
  ibnr?: boolean | null;
  /**
   * The tripId for the trip to check into
   * @example "b37ff515-22e1-463c-94de-3ad7964b5cb8"
   */
  tripId?: string | null;
  /**
   * The line name for the trip to check into
   * @example "S 4"
   */
  lineName?: string | null;
  /**
   * Station-ID of the starting point (see `ibnr`)
   * @example 8000191
   */
  start?: number;
  /**
   * Station-ID of the destination (see `ibnr`)
   * @example 8000192
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
   * If true, the checkin is created even on collision. No points awarded.
   * @example false
   */
  force?: boolean | null;
  /** Also check in these user IDs (max. 10). Requires mutual follow. */
  with?: number[] | null;
}

/**
 * UpdateProfileInformationRequest
 * UpdateProfileInformationRequest
 */
export interface UpdateProfileInformationRequest {
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
  /**
   * @maxLength 500
   * @example "Hi there! I am Gertrud!"
   */
  bio?: string | null;
  /**
   * Experimental features enabled
   * @example false
   */
  experimental?: boolean;
  profileLinks?: ProfileLinkResource[] | null;
  /** @example "Europe/Berlin" */
  timezone?: string;
}

export interface AlertResource {
  /** @example "123e4567-e89b-12d3-a456-426614174000" */
  id: string;
  /** @example "info" */
  type: "info" | "warning" | "danger" | "success";
  /**
   * @format date-time
   * @example "2023-10-01T00:00:00Z"
   */
  active_from: string;
  /**
   * @format date-time
   * @example "2023-10-31T23:59:59Z"
   */
  active_until: string | null;
  /** @example "https://example.com" */
  url: string | null;
  translations: AlertTranslationResource[];
}

export interface AlertTranslationResource {
  /** @example "Alert Title" */
  title: string;
  /** @example "Alert Content" */
  content: string;
  /** @example "https://example.com" */
  url: string;
  /** @example "en" */
  locale: string;
}

/** Area */
export interface AreaResource {
  /** @example "Karlsruhe" */
  name: string;
  /** @example true */
  default: boolean;
  /** @example "1" */
  adminLevel: number;
}

/** PointsCalculation */
export interface PointsCalculation {
  /**
   * Basepoints for this type of vehicle
   * @format float
   * @example 0.5
   */
  base?: number;
  /**
   * Points for the travelled distance
   * @format float
   * @example 0.25
   */
  distance?: number;
  /**
   * @format float
   * @example 0.25
   */
  factor?: number;
  /** What is the reason for the points calculation factor? (0=in time => 100%, 1=good enough => 25%, 2=not sufficient (1 point), 3=forced => no points, 4=manual trip => no points, 5=points disabled) */
  reason?: PointReason;
}

/**
 * Points
 * Points model
 */
export interface Points {
  /**
   * points
   * @example 1
   */
  points?: number;
  calculation?: PointsCalculation;
  /** extra points that can be given */
  additional?: any[] | null;
}

/** CheckinResponse */
export interface CheckinSuccessResource {
  status?: StatusResource;
  /** Points model */
  points?: Points;
  /** Statuses of other people on this connection */
  alsoOnThisConnection?: StatusResource[];
}

/** Client */
export interface ClientResource {
  /** @example 1 */
  id: number;
  /** @example "Träwelling App" */
  name: string;
  /** @example "https://traewelling.de/privacy-policy" */
  privacyPolicyUrl: string;
}

/**
 * CommunityProfile
 * Community contribution profile data
 */
export interface CommunityProfile {
  /**
   * Total contribution XP earned
   * @example 75
   */
  xp: number;
  /**
   * Current contribution level
   * @example 1
   */
  level: number;
  /**
   * XP required for next level
   * @example 150
   */
  nextLevelXP: number;
  /**
   * Progress percentage to next level
   * @format float
   * @example 25
   */
  progressPercent: number;
}

/**
 * ContributionHistory
 * A single contribution history entry
 */
export interface ContributionHistory {
  /**
   * @format uuid
   * @example "9e3a1b2c-4d5e-6f7a-8b9c-0d1e2f3a4b5c"
   */
  id: string;
  /** @example "event_suggested" */
  actionType: string;
  /** @example "event_suggestion" */
  entityType: string;
  /** @example 42 */
  entityId: number;
  /** @example 5 */
  xpChange: number;
  /** @example 0 */
  levelBefore: number;
  /** @example 1 */
  levelAfter: number;
  /** @example "Event approved: GPN 22" */
  note?: string | null;
  /**
   * @format date-time
   * @example "2026-02-15T12:00:00Z"
   */
  createdAt: string;
}

/** DataSourceResource */
export interface DataSourceResource {
  /** @example "foobar" */
  id: string;
  /** @example "Provided by foobar under CC BY 4.0" */
  attribution: string;
}

/**
 * Departure
 * A single departure at a station
 */
export interface DepartureResource {
  /**
   * Unique trip identifier
   * @example "1|200513|0|81|6012023"
   */
  tripId: string;
  /** The stop at which this departure occurs */
  stop: {
    /** @example "stop" */
    type?: string;
    /**
     * Träwelling internal station ID
     * @example 5181
     */
    id?: number;
    /** @example "Karlsruhe Hbf" */
    name?: string;
    location?: {
      /** @example "location" */
      type?: string;
      /**
       * IBNR identifier (if available)
       * @example "8000191"
       */
      id?: string | null;
      /**
       * @format float
       * @example 48.993207
       */
      latitude?: number;
      /**
       * @format float
       * @example 8.400977
       */
      longitude?: number;
    };
    /**
     * Deprecated. Always true for all modes.
     * @deprecated
     */
    products?: object;
  };
  /**
   * Actual departure time (null if no realtime data)
   * @format date-time
   * @example "2023-01-06T13:49:00+01:00"
   */
  when: string | null;
  /**
   * Scheduled departure time
   * @format date-time
   * @example "2023-01-06T13:49:00+01:00"
   */
  plannedWhen: string;
  /**
   * Delay in minutes (null if no realtime data). Deprecated, use when/plannedWhen difference.
   * @deprecated
   * @example 2
   */
  delay: number | null;
  /**
   * Actual platform (null if no realtime data)
   * @example "3a"
   */
  platform: string | null;
  /**
   * Scheduled platform
   * @example "3"
   */
  plannedPlatform: string | null;
  /**
   * Final destination of the trip
   * @example "Zürich HB"
   */
  direction: string;
  /**
   * Deprecated. Always null.
   * @deprecated
   * @example null
   */
  provenance?: string | null;
  line: {
    /** @example "line" */
    type?: string;
    /** @example "EC 9" */
    id?: string;
    /**
     * Journey number
     * @example "9"
     */
    fahrtNr?: string;
    /** @example "EC 9" */
    name?: string;
    /**
     * Route color as hex (without #)
     * @example "ff2e3e"
     */
    color?: string | null;
    /**
     * Route text color as hex (without #)
     * @example "ffffff"
     */
    textColor?: string | null;
    /**
     * Deprecated. Always true.
     * @deprecated
     * @example true
     */
    public?: boolean;
    /**
     * Deprecated. Use name.
     * @deprecated
     * @example "EC 9"
     */
    productName?: string;
    /**
     * Transit mode
     * @example "TRAIN"
     */
    mode?: string | null;
    /**
     * Deprecated HAFAS product category
     * @deprecated
     * @example "national"
     */
    product?: string | null;
    /**
     * Deprecated. Always "80____".
     * @deprecated
     * @example "80____"
     */
    adminCode?: string;
    /**
     * Deprecated. Always null.
     * @deprecated
     */
    operator?: object | null;
  };
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  remarks?: any[] | null;
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  origin?: object | null;
  /** Destination stop. Only name is currently populated; all other fields are deprecated placeholders. */
  destination?: {
    /** @example "stop" */
    type?: string;
    /**
     * Deprecated. Always 0.
     * @deprecated
     * @example 0
     */
    id?: number;
    /**
     * Final destination name
     * @example "Zürich HB"
     */
    name?: string;
    /**
     * Deprecated. All values are always 0.
     * @deprecated
     */
    location?: {
      /** @example "location" */
      type?: string;
      /**
       * Deprecated. Always 0.
       * @deprecated
       * @example 0
       */
      id?: number;
      /**
       * Deprecated. Always 0.
       * @deprecated
       * @format float
       * @example 0
       */
      latitude?: number;
      /**
       * Deprecated. Always 0.
       * @deprecated
       * @format float
       * @example 0
       */
      longitude?: number;
    };
    /**
     * Deprecated. Always true for all modes.
     * @deprecated
     */
    products?: object;
  };
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  currentTripPosition?: object | null;
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  loadFactor?: string | null;
  station: StationResource;
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
  id: number;
  /** @example "9-Euro-Ticket" */
  name: string;
  /** @example "9_euro_ticket" */
  slug: string;
  /** @example "NeunEuroTicket" */
  hashtag: string;
  /** @example "9-Euro-Ticket GmbH" */
  host: string;
  /** @example "https://9-euro-ticket.de" */
  url: string;
  /**
   * @format date
   * @example "2022-01-01"
   */
  begin: string;
  /**
   * @format date
   * @example "2022-01-02"
   */
  end: string;
  /** train station model */
  station: Station;
  isPride: StationResource;
}

export interface IcsEntryResource {
  /**
   * The unique identifier of the ICS token
   * @example 1
   */
  id: number;
  /**
   * The first 8 characters of the ICS token
   * @example "abcd1234"
   */
  token: string;
  /**
   * The name of the ICS token
   * @example "My ICS Token"
   */
  name: string;
  /**
   * The ISO 8601 timestamp when the ICS token was created
   * @format date-time
   * @example "2024-01-01T12:00:00Z"
   */
  createdAt?: string | null;
  /**
   * The ISO 8601 timestamp when the ICS token was last accessed
   * @format date-time
   * @example "2024-01-15T08:30:00Z"
   */
  lastAccessed: string | null;
}

/** LeaderboardUserResource */
export interface LeaderboardUserResource {
  /** User model with just basic information */
  user: LightUserResource;
  /**
   * duration travelled in minutes
   * @example 6
   */
  totalDuration: number;
  /**
   * distance travelled in meters
   * @example 12345
   */
  totalDistance: number;
  /** points of user */
  points: number;
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
  profilePicture: string;
  /** @example {"server":"mastodon.social","user_id":1234567} */
  mastodon?: object;
  /** @example false */
  preventIndex: boolean;
}

/**
 * Links
 * Pagination links
 */
export interface Links {
  /**
   * Shared OA schema for Laravel pagination links. Not a real resource.
   * @format uri
   * @example "https://traewelling.de/api/v1/ENDPOINT?page=1"
   */
  first?: string | null;
  /**
   * @format uri
   * @example null
   */
  last?: string | null;
  /**
   * @format uri
   * @example null
   */
  prev?: string | null;
  /**
   * @format uri
   * @example "https://traewelling.de/api/v1/ENDPOINT?page=2"
   */
  next?: string | null;
}

export interface OperatorResource {
  /** @example 1 */
  id: number;
  /** @example "db-regio-ag-nord" */
  identifier: string | null;
  /** @example "DB Regio AG Nord" */
  name: string;
}

/**
 * Meta
 * Pagination meta data
 */
export interface PaginationMeta {
  /**
   * Shared OA schema for Laravel pagination meta data. Not a real resource.
   * @example 2
   */
  current_page?: number;
  /** @example 16 */
  from?: number;
  /**
   * @format url
   * @example "https://traewelling.de/api/v1/ENDPOINT"
   */
  path?: string;
  /** @example 15 */
  per_page?: number;
  /** @example 30 */
  to?: number;
}

/**
 * ProfileLinkResource
 * ProfileLinkResource
 */
export interface ProfileLinkResource {
  /** @example "website" */
  name:
    | "website"
    | "instagram"
    | "bluesky"
    | "facebook"
    | "mastodon"
    | "tiktok"
    | "github";
  /** @example "https://traewelling.de" */
  url: string;
}

export interface SessionResource {
  /**
   * The session ID
   * @example "abc123"
   */
  id: string;
  /**
   * The masked IP address of the session
   * @example "192.168.***.***"
   */
  ip: string;
  /**
   * The user agent string of the session
   * @example "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
   */
  userAgent: string;
  /**
   * The platform of the session
   * @example "Windows"
   */
  platform: string;
  /**
   * The type representing the device used in the session
   * @example "mobile"
   */
  deviceType: string;
  /**
   * The timestamp of the last activity in ISO 8601 format
   * @format date-time
   * @example "2024-06-01T12:34:56Z"
   */
  lastActivity: string;
}

/** StationIdentifier */
export interface StationIdentifierResource {
  /** @example "de_db_ril100" */
  type?: string;
  /** @example "RK" */
  identifier?: string;
}

/** Station */
export interface StationResource {
  /** @example "1" */
  id: number;
  /** @example "Karlsruhe Hbf" */
  name: string;
  /** @example "48.993207" */
  latitude: number;
  /** @example "8.400977" */
  longitude: number;
  /**
   * Deprecated. Always null. Use identifiers with type "de_db_ibnr" instead.
   * @deprecated
   * @example null
   */
  ibnr: number | null;
  /**
   * Deprecated. Always null. Use identifiers with type "de_db_ril100" instead.
   * @deprecated
   * @example null
   */
  rilIdentifier: string | null;
  areas: AreaResource[];
  identifiers?: StationIdentifierResource[];
}

/** StatisticsGlobalData */
export interface StatisticsGlobalData {
  /**
   * Globally travelled distance in meters
   * @example 1000
   */
  distance: number;
  /**
   * Globally travelled duration in minutes
   * @example 1000
   */
  duration: number;
  /**
   * Number of active users
   * @example 1000
   */
  activeUsers: number;
}

/** Status */
export interface StatusResource {
  /** @example 12345 */
  id: number;
  /**
   * User defined status text
   * @example "Hello world!"
   */
  body: any;
  /** Mentions in the status body */
  bodyMentions: MentionDto[];
  /** What type of travel (0=private, 1=business, 2=commute) did the user specify? */
  business: Business;
  /** What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated, 5=trusted) did the user specify? */
  visibility: StatusVisibility;
  /**
   * How many people have liked this status
   * @example 12
   */
  likes: number;
  /**
   * Did the currently authenticated user like this status? (if unauthenticated = false)
   * @example true
   */
  liked: boolean;
  /**
   * Do the author of this status and the currently authenticated user allow liking of statuses? Only show the like UI if set to true
   * @example true
   */
  isLikable: boolean;
  client: ClientResource;
  checkin: TransportResource;
  event: EventResource | null;
  /** User model with just basic information */
  user: LightUserResource;
  /** User who created this check-in on behalf of the status owner (null if self-checkin) */
  createdBy: LightUserResource | null;
  tags: StatusTagResource[];
  /**
   * creation date of this status
   * @format datetime
   * @example "2022-07-17T13:37:00+02:00"
   */
  createdAt: string;
}

/** StatusTagResource */
export interface StatusTagResource {
  /** @example "trwl:vehicle_number" */
  key: string;
  /** @example "94 80 0450 921 D-AVG" */
  value: string;
  /** @example "1" */
  visibility: number;
}

/** StopoverResource */
export interface StopoverResource {
  /** @example 12345 */
  id: number;
  /**
   * name of the station
   * @example "Karlsruhe Hbf"
   */
  name: string;
  /**
   * Deprecated. Always null. Use the station identifiers endpoint instead.
   * @deprecated
   * @example null
   */
  rilIdentifier: string | null;
  /**
   * Deprecated. Always null. Use the station identifiers endpoint instead.
   * @deprecated
   * @example null
   */
  evaIdentifier: string | null;
  /**
   * currently known arrival time. Equal to arrivalReal if known. Else equal to arrivalPlanned.
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  arrival: string | null;
  /**
   * planned arrival according to timetable records
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  arrivalPlanned: string | null;
  /**
   * real arrival according to live data
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  arrivalReal: string | null;
  /**
   * planned arrival platform according to timetable records
   * @example "5"
   */
  arrivalPlatformPlanned: string | null;
  /**
   * real arrival platform according to live data
   * @example "5 A-F"
   */
  arrivalPlatformReal: string | null;
  /**
   * currently known departure time. Equal to departureReal if known. Else equal to departurePlanned.
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  departure: string | null;
  /**
   * planned departure according to timetable records
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  departurePlanned: string | null;
  /**
   * real departure according to live data
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  departureReal: string | null;
  /**
   * planned departure platform according to timetable records
   * @example "5"
   */
  departurePlatformPlanned: string | null;
  /**
   * real departure platform according to live data
   * @example "5 A-F"
   */
  departurePlatformReal: string | null;
  /** @example "5 A-F" */
  platform: string | null;
  /**
   * Is there a delay in the arrival time?
   * @example false
   */
  isArrivalDelayed: boolean;
  /**
   * Is there a delay in the departure time?
   * @example false
   */
  isDepartureDelayed: boolean;
  /**
   * is this stopover cancelled?
   * @example false
   */
  cancelled: boolean;
}

export interface TokenResource {
  /**
   * The token ID
   * @example "abc123"
   */
  id: string;
  /**
   * The name of the client associated with the token
   * @example "MyApp"
   */
  client: string;
  /**
   * The scopes associated with the token
   * @example ["read","write"]
   */
  scopes: string[];
  /**
   * The timestamp when the token was created in ISO 8601 format
   * @format date-time
   * @example "2024-06-01T12:34:56Z"
   */
  createdAt: string;
  /**
   * The timestamp when the token expires in ISO 8601 format
   * @format date-time
   * @example "2024-07-01T12:34:56Z"
   */
  expiresAt: string;
}

/** TransportResource */
export interface TransportResource {
  /** @example "4711" */
  trip: number;
  /** @example "1|1234|567" */
  hafasId: string;
  /** Category of transport. */
  category: HafasTravelType;
  mode: MotisCategory | null;
  /**
   * Internal number of the journey
   * @example "4-a6s8-8"
   */
  number: any;
  /** @example "S 1" */
  lineName: string;
  /**
   * Hex color code of the route, if available
   * @example "FFEE00"
   */
  routeColor?: string | null;
  /**
   * Hex color code of the route text, if available
   * @example "FFFFFF"
   */
  routeTextColor?: string | null;
  /** @example 85639 */
  journeyNumber: number;
  /**
   * Manual journey number, if set by the user. This is intended for use cases like ICE lines in germany that have line number but are more widely known by their train number
   * @example "ICE 4"
   */
  manualJourneyNumber?: string | null;
  /**
   * Distance in meters
   * @example 10000
   */
  distance: number;
  /** @example 37 */
  points: number;
  /**
   * Duration in minutes
   * @example 30
   */
  duration: number;
  /**
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  manualDeparture: string | null;
  /**
   * @format date-time
   * @example "2022-07-17T13:37:00+02:00"
   */
  manualArrival: string | null;
  origin: StopoverResource;
  destination: StopoverResource;
  operator?: OperatorResource | null;
  dataSource?: DataSourceResource | null;
}

/** TripResource */
export interface TripResource {
  /** @example 1 */
  id?: number;
  /** Category of transport. */
  category?: HafasTravelType;
  mode?: MotisCategory | null;
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
  dataSource?: DataSourceResource | null;
}

/** TrustedUser */
export interface TrustedUserResource {
  /** User model with just basic information */
  user: LightUserResource;
  /**
   * @format date-time
   * @example "2024-07-28T00:00:00Z"
   */
  expiresAt: string | null;
}

/** UserAuth */
export interface UserAuthResource {
  /** @example "1" */
  id: number;
  /** @example "Gertrud" */
  displayName: string;
  /** @example "Gertrud123" */
  username: string;
  /** @example "https://traewelling.de/@Gertrud123/picture" */
  profilePicture: string;
  /** @example "100" */
  totalDistance: number;
  /** @example "100" */
  totalDuration: number;
  /** @example "100" */
  points: number;
  /** @example "https://mastodon.social/@Gertrud123" */
  mastodonUrl: string | null;
  /** @example false */
  privateProfile: boolean;
  /** @example false */
  preventIndex: boolean;
  /** @example true */
  likes_enabled: boolean;
  /** @example true */
  pointsEnabled?: boolean;
  /** @example "default" */
  mapProvider: string;
  home: StationResource;
  /** @example "de" */
  language: string;
  /** @example 0 */
  defaultStatusVisibility: number;
  /** @example ["admin","open-beta","closed-beta"] */
  roles: string[];
}

/**
 * Notification
 * Notification model
 */
export interface Notification {
  /** @example "bb1ba9a5-9c2b-43c3-b8c9-2f70651fc51c" */
  id?: string;
  /** @example "UserJoinedConnection" */
  type?: string;
  /** @example "<b>@bob</b> is in your connection!" */
  leadFormatted?: string;
  /** @example "@bob is in your connection!" */
  lead?: string;
  /** @example "@bob is on <b>S 81</b> from <b>Karlsruhe Hbf</b> to <b>Freudenstadt Hbf</b>." */
  noticeFormatted?: string;
  /** @example "@bob is on S 81 from Karlsruhe Hbf to Freudenstadt Hbf." */
  notice?: string;
  /** @example "https://traewelling.de/status/123456" */
  link?: string;
  data?: any[];
  /** @example "2023-01-01T00:00:00+00:00" */
  readAt?: string | null;
  /** @example "2023-01-01T00:00:00+00:00" */
  createdAt?: string;
  /** @example "2 days ago" */
  createdAtForHumans?: string;
}

/** UserProfileSettings */
export interface UserProfileSettingsResource {
  /** @example "Gertrud123" */
  username: string;
  /** @example "Gertrud" */
  displayName: string;
  /** @example "https://traewelling.de/@Gertrud123/picture" */
  profilePicture: string;
  /** @example false */
  privateProfile: boolean;
  /**
   * Did the user choose to prevent search engines from indexing their profile?
   * @example false
   */
  preventIndex: boolean;
  /** What type of visibility (0=public, 1=unlisted, 2=followers, 3=private, 4=authenticated, 5=trusted) did the user specify? */
  defaultStatusVisibility: StatusVisibility;
  /**
   * Number of days to hide the user's location history. Null if disabled.
   * @example 1
   */
  privacyHideDays?: number | null;
  /** @example true */
  password: boolean;
  /** @example "gertrud@traewelling.de" */
  email: string;
  /** @example true */
  emailVerified: boolean;
  /** @example true */
  profilePictureSet: boolean;
  /** @example "https://mastodon.social/@Gertrud123" */
  mastodon: string;
  /** What type of visibility (0=public, 1=unlisted, 2=followers, 3=private) did the user specify for future posts to Mastodon? Some instances such as chaos.social discourage bot posts on public timelines. */
  mastodonVisibility: MastodonVisibility;
  friendCheckin: FriendCheckinSetting;
  /** @example true */
  likesEnabled: boolean;
  /** @example true */
  pointsEnabled: boolean;
  /** What type of map provider (cargo, open-railway-map) did the user specify? */
  mapProvider: MapProvider;
  /** @example "Europe/Berlin" */
  timezone: string;
  /** @example "Hi there! I am Gertrud!" */
  bio: string;
  profileLinks: ProfileLinkResource[];
  /**
   * Experimental features enabled
   * @example false
   */
  experimental: boolean;
}

/**
 * User
 * User model
 */
export interface UserResource {
  /**
   * ID
   * @example 1
   */
  id: number;
  /**
   * Display name of the user
   * @example "Gertrud"
   */
  displayName: any;
  /**
   * username of user
   * @example "Gertrud123"
   */
  username: any;
  /**
   * URL of the profile picture of the user
   * @example "https://traewelling.de/@Gertrud123/picture"
   */
  profilePicture: any;
  /**
   * distance travelled by train in meters
   * @example 12345
   */
  trainDistance: number;
  /**
   * duration travelled by train in minutes
   * @example 6
   */
  trainDuration: number;
  /**
   * Current points of the last 7 days
   * @example 300
   */
  points: number;
  /**
   * URL to the Mastodon profile of the user
   * @example "https://chaos.social/@traewelling"
   */
  mastodonUrl: any;
  /**
   * is this profile set to private?
   * @example false
   */
  privateProfile: boolean;
  /**
   * Does this profile allow points? Only offer the UI to show points at any status if this setting is set to true. If set to false, the points will always be displayed as 0
   * @example true
   */
  points_enabled?: boolean;
  /**
   * Does this profile allow likes? Only offer the UI to like any status if this setting is set to true. If set to false, the likes API will return 403.
   * @example true
   */
  likes_enabled?: boolean;
  /**
   * Does this profile allow points? Only offer the UI to show points at any status if this setting is set to true. If set to false, the points will always be displayed as 0
   * @example true
   */
  pointsEnabled: boolean;
  /**
   * Can the currently authenticated user see the statuses of this user?
   * @example false
   */
  userInvisibleToMe: boolean;
  /**
   * Is this user muted by the currently authenticated user?
   * @example false
   */
  muted: boolean;
  /**
   * Is this user blocked by the currently authenticated user?
   * @example false
   */
  blocked: boolean;
  /**
   * Does the currently authenticated user follow this user?
   * @example false
   */
  following: boolean;
  /**
   * Is there a currently pending follow request?
   * @example false
   */
  followPending: boolean;
  /**
   * Is the user following you?
   * @example false
   */
  followedBy: boolean;
  /**
   * Did the user choose to prevent search engines from indexing their profile?
   * @example false
   */
  preventIndex: boolean;
  /**
   * Bio of the user
   * @example "Hi there! I am Gertrud!"
   */
  bio: string;
  /** Profile links of the user */
  profileLinks: ProfileLinkResource[];
}

/**
 * WebhookEventResource
 * WebhookEvent model
 */
export interface WebhookEventResource {
  /**
   * The type of the event
   * @example "notification"
   */
  type: any;
}

/**
 * WebhookResource
 * Webhook model
 */
export interface WebhookResource {
  /**
   * ID
   * @format int
   * @example 12345
   */
  id: any;
  client: ClientResource;
  /**
   * ID of the client which created this webhook
   * @format int
   * @example 12345
   */
  clientId: any;
  /**
   * ID of the user which created this webhook
   * @format int
   * @example 12345
   */
  userId: any;
  /**
   * URL where the webhook gets sent to
   * @example "https://example.com/webhook"
   */
  url: any;
  /**
   * The ISO 8601 timestamp when the webhook was created
   * @format date-time
   * @example "2024-01-01T12:00:00Z"
   */
  createdAt: string;
  /** List of events which are triggered for this webhook */
  events: WebhookEventResource[];
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

export type RequestParams = Omit<
  FullRequestParams,
  "body" | "method" | "query" | "path"
>;

export interface ApiConfig<SecurityDataType = unknown> {
  baseUrl?: string;
  baseApiParams?: Omit<RequestParams, "baseUrl" | "cancelToken" | "signal">;
  securityWorker?: (
    securityData: SecurityDataType | null,
  ) => Promise<RequestParams | void> | RequestParams | void;
  customFetch?: typeof fetch;
}

export interface HttpResponse<D extends unknown, E extends unknown = unknown>
  extends Response {
  data: D;
  error: E;
}

type CancelToken = Symbol | string | number;

export enum ContentType {
  Json = "application/json",
  JsonApi = "application/vnd.api+json",
  FormData = "multipart/form-data",
  UrlEncoded = "application/x-www-form-urlencoded",
  Text = "text/plain",
}

export class HttpClient<SecurityDataType = unknown> {
  public baseUrl: string = "https://traewelling.de/api/v1";
  private securityData: SecurityDataType | null = null;
  private securityWorker?: ApiConfig<SecurityDataType>["securityWorker"];
  private abortControllers = new Map<CancelToken, AbortController>();
  private customFetch = (...fetchParams: Parameters<typeof fetch>) =>
    fetch(...fetchParams);

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
    const keys = Object.keys(query).filter(
      (key) => "undefined" !== typeof query[key],
    );
    return keys
      .map((key) =>
        Array.isArray(query[key])
          ? this.addArrayQueryParam(query, key)
          : this.addQueryParam(query, key),
      )
      .join("&");
  }

  protected addQueryParams(rawQuery?: QueryParamsType): string {
    const queryString = this.toQueryString(rawQuery);
    return queryString ? `?${queryString}` : "";
  }

  private contentFormatters: Record<ContentType, (input: any) => any> = {
    [ContentType.Json]: (input: any) =>
      input !== null && (typeof input === "object" || typeof input === "string")
        ? JSON.stringify(input)
        : input,
    [ContentType.JsonApi]: (input: any) =>
      input !== null && (typeof input === "object" || typeof input === "string")
        ? JSON.stringify(input)
        : input,
    [ContentType.Text]: (input: any) =>
      input !== null && typeof input !== "string"
        ? JSON.stringify(input)
        : input,
    [ContentType.FormData]: (input: any) => {
      if (input instanceof FormData) {
        return input;
      }

      return Object.keys(input || {}).reduce((formData, key) => {
        const property = input[key];
        formData.append(
          key,
          property instanceof Blob
            ? property
            : typeof property === "object" && property !== null
              ? JSON.stringify(property)
              : `${property}`,
        );
        return formData;
      }, new FormData());
    },
    [ContentType.UrlEncoded]: (input: any) => this.toQueryString(input),
  };

  protected mergeRequestParams(
    params1: RequestParams,
    params2?: RequestParams,
  ): RequestParams {
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

  protected createAbortSignal = (
    cancelToken: CancelToken,
  ): AbortSignal | undefined => {
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

    return this.customFetch(
      `${baseUrl || this.baseUrl || ""}${path}${queryString ? `?${queryString}` : ""}`,
      {
        ...requestParams,
        headers: {
          ...(requestParams.headers || {}),
          ...(type && type !== ContentType.FormData
            ? { "Content-Type": type }
            : {}),
        },
        signal:
          (cancelToken
            ? this.createAbortSignal(cancelToken)
            : requestParams.signal) || null,
        body:
          typeof body === "undefined" || body === null
            ? null
            : payloadFormatter(body),
      },
    ).then(async (response) => {
      const r = response as HttpResponse<T, E>;
      r.data = null as unknown as T;
      r.error = null as unknown as E;

      const responseToParse = responseFormat ? response.clone() : response;
      const data = !responseFormat
        ? r
        : await responseToParse[responseFormat]()
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
export class Api<
  SecurityDataType extends unknown,
> extends HttpClient<SecurityDataType> {
  alerts = {
    /**
     * No description
     *
     * @tags Notifications
     * @name GetActiveAlerts
     * @summary Get all active alerts
     * @request GET:/alerts
     */
    getActiveAlerts: (params: RequestParams = {}) =>
      this.request<
        {
          data?: AlertResource[];
        },
        any
      >({
        path: `/alerts`,
        method: "GET",
        format: "json",
        ...params,
      }),
  };
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
     * @description This request issues a new Bearer-Token with a new expiration date while also revoking the old token.
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
  community = {
    /**
     * @description Returns contribution XP, level, and progress information for the authenticated user
     *
     * @tags Community
     * @name GetCommunityProfile
     * @summary Get your contribution profile
     * @request GET:/community/profile
     * @secure
     */
    getCommunityProfile: (params: RequestParams = {}) =>
      this.request<
        {
          /** Community contribution profile data */
          data?: CommunityProfile;
        },
        void
      >({
        path: `/community/profile`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Returns a cursor-paginated list of contribution history entries for the authenticated user
     *
     * @tags Community
     * @name GetCommunityHistory
     * @summary Get your contribution history
     * @request GET:/community/history
     * @secure
     */
    getCommunityHistory: (
      query?: {
        /** Cursor for pagination */
        cursor?: string;
        /**
         * Number of entries per page (min 5, max 50, default 15)
         * @min 5
         * @max 50
         * @default 15
         */
        limit?: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: ContributionHistory[];
        },
        void
      >({
        path: `/community/history`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),
  };
  app = {
    /**
     * @description Retrieves configuration information about the application, including features and supported languages.
     *
     * @tags Configuration Information
     * @name GetConfigurationInfo
     * @summary Get Application Configuration Information
     * @request GET:/app/configuration
     */
    getConfigurationInfo: (params: RequestParams = {}) =>
      this.request<ConfigurationInformation, any>({
        path: `/app/configuration`,
        method: "GET",
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
          /** Pagination links */
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
          data?: UserResource;
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
          data?: UserResource;
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
     * @tags User/Follow, Settings
     * @name GetFollowers
     * @summary List all followers
     * @request GET:/user/self/followers
     * @secure
     */
    getFollowers: (
      query?: {
        /**
         * Page number for pagination
         * @example 1
         */
        page?: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: UserResource[];
          /** Pagination links */
          links?: Links;
          /** Pagination meta data */
          meta?: PaginationMeta;
        },
        void
      >({
        path: `/user/self/followers`,
        method: "GET",
        query: query,
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
     * @request GET:/user/self/follow-requests
     * @secure
     */
    getFollowRequests: (
      query?: {
        /**
         * Page number for pagination
         * @example 1
         */
        page?: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: UserResource[];
          /** Pagination links */
          links?: Links;
          /** Pagination meta data */
          meta?: PaginationMeta;
        },
        any
      >({
        path: `/user/self/follow-requests`,
        method: "GET",
        query: query,
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
     * @request GET:/user/self/followings
     * @secure
     */
    getFollowings: (
      query?: {
        /**
         * Page number for pagination
         * @example 1
         */
        page?: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: UserResource[];
          /** Pagination links */
          links?: Links;
          /** Pagination meta data */
          meta?: PaginationMeta;
        },
        any
      >({
        path: `/user/self/followings`,
        method: "GET",
        query: query,
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
     * @request DELETE:/user/self/followers/{userId}
     * @secure
     */
    removeFollower: (userId?: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/user/self/followers/${userId}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),

    /**
     * No description
     *
     * @tags User/Follow
     * @name AcceptFollowRequest
     * @summary Accept a follow request
     * @request PUT:/user/self/follow-requests/{userId}
     * @secure
     */
    acceptFollowRequest: (userId?: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/user/self/follow-requests/${userId}`,
        method: "PUT",
        secure: true,
        ...params,
      }),

    /**
     * No description
     *
     * @tags User/Follow
     * @name RejectFollowRequest
     * @summary Reject a follow request
     * @request DELETE:/user/self/follow-requests/{userId}
     * @secure
     */
    rejectFollowRequest: (userId?: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/user/self/follow-requests/${userId}`,
        method: "DELETE",
        secure: true,
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
        expiresAt?: string | null;
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
     * @request DELETE:/user/{user}/trusted/{trusted}
     */
    trustedUserDestroy: (
      user: string,
      trusted: number,
      params: RequestParams = {},
    ) =>
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
          /** Pagination links */
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
          data?: UserResource;
        },
        void | {
          /** @example "User not accessible." */
          message: string;
          reason?: ViewUserForbiddenReason;
          /** User model */
          user?: UserResource;
        }
      >({
        path: `/user/${username}`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Block a specific user. That user will not be able to see your statuses or profile information, and cannot send you follow requests. Public statuses are still visible through the incognito mode.
     *
     * @tags User/Hide and Block
     * @name CreateBlock
     * @summary Block a user
     * @request POST:/user/{id}/block
     * @secure
     */
    createBlock: (id?: number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data?: UserResource;
        },
        void
      >({
        path: `/user/${id}/block`,
        method: "POST",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Unblock a specific user. They are now able to see your statuses and profile information again, and send you follow requests.
     *
     * @tags User/Hide and Block
     * @name DestroyBlock
     * @summary Unmute a user
     * @request DELETE:/user/{id}/block
     * @secure
     */
    destroyBlock: (id?: number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data?: UserResource;
        },
        void
      >({
        path: `/user/${id}/block`,
        method: "DELETE",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Mute a specific user. That way they will not be shown on your dashboard and in the active journeys tab
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
          data?: UserResource;
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
     * @description Unmute a specific user. That way they will be shown on your dashboard and in the active journeys tab again
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
          data?: UserResource;
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
     * @description Returns paginated search results for a user based on the given query.
     *
     * @tags User
     * @name SearchUsers
     * @summary Get paginated search results for combined search on username and (display)name
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
          data?: UserResource[];
          /** Pagination links */
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

    /**
     * @description Returns paginated search results for users based on the given parameters.
     *
     * @tags User
     * @name SearchUsersByParameters
     * @summary Get paginated search results for users by either username or (display)name
     * @request GET:/user/search
     * @secure
     */
    searchUsersByParameters: (
      query?: {
        /** Page of pagination */
        page?: number;
        /** Search for parts username */
        username?: any;
        /** Search for parts of users (display)name */
        name?: any;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: UserResource[];
          /** Pagination links */
          links?: Links;
          /** Pagination meta data */
          meta?: PaginationMeta;
        },
        void
      >({
        path: `/user/search`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),
  };
  icsTokens = {
    /**
     * @description Get all ICS tokens of the authenticated user
     *
     * @tags ICS Tokens
     * @name GetIcsTokens
     * @summary Get ICS tokens
     * @request GET:/ics-tokens
     * @secure
     */
    getIcsTokens: (params: RequestParams = {}) =>
      this.request<
        {
          /** The list of ICS tokens belonging to the authenticated user */
          data: IcsEntryResource[];
        },
        any
      >({
        path: `/ics-tokens`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Create a new ICS token for the authenticated user
     *
     * @tags ICS Tokens
     * @name CreateIcsToken
     * @summary Create ICS token
     * @request POST:/ics-tokens
     * @secure
     */
    createIcsToken: (
      data: {
        /**
         * The name of the ICS token
         * @example "My ICS Token"
         */
        name: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** The URL to access the ICS feed with the created token */
          data: {
            /**
             * The URL to access the ICS feed with the created token
             * @format uri
             * @example "https://example.com/ics?user_id=1&token=abcd1234&limit=10000&from=2010-01-01&until=2030-12-31"
             */
            url: string;
          };
        },
        void
      >({
        path: `/ics-tokens`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Revoke an ICS token of the authenticated user
     *
     * @tags ICS Tokens
     * @name RevokeIcsToken
     * @summary Revoke ICS token
     * @request DELETE:/ics-tokens/{tokenId}
     * @secure
     */
    revokeIcsToken: (tokenId: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/ics-tokens/${tokenId}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),
  };
  status = {
    /**
     * @description Returns array of users that liked the status. Can return an empty dataset when the status author or the requesting user has deactivated likes
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
          data?: UserResource[];
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
     * @description Returns paginated list of statuses, filtered by given parameters
     *
     * @tags Status
     * @name ListStatuses
     * @summary [Auth optional] List and filter statuses
     * @request GET:/status
     */
    listStatuses: (
      query?: {
        /**
         * Filter by text in status body
         * @example "Having a great trip!"
         */
        body?: string;
        /**
         * Filter by user ID
         * @example 42
         */
        user_id?: number;
        /**
         * Filter by origin station name
         * @example "Central Station"
         */
        origin_text?: string;
        /**
         * Filter by origin station ID
         * @example 5
         */
        origin_id?: number;
        /**
         * Filter by destination station name
         * @example "Main Square"
         */
        destination_text?: string;
        /**
         * Filter by destination station ID
         * @example 10
         */
        destination_id?: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: StatusResource[];
        },
        any
      >({
        path: `/status`,
        method: "GET",
        query: query,
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
    updateSingleStatus: (
      data: StatusUpdateBody,
      id?: number,
      params: RequestParams = {},
    ) =>
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
      this.request<void, void>({
        path: `/status/${id}`,
        method: "DELETE",
        secure: true,
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
          data?: StatusTagResource[];
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
     * @description Creates a single StatusTag Object, if user is authorized to. <br><br>The key of a tag is free text. You can choose it as you need it. However, <b>please use a namespace for tags</b> (<i>namespace:xxx</i>) that only affect your own application.<br><br>For tags related to standard actions we recommend the following tags in the trwl namespace:<br> <ul> <li>trwl:seat (i.e. 61)</li> <li>trwl:wagon (i.e. 25)</li> <li>trwl:ticket (i.e. BahnCard 100 first))</li> <li>trwl:price (420,69 €)</li> <li>trwl:travel_class (i.e. 1, 2, business, economy, ...)</li> <li>trwl:locomotive_class (BR424, BR450)</li> <li>trwl:journey_number (i.e. 1234. Used as a work-around for missing journey numbers)</li> <li>trwl:wagon_class (i.e. Bpmz)</li> <li>trwl:role (i.e. Tf, Zf, Gf, Lokführer, conducteur de train, ...)</li> <li>trwl:vehicle_number (i.e. 425 001, Tz9001, 123, ...)</li> <li>trwl:passenger_rights (i.e. yes / no / ID of claim)</li> <li>trwl:social_status – social availability indicator. Allowed values: <code>open</code> (open to chatting), <code>open_find_me</code> (open, but staying at seat), <code>open_lets_hang</code> (open and willing to move around), <code>do_not_disturb</code> (prefer not to be disturbed).</li> </ul>
     *
     * @tags Status
     * @name CreateSingleStatusTag
     * @summary Create a StatusTag
     * @request POST:/status/{statusId}/tags
     * @secure
     */
    createSingleStatusTag: (
      data: StatusTagResource,
      statusId?: number,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: StatusTagResource;
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
    updateSingleStatusTag: (
      data: StatusTagResource,
      statusId?: number,
      tagKey?: string,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: StatusTagResource;
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
    destroySingleStatusTag: (
      statusId?: number,
      tagKey?: string,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** @example "success" */
          status?: string;
        },
        void
      >({
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
          /** Pagination links */
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
      this.request<
        {
          /** @example "success" */
          status?: string;
        },
        void
      >({
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
  settings = {
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
      data: UpdateProfileInformationRequest,
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
     * @description Update the current user's email address
     *
     * @tags Settings
     * @name UpdateEmail
     * @summary Update the current user's email address
     * @request PUT:/settings/email
     * @secure
     */
    updateEmail: (
      data: {
        /**
         * @format email
         * @example "mail@example.com"
         */
        email?: string;
        /**
         * @format password
         * @example "thisisnotasecurepassword123"
         */
        password?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: UserProfileSettingsResource;
        },
        void
      >({
        path: `/settings/email`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Resend verification email
     *
     * @tags Settings
     * @name ResendVerificationEmail
     * @summary Resend verification email
     * @request POST:/settings/email/verification
     * @secure
     */
    resendVerificationEmail: (params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/settings/email/verification`,
        method: "POST",
        secure: true,
        ...params,
      }),

    /**
     * @description Upload a new profile picture for the current user
     *
     * @tags Settings
     * @name UploadProfilePicture
     * @summary Upload a new profile picture for the current user
     * @request POST:/settings/profile-picture
     * @secure
     */
    uploadProfilePicture: (
      data: {
        /**
         * @format base64
         * @example "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA..."
         */
        image?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** @example "Profile picture updated successfully." */
          message?: string;
        },
        void
      >({
        path: `/settings/profile-picture`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Delete the current user's profile picture
     *
     * @tags Settings
     * @name DeleteProfilePicture
     * @summary Delete the current user's profile picture
     * @request DELETE:/settings/profile-picture
     * @secure
     */
    deleteProfilePicture: (params: RequestParams = {}) =>
      this.request<
        {
          /** @example "Profile picture deleted successfully." */
          message?: string;
        },
        void
      >({
        path: `/settings/profile-picture`,
        method: "DELETE",
        secure: true,
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
         * Username of the to be deleted account (needs to match the currently logged in user)
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
  security = {
    /**
     * @description Get all active sessions for the authenticated user
     *
     * @tags Security
     * @name GetSessions
     * @summary Get active sessions
     * @request GET:/security/sessions
     * @secure
     */
    getSessions: (params: RequestParams = {}) =>
      this.request<
        {
          data?: SessionResource[];
        },
        any
      >({
        path: `/security/sessions`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Delete all active sessions for the authenticated user
     *
     * @tags Security
     * @name DeleteAllSessions
     * @summary Delete all sessions
     * @request DELETE:/security/sessions
     * @secure
     */
    deleteAllSessions: (params: RequestParams = {}) =>
      this.request<void, any>({
        path: `/security/sessions`,
        method: "DELETE",
        secure: true,
        ...params,
      }),

    /**
     * @description Delete a connected social provider from the authenticated user
     *
     * @tags Security
     * @name DeleteSocialProvider
     * @summary Delete social provider
     * @request DELETE:/security/social
     * @secure
     */
    deleteSocialProvider: (
      data: {
        /** The social provider to delete */
        provider: "mastodon";
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/security/social`,
        method: "DELETE",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),

    /**
     * @description Get all active API tokens for the authenticated user
     *
     * @tags Security
     * @name GetTokens
     * @summary Get active API tokens
     * @request GET:/security/tokens
     * @secure
     */
    getTokens: (params: RequestParams = {}) =>
      this.request<
        {
          data: TokenResource[];
        },
        any
      >({
        path: `/security/tokens`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Create a new API token for the authenticated user
     *
     * @tags Security
     * @name CreateToken
     * @summary Create API token
     * @request POST:/security/tokens
     * @secure
     */
    createToken: (params: RequestParams = {}) =>
      this.request<
        {
          data: {
            /**
             * The newly created API token
             * @example "abc123def456"
             */
            token: string;
          };
        },
        any
      >({
        path: `/security/tokens`,
        method: "POST",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Revoke all API tokens for the authenticated user
     *
     * @tags Security
     * @name RevokeAllTokens
     * @summary Revoke all API tokens
     * @request DELETE:/security/tokens
     * @secure
     */
    revokeAllTokens: (params: RequestParams = {}) =>
      this.request<void, any>({
        path: `/security/tokens`,
        method: "DELETE",
        secure: true,
        ...params,
      }),

    /**
     * @description Revoke a specific API token for the authenticated user
     *
     * @tags Security
     * @name RevokeToken
     * @summary Revoke API token
     * @request DELETE:/security/tokens/{tokenId}
     * @secure
     */
    revokeToken: (tokenId: string, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/security/tokens/${tokenId}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),
  };
  stations = {
    /**
     * @description UNSTABLE: Returns stations by fuzzy text, exact identifier, or within a bounding box (BBOX). **CAUTION:** Slashes in {query} must be replaced (e.g. with %20).
     *
     * @tags Checkin
     * @name IndexStation
     * @summary Search for stations
     * @request GET:/stations
     * @secure
     */
    indexStation: (
      query?: {
        /**
         * Fuzzy station search
         * @maxLength 255
         * @example "Karlsruhe Hbf"
         */
        query?: string;
        /**
         * Identifier provider for exact lookup
         * @example "ibnr"
         */
        identifier_provider?: "ibnr" | "transitous";
        /**
         * Station identifier for exact lookup
         * @maxLength 255
         * @example "8000191"
         */
        identifier?: string;
        /**
         * Minimum latitude of BBOX (WGS84, -90..90)
         * @format float
         * @example 48.9
         */
        min_lat?: number;
        /**
         * Maximum latitude of BBOX (WGS84, -90..90)
         * @format float
         * @example 49.1
         */
        max_lat?: number;
        /**
         * Minimum longitude of BBOX (WGS84, -180..180)
         * @format float
         * @example 8.2
         */
        min_lon?: number;
        /**
         * Maximum longitude of BBOX (WGS84, -180..180)
         * @format float
         * @example 8.6
         */
        max_lon?: number;
        /**
         * Maximum number of results (capped at 100).
         * @min 1
         * @max 100
         * @example 50
         */
        limit?: number;
        /** Include station identifiers in the response. */
        withIdentifiers?: boolean;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: StationResource[];
        },
        void
      >({
        path: `/stations`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description This request returns a single station object
     *
     * @tags Checkin
     * @name ShowStation
     * @summary Show station
     * @request GET:/stations/{id}
     * @secure
     */
    showStation: (
      id?: any,
      query?: {
        /** Include station identifiers in the response. */
        withIdentifiers?: boolean;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: StationResource;
        },
        void
      >({
        path: `/stations/${id}`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
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
               * Duration in minutes
               * @example 425
               */
              duration?: number;
            }[];
            /** The categories of the travel */
            categories?: {
              /** Category of transport. */
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
        /** If this parameter is set, the polylines will be returned as well. Otherwise attribute is null. */
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
            /**
             * Nearest earlier date with check-ins (YYYY-MM-DD), or null.
             * @example "2024-04-07"
             */
            prevDate?: string | null;
            /**
             * Nearest later date with check-ins (YYYY-MM-DD), or null.
             * @example "2024-04-11"
             */
            nextDate?: string | null;
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
          data?: StatisticsGlobalData;
          meta?: {
            /** @example "2021-01-01T00:00:00.000000Z" */
            from?: any;
            /** @example "2021-02-01T00:00:00.000000Z" */
            until?: any;
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
          /** Pagination links */
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
     * @description Returns paginated statuses of the authenticated user, that are more than 20 minutes in the future
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
          /** Pagination links */
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
    getTagsForMultipleStatuses: (
      statusIds?: string,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: {
            "1337"?: StatusTagResource[];
            "4711"?: StatusTagResource[];
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
          data?: DepartureResource[];
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
            /** List of licenses that were filtered out */
            removedLicenses?: (string | LicenseDto)[];
            /**
             * Number of removed entries due to license filtering
             * @example 2
             */
            removedCount?: number;
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
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data?: TripResource[];
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
      this.request<
        CheckinSuccessResource,
        void | {
          /** @example "You are not allowed to check in the following users: 1" */
          message?: string;
          meta?: {
            invalidUsers?: number[];
          };
        }
      >({
        path: `/trains/checkin`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description This request returns an array of max. 10 station objects matching the query. **CAUTION:** All slashes (as well as encoded to %2F) in {query} need to be replaced, preferrably by a space (%20)
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
     * @description This request returns an array of max. 10 most recent station objects that the user has arrived at.
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
  trips = {
    /**
     * @description Returns all statuses visible to the (un)authenticated user for a given trip
     *
     * @tags Trips
     * @name GetTripStatuses
     * @summary [Auth optional] Get statuses for a trip
     * @request GET:/trips/{id}/statuses
     * @secure
     */
    getTripStatuses: (id: number, params: RequestParams = {}) =>
      this.request<
        {
          data?: StatusResource[];
        },
        void
      >({
        path: `/trips/${id}/statuses`,
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
     * @summary Get webhooks for current user and current application.
     * @request GET:/webhooks
     * @secure
     */
    getWebhooks: (params: RequestParams = {}) =>
      this.request<
        {
          data: WebhookResource[];
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
          data: WebhookResource;
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
     * @summary Delete a webhook if the user and application is authorized to do
     * @request DELETE:/webhooks/{id}
     * @secure
     */
    deleteWebhook: (id?: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/webhooks/${id}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),
  };
  yearInReview = {
    /**
     * @description Please note: This endpoint is only available when the year in review feature is enabled in the backend configuration. There is no full documentation - this endpoint may change every year.
     *
     * @tags Statistics
     * @name GetYearInReview
     * @summary Returns the year in review for the given year and authenticated user
     * @request GET:/year-in-review
     * @secure
     */
    getYearInReview: (params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/year-in-review`,
        method: "GET",
        secure: true,
        ...params,
      }),
  };
}

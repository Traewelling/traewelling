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
 * StatusTagKey
 * StatusTagKey
 * @example "trwl:social_status"
 */
export enum StatusTagKey {
  TrwlSeat = "trwl:seat",
  TrwlWagon = "trwl:wagon",
  TrwlTicket = "trwl:ticket",
  TrwlTravelClass = "trwl:travel_class",
  TrwlLocomotiveClass = "trwl:locomotive_class",
  TrwlWagonClass = "trwl:wagon_class",
  TrwlRole = "trwl:role",
  TrwlVehicleNumber = "trwl:vehicle_number",
  TrwlPassengerRights = "trwl:passenger_rights",
  TrwlJourneyNumber = "trwl:journey_number",
  TrwlPrice = "trwl:price",
  TrwlSocialStatus = "trwl:social_status",
}

/**
 * StationIdentifierType
 * The type of the station identifier to look up. Not all types are available for every station. Subject to unannounced change.
 *     * motis – all transitous.org/motis supplied identifiers
 *     * wikidata_id – ID of wikidata.org
 *     * de_db_ril100 – Germany: Deutsche Bahn Richtlinie 100 identifier (e.g. RK for Karlsruhe Hbf)
 *     * de_db_ibnr – Germany: internal train station ID of Deutsche Bahn (e.g. 8000191 for Karlsruhe Hbf)
 *
 * @example 0
 */
export enum StationIdentifierType {
  MOTIS = "MOTIS",
  WIKIDATA_ID = "WIKIDATA_ID",
  IFOPT = "IFOPT",
  DE_DB_RIL100 = "DE_DB_RIL100",
  DE_DB_IBNR = "DE_DB_IBNR",
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
  FreightTrain = "freightTrain",
}

/**
 * ExportableFileType
 * The file type to export the data in. The available columns depend on the file type.
 */
export enum ExportableFileType {
  Pdf = "pdf",
  CsvHuman = "csv_human",
  CsvMachine = "csv_machine",
  Json = "json",
}

/**
 * ExportableColumn
 * Columns that can be exported in the export file.
 */
export enum ExportableColumn {
  StatusId = "status_id",
  JourneyType = "journey_type",
  LineName = "line_name",
  JourneyNumber = "journey_number",
  OriginName = "origin_name",
  OriginCoordinates = "origin_coordinates",
  DeparturePlanned = "departure_planned",
  DepartureReal = "departure_real",
  DestinationName = "destination_name",
  DestinationCoordinates = "destination_coordinates",
  ArrivalPlanned = "arrival_planned",
  ArrivalReal = "arrival_real",
  Duration = "duration",
  Distance = "distance",
  Points = "points",
  Body = "body",
  TravelType = "travel_type",
  StatusTags = "status_tags",
  Operator = "operator",
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
  /** Cooldown time in days between gdpr exports */
  gdprExportCooldown: number;
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
  type: number;
  /** @example "{}" */
  properties: object;
  geometry: {
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
  type: string;
  features: Coordinate[];
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
  point: Coordinate | null;
  /**
   * polyline
   * geojson point collection of the next line segment
   */
  polyline: FeatureCollection | null;
  /**
   * arrival
   * arrival at end of polyline in UNIX time format
   * @format integer
   * @example 1692538680
   */
  arrival: number;
  /**
   * departure
   * departure at start of polyline in UNIX time format
   * @format integer
   * @example 1692538740
   */
  departure: number;
  /**
   * lineName
   * name of line
   * @format string
   * @example "ICE 123"
   */
  lineName: string;
  /**
   * statusId
   * ID of status
   * @deprecated
   * @format int
   * @example 12345
   */
  statusId: number;
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

/** StationUsageDto */
export interface StationUsageDto {
  /** @example 12 */
  stopovers: number;
  /**
   * Trips with this station as origin or destination
   * @example 3
   */
  trips: number;
  /** @example 0 */
  events: number;
  /** @example 0 */
  eventSuggestions: number;
  /** @example 2 */
  identifiers: number;
  /**
   * Route segments starting or ending at this station
   * @example 4
   */
  routeSegments: number;
  /**
   * Users with this station as home station
   * @example 1
   */
  homeUsers: number;
}

/** StationUsageMoveResultDto */
export interface StationUsageMoveResultDto {
  /**
   * Number of moved stopovers, including duplicates that were merged into existing stopovers on the target station
   * @example 12
   */
  stopovers: number;
  /**
   * Number of moved trip origin/destination references
   * @example 3
   */
  trips: number;
  /** @example 0 */
  events: number;
  /** @example 0 */
  eventSuggestions: number;
  /**
   * Number of moved route segment sides. Only sides without an identifier binding are moved, identifier-bound sides follow their identifier
   * @example 4
   */
  routeSegments: number;
  /**
   * Number of users whose home station was moved
   * @example 1
   */
  homeUsers: number;
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
  id: number;
  /**
   * name
   * name of the station
   * @example "Karlsruhe Hbf"
   */
  name: string;
  /**
   * latitude
   * latitude of the station
   * @format float
   * @example "48.991591"
   */
  latitude: number;
  /**
   * longitude
   * longitude of the station
   * @format float
   * @example "8.400538"
   */
  longitude: number;
  /**
   * ibnr
   * IBNR of the station
   * @example "8000191"
   */
  ibnr: number | null;
  /**
   * rilIdentifier
   * Identifier specified in 'Richtline 100' of the Deutsche Bahn
   * @example "RK"
   */
  rilIdentifier: string | null;
  /**
   * identifiers
   * List of external station identifiers (IBNR, RIL100, IFOPT, Wikidata, MOTIS). Null when not loaded.
   */
  identifiers: StationIdentifierResource[] | null;
}

/** WebhookDayStatsDto */
export interface WebhookDayStatsDto {
  /**
   * @format date
   * @example "2026-04-01"
   */
  date: string;
  /** @example 20 */
  total: number;
  /** @example 15 */
  success: number;
  /** @example 5 */
  failed: number;
}

/** WebhookEventStatsDto */
export interface WebhookEventStatsDto {
  /** @example "checkin_create" */
  event: string;
  /** @example 100 */
  total: number;
}

/** WebhookResponseCodeStatsDto */
export interface WebhookResponseCodeStatsDto {
  /** @example 200 */
  responseCode: number | null;
  /** @example 120 */
  total: number;
}

/** AdminEventRequest */
export interface AdminEventRequest {
  /** @maxLength 255 */
  name: string;
  /** @maxLength 30 */
  hashtag?: string | null;
  /** @maxLength 255 */
  host?: string | null;
  /** @maxLength 255 */
  url?: string | null;
  station_id?: number | null;
  /** @format date */
  checkin_start: string;
  /** @format date */
  checkin_end: string;
  /** @format date */
  event_start?: string | null;
  /** @format date */
  event_end?: string | null;
}

/** BearerTokenResponse */
export interface BearerTokenResponse {
  /**
   * Bearer Token. Use in Authentication-Header with prefix 'Bearer '. (space is needed)
   * @example "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
   */
  token: string;
  /**
   * End of life for this token.
   * @example "2023-10-19T15:15:06+02:00"
   */
  expires_at: string;
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
  name: string;
  /**
   * host of the event
   * @example "MiWuLa"
   */
  host: string | null;
  /**
   * Timestamp for the start of the event
   * @format date-time
   * @example "2022-06-01T00:00:00+02:00"
   */
  begin: string;
  /**
   * Timestamp for the end of the event
   * @format date-time
   * @example "2022-08-31T23:59:00+02:00"
   */
  end: string;
  /**
   * external URL for this event
   * @maxLength 255
   * @example "https://www.example.com/event"
   */
  url: string | null;
  /**
   * hashtag for this event
   * @maxLength 40
   * @example "gpn21"
   */
  hashtag: string | null;
  /**
   * Query string for the nearest station. Deprecated: use nearestStationId instead.
   * @deprecated
   * @maxLength 255
   * @example "Berlin Hbf"
   */
  nearestStation: string | null;
  /**
   * ID of the nearest station to this event
   * @example 1
   */
  nearestStationId: number | null;
}

/** LikeResponse */
export interface LikeResponse {
  /**
   * Amount of likes
   * @format int32
   * @example 12
   */
  count: number;
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
 * StatusAssignTicketBody
 * Assign or remove a ticket from a status
 */
export interface StatusAssignTicketBody {
  /**
   * UUID of the ticket to assign, or null to remove the assignment
   * @format uuid
   * @example "00000000-0000-0000-0000-000000000000"
   */
  ticketId: string | null;
}

/**
 * Polyline
 * Polyline of a single status as GeoJSON Feature
 */
export interface Polyline {
  /** @example "Feature" */
  type: string;
  geometry: {
    /** @example "LineString" */
    type?: string;
    coordinates?: any[];
  };
  properties: {
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
   * @deprecated
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
   * Träwelling-Station-ID of the starting point, required without startIdentifier
   * @example 8000191
   */
  start?: number;
  /**
   * (EXPERIMENTAL: this is not guaranteed to work. It might lead to inconsistent behaviour) External station identifier of the starting point, required without startIdentifier, requires startIdentifierType
   * @example "de-0815-1234:56:78"
   */
  startIdentifier?: string;
  /**
   * The type of the station identifier to look up. Not all types are available for every station. Subject to unannounced change.
   *     * motis – all transitous.org/motis supplied identifiers
   *     * wikidata_id – ID of wikidata.org
   *     * de_db_ril100 – Germany: Deutsche Bahn Richtlinie 100 identifier (e.g. RK for Karlsruhe Hbf)
   *     * de_db_ibnr – Germany: internal train station ID of Deutsche Bahn (e.g. 8000191 for Karlsruhe Hbf)
   *
   */
  startIdentifierType?: StationIdentifierType;
  /**
   * Träwelling-Station-ID of the destination, required without destinationIdentifier
   * @example 8000192
   */
  destination?: number;
  /**
   * (EXPERIMENTAL: this is not guaranteed to work. It might lead to inconsistent behaviour) External station identifier of the destination, required without destinationIdentifier, requires destinationIdentifierType
   * @example "de-0815-1234:56:78"
   */
  destinationIdentifier?: string;
  /**
   * The type of the station identifier to look up. Not all types are available for every station. Subject to unannounced change.
   *     * motis – all transitous.org/motis supplied identifiers
   *     * wikidata_id – ID of wikidata.org
   *     * de_db_ril100 – Germany: Deutsche Bahn Richtlinie 100 identifier (e.g. RK for Karlsruhe Hbf)
   *     * de_db_ibnr – Germany: internal train station ID of Deutsche Bahn (e.g. 8000191 for Karlsruhe Hbf)
   *
   */
  destinationIdentifierType?: StationIdentifierType;
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

/** StoreOAuthClientRequest */
export interface StoreOAuthClientRequest {
  /** @example "My App" */
  name: string;
  /** @example "https://example.com/callback" */
  redirect: string;
  /** @example true */
  confidential?: boolean;
  /** @example false */
  webhooksEnabled?: boolean;
  /** @example "https://example.com/webhook" */
  authorizedWebhookUrl?: string | null;
  /** @example "https://example.com/privacy" */
  privacyPolicyUrl?: string | null;
}

/**
 * UpdateProfileInformationRequest
 * All fields are optional. Only send what you want to change.
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

/** ActivityLog */
export interface ActivityLogResource {
  id: number;
  causer: {
    id: number;
    name: string;
    username: string;
  } | null;
  description: string;
  subjectType: string | null;
  subjectFullType: string | null;
  subjectId: number | null;
  changes: {
    old: Record<string, any>;
    attributes: Record<string, any>;
  };
  /** @format date-time */
  createdAt: string;
}

/** AdminStatusResource */
export interface AdminStatusResource {
  /** @example 12345 */
  id: number;
  body: string | null;
  /** @example 0 */
  visibility: number;
  /** @example 0 */
  business: number;
  moderationNotes: string | null;
  lockVisibility: boolean;
  hideBody: boolean;
  eventId: number | null;
  /** User model with just basic information */
  user: LightUserResource;
  checkin: TransportResource | null;
  stopovers: StopoverResource[];
  /** @format date-time */
  createdAt: string;
  /** @format date-time */
  updatedAt: string;
  client: ClientResource | null;
  createdBy: LightUserResource | null;
}

/** AdminStopover */
export interface AdminStopoverResource {
  id: number;
  station: {
    id?: number;
    name?: string;
  };
  /** @format date-time */
  arrivalPlanned: string | null;
  /** @format date-time */
  arrivalReal: string | null;
  /** @format date-time */
  departurePlanned: string | null;
  /** @format date-time */
  departureReal: string | null;
  /** @format uuid */
  routeSegmentId: string | null;
  routeSegmentType: "identifier" | "station" | null;
  /** @format uuid */
  stationIdentifierId: string | null;
}

/** AdminTrip */
export interface AdminTripResource {
  id: number;
  tripId: string;
  checkinsCount: number | null;
  category: string;
  mode: string | null;
  number: string | null;
  lineName: string | null;
  routeColor: string | null;
  journeyNumber: number | null;
  operator: string | null;
  source: string | null;
  user: LightUserResource | null;
  /** @format date-time */
  lastRefreshed: string | null;
  origin: {
    id?: number;
    name?: string;
  } | null;
  destination: {
    id?: number;
    name?: string;
  } | null;
  stopovers: AdminStopoverResource[];
  statuses: AdminStatusResource[];
}

/** AdminUserListItem */
export interface AdminUserListResource {
  id: number;
  username: string;
  displayName: string;
  email: string | null;
  /** @format date-time */
  emailVerifiedAt: string | null;
  mastodonUrl: string | null;
  /** @format date-time */
  lastLogin: string | null;
  /** @format date-time */
  createdAt: string;
}

/** AdminUserResource */
export interface AdminUserResource {
  id: number;
  username: string;
  displayName: string;
  email: string | null;
  /** @format date-time */
  emailVerifiedAt: string | null;
  hasPassword: boolean;
  mastodonUrl: string | null;
  /** @format date-time */
  lastLogin: string | null;
  /** @format date-time */
  createdAt: string;
  /** Total distance in metres */
  trainDistance: number;
  /** Total duration in minutes */
  trainDuration: number;
  points: number;
  roles: string[];
  allRoles: {
    name: string;
    permissions: string[];
  }[];
  mailChanges: {
    id: string;
    oldEmail: string;
    newEmail: string;
    /** @format date-time */
    createdAt: string | null;
  }[];
  /** @format date-time */
  privacyPolicyCurrent: string | null;
  /** @format date-time */
  privacyPolicyFuture: string | null;
  privacyPolicyFutureExists: boolean;
  recentStatuses: AdminStatusResource[];
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

export interface ChangelogChangeResource {
  /**
   * The emoji representing the type of change. See gitmoji.com for reference.
   * @example "🐛"
   */
  emoji: any;
  /**
   * A short description of the change.
   * @example "Added new feature X"
   */
  info: any;
  /**
   * The full markdown line from the changelog, including the emoji and any additional
   * @example "* :bug: Fixed this and that by @username"
   */
  fullText: any;
}

export interface ChangelogResource {
  /**
   * The title of the release
   * @example "2026-04-01"
   */
  title: string;
  /** The markdown description of the release */
  description: string;
  /** The tag name of the release */
  tagName: string;
  /**
   * The release date of the release
   * @format date-time
   */
  createdAt: string;
  /** The changes of the release */
  changes: ChangelogChangeResource[];
}

/** PointsCalculation */
export interface PointsCalculation {
  /**
   * Basepoints for this type of vehicle
   * @format float
   * @example 0.5
   */
  base: number;
  /**
   * Points for the travelled distance
   * @format float
   * @example 0.25
   */
  distance: number;
  /**
   * @format float
   * @example 0.25
   */
  factor: number;
  /** What is the reason for the points calculation factor? (0=in time => 100%, 1=good enough => 25%, 2=not sufficient (1 point), 3=forced => no points, 4=manual trip => no points, 5=points disabled) */
  reason: PointReason;
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
  points: number;
  calculation: PointsCalculation;
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  additional: any[] | null;
}

/** CheckinResponse */
export interface CheckinSuccessResource {
  status: StatusResource;
  /** Points model */
  points: Points;
  /** Statuses of other people on this connection */
  alsoOnThisConnection: StatusResource[];
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
  note: string | null;
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
  attribution: string | null;
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
  /**
   * The stop at which this departure occurs
   * @deprecated
   */
  stop: {
    /**
     * @deprecated
     * @example "stop"
     */
    type?: string;
    /**
     * Träwelling internal station ID
     * @deprecated
     * @example 5181
     */
    id?: number;
    /**
     * @deprecated
     * @example "Karlsruhe Hbf"
     */
    name?: string;
    /** @deprecated */
    location?: {
      /**
       * @deprecated
       * @example "location"
       */
      type?: string;
      /**
       * IBNR identifier (if available)
       * @deprecated
       * @example "8000191"
       */
      id?: string | null;
      /**
       * @deprecated
       * @format float
       * @example 48.993207
       */
      latitude?: number;
      /**
       * @deprecated
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
   * Deprecated. Use the difference between when and plannedWhen instead.
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
  provenance: string | null;
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
     * Product category
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
  remarks: any[] | null;
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  origin: object | null;
  /**
   * Deprecated. Use direction instead.
   * @deprecated
   */
  destination: object;
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  currentTripPosition: object | null;
  /**
   * Deprecated. Always null.
   * @deprecated
   */
  loadFactor: string | null;
  /**
   * Whether this departure is cancelled
   * @example false
   */
  cancelled: boolean;
  station: StationResource;
}

/**
 * EventAdminResource
 * Full event data for admin management
 */
export interface EventAdminResource {
  /** @example 1 */
  id: number;
  /** @example "Berlin Bahnhofsfest" */
  name: string;
  /** @example "berlin_bahnhofsfest" */
  slug: string;
  /** @example "BahnhofsFest" */
  hashtag: string | null;
  /** @example "DB AG" */
  host: string | null;
  /** @example "https://example.com" */
  url: string | null;
  /**
   * @format date
   * @example "2025-06-01"
   */
  checkin_start: string;
  /**
   * @format date
   * @example "2025-06-30"
   */
  checkin_end: string;
  /** @format date */
  event_start: string | null;
  /** @format date */
  event_end: string | null;
  status: "future" | "current" | "past";
  station: Station | null;
}

/** EventDetails */
export interface EventDetailsResource {
  /** @example 39 */
  id: number;
  /** @example "9_euro_ticket" */
  slug: string;
  /**
   * distance travelled in meters
   * @example 12345
   */
  totalDistance: number;
  /**
   * duration travelled in minutes
   * @example 12345
   */
  totalDuration: number;
  /**
   * Deprecated. Use totalDistance instead.
   * @deprecated
   * @example 12345
   */
  trainDistance: number;
  /**
   * Deprecated. Use totalDuration instead.
   * @deprecated
   * @example 12345
   */
  trainDuration: number;
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
  hashtag: string | null;
  /** @example "9-Euro-Ticket GmbH" */
  host: string | null;
  /** @example "https://9-euro-ticket.de" */
  url: string | null;
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
  /** @example true */
  isPride: boolean;
  station: StationResource | null;
  /** @example 12345 */
  totalDistance: number;
  /** @example 12345 */
  totalDuration: number;
}

/**
 * EventSuggestionResource
 * Event suggestion submitted by a user
 */
export interface EventSuggestionResource {
  /** @example 1 */
  id: number;
  /** @example "Berliner Fahrradfest" */
  name: string;
  host: string | null;
  url: string | null;
  hashtag: string | null;
  /**
   * @format date
   * @example "2025-07-01"
   */
  begin: string;
  /**
   * @format date
   * @example "2025-07-03"
   */
  end: string;
  station: {
    id?: number;
    name?: string;
  } | null;
  user: {
    id?: number;
    username?: string;
  } | null;
  processed: boolean;
  /** @format date-time */
  created_at: string;
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
  createdAt: string | null;
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
  /**
   * UUID
   * @format uuid
   * @example "00000000-0000-0000-0000-000000000000"
   */
  uuid: string;
  /** @example "Gertrud" */
  displayName: string;
  /** @example "Gertrud123" */
  username: string;
  /** @example "https://traewelling.de/@Gertrud123/picture" */
  profilePicture: string;
  /** @example {"server":"mastodon.social","user_id":1234567} */
  mastodon: object;
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
  first: string | null;
  /**
   * @format uri
   * @example null
   */
  last: string | null;
  /**
   * @format uri
   * @example null
   */
  prev: string | null;
  /**
   * @format uri
   * @example "https://traewelling.de/api/v1/ENDPOINT?page=2"
   */
  next: string | null;
}

/**
 * MotisSourceLicense
 * A transit data source used by this instance, with its license information.
 */
export interface MotisSourceLicenseResource {
  /** @example "de-DELFI" */
  name: string | null;
  /** @example "DELFI e.V." */
  humanName: string | null;
  /** @example "de" */
  country: string | null;
  sourceUrl: string | null;
  /** @example "CC-BY-4.0" */
  spdx: string | null;
  licenseUrl: string | null;
  attributionText: string | null;
  active: boolean;
  forceActive: boolean;
  manualLicense: {
    humanName?: string | null;
    licenseUrl?: string | null;
  } | null;
}

/**
 * OAuthClientResource
 * OAuth application owned by the authenticated user
 */
export interface OAuthClientResource {
  /** @example 42 */
  id: number;
  /** @example "My App" */
  name: string;
  /** @example "https://example.com/callback" */
  redirect: string;
  /** @example true */
  confidential: boolean;
  /** @example false */
  webhooksEnabled: boolean;
  /** @example "https://example.com/webhook" */
  authorizedWebhookUrl: string | null;
  /** @example "https://example.com/privacy" */
  privacyPolicyUrl: string | null;
  /** @example 3 */
  activeTokensCount: number;
  /** @example true */
  hasWebhooks: boolean;
  /**
   * Only present immediately after creation or secret regeneration
   * @example "abc123"
   */
  plainSecret: string | null;
  /**
   * @format date-time
   * @example "2026-01-01T00:00:00Z"
   */
  createdAt: string;
}

export interface OperatorIdentifierResource {
  /** @example "motis" */
  type: string;
  /** @example "de:db-regio-ag" */
  identifier: string;
  /** @example "DB Regio AG" */
  name: string | null;
}

export interface OperatorResource {
  /** @example "operator" */
  type: string;
  /**
   * Numeric legacy ID. Deprecated: will become a UUID after 2026-09-30.
   * @deprecated
   * @example 1
   */
  id: number;
  /**
   * Stable UUID identifier for this operator.
   * @format uuid
   * @example "00000000-0000-0000-0000-000000000000"
   */
  uuid: string;
  /**
   * Legacy HAFAS operator ID. Always NULL for new operators. Will be removed soon.
   * @deprecated
   * @example "db-regio-ag-nord"
   */
  identifier: string | null;
  /** @example "DB Regio AG Nord" */
  name: string;
  identifiers: OperatorIdentifierResource[];
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
  current_page: number;
  /** @example 16 */
  from: number;
  /**
   * @format url
   * @example "https://traewelling.de/api/v1/ENDPOINT"
   */
  path: string;
  /** @example 15 */
  per_page: number;
  /** @example 30 */
  to: number;
}

export interface PrivacyPolicy {
  /**
   * UUID of the privacy policy
   * @format uuid
   * @example "00000000-0000-0000-0000-000000000000"
   */
  id: string;
  /**
   * Date and time from which this privacy policy is valid
   * @format date-time
   * @example "2022-01-05T16:26:14.000000Z"
   */
  validFrom: string;
  /**
   * Privacy policy text in English (Markdown)
   * @example "This is the english privacy policy"
   */
  en: string;
  /**
   * Privacy policy text in German (Markdown)
   * @example "Dies ist die deutsche Datenschutzerklärung"
   */
  de: string;
  /**
   * When the current user accepted this privacy policy. Null if not yet accepted.
   * @format date-time
   * @example "2022-01-05T16:26:14.000000Z"
   */
  acceptedAt: string | null;
  /**
   * True if the user has accepted a previous (now outdated) version of the privacy policy.
   * @example false
   */
  hasOldAcceptance: boolean;
  /** Next privacy policy that is not yet in effect, if any. */
  upcoming: {
    /**
     * @format uuid
     * @example "00000000-0000-0000-0000-000000000000"
     */
    id?: string;
    /**
     * @format date-time
     * @example "2022-01-05T16:26:14.000000Z"
     */
    validFrom?: string;
    /** @example "This is the english privacy policy" */
    en?: string;
    /** @example "Dies ist die deutsche Datenschutzerklärung" */
    de?: string;
    /**
     * @format date-time
     * @example null
     */
    acceptedAt?: string | null;
  } | null;
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

export interface ReportResource {
  /**
   * @format uuid
   * @example "123e4567-e89b-12d3-a456-426614174000"
   */
  id: string;
  /** @example "open" */
  status: "open" | "waiting" | "closed";
  /** @example "Status" */
  subject_type: string;
  /** @example 1 */
  subject_id: number;
  reason: "inappropriate" | "implausible" | "spam" | "illegal" | "other" | null;
  description: string | null;
  reporter: LightUserResource | null;
  /** @format date-time */
  created_at: string;
  activities:
    | {
        id?: number;
        description?: string;
        causer?: LightUserResource | null;
        properties?: object | null;
        /** @format date-time */
        created_at?: string;
      }[]
    | null;
}

/** RouteSegmentResource */
export interface RouteSegmentResource {
  /**
   * @format uuid
   * @example "01960000-0000-7000-8000-000000000001"
   */
  id: string;
  fromStation: StationResource | null;
  toStation: StationResource | null;
  fromIdentifier: StationIdentifierResource | null;
  toIdentifier: StationIdentifierResource | null;
  /**
   * Distance in meters
   * @example 42300
   */
  distance: number | null;
  /**
   * Duration in seconds
   * @example 5400
   */
  duration: number | null;
  /** @example "rails" */
  pathType: string | null;
  /**
   * Google Encoded Polyline
   * @example "_p~iF~ps|U_ulLnnqC_mqNvxq`@"
   */
  polyline: string;
  /** @example 5 */
  polylinePrecision: number;
  /**
   * Number of custom waypoints, or null if none set
   * @example 4
   */
  customWaypointsCount: number | null;
  /** Custom waypoint coordinates used as BRouter input, or null if not set. */
  customWaypoints:
    | {
        lat?: number;
        lng?: number;
      }[]
    | null;
  /**
   * Number of trips using this segment.
   * @example 12
   */
  tripsCount: number | null;
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
  /**
   * @format uuid
   * @example "550e8400-e29b-41d4-a716-446655440000"
   */
  id: string;
  /** @example "de_db_ril100" */
  type: string;
  /** @example "RK" */
  identifier: string;
  /** @example "Karlsruhe Hbf" */
  name: string | null;
  /** @example "db" */
  origin: string | null;
  /**
   * @format float
   * @example 48.993207
   */
  latitude: number | null;
  /**
   * @format float
   * @example 8.400977
   */
  longitude: number | null;
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
  identifiers: StationIdentifierResource[];
  /** @example "60" */
  time_offset: number | null;
  /** @format date-time */
  created_at: string | null;
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
  client: ClientResource | null;
  checkin: TransportResource;
  event: EventResource | null;
  /** User model with just basic information */
  user: LightUserResource;
  /** User who created this check-in on behalf of the status owner (null if self-checkin) */
  createdBy: LightUserResource | null;
  tags: StatusTagResource[];
  /** The ticket assigned to this status. Only present for the status owner. */
  ticket: TicketResource | null;
  /** A note left by the moderation team, e.g. a warning or hint explaining why this status was moderated. Only present for the status owner. */
  moderation_notes: string | null;
  /** Whether the visibility is locked by an admin and cannot be changed by the owner. Only present for the status owner. */
  lock_visibility: boolean | null;
  /** Whether the status body is hidden from other users by an admin. Only present for the status owner. */
  hide_body: boolean | null;
  /**
   * creation date of this status
   * @format datetime
   * @example "2022-07-17T13:37:00+02:00"
   */
  createdAt: string;
}

/** StatusTagResource */
export interface StatusTagResource {
  /** regex:/^\w[^\/\n\r%?\<>]*$/ */
  key: StatusTagKey | string;
  /** Values allowed for the tag trwl:social_status */
  value: "open" | "open_find_me" | "open_lets_hang" | "do_not_disturb" | string;
  /** @example "1" */
  visibility: number;
}

/** StatusTagSuggestionResource */
export interface StatusTagSuggestionResource {
  /** @example "trwl:vehicle_number" */
  key: string;
  /** @example "94 80 0450 921 D-AVG" */
  value: string;
}

/** StopoverResource */
export interface StopoverResource {
  /**
   * Deprecated as station ID. Currently holds the station ID, which is not unique within a trip. Use station for station details. After 2026-11-30 this field will be repurposed to hold the unique stopover ID.
   * @deprecated
   * @example 12345
   */
  id: number;
  /**
   * Deprecated. Temporary field holding the unique ID of this specific stopover within the trip. Only available until id is repurposed to the stopover ID (after 2026-11-30), then removed.
   * @deprecated
   * @example 987654
   */
  stopoverId: number;
  station: StationResource;
  /**
   * Deprecated. Name of the station. Use station.name instead.
   * @deprecated
   * @example "Karlsruhe Hbf"
   */
  name: string;
  /**
   * Deprecated. Only present with withIdentifiers=true. Use station.identifiers instead.
   * @deprecated
   */
  identifiers: StationIdentifierResource[];
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
   * Deprecated. Use arrivalReal (if not null) or arrivalPlanned instead.
   * @deprecated
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
   * Deprecated. Use departureReal (if not null) or departurePlanned instead.
   * @deprecated
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

/**
 * TicketResource
 * A transit ticket / Fahrkarte
 */
export interface TicketResource {
  /**
   * UUID of the ticket
   * @format uuid
   * @example "00000000-0000-0000-0000-000000000000"
   */
  id: string;
  /**
   * User-defined name of the ticket
   * @example "My BahnCard 100"
   */
  name: string;
  /**
   * Start of validity period (ISO 8601 date)
   * @format date
   * @example "2026-01-01"
   */
  validFrom: string | null;
  /**
   * End of validity period (ISO 8601 date)
   * @format date
   * @example "2026-12-31"
   */
  validUntil: string | null;
  /**
   * Price of the ticket
   * @format float
   * @example 3199
   */
  price: number | null;
  /**
   * Currency of the price (free-form, e.g. EUR, CHF)
   * @example "EUR"
   */
  currency: string | null;
  /**
   * ISO 8601 timestamp of creation
   * @format date-time
   * @example "2026-03-01T00:00:00Z"
   */
  createdAt: string;
  /**
   * Number of trips assigned to this ticket
   * @example 42
   */
  tripCount: number;
  /**
   * Total distance of all trips assigned to this ticket in meters
   * @example 12340
   */
  totalDistance: number;
  /**
   * Total duration of all trips assigned to this ticket in minutes
   * @example 1020
   */
  totalDuration: number;
}

/**
 * TicketStatisticsResource
 * Usage statistics for a single ticket
 */
export interface TicketStatisticsResource {
  /**
   * Total number of trips assigned to this ticket
   * @example 42
   */
  tripCount: number;
  /**
   * Total distance of all assigned trips in meters
   * @example 123400
   */
  distance: number;
  /**
   * Total duration of all assigned trips in minutes
   * @example 1020
   */
  duration: number;
  /**
   * Date of the first trip using this ticket (YYYY-MM-DD)
   * @format date
   * @example "2026-01-03"
   */
  firstUsed: string | null;
  /**
   * Date of the most recent trip using this ticket (YYYY-MM-DD)
   * @format date
   * @example "2026-03-14"
   */
  lastUsed: string | null;
  /**
   * Ticket price divided by number of trips. Null if no price set.
   * @format float
   * @example 76.17
   */
  costPerTrip: number | null;
  /**
   * Ticket price per kilometer. Null if no price set or total distance is zero.
   * @format float
   * @example 0.26
   */
  costPerKm: number | null;
  /**
   * Ticket price per hour of travel. Null if no price set or total duration is zero.
   * @format float
   * @example 4.48
   */
  costPerHour: number | null;
  /** Trip counts and distances grouped by travel purpose */
  purposes: {
    /**
     * Business value (0=private, 1=business, 2=commute)
     * @example "2"
     */
    reason?: string | null;
    /** @example 30 */
    count?: number;
    /**
     * Total distance for this purpose in meters
     * @example 9000
     */
    distance?: number;
  }[];
  /** Trip counts and distances grouped by transport category */
  categories: {
    /**
     * Transport category (e.g. nationalExpress, tram, bus)
     * @example "nationalExpress"
     */
    name?: string | null;
    /** @example 28 */
    count?: number;
    /**
     * Total distance for this category in meters
     * @example 102000
     */
    distance?: number;
  }[];
  /** Distance grouped by operator, top 10 by distance */
  operators: {
    /**
     * Operator name
     * @example "DB Fernverkehr"
     */
    name?: string | null;
    /** @example 28 */
    count?: number;
    /**
     * Total distance for this operator in meters
     * @example 102000
     */
    distance?: number;
  }[];
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
  routeColor: string | null;
  /**
   * Hex color code of the route text, if available
   * @example "FFFFFF"
   */
  routeTextColor: string | null;
  /** @example 85639 */
  journeyNumber: number;
  /**
   * Manual journey number, if set by the user. This is intended for use cases like ICE lines in germany that have line number but are more widely known by their train number
   * @example "ICE 4"
   */
  manualJourneyNumber: string | null;
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
  operator: OperatorResource | null;
  dataSource: DataSourceResource | null;
}

/** TripResource */
export interface TripResource {
  /** @example 1 */
  id: number;
  /**
   * Internal trip identifier (use this for the checkin flow)
   * @example "00000000-0000-0000-0000-000000000000"
   */
  tripId: string;
  /** Category of transport. */
  category: HafasTravelType;
  mode: MotisCategory | null;
  /** @example "4-a6s4-4" */
  number: string;
  /** @example "S 4" */
  lineName: string;
  /** @example "34427" */
  journeyNumber: number;
  /** train station model */
  origin: Station;
  /** train station model */
  destination: Station;
  stopovers: StopoverResource[];
  dataSource: DataSourceResource | null;
  /** If this trip is an interlined through-running service, this contains the immediately following trip (different line name/color, no transfer required). */
  continuationTrip: TripResource | null;
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
  /**
   * UUID
   * @format uuid
   * @example "00000000-0000-0000-0000-000000000000"
   */
  uuid: string;
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
  pointsEnabled: boolean;
  /** @example "default" */
  mapProvider: string;
  home: StationResource;
  /** @example "de" */
  language: string;
  /** @example 0 */
  defaultStatusVisibility: number;
  /** @example ["admin","open-beta","closed-beta"] */
  roles: string[];
  /**
   * @format date-time
   * @example "2024-01-01T00:00:00Z"
   */
  recentGdprExport: string | null;
}

/**
 * Notification
 * Notification model
 */
export interface Notification {
  /** @example "bb1ba9a5-9c2b-43c3-b8c9-2f70651fc51c" */
  id: string;
  /** @example "UserJoinedConnection" */
  type: string;
  /** @example "<b>@bob</b> is in your connection!" */
  leadFormatted: string;
  /** @example "@bob is in your connection!" */
  lead: string;
  /** @example "@bob is on <b>S 81</b> from <b>Karlsruhe Hbf</b> to <b>Freudenstadt Hbf</b>." */
  noticeFormatted: string;
  /** @example "@bob is on S 81 from Karlsruhe Hbf to Freudenstadt Hbf." */
  notice: string;
  /** @example "https://traewelling.de/status/123456" */
  link: string;
  data: any[];
  /** @example "2023-01-01T00:00:00+00:00" */
  readAt: string | null;
  /** @example "2023-01-01T00:00:00+00:00" */
  createdAt: string;
  /** @example "2 days ago" */
  createdAtForHumans: string;
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
  privacyHideDays: number | null;
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
   * UUID
   * @format uuid
   * @example "00000000-0000-0000-0000-000000000000"
   */
  uuid: string;
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
   * distance travelled in meters
   * @example 12345
   */
  totalDistance: number;
  /**
   * duration travelled in minutes
   * @example 6
   */
  totalDuration: number;
  /**
   * Current points of the last 7 days
   * @example 300
   */
  points: number;
  /**
   * URL to the Mastodon profile of the user
   * @example "https://chaos.social/@traewelling"
   */
  mastodonUrl: any | null;
  /**
   * is this profile set to private?
   * @example false
   */
  privateProfile: boolean;
  /**
   * Does this profile allow points? Only offer the UI to show points at any status if this setting is set to true. If set to false, the points will always be displayed as 0
   * @example true
   */
  points_enabled: boolean;
  /**
   * Does this profile allow likes? Only offer the UI to like any status if this setting is set to true. If set to false, the likes API will return 403.
   * @example true
   */
  likes_enabled: boolean;
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
  bio: string | null;
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
   * Deprecated. Use client.id instead.
   * @deprecated
   * @format int
   * @example 12345
   */
  clientId: any;
  /** User model with just basic information */
  user: LightUserResource;
  /**
   * Deprecated. Use user.id instead.
   * @deprecated
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
  /**
   * ISO 8601 timestamp when the webhook was automatically disabled due to repeated failures, or null if active
   * @format date-time
   * @example "2026-04-08T10:00:00Z"
   */
  disabledAt: string | null;
}

/**
 * WebhookStatsResource
 * Webhook call log statistics for an OAuth application over the last 7 days
 */
export interface WebhookStatsResource {
  /** @example 42 */
  clientId: number;
  /** @example "My App" */
  clientName: string;
  /** @example 150 */
  total: number;
  byDay: WebhookDayStatsDto[];
  byEvent: WebhookEventStatsDto[];
  byResponseCode: WebhookResponseCodeStatsDto[];
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
  app = {
    /**
     * No description
     *
     * @name GetChangelog
     * @request GET:/app/changelog
     */
    getChangelog: (params: RequestParams = {}) =>
      this.request<
        {
          data: ChangelogResource[];
        },
        any
      >({
        path: `/app/changelog`,
        method: "GET",
        format: "json",
        ...params,
      }),

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
  admin = {
    /**
     * @description Requires "view activity" permission. Returns the last 3 months of activity log entries, excluding system entries.
     *
     * @tags Admin
     * @name GetAdminActivity
     * @summary List activity log
     * @request GET:/admin/activity
     * @secure
     */
    getAdminActivity: (
      query?: {
        cursor?: string;
        /** Full class name to filter by subject type, requires subjectId */
        subjectType?: string;
        /** Subject ID to filter by, requires subjectType */
        subjectId?: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: ActivityLogResource[];
        },
        void
      >({
        path: `/admin/activity`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name GetAdminEvents
     * @summary List events for admin management.
     * @request GET:/admin/events
     * @secure
     */
    getAdminEvents: (
      query?: {
        search?: string;
        status?: "future" | "current" | "past";
        cursor?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: EventAdminResource[];
        },
        void
      >({
        path: `/admin/events`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name CreateAdminEvent
     * @summary Create a new event.
     * @request POST:/admin/events
     * @secure
     */
    createAdminEvent: (data: AdminEventRequest, params: RequestParams = {}) =>
      this.request<
        {
          /** Full event data for admin management */
          data: EventAdminResource;
        },
        void
      >({
        path: `/admin/events`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name GetAdminEvent
     * @summary Get a single event for editing.
     * @request GET:/admin/events/{id}
     * @secure
     */
    getAdminEvent: (id: number, params: RequestParams = {}) =>
      this.request<
        {
          /** Full event data for admin management */
          data: EventAdminResource;
        },
        void
      >({
        path: `/admin/events/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name UpdateAdminEvent
     * @summary Update an existing event.
     * @request PUT:/admin/events/{id}
     * @secure
     */
    updateAdminEvent: (
      id: number,
      data: AdminEventRequest,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** Full event data for admin management */
          data: EventAdminResource;
        },
        void
      >({
        path: `/admin/events/${id}`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name DeleteAdminEvent
     * @summary Delete an event.
     * @request DELETE:/admin/events/{id}
     * @secure
     */
    deleteAdminEvent: (id: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/admin/events/${id}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name GetAdminEventSuggestions
     * @summary List unprocessed event suggestions.
     * @request GET:/admin/event-suggestions
     * @secure
     */
    getAdminEventSuggestions: (
      query?: {
        cursor?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: EventSuggestionResource[];
        },
        void
      >({
        path: `/admin/event-suggestions`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name GetAdminEventSuggestion
     * @summary Get a single suggestion with parallel events for the accept view.
     * @request GET:/admin/event-suggestions/{id}
     * @secure
     */
    getAdminEventSuggestion: (id: number, params: RequestParams = {}) =>
      this.request<
        {
          data: {
            /** Event suggestion submitted by a user */
            suggestion?: EventSuggestionResource;
            parallelEvents?: {
              id?: number;
              name?: string;
              slug?: string;
              /** @format date */
              checkin_start?: string;
              /** @format date */
              checkin_end?: string;
              /** @format float */
              similarity?: number;
            }[];
          };
        },
        void
      >({
        path: `/admin/event-suggestions/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name AcceptAdminEventSuggestion
     * @summary Accept an event suggestion and create the event.
     * @request POST:/admin/event-suggestions/{id}/accept
     * @secure
     */
    acceptAdminEventSuggestion: (
      id: number,
      data: AdminEventRequest,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** Full event data for admin management */
          data: EventAdminResource;
        },
        void
      >({
        path: `/admin/event-suggestions/${id}/accept`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name DenyAdminEventSuggestion
     * @summary Deny an event suggestion.
     * @request POST:/admin/event-suggestions/{id}/deny
     * @secure
     */
    denyAdminEventSuggestion: (
      id: number,
      data: {
        reason:
          | "denied"
          | "too-late"
          | "duplicate"
          | "not-applicable"
          | "missing-information";
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/admin/event-suggestions/${id}/deny`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name GetAdminStatuses
     * @summary List statuses for admin moderation. Admin only.
     * @request GET:/admin/statuses
     * @secure
     */
    getAdminStatuses: (
      query?: {
        /** Filter by user name or username */
        userQuery?: string;
        cursor?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: AdminStatusResource[];
        },
        void
      >({
        path: `/admin/statuses`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name GetAdminStatus
     * @summary Get a single status with all admin details. Admin only.
     * @request GET:/admin/statuses/{id}
     * @secure
     */
    getAdminStatus: (id: number, params: RequestParams = {}) =>
      this.request<
        {
          data: AdminStatusResource;
        },
        void
      >({
        path: `/admin/statuses/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Admin
     * @name UpdateAdminStatus
     * @summary Update a status including moderation fields. Admin only.
     * @request PUT:/admin/statuses/{id}
     * @secure
     */
    updateAdminStatus: (
      id: number,
      data: {
        /** Origin stopover ID (train_stopovers.id) of this trip */
        origin: number;
        /** Destination stopover ID (train_stopovers.id) of this trip */
        destination: number;
        /** @maxLength 280 */
        body?: string | null;
        visibility: number;
        business?: number | null;
        eventId?: number | null;
        points?: number | null;
        /** @maxLength 255 */
        moderationNotes?: string | null;
        lockVisibility?: boolean | null;
        hideBody?: boolean | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: AdminStatusResource;
        },
        void
      >({
        path: `/admin/statuses/${id}`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Admin only. Returns a cursor-paginated list of all trips with checkin counts.
     *
     * @tags Admin
     * @name GetAdminTrips
     * @summary List trips
     * @request GET:/admin/trips
     * @secure
     */
    getAdminTrips: (
      query?: {
        cursor?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: AdminTripResource[];
        },
        void
      >({
        path: `/admin/trips`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Admin only. Returns full trip details including stopovers with route segment info and checkins.
     *
     * @tags Admin
     * @name GetAdminTrip
     * @summary Get trip details
     * @request GET:/admin/trips/{id}
     * @secure
     */
    getAdminTrip: (id: number, params: RequestParams = {}) =>
      this.request<
        {
          data: AdminTripResource;
        },
        void
      >({
        path: `/admin/trips/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Admin only. Dispatches a background job to recalculate the polyline for the given trip.
     *
     * @tags Admin
     * @name RerouteAdminTrip
     * @summary Dispatch reroute job
     * @request POST:/admin/trips/{id}/reroute
     * @secure
     */
    rerouteAdminTrip: (id: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/admin/trips/${id}/reroute`,
        method: "POST",
        secure: true,
        ...params,
      }),

    /**
     * @description Admin only. Returns a cursor-paginated list of all users, optionally filtered by a search query.
     *
     * @tags Admin
     * @name GetAdminUsers
     * @summary List users
     * @request GET:/admin/users
     * @secure
     */
    getAdminUsers: (
      query?: {
        cursor?: string;
        query?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: AdminUserListResource[];
        },
        void
      >({
        path: `/admin/users`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Admin only. Returns full details for a single user including stats, roles, mail changes, and recent statuses.
     *
     * @tags Admin
     * @name GetAdminUser
     * @summary Get user details
     * @request GET:/admin/users/{id}
     * @secure
     */
    getAdminUser: (id: number, params: RequestParams = {}) =>
      this.request<
        {
          data: AdminUserResource;
        },
        void
      >({
        path: `/admin/users/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Admin only. Updates the email address for a user and sends a verification notification.
     *
     * @tags Admin
     * @name UpdateAdminUserEmail
     * @summary Update user email
     * @request PUT:/admin/users/{id}/email
     * @secure
     */
    updateAdminUserEmail: (
      id: number,
      data: {
        /** @format email */
        email: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/admin/users/${id}/email`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),

    /**
     * @description Admin only. Syncs roles for a user. The admin role is protected and cannot be removed.
     *
     * @tags Admin
     * @name UpdateAdminUserRoles
     * @summary Update user roles
     * @request PUT:/admin/users/{id}/roles
     * @secure
     */
    updateAdminUserRoles: (
      id: number,
      data: {
        roles: string[];
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/admin/users/${id}/roles`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),
  };
  alerts = {
    /**
     * No description
     *
     * @tags Notifications
     * @name GetAlerts
     * @summary Get alerts. Without ?all returns only currently active alerts. With ?all=true (admin only) returns all alerts with cursor pagination.
     * @request GET:/alerts
     */
    getAlerts: (
      query?: {
        /** Admin only: return all alerts regardless of active dates. */
        all?: boolean;
        cursor?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: AlertResource[];
        },
        any
      >({
        path: `/alerts`,
        method: "GET",
        query: query,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Notifications
     * @name CreateAlert
     * @summary Create a new alert. Admin only.
     * @request POST:/alerts
     * @secure
     */
    createAlert: (
      data: {
        type: "info" | "warning" | "danger" | "success";
        /** @format date */
        active_from: string;
        /** @format date */
        active_until?: string | null;
        title_de: string;
        content_de: string;
        title_en: string;
        content_en: string;
        url_de?: string | null;
        url_en?: string | null;
        url?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: AlertResource;
        },
        void
      >({
        path: `/alerts`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Notifications
     * @name GetAlert
     * @summary Get a single alert. Admin only.
     * @request GET:/alerts/{id}
     * @secure
     */
    getAlert: (id: string, params: RequestParams = {}) =>
      this.request<
        {
          data: AlertResource;
        },
        void
      >({
        path: `/alerts/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Notifications
     * @name UpdateAlert
     * @summary Update an alert. Admin only.
     * @request PUT:/alerts/{id}
     * @secure
     */
    updateAlert: (
      id: string,
      data: {
        type: "info" | "warning" | "danger" | "success";
        /** @format date */
        active_from: string;
        /** @format date */
        active_until?: string | null;
        title_de: string;
        content_de: string;
        title_en: string;
        content_en: string;
        url_de?: string | null;
        url_en?: string | null;
        url?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: AlertResource;
        },
        void
      >({
        path: `/alerts/${id}`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Notifications
     * @name DeleteAlert
     * @summary Delete an alert. Admin only.
     * @request DELETE:/alerts/{id}
     * @secure
     */
    deleteAlert: (id: string, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/alerts/${id}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),
  };
  applications = {
    /**
     * @description Returns all OAuth applications owned by the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.
     *
     * @tags Applications
     * @name GetApplications
     * @summary List OAuth applications
     * @request GET:/applications
     * @secure
     */
    getApplications: (params: RequestParams = {}) =>
      this.request<
        {
          data: OAuthClientResource[];
        },
        void
      >({
        path: `/applications`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Create a new OAuth application for the authenticated user. Requires a personal access token — third-party OAuth application tokens are not accepted.
     *
     * @tags Applications
     * @name CreateApplication
     * @summary Create OAuth application
     * @request POST:/applications
     * @secure
     */
    createApplication: (
      data: StoreOAuthClientRequest,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** OAuth application owned by the authenticated user */
          data: OAuthClientResource;
        },
        void
      >({
        path: `/applications`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Update an OAuth application owned by the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.
     *
     * @tags Applications
     * @name UpdateApplication
     * @summary Update OAuth application
     * @request PUT:/applications/{clientId}
     * @secure
     */
    updateApplication: (
      clientId: number,
      data: StoreOAuthClientRequest,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** OAuth application owned by the authenticated user */
          data: OAuthClientResource;
        },
        void
      >({
        path: `/applications/${clientId}`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Delete an OAuth application owned by the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.
     *
     * @tags Applications
     * @name DeleteApplication
     * @summary Delete OAuth application
     * @request DELETE:/applications/{clientId}
     * @secure
     */
    deleteApplication: (clientId: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/applications/${clientId}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),

    /**
     * @description Returns webhook call log statistics for the last 7 days for a given OAuth application. Only the application owner or admins can access it.
     *
     * @tags Applications
     * @name GetApplicationWebhookStats
     * @summary Get webhook call statistics for an application
     * @request GET:/applications/{clientId}/webhook-stats
     * @secure
     */
    getApplicationWebhookStats: (
      clientId: number,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** Webhook call log statistics for an OAuth application over the last 7 days */
          data: WebhookStatsResource;
        },
        void
      >({
        path: `/applications/${clientId}/webhook-stats`,
        method: "GET",
        secure: true,
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
          status: any;
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
          data: UserAuthResource;
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
          data: BearerTokenResponse;
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
          data: CommunityProfile;
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
          data: ContributionHistory[];
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
          data: EventResource;
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
          data: EventDetailsResource;
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
          data: StatusResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
        /** Show only upcoming events (only applicable, if from & to are not used) */
        upcoming?: boolean;
        /**
         * From date – returns all events in date range (required with "until")
         * @format date
         */
        from?: string;
        /**
         * Until date – returns all events in date range (required with "from")
         * @format date
         */
        until?: string;
        /** Page of pagination */
        page?: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: EventResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
  export = {
    /**
     * @description Requests a full GDPR data export. The export is processed asynchronously and delivered via email. Only available when the GDPR export feature is enabled for the account. Subject to a per-user cooldown (see `gdprExportCooldown` in the configuration endpoint). The `recentGdprExport` field on the authenticated user resource reflects the last request timestamp.
     *
     * @tags Export
     * @name RequestGdprExport
     * @summary Request a GDPR data export
     * @request POST:/export/gdpr
     * @secure
     */
    requestGdprExport: (params: RequestParams = {}) =>
      this.request<
        {
          /** @example "Export successfully requested." */
          message: string;
        },
        void
      >({
        path: `/export/gdpr`,
        method: "POST",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Generates a downloadable export of the authenticated user's statuses. Supported formats are `pdf`, `csv_human` (human-readable column headings), `csv_machine` (machine-readable column headings), and `json`. The `columns` parameter selects which fields to include and is required for PDF and CSV formats; it is ignored for JSON. The date range may not exceed 365 days, and the result set is capped at 2000 trips.
     *
     * @tags Export
     * @name GenerateStatusExport
     * @summary Export statuses as PDF, CSV or JSON
     * @request POST:/export/statuses
     * @secure
     */
    generateStatusExport: (
      data: {
        /**
         * Start date of the export period (inclusive)
         * @format date
         * @example "2024-01-01"
         */
        from: string;
        /**
         * End date of the export period (inclusive)
         * @format date
         * @example "2024-01-31"
         */
        until: string;
        /** Columns to include. Required for pdf/csv formats, ignored for json. */
        columns?: ExportableColumn[];
        /** The file type to export the data in. The available columns depend on the file type. */
        filetype: ExportableFileType;
      },
      params: RequestParams = {},
    ) =>
      this.request<File, void>({
        path: `/export/statuses`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
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
    createFollow: (id?: string | number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data: UserResource;
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
    destroyFollow: (id?: string | number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data: UserResource;
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
          data: UserResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: UserResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: UserResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
    removeFollower: (userId?: string | number, params: RequestParams = {}) =>
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
    acceptFollowRequest: (
      userId?: string | number,
      params: RequestParams = {},
    ) =>
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
    rejectFollowRequest: (
      userId?: string | number,
      params: RequestParams = {},
    ) =>
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
          data: StatusResource;
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
          data: TrustedUserResource[];
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
        /**
         * User-ID or UUID
         * @example "00000000-0000-0000-0000-000000000000"
         */
        userId: string;
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
          data: TrustedUserResource[];
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
      user: string | number,
      trusted: string | number,
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
          data: StatusResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: UserResource;
        },
        void | {
          /** @example "User not accessible." */
          message: string;
          reason: ViewUserForbiddenReason;
          /** User model */
          user: UserResource;
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
    createBlock: (id?: string | number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data: UserResource;
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
    destroyBlock: (id?: string | number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data: UserResource;
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
    createMute: (id?: string | number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data: UserResource;
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
    destroyMute: (id?: string | number, params: RequestParams = {}) =>
      this.request<
        {
          /** User model */
          data: UserResource;
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
          data: UserResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: UserResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: UserResource[];
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
          data: LikeResponse;
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
          data: LikeResponse;
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
     * @description Returns cursor-paginated statuses filtered by given parameters. The departure window (from..to) defaults to the last 7 days and must not exceed 365 days.
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
         * Lower bound for departure (date, e.g. 2024-01-01). Defaults to 7 days before "to".
         * @format date
         * @example "2024-01-01"
         */
        from?: string;
        /**
         * Upper bound for departure (date, e.g. 2024-01-31). Defaults to now+20min. Range from..to must not exceed 365 days.
         * @format date
         * @example "2024-01-31"
         */
        to?: string;
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
          data: StatusResource[];
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
    getSingleStatus: (
      id?: number,
      query?: {
        /**
         * Include station identifiers in origin and destination stopovers
         * @example true
         */
        withIdentifiers?: boolean;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: StatusResource;
        },
        void
      >({
        path: `/status/${id}`,
        method: "GET",
        query: query,
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
          data: StatusResource;
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
          data: StatusTagResource[];
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
     * @description Creates a single StatusTag Object, if user is authorized to. Triggers a `checkin_update` webhook event. <br><br>The key of a tag is free text. You can choose it as you need it. However, <b>please use a namespace for tags</b> (<i>namespace:xxx</i>) that only affect your own application.<br><br>For tags related to standard actions we recommend the following tags in the trwl namespace:<br> <ul> <li>trwl:seat (i.e. 61)</li> <li>trwl:wagon (i.e. 25)</li> <li>trwl:ticket (i.e. BahnCard 100 first))</li> <li>trwl:price (420,69 €)</li> <li>trwl:travel_class (i.e. 1, 2, business, economy, ...)</li> <li>trwl:locomotive_class (BR424, BR450)</li> <li>trwl:journey_number (i.e. 1234. Used as a work-around for missing journey numbers)</li> <li>trwl:wagon_class (i.e. Bpmz)</li> <li>trwl:role (i.e. Tf, Zf, Gf, Lokführer, conducteur de train, ...)</li> <li>trwl:vehicle_number (i.e. 425 001, Tz9001, 123, ...)</li> <li>trwl:passenger_rights (i.e. yes / no / ID of claim)</li> <li>trwl:social_status – social availability indicator. Allowed values: <code>open</code> (open to chatting), <code>open_find_me</code> (open, but staying at seat), <code>open_lets_hang</code> (open and willing to move around), <code>do_not_disturb</code> (prefer not to be disturbed).</li> </ul>
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
          data: StatusTagResource;
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
     * @description Updates a single StatusTag Object, if user is authorized to. Triggers a `checkin_update` webhook event.
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
          data: StatusTagResource;
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
     * @description Deletes a single StatusTag Object, if user is authorized to. Triggers a `checkin_update` webhook event.
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
          status: string;
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
  motisSources = {
    /**
     * @description Returns the transit data sources used by this instance, with their license information.
     *
     * @tags Debug
     * @name GetMotisSources
     * @summary List transit data sources
     * @request GET:/motis-sources
     */
    getMotisSources: (params: RequestParams = {}) =>
      this.request<
        {
          data: MotisSourceLicenseResource[];
        },
        any
      >({
        path: `/motis-sources`,
        method: "GET",
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
          data: number;
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
          data: Notification[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: Notification;
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
          data: Notification;
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
          status: string;
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
     * @name GetOperators
     * @summary Get a list of operators, optionally filtered by name.
     * @request GET:/operators
     */
    getOperators: (
      query?: {
        /** Filter operators by name (minimum 2 characters) */
        query?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: OperatorResource[];
        },
        void
      >({
        path: `/operators`,
        method: "GET",
        query: query,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Checkin
     * @name MergeOperators
     * @summary Merge two operators into one (admin only).
     * @request PUT:/operators/{oldOperatorId}/merge/{newOperatorId}
     */
    mergeOperators: (
      oldOperatorId: string,
      newOperatorId: string,
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/operators/${oldOperatorId}/merge/${newOperatorId}`,
        method: "PUT",
        ...params,
      }),
  };
  privacyPolicies = {
    /**
     * @description Get the current privacy policy
     *
     * @tags Privacy Policy
     * @name GetCurrentPrivacyPolicy
     * @summary Get the current privacy policy
     * @request GET:/privacy-policies/current
     */
    getCurrentPrivacyPolicy: (params: RequestParams = {}) =>
      this.request<
        {
          data: PrivacyPolicy;
        },
        any
      >({
        path: `/privacy-policies/current`,
        method: "GET",
        format: "json",
        ...params,
      }),

    /**
     * @description Accept the current privacy policy
     *
     * @tags Privacy Policy
     * @name AcceptPrivacyPolicy
     * @summary Accept the current privacy policy
     * @request PUT:/privacy-policies/{id}/acceptance
     * @secure
     */
    acceptPrivacyPolicy: (id?: any, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/privacy-policies/${id}/acceptance`,
        method: "PUT",
        secure: true,
        ...params,
      }),
  };
  reports = {
    /**
     * No description
     *
     * @tags Report
     * @name ListReports
     * @summary List all reports. Admin only.
     * @request GET:/reports
     * @secure
     */
    listReports: (
      query?: {
        cursor?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: ReportResource[];
        },
        void
      >({
        path: `/reports`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Report
     * @name CreateReport
     * @summary Report a Status, Event or User to the admins.
     * @request POST:/reports
     * @secure
     */
    createReport: (
      data: {
        /** @example "Status" */
        subjectType: "Event" | "Status" | "Trip" | "User";
        /** @example 1 */
        subjectId: number;
        /** @example "inappropriate" */
        reason: "inappropriate" | "implausible" | "spam" | "illegal" | "other";
        /** @example "The status is inappropriate because..." */
        description: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/reports`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),

    /**
     * No description
     *
     * @tags Report
     * @name GetReport
     * @summary Get a single report with activity log. Admin only.
     * @request GET:/reports/{id}
     * @secure
     */
    getReport: (id: string, params: RequestParams = {}) =>
      this.request<
        {
          data: ReportResource;
        },
        void
      >({
        path: `/reports/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Report
     * @name UpdateReport
     * @summary Update a report status. Admin only.
     * @request PUT:/reports/{id}
     * @secure
     */
    updateReport: (
      id: string,
      data: {
        status: "open" | "waiting" | "closed";
        description?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/reports/${id}`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),
  };
  routeSegments = {
    /**
     * No description
     *
     * @tags Polyline
     * @name ListRouteSegments
     * @summary List route segments for a given station pair (admin only).
     * @request GET:/route-segments
     */
    listRouteSegments: (
      query: {
        from_station_id: number;
        to_station_id: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: RouteSegmentResource[];
        },
        void
      >({
        path: `/route-segments`,
        method: "GET",
        query: query,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Polyline
     * @name CreateRouteSegment
     * @summary Create a straight-line route segment between two stations (admin only).
     * @request POST:/route-segments
     */
    createRouteSegment: (
      data: {
        /** @example 8000105 */
        from_station_id: number;
        /** @example 8000261 */
        to_station_id: number;
        /**
         * If provided, the new segment is assigned to this stopover and the duration is derived from the timetable.
         * @example 42
         */
        stopover_id?: number | null;
        /**
         * UUID of the StationIdentifier for the origin. Must be provided together with to_identifier_id.
         * @format uuid
         */
        from_identifier_id?: string | null;
        /**
         * UUID of the StationIdentifier for the destination. Must be provided together with from_identifier_id.
         * @format uuid
         */
        to_identifier_id?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: RouteSegmentResource;
        },
        void
      >({
        path: `/route-segments`,
        method: "POST",
        body: data,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Polyline
     * @name GetRouteSegment
     * @summary Get a single route segment with station names and counts (admin only).
     * @request GET:/route-segments/{id}
     */
    getRouteSegment: (id: string, params: RequestParams = {}) =>
      this.request<
        {
          data: RouteSegmentResource;
        },
        void
      >({
        path: `/route-segments/${id}`,
        method: "GET",
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Polyline
     * @name DeleteRouteSegment
     * @summary Delete a route segment (admin only). All stopovers using this segment are reassigned to another matching segment if available, otherwise their assignment is cleared.
     * @request DELETE:/route-segments/{id}
     */
    deleteRouteSegment: (id: string, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/route-segments/${id}`,
        method: "DELETE",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Polyline
     * @name AssignRouteSegmentToStopovers
     * @summary Dispatch a background job that assigns this segment to all matching unassigned stopovers (admin only).
     * @request POST:/route-segments/{id}/assign-stopovers
     */
    assignRouteSegmentToStopovers: (id: string, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/route-segments/${id}/assign-stopovers`,
        method: "POST",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Polyline
     * @name BrouterPreviewRouteSegment
     * @summary Request a BRouter route preview for the given waypoints (admin only).
     * @request POST:/route-segments/{id}/brouter-preview
     */
    brouterPreviewRouteSegment: (
      id: string,
      data: {
        waypoints: {
          lat: number;
          lng: number;
        }[];
        path_type?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          coordinates: {
            lat: number;
            lng: number;
          }[];
          /** Distance in meters */
          distance: number;
        },
        void
      >({
        path: `/route-segments/${id}/brouter-preview`,
        method: "POST",
        body: data,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Polyline
     * @name ApplyPolylineToRouteSegment
     * @summary Save custom waypoints and regenerate the segment's polyline via BRouter (admin only).
     * @request POST:/route-segments/{id}/polyline
     */
    applyPolylineToRouteSegment: (
      id: string,
      data: {
        waypoints: {
          lat: number;
          lng: number;
        }[];
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          polyline: string;
          /** Distance in meters */
          distance: number;
          customWaypoints: {
            lat: number;
            lng: number;
          }[];
        },
        void
      >({
        path: `/route-segments/${id}/polyline`,
        method: "POST",
        body: data,
        type: ContentType.Json,
        format: "json",
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
          data: SessionResource[];
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
     * @description Create a new API token for the authenticated user. Requires a personal access token, third-party OAuth application tokens are not accepted.
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
  settings = {
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
          data: UserProfileSettingsResource;
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
          data: UserProfileSettingsResource;
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
        email: string;
        /**
         * Required only if the account already has a password set.
         * @format password
         * @example "thisisnotasecurepassword123"
         */
        password?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: UserProfileSettingsResource;
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
     * @description Change the current user's password.
     *
     * @tags Settings
     * @name UpdatePassword
     * @summary Change password
     * @request PUT:/settings/password
     * @secure
     */
    updatePassword: (
      data: {
        /**
         * Current password (required if the account has a password set)
         * @format password
         */
        currentPassword?: string;
        /**
         * @format password
         * @minLength 8
         */
        password: string;
        /** @format password */
        password_confirmation: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: UserProfileSettingsResource;
        },
        void
      >({
        path: `/settings/password`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
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
          message: string;
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
          message: string;
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
     * @description Import the profile picture from the connected Mastodon account
     *
     * @tags Settings
     * @name ImportProfilePictureFromMastodon
     * @summary Import profile picture from Mastodon
     * @request POST:/settings/profile-picture/mastodon
     * @secure
     */
    importProfilePictureFromMastodon: (params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/settings/profile-picture/mastodon`,
        method: "POST",
        secure: true,
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
  stations = {
    /**
     * @description Admin only. Returns the number of records referencing this station. The station can only be deleted when all counts are zero.
     *
     * @tags Stations
     * @name GetStationUsages
     * @summary Get station usage counts
     * @request GET:/stations/{id}/usages
     * @secure
     */
    getStationUsages: (id: number, params: RequestParams = {}) =>
      this.request<
        {
          data: StationUsageDto;
        },
        void
      >({
        path: `/stations/${id}/usages`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Admin only. Moves records referencing this station to another station, so the station can be emptied and deleted. Stopovers already existing identically on the target station are merged into them. Route segment sides bound to a station identifier are not moved here, they follow their identifier via the identifier move endpoint. Station identifiers must also be moved via their own endpoint.
     *
     * @tags Stations
     * @name MoveStationUsages
     * @summary Move station references to another station
     * @request PUT:/stations/{id}/usages/move
     * @secure
     */
    moveStationUsages: (
      id: number,
      data: {
        /** @example 42 */
        target_station_id: number;
        /** Reference types to move. Defaults to all movable types. */
        types?: (
          | "stopovers"
          | "trips"
          | "events"
          | "eventSuggestions"
          | "routeSegments"
          | "homeUsers"
        )[];
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: StationUsageMoveResultDto;
        },
        void
      >({
        path: `/stations/${id}/usages/move`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
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
          data: StationResource;
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

    /**
     * @description Admin only. Deletes a station. Only possible when no other records reference the station, see the usages endpoint.
     *
     * @tags Stations
     * @name DeleteStation
     * @summary Delete a station
     * @request DELETE:/stations/{id}
     * @secure
     */
    deleteStation: (id: number, params: RequestParams = {}) =>
      this.request<
        void,
        void | {
          /** @example "Station is still in use and cannot be deleted" */
          message: string;
          data: StationUsageDto;
        }
      >({
        path: `/stations/${id}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),

    /**
     * @description Admin only. Update a station's name, coordinates, or time offset.
     *
     * @tags Stations
     * @name UpdateStation
     * @summary Update a station
     * @request PATCH:/stations/{id}
     * @secure
     */
    updateStation: (
      id: number,
      data: {
        /** @maxLength 255 */
        name?: string | null;
        /**
         * @format float
         * @min -90
         * @max 90
         */
        latitude?: number | null;
        /**
         * @format float
         * @min -180
         * @max 180
         */
        longitude?: number | null;
        time_offset?: number | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: StationResource;
        },
        void
      >({
        path: `/stations/${id}`,
        method: "PATCH",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

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
         * Maximum number of results for the bounding-box query (capped at 1000).
         * @min 1
         * @max 1000
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
          data: StationResource[];
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
     * @description Admin only. Manually add an identifier to a station. The `origin` field will be set to `null`.
     *
     * @tags Stations
     * @name StoreStationIdentifier
     * @summary Add a station identifier
     * @request POST:/stations/{stationId}/identifiers
     * @secure
     */
    storeStationIdentifier: (
      stationId: number,
      data: {
        /**
         * The type of the station identifier to look up. Not all types are available for every station. Subject to unannounced change.
         *     * motis – all transitous.org/motis supplied identifiers
         *     * wikidata_id – ID of wikidata.org
         *     * de_db_ril100 – Germany: Deutsche Bahn Richtlinie 100 identifier (e.g. RK for Karlsruhe Hbf)
         *     * de_db_ibnr – Germany: internal train station ID of Deutsche Bahn (e.g. 8000191 for Karlsruhe Hbf)
         *
         */
        type: StationIdentifierType;
        /**
         * @maxLength 255
         * @example "de:08212:1"
         */
        identifier: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/stations/${stationId}/identifiers`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),

    /**
     * @description Admin only. Update the type and value of an existing station identifier.
     *
     * @tags Stations
     * @name UpdateStationIdentifier
     * @summary Update a station identifier
     * @request PATCH:/stations/{stationId}/identifiers/{identifierId}
     * @secure
     */
    updateStationIdentifier: (
      stationId: number,
      identifierId: string,
      data: {
        /**
         * The type of the station identifier to look up. Not all types are available for every station. Subject to unannounced change.
         *     * motis – all transitous.org/motis supplied identifiers
         *     * wikidata_id – ID of wikidata.org
         *     * de_db_ril100 – Germany: Deutsche Bahn Richtlinie 100 identifier (e.g. RK for Karlsruhe Hbf)
         *     * de_db_ibnr – Germany: internal train station ID of Deutsche Bahn (e.g. 8000191 for Karlsruhe Hbf)
         *
         */
        type: StationIdentifierType;
        /**
         * @maxLength 255
         * @example "de:08212:1"
         */
        identifier: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<void, void>({
        path: `/stations/${stationId}/identifiers/${identifierId}`,
        method: "PATCH",
        body: data,
        secure: true,
        type: ContentType.Json,
        ...params,
      }),

    /**
     * @description Admin only. Move a station identifier to a different station. Also moves the stopovers created via this identifier, updates origin/destination of affected trips and re-points route segments. Stopovers that would collide with an already existing stopover on the target station are skipped and reported.
     *
     * @tags Stations
     * @name MoveStationIdentifier
     * @summary Move a station identifier
     * @request PUT:/stations/{stationId}/identifiers/{identifierId}/move
     * @secure
     */
    moveStationIdentifier: (
      stationId: number,
      identifierId: string,
      data: {
        /** @example 42 */
        target_station_id: number;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: {
            /** @example 12 */
            movedStopovers: number;
            /**
             * Stopovers not moved because an identical stopover already exists on the target station
             * @example 1
             */
            skippedStopovers: number;
            /** @example 3 */
            updatedTrips: number;
            /** @example 2 */
            updatedRouteSegments: number;
          };
        },
        void
      >({
        path: `/stations/${stationId}/identifiers/${identifierId}/move`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
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
          data: LeaderboardUserResource[];
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
          data: LeaderboardUserResource[];
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
          data: LeaderboardUserResource[];
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
          data: LeaderboardUserResource[];
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
          data: {
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
          data: {
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
          data: StatisticsGlobalData;
          meta: {
            /** @example "2021-01-01T00:00:00.000000Z" */
            from: any;
            /** @example "2021-02-01T00:00:00.000000Z" */
            until: any;
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

    /**
     * No description
     *
     * @tags Statistics
     * @name GetStatisticsOverview
     * @summary Get a summary of personal statistics for a date range
     * @request GET:/statistics/overview
     * @secure
     */
    getStatisticsOverview: (
      query?: {
        /**
         * Start date
         * @example "2024-01-01"
         */
        from?: any;
        /**
         * End date
         * @example "2024-12-31"
         */
        until?: any;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: {
            summary?: {
              /** @example 42 */
              total_checkins?: number;
              /** @example 15 */
              active_days?: number;
              /**
               * @format float
               * @example 1234.56
               */
              total_distance_km?: number;
              /**
               * @format float
               * @example 29.39
               */
              mean_distance_km?: number;
              longest_checkin_by_distance?: StatusResource | null;
              shortest_checkin_by_distance?: StatusResource | null;
              longest_checkin_by_duration?: StatusResource | null;
              shortest_checkin_by_duration?: StatusResource | null;
            };
          };
        },
        void
      >({
        path: `/statistics/overview`,
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
     * @name GetStatisticsHistory
     * @summary Get all-time checkin counts and distances grouped by year, month, and week
     * @request GET:/statistics/history
     * @secure
     */
    getStatisticsHistory: (params: RequestParams = {}) =>
      this.request<
        {
          data: {
            yearly?: {
              /** @example "2024" */
              period?: string;
              /** @example "year" */
              period_type?: string;
              /** @example 42 */
              checkin_count?: number;
              /**
               * @format float
               * @example 1234.56
               */
              distance_km?: number;
            }[];
            monthly?: any[];
            weekly?: any[];
          };
        },
        void
      >({
        path: `/statistics/history`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Statistics
     * @name GetStatisticsFavorites
     * @summary Get favorite stations, lines, and routes for a date range
     * @request GET:/statistics/favorites
     * @secure
     */
    getStatisticsFavorites: (
      query?: {
        /**
         * Start date
         * @example "2024-01-01"
         */
        from?: any;
        /**
         * End date
         * @example "2024-12-31"
         */
        until?: any;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: {
            stations?: {
              /** @example 1 */
              station_id?: number;
              /** @example "Frankfurt Hbf" */
              name?: string;
              /** @example 12 */
              count?: number;
            }[];
            lines?: any[];
            routes?: any[];
          };
        },
        void
      >({
        path: `/statistics/favorites`,
        method: "GET",
        query: query,
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
          data: StatusResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: StatusResource[];
          /** Pagination links */
          links: Links;
          /** Pagination meta data */
          meta: PaginationMeta;
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
          data: StatusResource[];
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
     * @description Assign or remove a ticket from a status. Only the status owner can perform this action.
     *
     * @tags Tickets
     * @name AssignTicketToStatus
     * @summary Assign or remove a ticket from a status
     * @request PUT:/statuses/{id}/tickets
     * @secure
     */
    assignTicketToStatus: (
      data: StatusAssignTicketBody,
      id?: number,
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: StatusResource;
        },
        void
      >({
        path: `/statuses/${id}/tickets`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
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
          data: {
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
          data: LivePointDto[];
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
          data: LivePointDto[];
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
          data: {
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
          data: {
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

    /**
     * @description Admin only. Deletes a stopover, e.g. a duplicate created by a real-time refresh. Stopovers referenced by checkins cannot be deleted.
     *
     * @tags Trips
     * @name DeleteStopover
     * @summary Delete a stopover
     * @request DELETE:/stopovers/{id}
     * @secure
     */
    deleteStopover: (id: number, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/stopovers/${id}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),
  };
  tags = {
    /**
     * @description Returns tag suggestions based on the user's most recently used key:value pairs and the most frequently used key:value pairs in the last 3 days (minimum 2 uses).
     *
     * @tags Status
     * @name GetTagSuggestions
     * @summary Get tag suggestions for the authenticated user
     * @request GET:/tags/suggestions
     * @secure
     */
    getTagSuggestions: (params: RequestParams = {}) =>
      this.request<
        {
          data: StatusTagSuggestionResource[];
        },
        void
      >({
        path: `/tags/suggestions`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),
  };
  tickets = {
    /**
     * @description Returns all tickets of the currently authenticated user. Only available to users with the closed-beta role. Optionally filter by validity date using the `validOn` parameter.
     *
     * @tags Tickets
     * @name GetTickets
     * @summary List all tickets of the current user
     * @request GET:/tickets
     * @secure
     */
    getTickets: (
      query?: {
        /**
         * Only return tickets valid on this date (YYYY-MM-DD). A ticket is valid if its valid_from is on or before this date (or null) and its valid_until is on or after this date (or null).
         * @format date
         * @example "2026-01-15"
         */
        validOn?: string;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: TicketResource[];
        },
        void
      >({
        path: `/tickets`,
        method: "GET",
        query: query,
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Creates a new ticket for the currently authenticated user. Only available to users with the closed-beta role.
     *
     * @tags Tickets
     * @name CreateTicket
     * @summary Create a ticket
     * @request POST:/tickets
     * @secure
     */
    createTicket: (
      data: {
        /** @example "My BahnCard 100" */
        name: string;
        /**
         * @format date
         * @example "2026-01-01"
         */
        valid_from?: string | null;
        /**
         * @format date
         * @example "2026-12-31"
         */
        valid_until?: string | null;
        /**
         * @format float
         * @example 3199
         */
        price?: number | null;
        /** @example "EUR" */
        currency?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** A transit ticket / Fahrkarte */
          data: TicketResource;
        },
        void
      >({
        path: `/tickets`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Returns a single ticket of the currently authenticated user.
     *
     * @tags Tickets
     * @name GetTicket
     * @summary Get a ticket
     * @request GET:/tickets/{id}
     * @secure
     */
    getTicket: (id: string, params: RequestParams = {}) =>
      this.request<
        {
          /** A transit ticket / Fahrkarte */
          data: TicketResource;
        },
        void
      >({
        path: `/tickets/${id}`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Updates a ticket of the currently authenticated user.
     *
     * @tags Tickets
     * @name UpdateTicket
     * @summary Update a ticket
     * @request PUT:/tickets/{id}
     * @secure
     */
    updateTicket: (
      id: string,
      data: {
        /** @example "My BahnCard 100" */
        name?: string;
        /**
         * @format date
         * @example "2026-01-01"
         */
        valid_from?: string | null;
        /**
         * @format date
         * @example "2026-12-31"
         */
        valid_until?: string | null;
        /**
         * @format float
         * @example 3199
         */
        price?: number | null;
        /** @example "EUR" */
        currency?: string | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** A transit ticket / Fahrkarte */
          data: TicketResource;
        },
        void
      >({
        path: `/tickets/${id}`,
        method: "PUT",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * @description Deletes a ticket of the currently authenticated user. Associated statuses will have their ticket reference removed.
     *
     * @tags Tickets
     * @name DeleteTicket
     * @summary Delete a ticket
     * @request DELETE:/tickets/{id}
     * @secure
     */
    deleteTicket: (id: string, params: RequestParams = {}) =>
      this.request<void, void>({
        path: `/tickets/${id}`,
        method: "DELETE",
        secure: true,
        ...params,
      }),

    /**
     * @description Returns usage statistics for a single ticket of the currently authenticated user.
     *
     * @tags Tickets
     * @name GetTicketStatistics
     * @summary Get statistics for a ticket
     * @request GET:/tickets/{id}/statistics
     * @secure
     */
    getTicketStatistics: (id: string, params: RequestParams = {}) =>
      this.request<
        {
          /** Usage statistics for a single ticket */
          data: TicketStatisticsResource;
        },
        void
      >({
        path: `/tickets/${id}/statistics`,
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
          data: DepartureResource[];
          meta: {
            /** train station model */
            station: Station;
            times: {
              /**
               * @format date-time
               * @example "2020-01-01T12:00:00.000Z"
               */
              now: string;
              /**
               * @format date-time
               * @example "2020-01-01T11:45:00.000Z"
               */
              prev: string;
              /**
               * @format date-time
               * @example "2020-01-01T12:15:00.000Z"
               */
              next: string;
            };
            /** List of licenses that were filtered out */
            removedLicenses: (string | LicenseDto)[];
            /**
             * Number of removed entries due to license filtering
             * @example 2
             */
            removedCount: number;
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
          data: any;
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
          data: TripResource;
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
          data: any[];
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
        | void
        | {
            /** @example "You are not allowed to check in the following users: 1" */
            message: string;
            meta: {
              invalidUsers: number[];
            };
          }
        | {
            /**
             * Deprecated: use data.conflicts instead
             * @deprecated
             */
            message: {
              /** @deprecated */
              status_id: number | null;
              /** @deprecated */
              lineName: string | null;
            };
            data: {
              conflicts: StatusResource[];
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
          data: StationResource[];
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
          data: StationResource[];
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
          data: StatusResource[];
        },
        void
      >({
        path: `/trips/${id}/statuses`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Routes between the given stations using the appropriate BRouter profile for the category. Falls back to straight-line segments if BRouter cannot route a segment (e.g. no railway near that station). Returns a GeoJSON LineString feature.
     *
     * @tags Trips
     * @name RoutePreviewTrip
     * @summary Preview the routing for a manual trip before creating it.
     * @request POST:/trips/route-preview
     * @secure
     */
    routePreviewTrip: (
      data: {
        /** Category of transport. */
        category: HafasTravelType;
        /**
         * Ordered list of station IDs (origin first, destination last).
         * @minItems 2
         */
        stationIds: number[];
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          /** GeoJSON Feature (LineString) */
          data: object;
        },
        void
      >({
        path: `/trips/route-preview`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),

    /**
     * No description
     *
     * @tags Trips
     * @name CreateTrip
     * @summary Create a manual trip.
     * @request POST:/trips
     * @secure
     */
    createTrip: (
      data: {
        /** @example "regional" */
        category: string;
        /** @example "RE 1" */
        lineName: string;
        /** @example 12345 */
        journeyNumber?: number | null;
        /**
         * Operator UUID (preferred) or numeric legacy ID. Use the `uuid` field from the operators endpoint.
         * @example "00000000-0000-0000-0000-000000000000"
         */
        operatorId?: string | null;
        /** @example 8000105 */
        originId: number;
        /**
         * @format date-time
         * @example "2025-01-01T10:00:00Z"
         */
        originDeparturePlanned: string;
        /** @example 8000261 */
        destinationId: number;
        /**
         * @format date-time
         * @example "2025-01-01T12:00:00Z"
         */
        destinationArrivalPlanned: string;
        stopovers?:
          | {
              /** @example 8000240 */
              stationId?: number;
              /**
               * @format date-time
               * @example "2025-01-01T11:00:00Z"
               */
              arrival?: string | null;
              /**
               * @format date-time
               * @example "2025-01-01T11:02:00Z"
               */
              departure?: string | null;
            }[]
          | null;
      },
      params: RequestParams = {},
    ) =>
      this.request<
        {
          data: TripResource;
        },
        void
      >({
        path: `/trips`,
        method: "POST",
        body: data,
        secure: true,
        type: ContentType.Json,
        format: "json",
        ...params,
      }),
  };
  users = {
    /**
     * @description Returns all users blocked by the authenticated user.
     *
     * @tags User/Hide and Block
     * @name GetBlockedUsers
     * @summary List blocked users
     * @request GET:/users/self/blocks
     * @secure
     */
    getBlockedUsers: (params: RequestParams = {}) =>
      this.request<
        {
          data: LightUserResource[];
        },
        void
      >({
        path: `/users/self/blocks`,
        method: "GET",
        secure: true,
        format: "json",
        ...params,
      }),

    /**
     * @description Returns all users muted by the authenticated user.
     *
     * @tags User/Hide and Block
     * @name GetMutedUsers
     * @summary List muted users
     * @request GET:/users/self/mutes
     * @secure
     */
    getMutedUsers: (params: RequestParams = {}) =>
      this.request<
        {
          data: LightUserResource[];
        },
        void
      >({
        path: `/users/self/mutes`,
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

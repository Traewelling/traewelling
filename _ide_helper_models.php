<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property string $id
 * @property string $type
 * @property string|null $url
 * @property \Illuminate\Support\Carbon $active_from
 * @property \Illuminate\Support\Carbon|null $active_until
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AlertTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereActiveFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereActiveUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereUrl($value)
 */
	class Alert extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $alert_id
 * @property string $locale
 * @property string $title
 * @property string $content
 * @property string|null $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Alert|null $banner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereAlertId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AlertTranslation whereUrl($value)
 */
	class AlertTranslation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property int $adminLevel
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AreasStationsMap|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Station> $stations
 * @property-read int|null $stations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereAdminLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereUpdatedAt($value)
 */
	class Area extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $station_id
 * @property string $area_id
 * @property bool $default Whether it's the default area for the station
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Area $area
 * @property-read \App\Models\Station $station
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereUpdatedAt($value)
 */
	class AreasStationsMap extends \Eloquent {}
}

namespace App\Models{
/**
 * @todo rename table to "Checkin" (without Train - we have more than just trains)
 * @todo merge model with "Status" because the difference between trip sources (HAFAS,
 *        User, and future sources) should be handled in the Trip model.
 * @todo use the `id` from trips, instead of the hafas trip id - this is duplicated data
 * @todo drop the `departure` and `arrival` columns and use the stopover instead
 * @property int $id
 * @property int $status_id
 * @property int|null $user_id workaround for unique key
 * @property string $trip_id
 * @property int|null $origin_stopover_id
 * @property int|null $destination_stopover_id
 * @property int|null $distance meters
 * @property int $duration Duration in minutes. Cached value with real time and manual data. Null if not yet calculated.
 * @property $departure
 * @property $manual_departure User-defined override of the departure
 * @property $arrival
 * @property $manual_arrival User-defined override of the arrival
 * @property int|null $points
 * @property bool $forced
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Stopover|null $destinationStopover
 * @property-read \Illuminate\Support\Collection<Status> $also_on_this_connection
 * @property-read \stdClass $display_arrival
 * @property-read \stdClass $display_departure
 * @property-read float $speed
 * @property-read \App\Models\Stopover|null $originStopover
 * @property-read \App\Models\Status $status
 * @property-read \App\Models\Trip|null $trip
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CheckinFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereDestinationStopoverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereForced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereManualArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereManualDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereOriginStopoverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereUserId($value)
 */
	class Checkin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $hashtag
 * @property string|null $host
 * @property string|null $url
 * @property int|null $station_id
 * @property \Illuminate\Support\Carbon $checkin_start
 * @property \Illuminate\Support\Carbon $checkin_end
 * @property \Illuminate\Support\Carbon|null $event_start If different from checkin_start
 * @property \Illuminate\Support\Carbon|null $event_end If different from checkin_end
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Carbon\Carbon $end
 * @property-read bool $has_extended_checkin
 * @property-read bool $is_pride
 * @property-read \Carbon\Carbon $start
 * @property-read int $total_distance
 * @property-read int $total_duration
 * @property-read \App\Models\Station|null $station
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Status> $statuses
 * @property-read int|null $statuses_count
 * @method static \Database\Factories\EventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCheckinEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCheckinStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEventEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEventStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereHashtag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUrl($value)
 */
	class Event extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $host
 * @property string|null $url
 * @property int|null $station_id
 * @property \Illuminate\Support\Carbon|null $begin
 * @property \Illuminate\Support\Carbon|null $end
 * @property string|null $hashtag
 * @property int|null $admin_notification_id
 * @property bool $processed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Station|null $station
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\EventSuggestionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereAdminNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereBegin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereHashtag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSuggestion whereUserId($value)
 */
	class EventSuggestion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $follow_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $following
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\FollowFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereFollowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereUserId($value)
 */
	class Follow extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $follow_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $requestedFollow
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereFollowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereUserId($value)
 */
	class FollowRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $last_accessed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\IcsTokenFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereLastAccessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereUserId($value)
 */
	class IcsToken extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string|null $name
 * @property string|null $human_name
 * @property string|null $attribution
 * @property string|null $license_url
 * @property string|null $source_url
 * @property string|null $spdx
 * @property int $automatically_activate_source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MotisSourceLicense> $motisSourceLicenses
 * @property-read int|null $motis_source_licenses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereAttribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereAutomaticallyActivateSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereHumanName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereSpdx($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereUpdatedAt($value)
 */
	class License extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $status_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Status $status
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\LikeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Like whereUserId($value)
 */
	class Like extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $domain
 * @property string $client_id
 * @property string $client_secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SocialLoginProfile> $socialProfiles
 * @property-read int|null $social_profiles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MastodonServer whereUpdatedAt($value)
 */
	class MastodonServer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $status_id
 * @property int $mentioned_id
 * @property int $position
 * @property int $length
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $mentioned
 * @property-read \App\Models\Status $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereMentionedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mention whereUpdatedAt($value)
 */
	class Mention extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string|null $provider
 * @property string|null $country
 * @property string|null $name
 * @property string|null $human_name
 * @property string|null $license
 * @property string|null $license_url
 * @property string|null $source_url
 * @property string|null $spdx
 * @property string|null $license_id
 * @property int $active
 * @property int $force_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\License|null $manualLicense
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereForceActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereHumanName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereLicenseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereSpdx($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MotisSourceLicense whereUpdatedAt($value)
 */
	class MotisSourceLicense extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $secret
 * @property string|null $provider
 * @property string $redirect
 * @property int $webhooks_enabled
 * @property string|null $privacy_policy_url
 * @property string|null $authorized_webhook_url
 * @property bool $personal_access_client
 * @property bool $password_client
 * @property bool $revoked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\AuthCode> $authCodes
 * @property-read int|null $auth_codes_count
 * @property-read string|null $plain_secret
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\User|null $user
 * @method static \Laravel\Passport\Database\Factories\ClientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereAuthorizedWebhookUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient wherePasswordClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient wherePersonalAccessClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient wherePrivacyPolicyUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereRedirect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereWebhooksEnabled($value)
 */
	class OAuthClient extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $wikidata_id Wikidata ID of the operator
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OperatorIdentifier> $identifiers
 * @property-read int|null $identifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 * @method static \Database\Factories\OperatorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereWikidataId($value)
 */
	class Operator extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $operator_id
 * @property string $type e.g. hafas, motis
 * @property string $identifier
 * @property string|null $source Source of the identifier, e.g. motis_source
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Operator $operator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereOperatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereUpdatedAt($value)
 */
	class OperatorIdentifier extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $hash
 * @property string $polyline
 * @property string|null $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read PolyLine|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine wherePolyline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereUpdatedAt($value)
 */
	class PolyLine extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $body_md_de
 * @property string $body_md_en
 * @property \Illuminate\Support\Carbon $valid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereBodyMdDe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereBodyMdEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PrivacyAgreement whereValidAt($value)
 */
	class PrivacyAgreement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $user_id
 * @property \App\Enum\ProfileLinkName $name
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereUserId($value)
 */
	class ProfileLink extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \App\Enum\Report\ReportStatus $status Enum ReportStatus
 * @property string $subject_type
 * @property int $subject_id
 * @property \App\Enum\Report\ReportReason|null $reason Enum ReportReason or null.
 * @property string|null $description
 * @property int|null $reporter_id
 * @property int|null $admin_notification_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $reporter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereAdminNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReporterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUpdatedAt($value)
 */
	class Report extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $payload
 * @property int $last_activity
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Session whereUserId($value)
 */
	class Session extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $mastodon_id
 * @property int|null $mastodon_server
 * @property string|null $mastodon_token
 * @property \App\Enum\MastodonVisibility $mastodon_visibility
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MastodonServer|null $mastodonServer
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereMastodonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereMastodonServer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereMastodonToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereMastodonVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialLoginProfile whereUserId($value)
 */
	class SocialLoginProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * @todo rename table to "Station" (without Train - we have more than just trains)
 * @property int $id
 * @property int|null $ibnr
 * @property string|null $wikidata_id
 * @property string|null $ifopt_a Country
 * @property int|null $ifopt_b Administrative Area
 * @property int|null $ifopt_c Mode or Stop Place
 * @property int|null $ifopt_d Stop Place or Stop Place Component
 * @property int|null $ifopt_e Stop Place Component (or unused)
 * @property string|null $rilIdentifier
 * @property string $name
 * @property float $latitude
 * @property float $longitude
 * @property string|null $source
 * @property int $relevance
 * @property int|null $time_offset Defines the offset of the train station relative to Europe/Berlin
 * @property int|null $shift_time If false, the timezone of the hafas request will not be shifted to Europe/Berlin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\AreasStationsMap|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Area> $areas
 * @property-read int|null $areas_count
 * @property-read string|null $ifopt
 * @property-read string|null $localized_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StationName> $names
 * @property-read int|null $names_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StationIdentifier> $stationIdentifiers
 * @property-read int|null $station_identifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stopover> $stopovers
 * @property-read int|null $stopovers_count
 * @method static \Database\Factories\StationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereIbnr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereIfoptA($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereIfoptB($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereIfoptC($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereIfoptD($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereIfoptE($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereRelevance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereRilIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereShiftTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereTimeOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereWikidataId($value)
 */
	class Station extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $relevance
 * @property int $station_id
 * @property string $type
 * @property string|null $origin
 * @property string $identifier
 * @property string|null $name Name of the station provided by the data source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Station $station
 * @method static \Database\Factories\StationIdentifierFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereRelevance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereUpdatedAt($value)
 */
	class StationIdentifier extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $station_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Station $station
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereUpdatedAt($value)
 */
	class StationName extends \Eloquent {}
}

namespace App\Models{
/**
 * @todo merge model with "Checkin" (later only "Checkin") because the difference between trip sources (HAFAS,
 *       User, and future sources) should be handled in the Trip model.
 * @property int $id
 * @property string|null $body
 * @property int $user_id
 * @property \App\Enum\Business $business
 * @property \App\Enum\StatusVisibility $visibility
 * @property int|null $event_id
 * @property string|null $mastodon_post_id
 * @property int|null $client_id
 * @property string|null $moderation_notes Notes from the moderation team - visible to the user
 * @property bool $lock_visibility Prevent the user from changing the visibility of the status?
 * @property bool $hide_body Hide the body of the status from other users?
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Checkin|null $checkin
 * @property-read \App\Models\OAuthClient|null $client
 * @property-read \App\Models\Event|null $event
 * @property-read string $description
 * @property-read bool|null $favorited
 * @property-read bool $status_invisible_to_me
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Like> $likes
 * @property-read int|null $likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mention> $mentions
 * @property-read int|null $mentions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StatusTag> $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\Checkin|null $trainCheckin
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\StatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereBusiness($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereHideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereLockVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereMastodonPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereModerationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereVisibility($value)
 */
	class Status extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $status_id
 * @property string $key
 * @property string $value
 * @property \App\Enum\StatusVisibility $visibility
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Enum\StatusTagKey|null $key_enum
 * @property-read \App\Models\Status $status
 * @method static \Database\Factories\StatusTagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereVisibility($value)
 */
	class StatusTag extends \Eloquent {}
}

namespace App\Models{
/**
 * @todo rename table to "Stopover" (without Train - we have more than just trains)
 * @todo rename "train_station_id" to "station_id" - we have more than just trains.
 * @todo rename "cancelled" to "is_cancelled" - or split into "is_arrival_cancelled" and "is_departure_cancelled"? need
 *       to think about this.
 * @property int $id
 * @property string $trip_id
 * @property int $train_station_id
 * @property $arrival_planned
 * @property $arrival_real
 * @property string|null $arrival_platform_planned
 * @property string|null $arrival_platform_real
 * @property $departure_planned
 * @property $departure_real
 * @property string|null $departure_platform_planned
 * @property string|null $departure_platform_real
 * @property bool $cancelled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $route_segment_id
 * @property-read \Carbon\Carbon|null $arrival
 * @property-read \Carbon\Carbon|null $departure
 * @property-read bool $is_arrival_cancelled
 * @property-read bool $is_arrival_delayed
 * @property-read bool $is_departure_cancelled
 * @property-read bool $is_departure_delayed
 * @property-read string|null $platform
 * @property-read \App\Models\Station $station
 * @property-read \App\Models\Station $trainStation
 * @property-read \App\Models\Trip $trip
 * @method static \Database\Factories\StopoverFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalPlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalPlatformPlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalPlatformReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDeparturePlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDeparturePlatformPlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDeparturePlatformReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDepartureReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereRouteSegmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereTrainStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereUpdatedAt($value)
 */
	class Stopover extends \Eloquent {}
}

namespace App\Models{
/**
 * @todo rename table only to "Trip" (without Hafas)
 * @todo rename "linename" to "line_name" (or something else, but not "linename")
 * @todo drop origin and destination, when origin_id and destination_id are added
 * @property int $id
 * @property string $trip_id
 * @property \App\Enum\HafasTravelType $category
 * @property string $number
 * @property string $linename
 * @property string|null $route_color Hex color code of the route, without #
 * @property int|null $journey_number
 * @property int|null $operator_id
 * @property int $origin_id
 * @property int $destination_id
 * @property int|null $polyline_id
 * @property $departure
 * @property $arrival
 * @property \App\Enum\TripSource $source
 * @property string|null $motis_source
 * @property string|null $motis_source_license_id
 * @property int|null $user_id if not null, this trip belongs to the user (e.g. manually created trips)
 * @property \Illuminate\Support\Carbon|null $last_refreshed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Checkin> $checkins
 * @property-read int|null $checkins_count
 * @property-read \App\Models\Station $destinationStation
 * @property-read \App\Models\MotisSourceLicense|null $motisSourceLicense
 * @property-read \App\Models\Operator|null $operator
 * @property-read \App\Models\Station $originStation
 * @property-read \App\Models\PolyLine|null $polyline
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stopover> $stopovers
 * @property-read int|null $stopovers_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\TripFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereJourneyNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereLastRefreshed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereLinename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereMotisSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereMotisSourceLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereOperatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereOriginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip wherePolylineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereRouteColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereUserId($value)
 */
	class Trip extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $user_id
 * @property int $trusted_id
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $trusted
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereTrustedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereUserId($value)
 */
	class TrustedUser extends \Eloquent {}
}

namespace App\Models{
/**
 * @todo rename home_id to home_station_id
 * @todo rename mapprovider to map_provider
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string|null $avatar
 * @property string|null $bio
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $privacy_ack_at
 * @property string|null $password
 * @property int|null $home_id
 * @property bool $private_profile
 * @property \App\Enum\StatusVisibility $default_status_visibility
 * @property bool $prevent_index prevent search engines from indexing this profile
 * @property int|null $privacy_hide_days Set statuses private after x days
 * @property string|null $language
 * @property string $timezone
 * @property \App\Enum\User\FriendCheckinSetting $friend_checkin
 * @property bool $likes_enabled
 * @property bool $points_enabled
 * @property \App\Enum\MapProvider $mapprovider
 * @property \App\Enum\DataProvider $data_provider
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property \Illuminate\Support\Carbon|null $recent_gdpr_export
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $blockedByUsers
 * @property-read int|null $blocked_by_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $blockedUsers
 * @property-read int|null $blocked_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OAuthClient> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FollowRequest> $followRequests
 * @property-read int|null $follow_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Follow> $followings
 * @property-read int|null $followings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $follows
 * @property-read int|null $follows_count
 * @property-read bool $follow_pending
 * @property-read bool $followed_by
 * @property-read bool $following
 * @property-read bool $is_auth_user_blocked
 * @property-read bool $is_blocked_by_auth_user
 * @property-read string|null $mastodon_url
 * @property-read bool $muted
 * @property-read int $points
 * @property-read float $train_distance
 * @property-read float $train_duration
 * @property-read bool $user_invisible_to_me
 * @property-read \App\Models\Station|null $home
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IcsToken> $icsTokens
 * @property-read int|null $ics_tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Like> $likes
 * @property-read int|null $likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $mutedUsers
 * @property-read int|null $muted_users_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OAuthClient> $oAuthClients
 * @property-read int|null $o_auth_clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProfileLink> $profileLinks
 * @property-read int|null $profile_links_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Session> $sessions
 * @property-read int|null $sessions_count
 * @property-read \App\Models\SocialLoginProfile|null $socialProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Status> $statuses
 * @property-read int|null $statuses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Checkin> $trainCheckins
 * @property-read int|null $train_checkins_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TrustedUser> $trustedByUsers
 * @property-read int|null $trusted_by_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TrustedUser> $trustedUsers
 * @property-read int|null $trusted_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $userFollowRequests
 * @property-read int|null $user_follow_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $userFollowers
 * @property-read int|null $user_followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $userFollowings
 * @property-read int|null $user_followings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Webhook> $webhooks
 * @property-read int|null $webhooks_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDataProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDefaultStatusVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFriendCheckin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHomeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLikesEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMapprovider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePointsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePreventIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrivacyAckAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrivacyHideDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrivateProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRecentGdprExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent implements \Spatie\PersonalDataExport\ExportsPersonalData {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $blocked_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $blockedUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereBlockedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereUserId($value)
 */
	class UserBlock extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $muted_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $mutedUser
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserMuteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereMutedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereUserId($value)
 */
	class UserMute extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $oauth_client_id
 * @property int $user_id
 * @property string $url
 * @property string|null $secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OAuthClient $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WebhookEvent> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook whereOauthClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Webhook whereUserId($value)
 */
	class Webhook extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $user_id
 * @property int $oauth_client_id
 * @property bool $revoked
 * @property \Illuminate\Support\Carbon $expires_at
 * @property string $events
 * @property string $url
 * @property-read \App\Models\OAuthClient $client
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereEvents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereOauthClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereUserId($value)
 */
	class WebhookCreationRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $webhook_id
 * @property \App\Enum\WebhookEvent $event
 * @property-read \App\Models\Webhook|null $webhook
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent whereWebhookId($value)
 */
	class WebhookEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WikidataEntity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WikidataEntity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WikidataEntity query()
 */
	class WikidataEntity extends \Eloquent {}
}


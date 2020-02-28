<?php

return [
    "empty" => "You have not received any notifications yet.",
    "title" => "Notifications",
    "mark-all-read" => "Mark all as read",
    "mark-as-unread" => "Mark as unread",
    "mark-as-read" => "Mark as read",
    "statusLiked" => [
        "lead" => "<b>@:likerUsername</b> liked your check-in.",
        "notice" => "Journey in :line on :createdDate|Journey in line :line on :createdDate"
    ],
    "userFollowed" => [
        "lead" => "<b>@:followerUsername</b> is following you."
    ],
    "socialNotShared" => [
        "lead" => "Your Check-in has not been shared to :Platform.",
        "mastodon" => [
            "401" => "Your instance has sent us an <code>401 Unauthorized</code> error when we tried to toot - please consider reconnecting Träwelling to Mastodon.",
            "429" => "It looks like we've been rate-limited by your instance. (<code>429 Too Many Requests</code>)",
            "504" => "It looks like your Mastodon instance was not available when we tried to toot. (<code>504 Bad Gateway</code>)",
        ],
        "twitter" => [
            "401" => "Twitter has sent us an <code>401 Unauthorized</code> error when we tried to toot - please consider reconnecting Träwelling to Mastodon.",
            "429" => "It looks like we've been rate-limited by Twitter. (<code>429 Too Many Requests</code>)",
            "504" => "It looks like Twitter was having fun with whales when we tried to tweet your check-in. (<code>504 Bad Gateway</code>)",
        ]
    ],
    "userJoinedConnection" => [
        "lead" => "<b>@:username</b> is in your connection!",
        "notice" => "They are on <b>:linename</b> from <b>:origin</b> to <b>:destination</b>.|They are on line <b>:linename</b> from <b>:origin</b> to <b>:destination</b>."
    ]
];
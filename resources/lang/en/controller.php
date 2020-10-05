<?php
return [
    "social"    => [
        "already-connected-error" => "This Account is already connected to another user",
        "create-error"            => "There has been an error creating your account.",
        "delete-never-connected"  => "Your user does not have a Social Login provider",
        "delete-set-password"     => "You need to set a password before deleting a SSO-Provider to prevent you from locking yourself out.",
        "deleted"                 => "Social Login Provider has been deleted"
    ],
    "status"    => [
        "status-not-found"        => "Status not found",
        "create-success"          => "Status successfully created!",
        "delete-ok"               => "Status successfully deleted.",
        "email-not-verified"      => "You have not verified your mail yet!",
        "email-resend-mail"       => "Resend link.",
        "export-invalid-dates"    => "Those aren't valid dates.",
        "export-neither-business" => "You can't uncheck both private and business trips",
        "like-already"            => "Like already exists",
        "like-deleted"            => "Like deleted",
        "like-not-found"          => "Like not found",
        "like-ok"                 => "Like created",
        "not-permitted"           => "You 're not permitted to do this"
    ],
    "transport" => [
        "checkin-heading"        => "Checked in!",
        "checkin-ok"             => "You've successfully checked into :lineName!|You've successfully checked into line :lineName!",
        "no-name-given"          => "You need to provide a station name!",
        "not-in-stopovers"       => "Start-ID is not in stopovers.",
        "overlapping-checkin"    => "You have an overlapping checkin with connection :linename: <a href=\":url\">#:id</a>",
        "also-in-connection"     => "Also in this connection are:",
        "social-post"            => "I'm in :lineName towards :Destination! #NowTräwelling |I'm in line :lineName towards :Destination! #NowTräwelling ",
        "social-post-with-event" => "I'm in :lineName towards #:hashtag via :Destination! #NowTräwelling | I'm in Line :lineName towards #:hashtag via :Destination! #NowTräwelling ",
        "social-post-for"        => " for #:hashtag",
        "no-station-found"       => "No station has been found for this search.",
    ],
    "user"      => [
        "follow-404"                  => "This follow does not exist.",
        "follow-already-exists"       => "This follow already exists.",
        "follow-delete-not-permitted" => "This action is not permitted.",
        "follow-destroyed"            => "This follow has been destroyed.",
        "follow-ok"                   => "Followed user.",
        "password-changed-ok"         => "Password changed.",
        "password-wrong"              => "Password wrong."
    ]
];

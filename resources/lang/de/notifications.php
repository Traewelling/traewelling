<?php

return [
    "empty" => "Du hast bisher noch keine Benachrichtigungen bekommen.",
    "title" => "Benachrichtigungen",
    "mark-all-read" => "Alle als gelesen markieren",
    "mark-as-unread" => "Als ungelesen markieren",
    "mark-as-read" => "Als gelesen markieren",
    "statusLiked" => [
        "lead" => "<b>@:likerUsername</b> gefällt Dein Check-in.",
        "notice" => "Reise mit :line am :createdDate|Reise mit Linie :line am :createdDate"
    ],
    "userFollowed" => [
        "lead" => "<b>@:followerUsername</b> folgt Dir jetzt."
    ],
    "socialNotShared" => [
        "lead" => "Dein Check-in wurde nicht auf :Platform geteilt.",
        "mastodon" => [
            "401" => "Deine Instanz hat uns einen <code>401 Unauthorized</code>-Fehler gemeldet, als wir versucht haben, Deinen Check-In zu tooten. Vielleicht hilft es, Mastodon erneut mit Träwelling zu verbinden?",
            "429" => "Es scheint, dass Träwelling temporär von Deiner Instanz gesperrt wurde. (<code>429 Too Many Requests</code>)",
            "504" => "Deine Instanz war nicht verfügbar, als wir versucht haben, Deinen Check-In zu tooten. (<code>504 Bad Gateway</code>)",
        ],
        "twitter" => [
            "401" => "Twitter hat uns einen <code>401 Unauthorized</code>-Fehler gemeldet, als wir versucht haben, Deinen Check-In zu twittern. Vielleicht hilft es, Twitter erneut mit Träwelling zu verbinden?",
            "429" => "Es scheint, dass Träwelling temporär von Twitter gesperrt wurde. (<code>429 Too Many Requests</code>)",
            "504" => "Twitter war down, als wir versucht haben, Deinen Check-In zu twittern. (<code>504 Bad Gateway</code>)",
        ]
    ],
    "userJoinedConnection" => [
        "lead" => "<b>@:username</b> ist auch in Deiner Verbindung!",
        "notice" => "@:username reist mit <b>:linename</b> von <b>:origin</b> nach <b>:destination</b>.|@:username reist mit Linie <b>:linename</b> von <b>:origin</b> nach <b>:destination</b>."
    ]
];
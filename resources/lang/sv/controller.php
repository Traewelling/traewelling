<?php
return [
    "social"    => [
        "already-connected-error" => "Det här kontot är redan associerat till en annan användare.",
        "create-error"            => "Det uppstod ett problem med att skapa ditt konto.",
        "delete-never-connected"  => "Du har ingen social login provider.",
        "delete-set-password"     => "Innan du tar bort en SSO provider måste du ange ett lösenord för att inte stänga dig själv ute.",
        "deleted"                 => "Förbindelsen har upphävats."
    ],
    "status"    => [
        "status-not-found"        => "Status hittades inte",
        "create-success"          => "Status skapats.",
        "delete-ok"               => "Status raderad.",
        "email-not-verified"      => "Du har ännu inte verifierat din e-postadress. Skicka bekräftelsen <a href=\":url\">igen</a>",
        "export-invalid-dates"    => "Det här är inte giltiga data.",
        "export-neither-business" => "Du kan inte avmarkera både privata och affärsresor.",
        "like-already"            => "Like finns redan.",
        "like-deleted"            => "Like raderats.",
        "like-not-found"          => "Like hittades inte.",
        "like-ok"                 => "Liked!",
        "not-permitted"           => "DET får du inte."
    ],
    "transport" => [
        "checkin-heading"        => "Checkat in",
        "checkin-ok"             => "Du har lyckats checka in på :lineName!|Du har lyckats checka in på tågförbindelsen :lineName!",
        "no-name-given"          => "Du måste ange ett stationsnamn!",
        "not-in-stopovers"       => "Start-ID är inte bland tågstoppen.",
        "overlapping-checkin"    => "Du har redan en incheckning i anslutningen :linename: <a href=\":url\">#:id</a>",
        "also-in-connection"     => "Också i detta tågförbindelse är:",
        "social-post"            => "Jag är för närvarande i :lineName till :Destination! #NowTräwelling |Jag är för närvarande i tågförbindelsen :lineName till :Destination! #NowTräwelling ",
        "social-post-with-event" => "Jag är för närvarande i :lineName till :Destination för #:hashtag! #NowTräwelling | Jag är för närvarande i tågförbindelsen :lineName till #Destination för #:hashtag! #NowTräwelling ",
        "social-post-for"        => " åt ",
        "no-station-found"       => "Inget stopp hittades för denna sökning.",
    ],
    "user"      => [
        "follow-404"                  => "Denna follow existerar inte.",
        "follow-already-exists"       => "Du följer redan den här personen.",
        "follow-delete-not-permitted" => "Denna aktion är inte tillåten.",
        "follow-destroyed"            => "Du följer inte längre den här personen.",
        "follow-ok"                   => "Användaren följdes.",
        "password-changed-ok"         => "Lösenordet har ändrats.",
        "password-wrong"              => "Det gamla lösenordet är fel"
    ]
];

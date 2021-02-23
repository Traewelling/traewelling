<?php
return [
    "accepted"             => ":attribute muss akzeptiert werden.",
    "active_url"           => ":attribute ist keine korrekte URL.",
    "after"                => ":attribute muss ein Datum nach dem :date sein.",
    "after_or_equal"       => ":attribute muss ein Datum nach dem oder am :date sein.",
    "alpha"                => ":attribute darf nur Buchstaben enthalten.",
    "alpha_dash"           => ":attribute darf nur Buchstaben, Zahlen und Bindestriche enthalten.",
    "alpha_num"            => ":attribute darf nur Buchstaben und Zahlen enthalten.",
    "array"                => ":attribute muss eine Liste sein.",
    "attributes"           => [
        "email"                 => "E-Mail Adresse",
        "name"                  => "Name",
        "password"              => "Passwort",
        "password_confirmation" => "Passwort-Bestätigung",
        "remember"              => "Zugangsdaten merken"
    ],
    "before"               => ":attribute muss ein Datum vor dem :date sein.",
    "before_or_equal"      => ":attribute muss ein Datum vor dem oder am :date sein.",
    "between"              => [
        "array"   => ":attribute muss zwischen :min und :max Einträge haben.",
        "file"    => ":attribute muss zwischen :min und :max Kilobytes sein.",
        "numeric" => ":attribute muss zwischen :min und :max sein.",
        "string"  => ":attribute muss zwischen :min und :max Zeichen sein."
    ],
    "boolean"              => ":attribute muss wahr oder falsch sein.",
    "confirmed"            => "Die :attribute-Bestätigung stimmt nicht überein.",
    "custom"               => ["attribute-name" => ["rule-name" => "custom-message"]],
    "date"                 => ":attribute ist kein gültiges Datum.",
    "date_equals"          => "Das Attribut :attribute muss ein Datum sein, das dem Datum :date entspricht.",
    "date_format"          => ":attribute entspricht nicht dem Format: :format.",
    "different"            => ":attribute und :other müssen verschieden sein.",
    "digits"               => ":attribute muss :digits Ziffern lang sein.",
    "digits_between"       => ":attribute muss zwischen :min und :max Ziffern lang sein.",
    "dimensions"           => ":attribute hat inkorrekte Bild-Dimensionen.",
    "distinct"             => ":attribute hat einen doppelten Wert.",
    "email"                => ":attribute muss eine korrekte E-Mail-Adresse sein.",
    "ends_with"            => "Das Attribut :attribute muss mit einem der folgenden Werte enden: :values",
    "exists"               => "Ausgewählte(s) :attribute ist inkorrekt.",
    "file"                 => ":attribute muss eine Datei sein.",
    "filled"               => ":attribute muss ausgefüllt werden.",
    "gt"                   => [
        "array"   => ":attribute muss mehr als :value Elemente haben.",
        "file"    => ":attribute muss grö&szlig;er als :value Kilobytes sein.",
        "numeric" => ":attribute muss grö&szlig;er als :value sein.",
        "string"  => ":attribute muss länger als :value Buchstaben sein."
    ],
    "gte"                  => [
        "array"   => ":attribute muss mindestens :value Elemente haben.",
        "file"    => ":attribute muss mindestens :value Kilobytes groß sein.",
        "numeric" => ":attribute muss grö&szlig;er oder gleich :value sein.",
        "string"  => ":attribute muss mindestens :value Buchstaben lang sein."
    ],
    "image"                => ":attribute muss ein Bild sein.",
    "in"                   => "Ausgewählte(s) :attribute ist inkorrekt.",
    "in_array"             => ":attribute existiert nicht in :other.",
    "integer"              => ":attribute muss eine Ganzzahl sein.",
    "ip"                   => ":attribute muss eine korrekte IP-Adresse sein.",
    "ipv4"                 => ":attribute muss eine korrekte IPv4-Adresse sein.",
    "ipv6"                 => ":attribute muss eine korrekte IPv6-Adresse sein.",
    "json"                 => ":attribute muss ein korrekter JSON-String sein.",
    "lt"                   => [
        "array"   => ":attribute muss weniger als :value Elemente haben.",
        "file"    => ":attribute muss kleiner als :value Kilobytes sein.",
        "numeric" => ":attribute muss kleiner als :value sein.",
        "string"  => ":attribute muss kürzer als :value Buchstaben sein."
    ],
    "lte"                  => [
        "array"   => ":attribute darf maximal :value Elemente haben.",
        "file"    => ":attribute darf maximal :value Kilobytes groß sein.",
        "numeric" => ":attribute muss kleiner oder gleich :value sein.",
        "string"  => ":attribute darf maximal :value Buchstaben lang sein."
    ],
    "max"                  => [
        "array"   => ":attribute darf nicht mehr als :max Einträge enthalten.",
        "file"    => ":attribute darf nicht grö&szlig;er als :max Kilobytes sein.",
        "numeric" => ":attribute darf nicht grö&szlig;er als :max sein.",
        "string"  => ":attribute darf nicht länger als :max Zeichen sein."
    ],
    "mimes"                => ":attribute muss eine Datei in folgendem Format sein: :values.",
    "mimetypes"            => ":attribute muss eine Datei in folgendem Format sein: :values.",
    "min"                  => [
        "array"   => ":attribute muss mindestens :min Einträge haben..",
        "file"    => ":attribute muss mindestens :min Kilobytes gro&szlig; sein.",
        "numeric" => ":attribute muss mindestens :min sein.",
        "string"  => ":attribute muss mindestens :min Zeichen lang sein."
    ],
    "not_in"               => "Ausgewählte(s) :attribute ist inkorrekt.",
    "not_regex"            => ":attribute ist inkorrekt.",
    "numeric"              => ":attribute muss eine Zahl sein.",
    "present"              => ":attribute muss vorhanden sein.",
    "regex"                => "Das :attribute-Format ist inkorrekt.",
    "required"             => ":attribute field wird benötigt.",
    "required_if"          => ":attribute field wird benötigt wenn :other einen Wert von :value hat.",
    "required_unless"      => ":attribute field wird benötigt au&szlig;er :other ist in den Werten :values enthalten.",
    "required_with"        => ":attribute field wird benötigt wenn :values vorhanden ist.",
    "required_with_all"    => ":attribute field wird benötigt wenn :values vorhanden ist.",
    "required_without"     => ":attribute field wird benötigt wenn :values nicht vorhanden ist.",
    "required_without_all" => ":attribute field wird benötigt wenn keine der Werte :values vorhanden ist.",
    "same"                 => ":attribute und :other müssen gleich sein.",
    "size"                 => [
        "array"   => ":attribute muss :size Einträge enthalten.",
        "file"    => ":attribute muss :size Kilobytes gro&szlig; sein.",
        "numeric" => ":attribute muss :size gro&szlig; sein.",
        "string"  => ":attribute muss :size Zeichen lang sein."
    ],
    "starts_with"          => ":attribute darf nicht mit einem dieser Werte anfangen: :values",
    "string"               => ":attribute muss Text sein.",
    "timezone"             => ":attribute muss eine korrekte Zeitzone sein.",
    "unique"               => ":attribute wurde bereits verwendet.",
    "uploaded"             => "Der Upload von :attribute schlug fehl.",
    "url"                  => "Das :attribute-Format ist inkorrekt.",
    "uuid"                 => ":attribute muss eine gültige UUID sein."
];

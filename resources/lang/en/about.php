<?php
return [
    "basepoints" => "Base points",
    "block1" => "Träwelling is a free check-in service that lets you tell your friends where you are and where you can log your public transit journeys. In short, you can check into trains and get points for it.",
    // Wir möchten, dass Du möglichst zeitnah eincheckst, damit die Plattform immer einen aktuellen Stand Deiner Reisen hat. Darum gibt es die vollen Punkte nur, wenn Du während Deiner Reise oder 20min vor Live-Abfahrt (Plan-Abfahrt + Verspätung) eincheckst. Wer 1h vor oder nach dem Reisezeitraum einsteigt, bekommt immerhin noch 1/4 der Punkte. Wenn Du noch früher oder später in eine Verbindung eincheckst, bekommst Du die vollen Kilometer und Stunden gutgeschrieben, aber nur einen Mitleidspunkt.
    "calculation" => "The distance is rounded to the nearest 10km and then divided by 10. Afterwards the base points are added.<br/>10 + round up(143/10) = 10 + 15 = 25</code> &nbsp;points, for a S-Bahn trip of 8km there is <code>2 + round up(8/10) = 2 + 1 = 3</code>&nbsp;points.<br />We want you to keep Träwelling on track with your journeys. For that reason, you only receive the whole amount of points, if you check-in during the journey or 20min before you leave (scheduled departure and live-delay, according to Deutsche Bahn). When you check-in within 1 hour before or after the itinerary, you can still get a quarter of the points. However, if you check-in even earlier or later, you will only get one compassion point.",
    "express" => "InterCity, EuroCity",
    "faq-heading" => "Frequently asked questions",
    "feature-missing" => "In this version of Träwelling we'll start from scratch – and may have missed little-used functions. If you would like to suggest a feature, just send an email to",
    "feature-missing-heading" => "Something's missing! Why was this feature removed?",
    "heading" => "About us",
    "international" => "InterCityExpress, TGV, RailJet",
    "name" => "The name is an allusion to the well-known <i>\"Senk ju for träwelling wis Deutsche Bahn\"</i>, which you should have heard in almost every long-distance train of the Deutsche Bahn.",
    "name-heading" => "Where does the name come from?",
    "no-train" => "We use an interface of the Deutsche Bahn, where not all offers are displayed directly. Unfortunately, we can't do much with it if your train is not there.",
    "no-train-heading" => "Why isn't my train listed?",
    "points-heading" => "How are points calculated?",
    "points1" => "The points consist of the product class and the distance of your journey.",
    "productclass" => "product category",
    "regional" => "Regional train/-express",
    "suburban" => "S-Bahn, Ferry",
    "tram" => "Tram / light rail, bus, subway",
    "who" => "Since 2013, Träwelling has been developed by ever different people. Partly only bugfixes, partly bigger changes. You can find more information in the",
    "who-heading" => "Who develops Träwelling?"
];

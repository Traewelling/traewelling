<?php

use App\Enum\HafasTravelType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void {
        foreach (self::$migrationData as $key => $value) {
            DB::table('hafas_trips')
              ->where('category', $key)
              ->update(['category' => $value]);
        }
    }

    public function down(): void {
        foreach (self::$migrationData as $key => $value) {
            DB::table('hafas_trips')
              ->where('created_at', '<', '2019-11-24 12:53:35')
              ->where('category', $value)
              ->update(['category' => $key]);
        }
    }

    private static array $migrationData = [
        "ATR"    => HafasTravelType::NATIONAL_EXPRESS, //Altaria
        "AVE"    => HafasTravelType::NATIONAL_EXPRESS, //Alta Velocidad ES
        "BAT"    => HafasTravelType::FERRY, //Schiff
        "BAV"    => HafasTravelType::FERRY, //Dampfschiff
        "BUS"    => HafasTravelType::BUS,
        "CNL"    => HafasTravelType::NATIONAL, // DB City Night Line, discontinued
        "D"      => HafasTravelType::NATIONAL_EXPRESS, //Schnellzug
        "E"      => HafasTravelType::NATIONAL_EXPRESS, //Eilzug
        "EC"     => HafasTravelType::NATIONAL, // EuroCity
        "EM"     => HafasTravelType::NATIONAL_EXPRESS, //Euromed
        "EN"     => HafasTravelType::NATIONAL, // EuroNight
        "ES"     => HafasTravelType::NATIONAL_EXPRESS, //Eurostar Italia
        "EST"    => HafasTravelType::NATIONAL_EXPRESS, //Eurostar
        "EXT"    => HafasTravelType::NATIONAL, //Extrazug
        "FAE"    => HafasTravelType::FERRY, //Fähre
        "FixBus" => HafasTravelType::NATIONAL,
        "FUN"    => HafasTravelType::REGIONAL, //Standseilbahn
        "GEX"    => HafasTravelType::REGIONAL_EXP, //Glacier Express
        "IC"     => HafasTravelType::NATIONAL_EXPRESS,
        "ICB"    => HafasTravelType::NATIONAL, //InterCity-Bus
        "ICE"    => HafasTravelType::NATIONAL_EXPRESS,
        "ICN"    => HafasTravelType::NATIONAL, //InterCity Neigezug
        "IN"     => HafasTravelType::NATIONAL, //InterCityNacht
        "IR"     => HafasTravelType::REGIONAL, //InterRegio
        "IRE"    => HafasTravelType::NATIONAL_EXPRESS,
        "KAT"    => HafasTravelType::FERRY, //Katamaran
        "M"      => HafasTravelType::TRAM, //Metro
        "NFB"    => HafasTravelType::BUS, //Niederflurbus
        "NFO"    => HafasTravelType::BUS, //Niederflur-Trolleybus
        "NFT"    => HafasTravelType::TRAM, //Niedeflur-Tram
        "NJ"     => HafasTravelType::NATIONAL,
        "NZ"     => HafasTravelType::NATIONAL, //Nacht-Zug
        "OIC"    => HafasTravelType::NATIONAL, //ÖBB InterCity
        "PE"     => HafasTravelType::REGIONAL,
        "R"      => HafasTravelType::NATIONAL,
        "RB"     => HafasTravelType::REGIONAL,
        "RE"     => HafasTravelType::REGIONAL_EXP,
        "RJ"     => HafasTravelType::NATIONAL, // OeBB RailJet
        "RJX"    => HafasTravelType::NATIONAL_EXPRESS, // OeBB Railjet Express
        "S"      => HafasTravelType::REGIONAL,
        "S12"    => HafasTravelType::REGIONAL,
        "S23"    => HafasTravelType::REGIONAL,
        "S27"    => HafasTravelType::REGIONAL,
        "S3"     => HafasTravelType::REGIONAL,
        "S5"     => HafasTravelType::REGIONAL,
        "S6"     => HafasTravelType::REGIONAL,
        "S8"     => HafasTravelType::REGIONAL,
        "T"      => HafasTravelType::TRAM,
        "TAL"    => HafasTravelType::REGIONAL, //Talgo
        "TER"    => HafasTravelType::REGIONAL, //TER200
        "TGV"    => HafasTravelType::NATIONAL_EXPRESS, // SNCF TGV
        "THA"    => HafasTravelType::NATIONAL_EXPRESS, // Thalys
        "TLK"    => HafasTravelType::NATIONAL, //Twoje Linie Kolejowe
        "VAE"    => HafasTravelType::REGIONAL, //Voralpen-Express
        "WB"     => HafasTravelType::REGIONAL, //Westbahn
        "X2"     => HafasTravelType::NATIONAL_EXPRESS, //X2000 Neigezug
    ];
};

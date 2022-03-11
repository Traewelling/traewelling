<?php

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
        "ATR"    => "",
        "AVE"    => "",
        "BAT"    => "",
        "BAV"    => "",
        "BUS"    => "suburban",
        "CNL"    => "national", // DB City Night Line, discontinued
        "D"      => "",
        "E"      => "",
        "EC"     => "national", // EuroCity
        "EM"     => "",
        "EN"     => "national", // EuroNight
        "ES"     => "",
        "EST"    => "",
        "EXT"    => "",
        "FAE"    => "",
        "FixBus" => "national",
        "FUN"    => "",
        "GEX"    => "",
        "IC"     => "nationalExpress",
        "ICB"    => "",
        "ICE"    => "nationalExpress",
        "ICN"    => "",
        "IN"     => "",
        "IR"     => "",
        "IRE"    => "nationalExpress",
        "KAT"    => "",
        "M"      => "",
        "NFB"    => "",
        "NFO"    => "",
        "NFT"    => "",
        "NJ"     => "national",
        "NZ"     => "",
        "OIC"    => "",
        "PE"     => "",
        "R"      => "national",
        "RB"     => "regional",
        "RE"     => "regionalExp",
        "RJ"     => "national", // OeBB RailJet
        "RJX"    => "nationalExpress", // OeBB Railjet Express
        "S"      => "regional",
        "S12"    => "regional",
        "S23"    => "regional",
        "S27"    => "regional",
        "S3"     => "regional",
        "S5"     => "regional",
        "S6"     => "regional",
        "S8"     => "regional",
        "T"      => "tram",
        "TAL"    => "",
        "TER"    => "",
        "TGV"    => "nationalExpress", // SNCF TGV
        "THA"    => "nationalExpress", // Thalys
        "TLK"    => "",
        "VAE"    => "",
        "WB"     => "",
        "X2"     => "",
    ];
};

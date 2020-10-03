<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PointsCalculation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pointscalculation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('transport_type')->nullable();
            $table->integer('value');
            $table->timestamps();
        });

        DB::table('pointscalculation')->insert([
            ['type' => 'train', 'transport_type' => 'tram', 'value' => '2'],
            ['type' => 'train', 'transport_type' => 'bus', 'value' => '2'],
            ['type' => 'train', 'transport_type' => 'subway', 'value' => '2'],
            ['type' => 'train', 'transport_type' => 'suburban', 'value' => '3'],
            ['type' => 'train', 'transport_type' => 'ferry', 'value' => '3'],
            ['type' => 'train', 'transport_type' => 'regional', 'value' => '5'],
            ['type' => 'train', 'transport_type' => 'regionalExp', 'value' => '6'],
            ['type' => 'train', 'transport_type' => 'express', 'value' => '10'],
            ['type' => 'train', 'transport_type' => 'national', 'value' => '10'],
            ['type' => 'train', 'transport_type' => 'nationalExpress', 'value' => '10'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pointscalculation');
    }
}

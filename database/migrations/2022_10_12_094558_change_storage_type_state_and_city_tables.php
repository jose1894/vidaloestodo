<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStorageTypeStateAndCityTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        \DB::statement('ALTER TABLE states ENGINE = InnoDB');
        \DB::statement('ALTER TABLE cities ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        \DB::statement('ALTER TABLE states ENGINE = MyISAM');
        \DB::statement('ALTER TABLE cities ENGINE = MyISAM');
    }
}

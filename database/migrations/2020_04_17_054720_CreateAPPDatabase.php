<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAPPDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('APP_Items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('APP_DBM_ID');
            $table->string('Slug');
            $table->longText('Item');
            $table->string('Unit_of_measure');
            $table->string('Type');
            $table->string('Part');
            $table->double('Price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('APP_Fixed');
    }
}

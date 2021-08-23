<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWfpActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wfp_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('devliverable_id');     //Deliverable table
            $table->mediumText('activities');       //iclinicsys training
            $table->string('timeframe');            //January to December
            $table->string('q1');                   //Quarter 1 amount
            $table->string('q2');                   //Quarter 2 amount
            $table->string('q3');                   //Quarter 3 amount
            $table->string('q4');                   //Quarter 4 amount
            $table->string('item')->default('N/A');                 //
            $table->double('cost',10 ,2);
            $table->integer('annual_budget_program_id');       //PHM, SDRS, Unfunded
            $table->integer('program_id');
            $table->integer('section_id');          //section ID
            $table->integer('division_id');         //division ID
            $table->integer('responsible_person');  //user ID
            $table->string('resp_person');
            $table->integer('year');  //year
            $table->longText('comment')->default('-')->nullable();
            $table->string('status')->default('pending');
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
        Schema::dropIfExists('wfp_activities');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePpmpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppmp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('wfp_id');
            $table->mediumText('general_description');
            $table->integer('qty');
            $table->string('unit');
            $table->string('abc');
            $table->double('estimated_budget');
            $table->string('MOP'); //Mode of Procurement (Bidding/Negotiated Procurement Specify/ Shopping / Direct Contracting)
            $table->string('milestones1')->default(' ');
            $table->string('milestones2')->default(' ');
            $table->string('milestones3')->default(' ');
            $table->string('milestones4')->default(' ');
            $table->string('milestones5')->default(' ');
            $table->string('milestones6')->default(' ');
            $table->string('milestones7')->default(' ');
            $table->string('milestones8')->default(' ');
            $table->string('milestones9')->default(' ');
            $table->string('milestones10')->default(' ');
            $table->string('milestones11')->default(' ');
            $table->string('milestones12')->default(' ');
            $table->integer('person_responsible');
            $table->longText('comment')->nullable();
            $table->string('status')->default('pending');
            $table->string('bidderWinner')->default('none');
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
        Schema::dropIfExists('ppmp');
    }
}

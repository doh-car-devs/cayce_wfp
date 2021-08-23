<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnualBudgetProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('annual_budget_programs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('annual_budget_id');
            // $table->integer('fund_source_id');
            $table->double('allocatedNEP');
            $table->double('allocatedAmount')->default(NULL)->nullable();
            $table->smallInteger('program_id')->default(99);
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
        Schema::dropIfExists('annual_budget_programs');
    }
}

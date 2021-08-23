<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnualBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('annual_budgets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('year');
            $table->integer('fund_source_id');
            $table->double('NEP');
            $table->double('amount')->default(NULL)->nullable();
            // $table->smallInteger('division')->default(99);
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
        Schema::dropIfExists('annual_budgets');
    }
}

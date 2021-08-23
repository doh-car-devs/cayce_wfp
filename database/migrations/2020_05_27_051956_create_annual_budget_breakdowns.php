<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnualBudgetBreakdowns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('annual_budget_breakdowns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('annual_budgets_id');
            $table->text('description')->nullable();
            $table->bigInteger('account_code')->nullable();
            $table->double('amount')->nullable();
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
        Schema::dropIfExists('fund_source_breakdowns');
    }
}

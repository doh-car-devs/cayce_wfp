<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Budget extends Model
{
    function DivisionBudgetAllocation($year, $section_id)
    {
        // previously annualPrograms
        $data = DB::table('annual_budget_programs')
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')   
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('annual_budgets.year', '=', $year)
            ->where('interfacev7.programs.section_id', '=', $section_id)
            ->where('annual_budget_programs.program_id', '!=', 99)
            ->get();
        return $data;
    }
}

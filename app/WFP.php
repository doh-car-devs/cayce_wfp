<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WFP extends Model
{
    function totalGAASAA($section_id, $year, $type)
    {
        if ($type == 'GAA')
            $sum = 'annual_budget_programs.allocatedAmount';
        if ($type == 'SAA')
            $sum = 'wfp_activities.cost';
        $data = DB::table('wfp_activities')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('section_id', '=', $section_id)
            ->where('fund_source_parents.parent_type_abbr', '=', $type)
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
            // ->sum($sum);
            ->get();
        return $data;
    }

    function printWFP($user, $flash, $selected)
    {
        // $selected = [0 => 46];
        $data = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.devliverable_id as wfp_id', 'wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->whereIn('wfp_activities.id', $selected)
            // ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
            // ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
            ->orderBy('function_type','ASC')
        ->get();
        return $data;
    }

    function printallWFP($user, $flash, $selected)
    {
        // $selected = [0 => 46];
        $data = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.devliverable_id as wfp_id', 'wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            // ->whereIn('wfp_activities.id', $selected)
            ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
            ->orderBy('function_type','ASC')
        ->get();
        return $data;
    }
    function printallDivisionWFP($user, $flash, $selected)
    {
        // $selected = [0 => 46];
        $data = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.devliverable_id as wfp_id', 'wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            // ->whereIn('wfp_activities.id', $selected)
            ->where('wfp_activities.division_id', '=', $user['division_id'])
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
            ->orderBy('function_type','ASC')
        ->get();
        return $data;
    }

    function masterWFP( $flash)
    {
        $data = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.devliverable_id as wfp_id', 'wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            // ->where('wfp_activities.section_id', '=', $user['section_id'])
            // ->where('wfp_activities.division_id', '=', 2)
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
            ->orderBy('function_type','ASC')
        ->get();
        return $data;
    }
}

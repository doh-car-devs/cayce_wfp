<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PPMP extends Model
{
    function allPPMP($section_id, $division_id, $year, $access_group, $remove = null)
    {

        if ($section_id == 8 && $division_id == 2 && $access_group == 97) {
            $data = DB::table('ppmp')
                ->select('wfp_activities.status as WFP_TABLE_STATUS','wfp_activities.id as origwfp_id','wfp_activities.devliverable_id as devliv_id','ppmp.created_at as ppmp_make_date', 'ppmp.updated_at as ppmp_update_date','ppmp.comment as ppmp_comment','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
                ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
                ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
                ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
                ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
                ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
                ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
                ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
                ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
                ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
                ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
                // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                // ->where('wfp_activities.status', '=', 'deleted')
                ->where('wfp_activities.year', '=', $year)
            ->get();
        }else{
            if ($remove == null) {
                $remove = [0];
            }
            $data = DB::table('ppmp')
                ->select('wfp_activities.status as WFP_TABLE_STATUS','wfp_activities.id as origwfp_id','wfp_activities.devliverable_id as devliv_id','ppmp.created_at as ppmp_make_date', 'ppmp.updated_at as ppmp_update_date','ppmp.comment as ppmp_comment','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
                ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
                ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
                ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
                ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
                ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
                ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
                ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
                ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
                ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
                ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
                // ->whereNotIn('ppmp.id', $remove)
                ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                // ->where('wfp_activities.status', '=', 'deleted')
                ->where('wfp_activities.year', '=', $year)
            ->get();
        }
        return $data;
    }

    function POItem($year, $bidder_id, $selected = null)
    {
        $data = DB::table('ppmp')
            ->select('procurement_modes.mode as MOP_mode','bids.*','bidders.*','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
            ->join('procurement_modes', 'ppmp.MOP', 'procurement_modes.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->join('bids', 'ppmp_items.item_id', 'bids.item_id')
            ->join('bidders', 'bids.bidder_id', 'bidders.id')
            // ->join('procurement_modes', 'ppmp.MOP', 'procurement_modes.id')
            // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
            ->where('bidders.id', $bidder_id)
            ->where('ppmp.bidderWinner', '<>', 'none')
            ->where('ppmp.status', '<>', 'deleted')
            // ->whereIn('ppmp.id', $selected)
            ->where('wfp_activities.year', '=', $year)
        ->get();
        return $data;
    }

    function PrintPOItem($year, $bidder_id, $selected = null)
    {
        $data = DB::table('ppmp')
            ->select('procurement_modes.mode as MOP_mode','bids.*','bidders.*','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
            ->join('procurement_modes', 'ppmp.MOP', 'procurement_modes.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->join('bids', 'ppmp_items.item_id', 'bids.item_id')
            ->join('bidders', 'bids.bidder_id', 'bidders.id')
            // ->join('procurement_modes', 'ppmp.MOP', 'procurement_modes.id')
            // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
            ->where('bidders.id', $bidder_id)
            ->where('ppmp.bidderWinner', '<>', 'none')
            ->where('ppmp.status', '<>', 'deleted')
            ->whereIn('ppmp.id', $selected)
            ->where('wfp_activities.year', '=', $year)
        ->get();
        return $data;
    }

    function printPR($section_id, $division_id, $year, $selected)
    {
        $data = DB::table('ppmp')
            ->select('ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmp_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
            ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->whereIn('ppmp.id', $selected)
            ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
            ->where('ppmp.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
        ->get();
        return $data;
    }

    function printConsolidatedPPMP($flash)
    {
        $data = DB::table('ppmp')
            ->select('ppmp.comment as ppmp_comment','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->Join('interfacev7.sections', 'programs.section_id', '=', 'interfacev7.sections.id')
            ->Join('interfacev7.divisions', 'sections.division_id', '=', 'interfacev7.divisions.id')
            ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
            // ->whereIn('ppmp.id', $selected)
            ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->where('ppmp.status', '<>', 'deleted')
            // ->where('interfacev7.divisions.id', '<>', '')
            ->where('wfp_activities.year', '=', $flash)
        ->get();
        return $data;
    }

    function printPPMP($user, $flash, $selected)
    {
        $data = DB::table('ppmp')
            ->select('ppmp.comment as ppmp_comment','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
            ->whereIn('ppmp.id', $selected)
            ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->where('ppmp.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
        ->get();
        return $data;
    }

    // function peekPPMP($section_id, $division_id, $year, $access_group, $remove = null)
    // {
    //     $data = DB::table('ppmp')
    //     ->select('ppmp.comment as ppmp_comment','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
    //     ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
    //     ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
    //     ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
    //     ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
    //     ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
    //     ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
    //     ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
    //     ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
    //     ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
    //     ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
    //     ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
    //     // ->whereNotIn('ppmp.id', $remove)
    //     ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
    //     ->where('ppmp.status', '<>', 'deleted')
    //     // ->where('wfp_activities.status', '=', 'deleted')
    //     ->where('wfp_activities.year', '=', $year)
    //     ->get();

    //     return $data;
    // }
}

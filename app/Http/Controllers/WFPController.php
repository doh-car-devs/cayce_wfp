<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Wfp_activity;

use App\WFP;
use App\PPMP;
use App\Budget;

class WFPController extends Controller
{
    /**
     * Class constructor.
     */
    private $Budget;
    private $WFP;
    private $PPMP;
    public function __construct()
    {
        $this->WFP = new WFP();
        $this->Budget = new Budget();
        $this->PPMP = new PPMP();
    }

    function createWFP(Request $request)
    {
        // check for input for export
        // return $inputExport;
        // if($inputExport !== null){
        //     return 'there is inputExport';
        // }

        $user = json_decode($request->header('LOGGED_USER'), true);
        // return $user->id;
        $flash = $request->header('FLASH');
        $funds = DB::table('fund_sources')
            ->select('fund_sources.type_id','fund_sources.id AS fund_id','fund_sources.parent_id as parent_id','fund_sources.source_name','fund_sources.source_abbr', 'fund_source_parents.parent_type', 'fund_source_parents.parent_type_abbr', 'fund_source_types.type', 'fund_source_types.type_abbr')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->get();
        $procurementModes = DB::table('procurement_modes')
            ->select('id','mode')
            ->get();
        $parentFunds = DB::table('fund_source_parents')->select('parent_type_abbr AS parent_abbr', 'parent_type AS parent', 'id')->get();
        $typeFunds = DB::table('fund_source_types')->select('type_abbr AS type_abbr', 'type as type', 'id')->get();
        $users = DB::connection('mysql_2')->table('users')->get();
            // ->select('id', 'name', 'name_middle', 'name_family')
            // ->get();
        // $users = DB::select('select id, name, name_middle, name_family from users');

        (array) $programFunds = DB::connection('mysql')->table('annual_budget_programs')
            ->select('*', 'annual_budget_programs.id AS annual_budget_program_id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            // ->Join('interfacev7.divisions', 'interfacev7.programs.division_id', '=', 'interfacev7.divisions.id')
            // ->Join('interfacev7.sections', 'interfacev7.programs.section_id', '=', 'interfacev7.sections.id')
            // ->where($user['program_id'], '=', 'annual_budget_programs.program_id')
            ->where('division_id', '=', $user['division_id'])->where('section_id', '=', $user['section_id'])
            ->where('annual_budgets.year', '=', $flash)
            ->get();
        foreach ($programFunds as $key => $value) {
            $programFunds[$key]->total_cost = 0;
            // $programFunds[$key]->total_cost_ppmp = 0;
        }

        $months = config('global.globalMonthFull');
        $monthsShort = config('global.globalMonthShort');

        $lhsdsection = DB::connection('mysql_2')->table('programs')
            ->select('sections.section_name', 'sections.id', 'sections.section_abbr')
            ->Join('divisions', 'programs.division_id', '=', 'divisions.id')
            ->Join('sections', 'programs.section_id', '=', 'sections.id')
            ->distinct()->get();

        $program = DB::connection('mysql_2')->table('programs')

            ->select('programs.division_id','programs.section_id','program_name','sections.id AS sec_id', 'programs.id', 'program_abbr AS program', 'division_name', 'section_abbr', 'program_name AS description')
            ->Join('divisions', 'programs.division_id', '=', 'divisions.id')
            ->Join('sections', 'programs.section_id', '=', 'sections.id')
            ->where('divisions.division_name', '<>', 'DOH-CAR')
            ->orderBy('divisions.id', 'ASC')
            ->get();

        $wfpCategory = array(
            'A. Strategic Functions', 'B. Core Functions', 'C. Support Functions',
        );

        (array) $allwfp = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.id as wfp_id', 'wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
        ->get();

        foreach ($allwfp as $key => $value) {
            $allwfp[$key]->total_cost_ppmp = 0;
        }

        (array) $allApprovedWFP = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.id as origwfp_id','wfp_activities.devliverable_id as wfp_id','wfp_activities.devliverable_id as devliv_id', 'wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
            // ->where('wfp_activities.status', '=', 'dhApproved')
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
        ->get();

        $deletedWFPwithPPMP = DB::table('wfp_activities')
            ->select('wfp_activities.status as wfp_status', 'ppmp.status AS ppmp_status', 'ppmp.id as PPMP_ID', 'wfp_activities.id as WFP_ID', 'wfp_activities.devliverable_id as orig_deliverable', 'wfp_activities.*' )
            ->join('ppmp', 'wfp_activities.devliverable_id', '=', 'ppmp.wfp_id')
            ->where('wfp_activities.status', 'deleted')
            ->where('ppmp.status', '<>', 'deleted')
        ->get();

        $lockedWFPsinceThereisPPMP = DB::table('wfp_activities')
            ->select('wfp_activities.status as wfp_status', 'ppmp.status AS ppmp_status', 'ppmp.id as PPMP_ID', 'wfp_activities.id as WFP_ID', 'wfp_activities.devliverable_id as orig_deliverable', 'wfp_activities.*' )
            ->join('ppmp', 'wfp_activities.devliverable_id', '=', 'ppmp.wfp_id')
            ->where('ppmp.status', '<>', 'deleted')
            ->where('wfp_activities.status', '<>', 'deleted')
        ->get();

        foreach ($allApprovedWFP as $key => $value) {
            // foreach($deletedWFPwithPPMP as $kk => $vv){
            //     if ($value->devliv_id == $vv->orig_deliverable) {
            //         $value->OTF_status = 'Deleted WFP WITH PPMP';
            //     }
            // }

            foreach($lockedWFPsinceThereisPPMP as $kk => $vv){
                if ($value->devliv_id == $vv->orig_deliverable) {
                    $value->OTF_status = 'locked';
                }
            }
            $allApprovedWFP[$key]->total_cost_ppmp = 0;
        }

        // START Inserting remaining budget for allocated budget
        $mergeval = [];
        $totalperBudget = [];
        foreach ($allwfp as $key => $value) {
            $mergeval[$key]['id'] = $value->annual_budget_program_id;
            $mergeval[$key]['NEP'] = $value->NEP;
            $mergeval[$key]['amount'] = $value->amount;
            $mergeval[$key]['cost'] = $value->cost;

            $mergeval[$key]['parent_type_abbr'] = $value->parent_type_abbr;
            $mergeval[$key]['source_abbr'] = $value->source_abbr;
            $mergeval[$key]['type_abbr'] = $value->type_abbr;
        }

        $uniqueID = array_unique(array_column($mergeval, 'id'));
        $newval = [];
        $uniqueID = array_values($uniqueID);
        foreach($mergeval as $key => $value) {
            foreach($uniqueID as $key2 => $value2) {
                if ($value['id'] == $value2) {
                    $newval[$value2][$key] = $value['cost'];
                }
            }
        }
        foreach ($newval as $key => $value) {
            $totalperBudget[$key] = array_sum($value);
        }
        foreach ($programFunds as $key => $value) {
            foreach ($totalperBudget as $key2 => $value2) {
                if ($value->annual_budget_program_id == $key2) {
                    $programFunds[$key]->total_cost = $value2;
                }
            }
        }
        // END Inserting remaining budget for allocated budget

        $allppmp = $this->PPMP->allPPMP($user['section_id'], $user['division_id'], $flash, $user['access_group']);
        // $mergevalppmp = [];
        // $totalperBudgetppmp = [];
        // foreach ($allppmp as $key => $value) {
        //     $mergevalppmp[$key]['id'] = $value->annual_budget_program_id;
        //     $mergevalppmp[$key]['NEP'] = $value->NEP;
        //     $mergevalppmp[$key]['amount'] = $value->amount;
        //     $mergevalppmp[$key]['cost'] = $value->cost;

        //     $mergevalppmp[$key]['parent_type_abbr'] = $value->parent_type_abbr;
        //     $mergevalppmp[$key]['source_abbr'] = $value->source_abbr;
        //     $mergevalppmp[$key]['type_abbr'] = $value->type_abbr;
        // }

        // START
        $mergevalppmp = [];
        $totalperBudgetppmp = [];
        foreach ($allppmp as $key => $value) {
            // foreach ($allApprovedWFP as $key => $value) {
                foreach($deletedWFPwithPPMP as $kk => $vv){
                    if ($value->devliv_id == $vv->orig_deliverable) {
                    // if ($value->wfp_id == $vv->WFP_ID) {
                        $value->OTF_status = 'Deleted WFP WITH PPMP';
                    }
                }
                // $allApprovedWFP[$key]->total_cost_ppmp = 0;
            // }
            $mergevalppmp[$key]['id'] = $value->annual_budget_program_id;
            $mergevalppmp[$key]['wfp_id'] = $value->wfp_id;
            $mergevalppmp[$key]['cost'] = $value->estimated_budget;

            $mergevalppmp[$key]['parent_type_abbr'] = $value->parent_type_abbr;
            $mergevalppmp[$key]['source_abbr'] = $value->source_abbr;
            $mergevalppmp[$key]['type_abbr'] = $value->type_abbr;
        }

        $uniqueAnnBuProIDppmp = array_unique(array_column($mergevalppmp, 'id'));
        $uniquewfpID = array_unique(array_column($mergevalppmp, 'wfp_id'));
        $newvalppmp = [];
        $uniquewfpID = array_values($uniquewfpID);
        foreach($mergevalppmp as $key => $value) {
            foreach($uniquewfpID as $key2 => $value2) {
                if ($value['wfp_id'] == $value2) {
                    $newvalppmp[$value2][$key] = $value['cost'];
                }
            }
        }
        foreach ($newvalppmp as $key => $value) {
            $totalperBudgetppmp[$key] = array_sum($value);
        }
        foreach ($allwfp as $key => $value) {
            foreach ($totalperBudgetppmp as $key2 => $value2) {
                if ($value->wfp_id == $key2) {
                    $allwfp[$key]->total_cost_ppmp = $value2;
                }
            }
        }

        foreach ($allApprovedWFP as $key => $value) {
            foreach ($totalperBudgetppmp as $key2 => $value2) {
                if ($value->wfp_id == $key2) {
                    $allApprovedWFP[$key]->total_cost_ppmp = $value2;
                }
            }
        }
        // END
                // sdfsdfsdsdf
                        // return response()->json([
                        //     'mergevalppmp' => $mergevalppmp,
                        //     'uniqueAnnBuProIDppmp' => $uniqueAnnBuProIDppmp,
                        //     'uniquewfpID' => $uniquewfpID,
                        //     'newvalppmp' => $newvalppmp,
                        //     'totalperBudgetppmp' => $totalperBudgetppmp,
                        // ], 200);
                // sdfsdfsdsdf

        $total = DB::table('wfp_activities')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('section_id', '=', $user['section_id'])->where('division_id', '=', $user['division_id'])
            ->where('fund_source_parents.parent_type_abbr', '=', 'SAA')
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
            ->sum('wfp_activities.cost');

        $totalAllocated = DB::table('annual_budget_programs')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.sections', 'interfacev7.programs.section_id', '=', 'interfacev7.sections.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('interfacev7.programs.division_id', '=', $user['division_id'])->where('interfacev7.programs.section_id', '=', $user['section_id'])
            ->where('fund_source_parents.parent_type_abbr', '=', 'SAA')
            ->where('annual_budgets.year', '=', $flash)
            ->sum('annual_budget_programs.allocatedAmount');

        $totalGAA = DB::table('wfp_activities')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('section_id', '=', $user['section_id'])->where('division_id', '=', $user['division_id'])
            ->where('fund_source_parents.parent_type_abbr', '=', 'GAA')
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
            ->sum('wfp_activities.cost');

        $totalAllocatedGAA = DB::table('annual_budget_programs')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.sections', 'interfacev7.programs.section_id', '=', 'interfacev7.sections.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('interfacev7.programs.division_id', '=', $user['division_id'])->where('interfacev7.programs.section_id', '=', $user['section_id'])
            ->where('fund_source_parents.parent_type_abbr', '=', 'GAA')
            ->where('annual_budgets.year', '=', $flash)
            // ->get();
            ->sum('annual_budget_programs.allocatedNEP');


        $totalGAAPPMP = DB::table('ppmp')
            ->select('ppmp.comment as ppmp_comment','ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)

            ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            // ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.sections', 'interfacev7.programs.section_id', '=', 'interfacev7.sections.id')
            ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
            // ->whereIn('ppmp.id', $selected)
            ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->where('wfp_activities.section_id', '=', $user['section_id'])
            ->where('wfp_activities.division_id', '=', $user['division_id'])
            ->where('fund_source_parents.parent_type_abbr', '=', 'GAA')
            ->where('ppmp.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $flash)
        ->sum('annual_budget_programs.allocatedNEP');

        return response()->json([

            'totalAllocatedGAA' => $totalAllocatedGAA,       //   Total of allocated by budget GAA
            'totalGAA' => $totalGAA,                         //   Total allocated by section   GAA
            'totalGAAPPMP' => $totalGAAPPMP,                         //   Total allocated by section   GAA
            'totalAllocated' => $totalAllocated,             //   Total of allocated by budget SAA
            'total' => $total,                               //   Total allocated by section   SAA
            // 'mergeval' => $mergeval,
            // 'newval' => $newval,
            'totalperBudget' => $totalperBudget,
            // 'mergevalppmp' => $mergevalppmp,
            // 'newvalppmp' => $newvalppmp,
            'totalperBudgetppmp' => $totalperBudgetppmp,
            'procurementModes' => $procurementModes,
            'typeFunds' => $typeFunds,
            'users' => $users,
            'lhsdsection' => $lhsdsection,
            'program' => $program,
            'funds' => $funds,
            'programFunds' => $programFunds,
            'parentFunds' => $parentFunds,
            'allwfp' => $allwfp,
            'allApprovedWFP' => $allApprovedWFP,
            'wfpCategory' => $wfpCategory,
            'allppmp' => $allppmp,
            'months' => $months,
            'monthsShort' => $monthsShort,
            'deletedWFPwithPPMP' => $deletedWFPwithPPMP,
            'lockedWFPsinceThereisPPMP' => $lockedWFPsinceThereisPPMP,
            'status' => 200
        ], 200);
    }

    function deliverableList(Request $request, $division_id, $section_id, $term)
    {
        $items = DB::table('deliverables')
            ->select('function as text', 'id as id')
            ->where('function', 'like', '%'.$term.'%')
            // ->where('division_id', '=', $division_id)
            // ->where('section_id', '=', $section_id)
            ->get();

        return response()->json([
            'items' => $items,
            'status' => 200
        ], 200);
    }

    function itemList(Request $request, $term)
    {

        $items = DB::table('app_items')
            ->select( DB::raw('CONCAT(item, " - (₱" ,Price, ")") AS text'),
                'id as id', 'Type as type', 'Price as price', 'Unit_of_measure as unit')
            ->where('item', 'like', '%'.$term.'%')
            // ->orWhere('Price', 'like', '%'.$term.'%')
            ->get();

        return response()->json([
            'items' => $items,
            'status' => 200
        ], 200);
    }

    function storeWFP(Request $request)
    {
        // return $request['request']['function_type'];
        $input = $request['form_data'];
        $input1 = array(
            'function' => $input['function'],
            'function_type' => $input['function_type'],
            'created_at' => \Carbon\Carbon::now(),
        );

        $input2 = array(
            'devliverable_id' => DB::getPdo()->lastInsertId(),
            'activities' => $input['activities'],
            'timeframe' => $input['timeframe'],
            'program_id' => $input['wfpProgram'],
            'q1' => $input['q1'],
            'q2' => $input['q2'],
            'q3' => $input['q3'],
            'q4' => $input['q4'],
            'item' => $input['item'],
            'cost' => $input['cost'],
            'responsible_person' => $input['responsible_person'],
            'annual_budget_program_id' => $input['fundSource_id'],
            'section_id' => $input['section_id'],
            'division_id' => $input['division_id'],
            'year' => $input['year'],
            'wfp_type' => $input['wfp_type'],
            'resp_person' => $input['resp_person'][0],
            'created_at' => \Carbon\Carbon::now(),
        );
        DB::beginTransaction();
        try {
            $input2['devliverable_id'] = DB::table('wfp_deliverable')->insertGetId($input1);
            DB::table('wfp_activities')->insert($input2);
            DB::commit();
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }



        // try {
        //     // DB::table('wfp_activities')->insert($input2);
        // } catch (\Illuminate\Database\QueryException  $e) {
        //     return response()->json([
        //         'error'=> $e->getMessage(),
        //         'status'=> 400
        //     ], 400);
        // }
        return response()->json([
            'success' => 'You have added '.$input1['function_type']. ' entry',
            'status' => 200
        ], 200);
    }
    function storeDeliverable(Request $request)
    {
        $input = $request['form_data'];
        $input1 = array(
            'function' => $input['function'],
            'division_id' => $input['division_id'],
            'section_id' => $input['section_id'],
        );

        DB::beginTransaction();
        try {
            // $input2['devliverable_id'] = DB::table('wfp_deliverable')->insertGetId($input1);
            DB::table('deliverables')->insert($input1);
            DB::commit();
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }

        return response()->json([
            'success' => 'You have added '.$input1['function']. ' Deliverable',
            'status' => 200
        ], 200);
    }

    function deleteWfp(Request $request, $id)
    {
        $input = array(
            'status' => 'deleted',
            'updated_at' => \Carbon\Carbon::now(),
        );

        try {
            DB::table('wfp_activities')
                ->where('devliverable_id', $id)
                ->update($input);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have deleted a WFP entry',
            'status' => 200
        ], 200);
    }

    function editWfp(Request $request)
    {
        $input = $request['form_data'];
        $input1 = array(
            'function' => $input['function'],
            'function_type' => $input['function_type'],
            // 'updated_at' => \Carbon\Carbon::now(),
        );
        $input2 = array(
            'activities' => $input['activities'],
            'timeframe' => $input['timeframe'],
            'status' => 'section-revised',
            'q1' => $input['q1'],
            'q2' => $input['q2'],
            'q3' => $input['q3'],
            'q4' => $input['q4'],
            'comment' => null,
            'item' => $input['item'],
            'cost' => $input['cost'],
            'responsible_person' => $input['responsible_person'],
            'annual_budget_program_id' => $input['fundSource_id'],
            'program_id' => $input['wfpProgram'],
            'section_id' => $input['section_id'],
            'division_id' => $input['division_id'],
            'year' => $input['selected-year2'],
            'resp_person' => $input['resp_person_edt'],
            'updated_at' => \Carbon\Carbon::now(),
        );
        DB::beginTransaction();
        try {
            DB::table('wfp_deliverable')
                ->where('id', $input['deliverable_id2'])
                ->update($input1);
            DB::table('wfp_activities')
                ->where('devliverable_id', $input['deliverable_id2'])
                ->update($input2);
            DB::commit();
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }


        // try {
        //     DB::table('wfp_activities')
        //         ->where('devliverable_id', $input['deliverable_id2'])
        //         ->update($input2);
        // } catch (\Illuminate\Database\QueryException  $e) {
        //     return response()->json([
        //         'error'=> $e->getMessage(),
        //         'status'=> 400
        //     ], 400);
        // }
        return response()->json([
            'success' => 'You have updated a WFP entry',
            'data' => $input,
            'data1' => $input1,
            'data2' => $input['deliverable_id2'],
            'status' => 200
        ], 200);
    }

    function divisionWFP(Request $request, $year, $division_id = null, $section_id = null, $program_id = null)
    {
        $user = json_decode($request->header('LOGGED_USER'), true);
        $flash = $request->header('FLASH');
        $flas2 = $request->header('FLASH2');
        $funds = DB::table('fund_sources')->get();

        $section = DB::connection('mysql_2')->table('divisions')
            ->join('sections', 'divisions.id', '=', 'sections.division_id')
            ->where('sections.division_id', '=', $user['division_id'])
            ->where('sections.section_abbr', '<>', 'Division Head')
            ->get();

        $wfpCategory = array(
            'A. Strategic Functions', 'B. Core Functions', 'C. Support Functions',
        );

        $program = DB::connection('mysql_2')->table('programs')
            ->select('sections.id AS sec_id', 'programs.id', 'program_abbr AS program', 'division_name', 'section_abbr', 'program_name AS description')
            ->Join('divisions', 'programs.division_id', '=', 'divisions.id')
            ->Join('sections', 'programs.section_id', '=', 'sections.id')
            ->get();

        $allwfp = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.id as origwfp_id','wfp_activities.devliverable_id as wfp_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $user['division_id'])
            ->where('wfp_activities.program_id', '=', $program_id)
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
            ->get();
        $allApprovedWFP = $allwfp;

        $divisionWFP = DB::connection('mysql')->table('wfp_activities')
            ->select('wfp_activities.id as origwfp_id','wfp_activities.devliverable_id as wfp_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            // ->where('wfp_activities.section_id', '=', $user['section_id'])
            ->where('wfp_activities.division_id', '=', $user['division_id'])
            // ->where('wfp_activities.program_id', '=', $program_id)
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
            ->get();

        // $allwfp = DB::table('wfp_activities')
        //     ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
        //     ->join('fund_sources', 'wfp_activities.annual_budget_program_id', '=', 'fund_sources.id')
        //     ->join('users', 'wfp_activities.resp_person', '=', 'users.id')
        //     ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $user['division_id'])
        //     ->where('wfp_activities.program_id', '=', $program_id)
        //     // ->where('wfp_activities.section_id', '=', $user->section)->where('wfp_activities.division_id', '=', $user->division)
        //     ->where('wfp_activities.status', '<>', 'deleted')
        //     ->where('wfp_activities.year', '=', $year)
        //     ->get();

        // $allppmp = DB::table('ppmp')
        //     ->select('ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.id AS original_ppmp_table_id','ppmp.status AS ppmp_status','wfp_activities.*','wfp_activities.status as status','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
        //     ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
        //     ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
        //     ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
        //     ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
        //     ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
        //     ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
        //     ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
        //     ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
        //     ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
        //     ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
        //     ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
        //     // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $user['division_id'])
        //     // ->where('wfp_activities.program_id', '=', $program_id)
        //     ->where('ppmp.status', '<>', 'deleted')
        //     ->where('wfp_activities.year', '=', $year)
        //     ->get();

            // $allppmp = DB::connection('mysql')->table('ppmp')
            //     ->select('*', 'ppmp.id AS ppmp_id','ppmp_items.item_name AS item_desc','ppmp.status as ppmp_status','ppmp.comment as ppmp_comment ',
            //         // DB::raw('(milestones1 + milestones2 + milestones3) * abc AS ppmpq1'),
            //         // DB::raw('(milestones4 + milestones5 + milestones6) * abc AS ppmpq2'),
            //         // DB::raw('(milestones7 + milestones8 + milestones9) * abc AS ppmpq3'),
            //         // DB::raw('(milestones10 + milestones11 + milestones12) * abc AS ppmpq4'),
            //         // DB::raw('milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12 AS qtyTotal'),
            //         // DB::raw('(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc AS qtyxabc'),
            //     )
            //     ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            //     ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
            //     ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            //     ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            //     ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            //     ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            //     ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            //     ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            //     ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            //     ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            //     ->join('procurement_modes', 'ppmp.MOP', 'procurement_modes.id')
            //     // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
            //     ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $user['division_id'])
            //     ->where('wfp_activities.program_id', '=', $program_id)
            //     ->where('ppmp.status', '<>', 'deleted')
            //     ->where('wfp_activities.year', '=', $year)
            //     ->get();
            $allppmp = DB::table('ppmp')
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
                ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
                ->join('interfacev7.users', 'wfp_activities.resp_person', 'interfacev7.users.id')
                ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $user['division_id'])
                ->where('wfp_activities.program_id', '=', $program_id)
                // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
            ->get();

        $done = DB::table('wfp_activities')
            ->where('section_id', '=', $section_id)->where('division_id', '=', $user['division_id'])
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
            ->where(function ($query){
                $query->where('status', '=', 'section-revised')
                    ->orWhere('status', '=', 'dhComment-pending')
                    ->orWhere('status', '=', 'pending');
            })
            ->count();

        if ($done == 0) {
            $done = true;
        }else {
            $done = false;
        }

        $total = DB::table('wfp_activities')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.sections', 'interfacev7.programs.section_id', '=', 'interfacev7.sections.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            // ->where('section_id', '=', $section_id)->where('division_id', '=', $user['division_id'])
            ->where('interfacev7.programs.division_id', '=', $user['division_id'])->where('interfacev7.programs.section_id', '=', $section_id)
            ->where('fund_source_parents.parent_type_abbr', '=', 'SAA')
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
            ->sum('wfp_activities.cost');

        $totalAllocated = DB::table('annual_budget_programs')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.sections', 'interfacev7.programs.section_id', '=', 'interfacev7.sections.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('interfacev7.programs.division_id', '=', $user['division_id'])->where('interfacev7.programs.section_id', '=', $section_id)
            ->where('fund_source_parents.parent_type_abbr', '=', 'SAA')
            ->where('annual_budgets.year', '=', $year)
            ->sum('annual_budget_programs.allocatedAmount');
// ->select('wfp_activities.cost as wfpCost', 'wfp_activities.annual_budget_program_id AS wfp_budget_id')
// ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', 'annual_budget_programs.id')
// ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
// ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
// ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
// ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
// ->where('fund_source_parents.parent_type_abbr', '=', 'SAA')
// ->where('wfp_activities.status', '<>', 'deleted')
// ->where('wfp_activities.year', '=', $flash)
// ->sum('annual_budget_programs.allocatedAmount');

        $totalGAA = DB::table('wfp_activities')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('section_id', '=', $section_id)->where('division_id', '=', $user['division_id'])
            ->where('fund_source_parents.parent_type_abbr', '=', 'GAA')
            ->where('wfp_activities.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
            ->sum('wfp_activities.cost');

        $totalAllocatedGAA = DB::table('annual_budget_programs')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.sections', 'interfacev7.programs.section_id', '=', 'interfacev7.sections.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('interfacev7.programs.division_id', '=', $user['division_id'])->where('interfacev7.programs.section_id', '=', $section_id)
            ->where('fund_source_parents.parent_type_abbr', '=', 'GAA')
            ->where('annual_budgets.year', '=', $year)
            // ->select('wfp_activities.cost as wfpCost', 'wfp_activities.annual_budget_program_id AS wfp_budget_id')
            // ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', 'annual_budget_programs.id')
            // ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
            // ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            // ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            // ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            // // ->where('section_id', '=', $section_id)->where('division_id', '=', $user['division_id'])
            // ->where('fund_source_parents.parent_type_abbr', '=', 'GAA')
            // ->where('wfp_activities.status', '<>', 'deleted')
            // ->where('wfp_activities.year', '=', $flash)
            ->sum('annual_budget_programs.allocatedNEP');

        $data = array(
            'allApprovedWFP' => $allApprovedWFP,
            'totalAllocatedGAA' => $totalAllocatedGAA,
            'totalGAA' => $totalGAA,
            'totalAllocated' => $totalAllocated,
            'total' => $total,
            'user' => $user,
            'funds' => $funds,
            'wfpCategory' => $wfpCategory,
            'allwfp' => $allwfp,
            'divisionWFP' => $divisionWFP,
            'allppmp' => $allppmp,
            'months' => config('global.globalMonthFull'),
            'monthsShort' => config('global.globalMonthShort'),
            'section' => $section,
            'program' => $program,
            'status' => $done,
        );
        return $data;
    }
    function dhComment(Request $request)
    {
        $input = $request['form_data'];
        $input1 = array(
            'devliverable_id' => $input['comment_id'],
            'comment' => $input['chiefComment'],
            'status' => 'dhComment-pending',
        );
        try {
            DB::table('wfp_activities')
                ->where('devliverable_id', $input1['devliverable_id'])
                ->update($input1);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have placed a comment',
            'status' => 200
        ], 200);
    }

    function wfpApprove(Request $request)
    {
        $input = $request['form_data'];
        $input1 = array(
            'devliverable_id' => $input['approve_id'],
            'status' => 'dhApproved',
            'comment' => null,
        );
        try {
            DB::table('wfp_activities')
                ->where('devliverable_id', $input1['devliverable_id'])
                ->update($input1);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have approved WFP entry',
            'status' => 200
        ], 200);
    }

    function wfpApproveYear(Request $request)
    {
        $input = $request['form_data'];
        $section = $request->section;
        $year = $input['final_year_id'];
        $input1 = array(
            'comment' => null,
            'status' => 'dhApproved-forPPMP',
        );
        try {
            DB::table('wfp_activities')
                ->where('year', $year)
                ->where('section_id', $section)
                ->where('wfp_activities.status', '<>', 'deleted')
                ->update($input1);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have placed a comment',
            'status' => 200
        ], 200);
    }


    function planning(Request $request)
    {
        $ard = DB::table('divisions')
            ->join('section', 'divisions.id', '=', 'section.division_id')
            ->where('section.division_id', '=', 1)
            ->get();
        $msd = DB::table('divisions')
            ->join('section', 'divisions.id', '=', 'section.division_id')
            ->where('section.division_id', '=', 2)
            ->get();
        $lhsd = DB::table('divisions')
            ->join('section', 'divisions.id', '=', 'section.division_id')
            ->where('section.division_id', '=', 3)
            ->get();
        $rled = DB::table('divisions')
            ->join('section', 'divisions.id', '=', 'section.division_id')
            ->where('section.division_id', '=', 4)
            ->get();
    }

    function generateWFPReport(Request $request)
    {
        $selected = explode("yyy",$request->header('FLASH'));
        $myUser = explode("xx",$request->header('FLASH2'));
        $user = ['section_id' => $myUser[0], 'division_id' => $myUser[1]];
        $flash = 2021;
        (array) $allwfp = $this->WFP->printWFP($user, $flash, $selected);
        // $allwfp = 1;

        return response()->json([
            'data' => $selected,
            'user' => $user,
            'allwfp' => $allwfp,
            'status' => 200
        ], 200);

    }

    function generateConsolidatedWFPReport(Request $request)
    {
        $selected = explode("yyy",$request->header('FLASH'));
        $myUser = explode("xx",$request->header('FLASH2'));
        $user = ['section_id' => $myUser[0], 'division_id' => $myUser[1]];
        $flash = 2021;

        if ($user['section_id'] == 24 || $user['section_id'] == 25 || $user['section_id'] == 26 || $user['section_id'] == 27) {
            (array) $allwfp = $this->WFP->printallDivisionWFP($user, $flash, $selected);
        }else{
            (array) $allwfp = $this->WFP->printallWFP($user, $flash, $selected);
        }
        return response()->json([
            'data' => $selected,
            'user' => $user,
            'allwfp' => $allwfp,
            'status' => 200
        ], 200);

    }

    function generateMasterConsolidatedWFPReport(Request $request)
    {
        $selected = explode("yyy",$request->header('FLASH'));
        // $myUser = explode("xx",$request->header('FLASH2'));
        // $user = ['section_id' => $myUser[0], 'division_id' => $myUser[1]];
        $flash = 2021;
        (array) $allwfp = $this->WFP->masterWFP($flash);

        return response()->json([
            // 'data' => $selected,
            // 'user' => $user,
            'allwfp' => $allwfp,
            'status' => 200
        ], 200);

    }


    function peakPPMP($id){
        return ($id);
        $data = DB::connection('mysql')->table('ppmp')
            // $data = DB::connection('mysql')->table('wfp_activities')
            // ->select('wfp_activities.devliverable_id as wfp_id', 'wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
            // ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            // ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            // ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            // ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            // ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            // ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            // ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            // ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            ->whereIn('ppmp.wfp_id', $id)
            // ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
            // ->where('wfp_activities.status', '<>', 'deleted')
            // ->where('wfp_activities.year', '=', $flash)
            // ->orderBy('function_type','ASC')
        ->get();

        return $data;
    }
}

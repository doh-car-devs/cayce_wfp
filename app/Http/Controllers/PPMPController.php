<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

use App\PPMP;

class PPMPController extends Controller
{

    private $PPMP;

    public function __construct()
    {
        $this->PPMP = new PPMP();
    }
    function storePPMP(Request $request)
    {
        $input = $request['form_data'];
        $data = array(
            'wfp_id' => $input['wfp_id'],
            'general_description' => $input['branch'].'-'.$input['ppmp_genDesc'],
            'qty' => $input['ppmp_qty'],
            'abc' => $input['ppmp_abc'],
            'unit' => $input['ppmp_unit'],
            'estimated_budget' => $input['ppmp_estBudget'],
            'MOP' => $input['ppmp_mop'],
            'milestones1' => $input['milestones1'],
            'milestones2' => $input['milestones2'],
            'milestones3' => $input['milestones3'],
            'milestones4' => $input['milestones4'],
            'milestones5' => $input['milestones5'],
            'milestones6' => $input['milestones6'],
            'milestones7' => $input['milestones7'],
            'milestones8' => $input['milestones8'],
            'milestones9' => $input['milestones9'],
            'milestones10' => $input['milestones10'],
            'milestones11' => $input['milestones11'],
            'milestones12' => $input['milestones12'],
            'ppmp_type' => $input['ppmp_type'],

            'person_responsible' => $input['responsible_person'],
            'created_at' => Carbon::now(),
        );
        $data2 = array(
            'branch' => $input['branch'],
            'item_id' => $input['branch'].'-'.$input['ppmp_genDesc'],
            'price' => $input['price'],
            'unit' => $input['unit'],
            'item_name' => $input['item_name']
        );
        DB::beginTransaction();
        try {
            DB::table('ppmp')->insert($data);
            $data2['ppmp_id'] = DB::getPdo()->lastInsertId();
            DB::table('ppmp_items')->updateOrInsert($data2);
            DB::commit();
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            return response()->json([
                'error'=> $e->getMessage(),
                'data'=> $data,
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have added PPMP entry',
            'status' => 200
        ], 200);
    }
    public function editPpmp(Request $request, $id)
    {
        $input = $request['form_data'];
        $input = array(
            'wfp_id' => $input['wfp_id'],
            'general_description' => $input['ppmp_genDesc'],
            'qty' => $input['ppmp_qty'],
            'abc' => $input['ppmp_abc'],
            'unit' => $input['ppmp_unit'],
            'estimated_budget' => $input['ppmp_estBudget'],
            'MOP' => $input['ppmp_mop'],
            'milestones1' => $input['milestones1'],
            'milestones2' => $input['milestones2'],
            'milestones3' => $input['milestones3'],
            'milestones4' => $input['milestones4'],
            'milestones5' => $input['milestones5'],
            'milestones6' => $input['milestones6'],
            'milestones7' => $input['milestones7'],
            'milestones8' => $input['milestones8'],
            'milestones9' => $input['milestones9'],
            'milestones10' => $input['milestones10'],
            'milestones11' => $input['milestones11'],
            'milestones12' => $input['milestones12'],
            'updated_at' => Carbon::now(),
            // 'id' => $id,

            // 'person_responsible' => $request->input('person_responsible'),
        );
        try {
            DB::table('ppmp')
            ->where('id',$id)
            ->update($input);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'data'=> $input,
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have edited PPMP entry',
            'status' => 200
        ], 200);
    }
    public function deletePPMP(Request $request, $id)
    {
        $input = array(
            'status' => 'deleted',
            'updated_at' => \Carbon\Carbon::now(),
        );
        try {
            DB::table('ppmp')
                ->where('id', $id)
                ->update($input);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have deleted a PPMP entry',
            'status' => 200
        ], 200);
    }

    public function createapp(Request $request, $fundType = null, $year = null, $division_id = null, $section_id = null, $program_id = null)
    {
        // choose 99 if all fund source
        if ($fundType == null) {
            $fundType = 1;
        }
        if ($fundType == 99) {
            $fundType = [1,2,3,4,5,6,7];
        }

        $input = $request['form_data'];
        $user = json_decode($request->header('LOGGED_USER'), true);
        // $flash = $request->header('FLASH');
        $funds = DB::table('fund_sources')->get();
        $users = DB::connection('mysql_2')->table('ppmp')
            ->select('select id, name, name_middle, name_family from users');

        $wfpCategory = array(
            'A. Strategic Functions', 'B. Core Functions', 'C. Support Functions',
        );


        if ( $section_id !== null && $program_id !== null) {
            $app = DB::table('ppmp')
                ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit',
                'fund_source_parents.parent_type as Fund Source Type',
                    DB::raw('SUM((milestones1 + milestones2 + milestones3) * abc) AS ppmpq1'),
                    DB::raw('SUM((milestones4 + milestones5 + milestones6) * abc) AS ppmpq2'),
                    DB::raw('SUM((milestones7 + milestones8 + milestones9) * abc) AS ppmpq3'),
                    DB::raw('SUM((milestones10 + milestones11 + milestones12) * abc) AS ppmpq4'),
                    DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
                    DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc) AS qtyxabc'),
                    DB::raw('SUM(milestones1) AS milestones1'),DB::raw('SUM(milestones2) AS milestones2'),DB::raw('SUM(milestones3) AS milestones3'),DB::raw('SUM(milestones4) AS milestones4'),DB::raw('SUM(milestones5) AS milestones5'),DB::raw('SUM(milestones6) AS milestones6'),
                    DB::raw('SUM(milestones7) AS milestones7'),DB::raw('SUM(milestones8) AS milestones8'),DB::raw('SUM(milestones9) AS milestones9'),DB::raw('SUM(milestones10) AS milestones10'),DB::raw('SUM(milestones11) AS milestones11'),DB::raw('SUM(milestones12) AS milestones12'),
                )
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')

                // join annual budget tables
                ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
                ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
                ->join('fund_sources', 'annual_budgets.fund_source_id', '=', 'fund_sources.id')
                ->join('fund_source_parents', 'fund_sources.parent_id', '=', 'fund_source_parents.id')
                ->where('fund_source_parents.id', '=', $fundType)

                ->where('wfp_activities.program_id', '=', $program_id)->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->groupBy('general_description', 'year', 'abc', 'itemUnit', 'parent_type')
                ->get();

            $items = DB::table('ppmp')
                ->select('item_name as item', 'general_description as id')
                ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
                ->groupBy('Item', 'general_description')
                ->get();

            foreach ($app as $key => $value) {
                foreach ($items as $key2 => $value2) {
                    if ($value->id == $value2->id) {
                        $value->item = $value2->item;
                    }
                }
            }

            $allppmp = DB::connection('mysql')->table('ppmp')
                ->select('*', 'ppmp.id AS ppmp_id','ppmp_items.item_name AS item_desc',
                    DB::raw('(milestones1 + milestones2 + milestones3) * abc AS ppmpq1'),
                    DB::raw('(milestones4 + milestones5 + milestones6) * abc AS ppmpq2'),
                    DB::raw('(milestones7 + milestones8 + milestones9) * abc AS ppmpq3'),
                    DB::raw('(milestones10 + milestones11 + milestones12) * abc AS ppmpq4'),
                    DB::raw('milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12 AS qtyTotal'),
                    DB::raw('(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc AS qtyxabc'),
                )
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
                ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
                ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
                ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
                ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
                ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
                ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
                ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
                ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
                ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
                ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->get();
        }elseif ($division_id == null &&  $section_id == null && $program_id == null) {
            $app = DB::table('ppmp')
                ->select('ppmp.id as FINALPPMPID','general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','fund_source_parents.parent_type_abbr','fund_sources.source_name as source_abbr','fund_source_types.type_abbr','ppmp_items.item_name','procurement_modes.mode',
                'fund_source_parents.parent_type as Fund Source Type',
                DB::raw('SUM((milestones1 + milestones2 + milestones3) * abc) AS ppmpq1'),
                    DB::raw('SUM((milestones4 + milestones5 + milestones6) * abc) AS ppmpq2'),
                    DB::raw('SUM((milestones7 + milestones8 + milestones9) * abc) AS ppmpq3'),
                    DB::raw('SUM((milestones10 + milestones11 + milestones12) * abc) AS ppmpq4'),
                    DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
                    DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc) AS qtyxabc'),
                    DB::raw('SUM(milestones1) AS milestones1'),DB::raw('SUM(milestones2) AS milestones2'),DB::raw('SUM(milestones3) AS milestones3'),DB::raw('SUM(milestones4) AS milestones4'),DB::raw('SUM(milestones5) AS milestones5'),DB::raw('SUM(milestones6) AS milestones6'),
                    DB::raw('SUM(milestones7) AS milestones7'),DB::raw('SUM(milestones8) AS milestones8'),DB::raw('SUM(milestones9) AS milestones9'),DB::raw('SUM(milestones10) AS milestones10'),DB::raw('SUM(milestones11) AS milestones11'),DB::raw('SUM(milestones12) AS milestones12'),
                )
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')

                //
                //
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

                // ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
                ->Join('interfacev7.sections', 'programs.section_id', '=', 'interfacev7.sections.id')
                ->Join('interfacev7.divisions', 'sections.division_id', '=', 'interfacev7.divisions.id')
                //
                //
                // ->where('wfp_activities.program_id', '=', $program_id)->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
                // ->where('interfacev7.divisions.id', '=', 4)
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->groupBy('FINALPPMPID','general_description', 'year', 'abc', 'itemUnit', 'parent_type_abbr', 'source_name', 'type_abbr','item_name', 'mode', 'parent_type')
                ->get();

            $items = DB::table('ppmp')
                ->select('item_name as item', 'general_description as id')
                ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
                ->groupBy('Item', 'general_description')
                ->get();

            foreach ($app as $key => $value) {
                foreach ($items as $key2 => $value2) {
                    if ($value->id == $value2->id) {
                        $value->item = $value2->item;
                    }
                }
            }

            $allppmp = DB::connection('mysql')->table('ppmp')
                ->select('*', 'ppmp.id AS ppmp_id','ppmp_items.item_name AS item_desc', 'ppmp_items.unit as itemUnit',
                    DB::raw('(milestones1 + milestones2 + milestones3) * abc AS ppmpq1'),
                    DB::raw('(milestones4 + milestones5 + milestones6) * abc AS ppmpq2'),
                    DB::raw('(milestones7 + milestones8 + milestones9) * abc AS ppmpq3'),
                    DB::raw('(milestones10 + milestones11 + milestones12) * abc AS ppmpq4'),
                    DB::raw('milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12 AS qtyTotal'),
                    DB::raw('(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc AS qtyxabc'),
                )
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
                ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
                ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
                ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
                ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
                ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
                ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
                ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
                ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
                ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
                ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
                ->whereIn('fund_source_parents.id', $fundType)
                // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->get();
        }
        else{
            $app = DB::table('ppmp')
                ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit',
                    'fund_source_parents.parent_type as Fund Source Type',
                    DB::raw('SUM((milestones1 + milestones2 + milestones3) * abc) AS ppmpq1'),
                    DB::raw('SUM((milestones4 + milestones5 + milestones6) * abc) AS ppmpq2'),
                    DB::raw('SUM((milestones7 + milestones8 + milestones9) * abc) AS ppmpq3'),
                    DB::raw('SUM((milestones10 + milestones11 + milestones12) * abc) AS ppmpq4'),
                    DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
                    DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc) AS qtyxabc'),
                    DB::raw('SUM(milestones1) AS milestones1'),DB::raw('SUM(milestones2) AS milestones2'),DB::raw('SUM(milestones3) AS milestones3'),DB::raw('SUM(milestones4) AS milestones4'),DB::raw('SUM(milestones5) AS milestones5'),DB::raw('SUM(milestones6) AS milestones6'),
                    DB::raw('SUM(milestones7) AS milestones7'),DB::raw('SUM(milestones8) AS milestones8'),DB::raw('SUM(milestones9) AS milestones9'),DB::raw('SUM(milestones10) AS milestones10'),DB::raw('SUM(milestones11) AS milestones11'),DB::raw('SUM(milestones12) AS milestones12'),
                )
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')

                ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
                ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
                ->join('fund_sources', 'annual_budgets.fund_source_id', '=', 'fund_sources.id')
                ->join('fund_source_parents', 'fund_sources.parent_id', '=', 'fund_source_parents.id')
                ->where('fund_source_parents.id', '=', $fundType)

                ->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->groupBy('general_description', 'year', 'abc', 'itemUnit', 'parent_type')
                ->get();

            $items = DB::table('ppmp')
                ->select('item_name as item', 'general_description as id')
                ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
                ->groupBy('Item', 'general_description')
                ->get();

            foreach ($app as $key => $value) {
                foreach ($items as $key2 => $value2) {
                    if ($value->id == $value2->id) {
                        $value->item = $value2->item;
                    }
                }
            }

            $allppmp = DB::connection('mysql')->table('ppmp')
                ->select('*', 'ppmp.id AS ppmp_id','ppmp_items.item_name AS item_desc',
                    DB::raw('(milestones1 + milestones2 + milestones3) * abc AS ppmpq1'),
                    DB::raw('(milestones4 + milestones5 + milestones6) * abc AS ppmpq2'),
                    DB::raw('(milestones7 + milestones8 + milestones9) * abc AS ppmpq3'),
                    DB::raw('(milestones10 + milestones11 + milestones12) * abc AS ppmpq4'),
                    DB::raw('milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12 AS qtyTotal'),
                    DB::raw('(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc AS qtyxabc'),
                )
                ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
                ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
                ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
                ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
                ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
                ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
                ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
                ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
                ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
                ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
                ->where('wfp_activities.division_id', '=', $division_id)
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->get();
        }

        $section = DB::connection('mysql_2')->table('divisions')
            ->join('sections', 'divisions.id', '=', 'sections.division_id')
            // ->where('section.division_id', '=', $user->division)
            ->where('sections.section_abbr', '<>', 'Division Head')
            ->get();

        $program = DB::connection('mysql_2')->table('programs')
            ->select('sections.id AS sec_id', 'programs.id', 'program_abbr AS program', 'division_name', 'section_abbr', 'program_name AS description')
            ->Join('divisions', 'programs.division_id', '=', 'divisions.id')
            ->Join('sections', 'programs.section_id', '=', 'sections.id')
            ->get();

        $data = array(
            'app' => $app,
            'appPPMPpala' => $allppmp,
            'items' => $items,
            'allppmp' => $allppmp,
            'users' => $users,
            'wfpCategory' => $wfpCategory,
            // 'total' => $total,
            'fundType' => $fundType,
            'section' => $section,
            'program' => $program,
        );

        return $data;
    }

    function ppmpApprove(Request $request)
    {
        $input = $request['form_data'];
        // return $input;
        // if ($input['chiefComment'] !== null) {
        if (array_key_exists('chiefComment', $input)) {
            $input1 = array(
                'status' => 'dhComment-pending',
                'comment' => $input['chiefComment'],
            );
            try {
                DB::table('ppmp')
                    ->where('id', $input['approve_id_ppmp'])
                    ->update($input1);
            } catch (\Illuminate\Database\QueryException  $e) {
                return response()->json([
                    'error'=> $e->getMessage(),
                    'status'=> 400
                ], 400);
            }
        }else{
            $input1 = array(
                'status' => 'dhApproved',
                'comment' => null,
            );
            try {
                DB::table('ppmp')
                    ->where('id', $input['approve_id_ppmp'])
                    ->update($input1);
            } catch (\Illuminate\Database\QueryException  $e) {
                return response()->json([
                    'error'=> $e->getMessage(),
                    'status'=> 400
                ], 400);
            }
        }
        return response()->json([
            'success' => 'You have approved PPMP entry',
            'status' => 200
        ], 200);
    }

    function generatePPMPReport(Request $request)
    {
        $selected = explode("yyy",$request->header('FLASH'));
        $myUser = explode("xx",$request->header('FLASH2'));
        $user = ['section_id' => $myUser[0], 'division_id' => $myUser[1]];
        $flash = 2021;
        (array) $allPPMP = $this->PPMP->printPPMP($user, $flash, $selected);
        // $allwfp = 1;

        return response()->json([
            'data' => $selected,
            'flash' => $flash,
            'user' => $user,
            'allPPMP' => $allPPMP,
            'status' => 200
        ], 200);

    }

    function generateAPPOffice(Request $request)
    {
        // return $this->createapp($request, 2021, null, null, null);
        $selected = explode("yyy",$request->header('FLASH'));
        $myUser = explode("xx",$request->header('FLASH2'));
        $user = ['section_id' => $myUser[0], 'division_id' => $myUser[1]];
        $flash = 2021;
        (array) $allPPMP = $this->PPMP->printConsolidatedPPMP($flash);
        // (array) $allPPMP = $this->PPMP->printPPMP($flash);
        // $allwfp = 1;

        return response()->json([
            'data' => $selected,
            'flash' => $flash,
            'user' => $user,
            'allPPMP' => $allPPMP,
            'app' => $allPPMP,
            'status' => 200
        ], 200);

    }

    function formatdelete(Request $request)
    {
        //  'hello';
        $arr = [];
        $ppmp_gendesc = DB::table('ppmp')
            ->select('general_description')
            ->get();

        foreach ($ppmp_gendesc as $key => $value) {
            $arr[$key] = $value->general_description;
        }
        // dd($ppmp_gendesc);
        // dd($arr);

        $data = DB::table('ppmp_items')
            ->wherein('item_id', $arr)
            ->get();
        dd($data);
        dd('waz na');

        // return $this->createapp($request, 2021, null, null, null);
        // $selected = explode("yyy",$request->header('FLASH'));
        // $myUser = explode("xx",$request->header('FLASH2'));
        // $user = ['section_id' => $myUser[0], 'division_id' => $myUser[1]];
        // $flash = 2021;
        // (array) $allPPMP = $this->PPMP->printConsolidatedPPMP($flash);
        // // $allwfp = 1;

        // return response()->json([
        //     'data' => $selected,
        //     'flash' => $flash,
        //     'user' => $user,
        //     'allPPMP' => $allPPMP,
        //     'status' => 200
        // ], 200);

    }
    // function peekPPMP($section_id, $division_id, $year, $access_group, $remove = null)
    function peekPPMP($wfp_id)
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
            ->join('procurement_modes', 'ppmp.MOP', '=', 'procurement_modes.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            // ->whereNotIn('ppmp.id', $remove)
            // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
            ->where('wfp_activities.id', $wfp_id)
            ->where('ppmp.status', '<>', 'deleted')
            // ->where('wfp_activities.status', '=', 'deleted')
            // ->where('wfp_activities.year', '=', $year)
        ->get();

        // return $data;
        return response()->json([
            'data' => $data,
        ], 200);
    }
}

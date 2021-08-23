<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

use App\WFP;
use App\PPMP;
use App\Budget;
// use App\Http\Controllers\BidsAwardsController;

class ProcurementController extends Controller
{
    private $Budget;
    private $WFP;
    private $PPMP;
    // private $bidderBids;
    public function __construct()
    {
        $this->WFP = new WFP();
        $this->Budget = new Budget();
        $this->PPMP = new PPMP();
        // $this->bidderBids = new bidderBids();
    }

    function purchaseRequest(Request $request, $year)
    {
        $user = json_decode($request->header('LOGGED_USER'), true);

        $lastPR = DB::table('requests')
            ->select('assigned_id')
            ->where('type', 'PR')
            ->orderBy('assigned_id', 'DESC')->first();

        $PRprep = substr($lastPR->assigned_id, 0, strpos($lastPR->assigned_id, '-'));
        $PRLast = substr($lastPR->assigned_id, strpos($lastPR->assigned_id, "-") + 1);
        $PRLast = $PRLast+1;

        $lastPO = DB::table('requests')
            ->select('assigned_id')
            ->where('type', 'PO')
            ->orderBy('assigned_id', 'DESC')->first();
        $POprep = substr($lastPO->assigned_id, 0, strpos($lastPO->assigned_id, '-'));
        $POLast = substr($lastPO->assigned_id, strpos($lastPO->assigned_id, "-") + 1);
        $POLast = $POLast+1;

        $items = DB::table('ppmp')
            ->select('item_name as item', 'general_description as id')
            ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
            ->groupBy('Item', 'general_description')
            ->get();

        if ($user['access_group'] == 97 && $user['section_id'] == 8 && $user['division_id'] == 2) {
            $prRequests = DB::table('requests')
                ->select('*', 'requests.id as ReqID')
                ->Join('interfacev7.divisions', 'requests.division_id', 'interfacev7.divisions.id')
                ->Join('interfacev7.sections', 'requests.section_id', 'interfacev7.sections.id')
                ->where('requests.type', 'PR')
                ->get();

                $app = DB::table('ppmp')
                ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','ppmp.MOP',
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
                // ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->groupBy('general_description', 'year', 'abc', 'itemUnit','ppmp.MOP')
                ->get();
        }else {
            $prRequests = DB::table('requests')
                ->select('*', 'requests.id as ReqID')
                ->Join('interfacev7.divisions', 'requests.division_id', 'interfacev7.divisions.id')
                ->Join('interfacev7.sections', 'requests.section_id', 'interfacev7.sections.id')
                ->where('requests.type', 'PR')
                ->where('requests.section_id', $user['section_id'])
                ->where('requests.division_id', $user['division_id'])
                ->get();

            $app = DB::table('ppmp')
                ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','ppmp.MOP',
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
                ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
                ->where('ppmp.status', '<>', 'deleted')
                ->where('wfp_activities.year', '=', $year)
                ->groupBy('general_description', 'year', 'abc', 'itemUnit','ppmp.MOP')
                ->get();
        }


        //get array of items existing with PR number
        $removeFromTable = DB::table('requests')
            ->select('contentIDs')
            ->Join('interfacev7.divisions', 'requests.division_id', 'interfacev7.divisions.id')
            ->Join('interfacev7.sections', 'requests.section_id', 'interfacev7.sections.id')
            ->where('requests.type', 'PR')
            ->where('requests.section_id', $user['section_id'])
            ->where('requests.division_id', $user['division_id'])
            ->get()->toArray();

    $removeFromTable = array_column($removeFromTable, 'contentIDs');
    $arrayofValues = [];
    foreach ($removeFromTable as $key => $value) {
        $arrayofValues[$key] = explode("yyy", $value);
        // $arrayofValues[$key] = $arrayofValues[$key][explode("yyy", $value[$key])];
        // array_push($arrayofValues, explode("yyy", $value[$key]));
        // $arrayofValues = explode("yyy", $value[$key]);
    }
    $finalArray = array();
    // foreach ($arrayofValues as $key => $value) {
    //     $finalArray = array_merge($value['key']);
    // }
    // foreach ($arrayofValues as $key => $value) {
    //     // array_push($finalArray, $value);
    //     $finalArray[] = $value;
    // }

        // foreach ($removeFromTable as $key => $value) {
        //     array_push($arrayofValues, explode("yyy", $value->contentIDs));
        // }
        // $arrayofValues = array_column($arrayofValues)

        $allppmp = $this->PPMP->allPPMP($user['section_id'], $user['division_id'], $year, $user['access_group'], $arrayofValues);

        foreach ($app as $key => $value) {
            foreach ($items as $key2 => $value2) {
                if ($value->id == $value2->id) {
                    $value->item = $value2->item;
                }
            }
        }
        $prItems = [];
        foreach ($prRequests as $key => $v) {
            $prItems[$key]['id'] = $v->ReqID;
            $prItems[$key]['itemsDisplay'] = explode("yyy",$v->contentIDs);
            $prItems[$key]['prnumber'] = $v->assigned_id;
            $prItems[$key]['items'] = $v->contentIDs;
            $prItems[$key]['user'] = $v->user;
            $prItems[$key]['status'] = $v->status;
            $prItems[$key]['section_id'] = $v->section_abbr;
            $prItems[$key]['division_id'] = $v->division_abbr;
            $prItems[$key]['sec_id'] = $v->section_id;
            $prItems[$key]['div_id'] = $v->division_id;
            $prItems[$key]['created_at'] = $v->created_at;
            foreach ($allppmp as $k2 => $v2) {
                // $v2->id = dbm-3
                foreach ($prItems[$key]['itemsDisplay'] as $k3 => $v3) {
                    if ($v2->ppmp_id == $v3 && $v2->section_id == $prItems[$key]['sec_id'] && $v2->division_id == $prItems[$key]['div_id']) {
                        $prItems[$key]['itemsss'][$k2]['requestID'] = $v->ReqID;
                        $prItems[$key]['itemsss'][$k2]['ppmp_id_3lvl'] = $v2->ppmp_id;
                        $prItems[$key]['itemsss'][$k2]['status'] = $v2->status;
                        $prItems[$key]['itemsss'][$k2]['item_name'] = $v2->item_name;
                        $prItems[$key]['itemsss'][$k2]['qtyTotal'] = $v2->qty;
                        $prItems[$key]['itemsss'][$k2]['itemUnit'] = $v2->unit;
                        $prItems[$key]['itemsss'][$k2]['abc'] = $v2->abc;
                        $prItems[$key]['itemsss'][$k2]['qtyxabc'] = $v2->estimated_budget;
                        $prItems[$key]['itemsss'][$k2]['MOP'] = $v2->mode;
                        $prItems[$key]['itemsss'][$k2]['source_name'] = $v2->source_name;
                    }else{
                    }
                }
            }
        }

        // check if existing
        foreach ($prItems as $key => $value) {
            if (! array_key_exists("itemsss", $prItems[$key])) {
                unset($prItems[$key]);
            }
        }


        $user = json_decode($request->header('LOGGED_USER'), true);


        return response()->json([
            'removeFromTable' => $removeFromTable,
            'arrayofValues' => $arrayofValues,
            'finalArray' => $finalArray,
            'PRprep' => $PRprep,
            'lastPR' => $lastPR,
            'lastPO' => $lastPO,
            'PRLast' => $PRLast ,
            'POprep' => $POprep,
            'POLast' => $POLast,
            'user' => $user,
            'prItems' => $prItems,
            'prRequests' => $prRequests,
            'app' => $app,
            'allppmp' => $allppmp,
            'status' => 200
        ], 200);
    }

    function requestPurchaseRequest(Request $request, $year){
        $selected = explode("yyy",$request->header('FLASH'));
        // $selected = array_map('current', $elected);
        $request->header('FLASH2');
        $user = explode("xx",$request->header('FLASH2'));

        $data = array(
            'type' => 'PR',
            'user' => $request->header('FLASH2'),
            'contentIDs' => $request->header('FLASH'),
            'division_id' => $user['1'],
            'section_id' => $user['0'],
            'status' => 'pending',
            'created_at' => Carbon::now(),
        );
        DB::beginTransaction();

        try {
            $id = DB::table('requests')->insertGetId($data);
            DB::table('ppmp')
                ->whereIn('id', $selected)
                ->update(['pr_status' => $id]);

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
            'success' => 'You have created a Purchase Request',
            'status' => 200
        ], 200);
    }

    function requestPurchaseOrder(Request $request, $year, $pono){
        $selected = explode("yyy",$request->header('FLASH'));
        $request->header('FLASH2');
        $user = explode("xx",$request->header('FLASH2'));

        $data = array(
            'type' => 'PO',
            'user' => $request->header('FLASH2'),
            'contentIDs' => $request->header('FLASH'),
            'assigned_id' => $pono,
            'division_id' => $user['1'],
            'section_id' => $user['0'],
            'status' => 'printed',
        );
        DB::beginTransaction();

        try {
            DB::table('requests')->insert($data);
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
            'success' => 'You have created a Purchase Request',
            'status' => 200
        ], 200);
    }



    function purchaseRequestNumber(Request $request, $prid, $prnumber)
    {
        $data = array(
            'assigned_id' => $prnumber,
            'status' => 'approved',
        );

        DB::beginTransaction();

        try {
            DB::table('requests')
                ->where('id', $prid)
                ->update($data);
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
            'success' => 'You have placed PR Number: '.$prnumber,
            'status' => 200
        ], 200);
    }

    function generatePurchaseRequest(Request $request, $year)
    {
        $selected = explode("yyy",$request->header('FLASH'));
        $request->header('FLASH2');
        $user = explode("xx",$request->header('FLASH2'));
        // $allppmp = $this->PPMP->printPR($user['0'], $user['1'], $year, $selected);

        // $app = DB::table('ppmp')
        //     ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','ppmp.MOP','wfp_activities.annual_budget_program_id','fund_source_parents.parent_type_abbr','fund_sources.source_abbr','fund_source_types.type_abbr',
        //         // DB::raw('CONCAT(fund_source_parents.parent_type_abbr,"-", fund_sources.source_abbr) AS source_abbr'),
        //         DB::raw('SUM((milestones1 + milestones2 + milestones3) * abc) AS ppmpq1'),
        //         DB::raw('SUM((milestones4 + milestones5 + milestones6) * abc) AS ppmpq2'),
        //         DB::raw('SUM((milestones7 + milestones8 + milestones9) * abc) AS ppmpq3'),
        //         DB::raw('SUM((milestones10 + milestones11 + milestones12) * abc) AS ppmpq4'),
        //         DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
        //         DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc) AS qtyxabc'),
        //         DB::raw('SUM(milestones1) AS milestones1'),DB::raw('SUM(milestones2) AS milestones2'),DB::raw('SUM(milestones3) AS milestones3'),DB::raw('SUM(milestones4) AS milestones4'),DB::raw('SUM(milestones5) AS milestones5'),DB::raw('SUM(milestones6) AS milestones6'),
        //         DB::raw('SUM(milestones7) AS milestones7'),DB::raw('SUM(milestones8) AS milestones8'),DB::raw('SUM(milestones9) AS milestones9'),DB::raw('SUM(milestones10) AS milestones10'),DB::raw('SUM(milestones11) AS milestones11'),DB::raw('SUM(milestones12) AS milestones12'),
        //     )
        //     ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
        //     ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
        //     ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', '=', 'annual_budgets.id')
        //     ->join('fund_sources', 'annual_budgets.fund_source_id', '=', 'fund_sources.id')
        //     ->join('fund_source_parents', 'fund_sources.parent_id', '=', 'fund_source_parents.id')
        //     ->join('fund_source_types', 'fund_sources.type_id', '=', 'fund_source_types.id')
        //     // ->where('wfp_activities.program_id', '=', $user['program_id'])
        //     // ->where('wfp_activities.section_id', '=', $user['section_id'])->where('wfp_activities.division_id', '=', $user['division_id'])
        //     ->whereIn('ppmp.general_description', $selected)
        //     // ->where('ppmp.status', '<>', 'deleted')
        //     ->where('wfp_activities.year', '=', $year)
        //     ->groupBy('general_description', 'year', 'abc', 'itemUnit','ppmp.MOP','wfp_activities.annual_budget_program_id', 'fund_source_parents.parent_type_abbr', 'fund_sources.source_abbr','fund_source_types.type_abbr')
        // ->get();


        $allppmp = DB::table('ppmp')
            ->select('ppmp_items.*','ppmp.*', 'procurement_modes.*','ppmp.status AS ppmp_status','ppmp.id AS ppmpTable_id','wfp_activities.*','wfp_deliverable.*','annual_budget_programs.*','annual_budgets.*','fund_sources.*','fund_source_parents.*','fund_source_types.*','interfacev7.programs.*','interfacev7.users.name_family','interfacev7.users.name',)
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
            ->whereIn('ppmp.id', $selected)
            ->where('wfp_activities.section_id', '=', $user[0])->where('wfp_activities.division_id', '=', $user[1])
            ->where('ppmp.status', '<>', 'deleted')
            ->where('wfp_activities.year', '=', $year)
        ->get();
        $prRequests = DB::table('requests')
            ->select('*', 'requests.id as ReqID')
            ->Join('interfacev7.divisions', 'requests.division_id', 'interfacev7.divisions.id')
            ->Join('interfacev7.sections', 'requests.section_id', 'interfacev7.sections.id')
            ->where('requests.section_id', $user[0])
            ->where('requests.division_id', $user[1])
            ->where('requests.type', 'PR')
        ->get();


        foreach ($prRequests as $k1 => $v1) {
            $v1->contentIDsArray = explode("yyy", $v1->contentIDs);
            foreach ($allppmp as $k2 => $v2) {
                foreach ($v1->contentIDsArray as $k3 => $v3) {
                    if ($v3 == $v2->ppmp_id) {
                        $v2->prNumber = $v1->assigned_id;
                    }
                }
            }
            // foreach ($allppmp as $k2 => $v2) {
            //     $v2->ppmp_ids = explode("yyy", $v1->contentIDs);
            //     $v2->prNumberAttach = $v1->assigned_id;
            // }
        }

        // foreach ($allppmp as $key => $value) {
        //     foreach ($selectedFromPR as $k => $v) {
        //         if ($value->item_id == $v->) {
        //             $allppmp[$key]->prNumber  = $v->assigned_id;
        //         }
        //     }
        // }
        // $items = DB::table('ppmp')
        //     ->select('item_name as item', 'general_description as id')
        //     ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
        //     ->groupBy('Item', 'general_description')
        //     ->get();

        // foreach ($allppmp as $key => $value) {
        //     foreach ($prRequests as $key2 => $value2) {
        //         if ($value->id == $value2->id) {
        //             $value->item = $value2->item;
        //         }
        //     }
        // }




        return response()->json([
            'data' => $selected,
            'user' => $user,
            'prRequests' => $prRequests,
            // 'selectedFromPR' => $selectedFromPR,
            'allppmp' => $allppmp,
            'status' => 200
        ], 200);

    }

    function generatePurchaseOrder(Request $request, $year)
    {
        // return $request->header('FLASH');
        $selected = explode("yyy",$request->header('FLASH'));
        // $selected = array('0' => "26");
        $user = explode("xx",$request->header('FLASH2'));
        $bidder_id = $request->header('FLASH3');

        // $app = DB::table('ppmp')
        //     ->select('*', 'ppmp.id as ppmpTable_id', 'ppmp_items.price as ppmp_items_price')
        //     ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
        //     ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
        //     ->where('ppmp.status', '<>', 'deleted')
        //     ->where('wfp_activities.year', '=', $year)
        //     ->whereIn('ppmp.id', $selected)
        //     ->get();

        // $bids = DB::table('bids')
        //     ->select('*', 'ppmp.id as ppmpTable_id', 'ppmp_items.price as ppmp_items_price')
        //     ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
        //     ->join('ppmp', 'bids.item_id', '=', 'ppmp.general_description')
        //     ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
        //     ->groupBy('general_description', 'year', 'abc', 'itemUnit')
        //     ->where('bidders.id', '=', $bidder_id)
        //     ->whereIn('ppmp.id', $selected)
        //     ->get();

            // $app = DB::table('ppmp')
            //     ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','item_name','MOP','bidders.bidder_name',
            //         DB::raw('SUM(ppmp.id+0) AS ppmp_id'),
            //         DB::raw('SUM(bid_amount+0) AS bidder_price'),
            //         // DB::raw('CONCAT(item_name,"sdf") AS item_names'),
            //         DB::raw('SUM((milestones1 + milestones2 + milestones3) * abc) AS ppmpq1'),
            //         DB::raw('SUM((milestones4 + milestones5 + milestones6) * abc) AS ppmpq2'),
            //         DB::raw('SUM((milestones7 + milestones8 + milestones9) * abc) AS ppmpq3'),
            //         DB::raw('SUM((milestones10 + milestones11 + milestones12) * abc) AS ppmpq4'),
            //         DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
            //         DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc) AS qtyxabc'),
            //         DB::raw('SUM(milestones1) AS milestones1'),DB::raw('SUM(milestones2) AS milestones2'),DB::raw('SUM(milestones3) AS milestones3'),DB::raw('SUM(milestones4) AS milestones4'),DB::raw('SUM(milestones5) AS milestones5'),DB::raw('SUM(milestones6) AS milestones6'),
            //         DB::raw('SUM(milestones7) AS milestones7'),DB::raw('SUM(milestones8) AS milestones8'),DB::raw('SUM(milestones9) AS milestones9'),DB::raw('SUM(milestones10) AS milestones10'),DB::raw('SUM(milestones11) AS milestones11'),DB::raw('SUM(milestones12) AS milestones12'),
            //     )
            //     ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
            //     ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
            //     ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
            //     ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
            //     // ->join('ppmp', 'bids.item_id', '=', 'ppmp.general_description')
            //     // ->where('ppmp.status', '<>', 'deleted')
            //     // ->where('wfp_activities.year', '=', $year)
            //     // ->where('bidders.id', '=', $bidder_id)
            //     // ->whereIn('ppmp_items.item_id', $selected)
            //     ->groupBy('general_description', 'year', 'abc', 'itemUnit', 'item_name', 'MOP','bidder_name')
            // ->get();
            //test=============================================================================================
            //test=============================================================================================
        //         $app = DB::table('ppmp')
        //         // ->select('*', 'ppmp.id as asdasd')
        //         ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','item_name','MOP','bidders.bidder_name',
        //             // DB::raw('SUM(ppmp.id+2) AS asdasd'),
        //             DB::raw('SUM(bid_amount+0) AS bidder_price'),
        //             DB::raw('SUM((milestones1 + milestones2 + milestones3) * abc) AS ppmpq1'),
        //             DB::raw('SUM((milestones4 + milestones5 + milestones6) * abc) AS ppmpq2'),
        //             DB::raw('SUM((milestones7 + milestones8 + milestones9) * abc) AS ppmpq3'),
        //             DB::raw('SUM((milestones10 + milestones11 + milestones12) * abc) AS ppmpq4'),
        //             DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
        //             DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * abc) AS qtyxabc'),
        //             // DB::raw('SUM(milestones1) AS milestones1'),DB::raw('SUM(milestones2) AS milestones2'),DB::raw('SUM(milestones3) AS milestones3'),DB::raw('SUM(milestones4) AS milestones4'),DB::raw('SUM(milestones5) AS milestones5'),DB::raw('SUM(milestones6) AS milestones6'),
        //             // DB::raw('SUM(milestones7) AS milestones7'),DB::raw('SUM(milestones8) AS milestones8'),DB::raw('SUM(milestones9) AS milestones9'),DB::raw('SUM(milestones10) AS milestones10'),DB::raw('SUM(milestones11) AS milestones11'),DB::raw('SUM(milestones12) AS milestones12'),
        //         )
        //         ->join('wfp_activities', 'ppmp.wfp_id', '=', 'wfp_activities.devliverable_id')
        //         ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
        //         ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
        // ->join('bidders', 'ppmp.bidderWinner', 'bidders.id')
        //         // ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
        //         // ->join('ppmp', 'bids.item_id', '=', 'ppmp.general_description')
        //         ->where('ppmp.status', '<>', 'deleted')
        //         ->where('wfp_activities.year', '=', $year)
        //         ->whereIn('ppmp.general_description', $selected)
        //         // ->groupBy('general_description', 'year', 'abc', 'itemUnit', 'item_name')
        //         ->groupBy('general_description', 'year', 'abc', 'itemUnit', 'item_name', 'MOP','bidder_name')
        //     ->get();
            //test=============================================================================================
            //test=============================================================================================
            $app = DB::table('ppmp')
                ->select( 'general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','item_name', 'bids.bid_amount AS bidder_price', 'ppmp.bidderWinner as asdasdasdasdasdasdasdasd', 'bids.bidder_id', 'ppmp.MOP', 'bidders.bidder_name', 'bidders.bidder_TIN', 'bidders.bidder_address', 'procurement_modes.mode',
                    DB::raw('SUM((milestones1 + milestones2 + milestones3) * bids.bid_amount) AS ppmpq1'),
                    DB::raw('SUM((milestones4 + milestones5 + milestones6) * bids.bid_amount) AS ppmpq2'),
                    DB::raw('SUM((milestones7 + milestones8 + milestones9) * bids.bid_amount) AS ppmpq3'),
                    DB::raw('SUM((milestones10 + milestones11 + milestones12) * bids.bid_amount) AS ppmpq4'),
                    DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
                    DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * bids.bid_amount) AS qtyxbidder_price'),
                )
                ->join('wfp_activities', 'ppmp.wfp_id','wfp_activities.devliverable_id')
                ->join('ppmp_items', 'ppmp.id', 'ppmp_items.ppmp_id')
                ->join('procurement_modes', 'ppmp.MOP', 'procurement_modes.id')
                ->join('bids', 'ppmp_items.item_id', 'bids.item_id')
                ->join('bidders', 'bids.bidder_id', 'bidders.id')
                ->where('ppmp.status', '<>', 'deleted')
                ->where('ppmp.bidderWinner', '<>', 'none')
                ->where('ppmp.bidderWinner', $bidder_id)
                ->where('bids.bidder_id', $bidder_id)
                ->where('wfp_activities.year', '=', $year)
                ->whereIn('ppmp.id', $selected)
                ->groupBy('general_description', 'year', 'abc', 'itemUnit', 'item_name', 'bids.bid_amount', 'ppmp.bidderWinner', 'bids.bidder_id', 'ppmp.MOP', 'bidders.bidder_name', 'bidders.bidder_TIN', 'bidders.bidder_address', 'procurement_modes.mode')
            ->get();

            // $items = DB::table('ppmp')
            //     ->select('item_name as item', 'general_description as id')
            //     ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
            //     ->groupBy('Item', 'general_description')
            //     ->get();

            // foreach ($app as $key => $value) {
            //     foreach ($items as $key2 => $value2) {
            //         if ($value->id == $value2->id) {
            //             $value->item = $value2->item;
            //         }
            //     }
            // }

        // $list = DB::table('ppmp_items')
        //     ->select('*')
        //     ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
        //     ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
        //     ->where('bidders.bidder_status', '=', 'complete')
        //     ->where('bidders.id', '=', $bidder_id)
        //     ->orderBy('ppmp_items.item_id')
        //     ->get();

        return response()->json([
            // 'app' => $app,
            'app' => $this->PPMP->PrintPOItem($year, $bidder_id, $selected),
            'selected' => $selected,
            'user' => $user,
            'bidder_id' => $bidder_id,
            // 'bids' => $bids,
            'status' => 200
        ], 200);

    }
}
        // // $user = json_decode($request->header('FLASH2'), true);
        // $user = explode("xx",$request->header('FLASH2'));
        // // return($user);
        // $allppmp = $this->PPMP->allPPMP($user['0'], $user['1'], $year);

        // return response()->json([
        //     'user' => $user,
        //     'allppmp' => $allppmp,
        //     'status' => 200
        // ], 200);

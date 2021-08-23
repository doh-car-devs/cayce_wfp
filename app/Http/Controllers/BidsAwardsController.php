<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

use App\PPMP;

class BidsAwardsController extends Controller
{
    private $PPMP;
    public function __construct()
    {
        $this->PPMP = new PPMP();
    }

    function index()
    {
        //
    }

    function appList($year, $MOP)
    {
        $app = DB::table('ppmp')
            ->select('general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit',
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
            ->where('ppmp.status', '<>', 'deleted')
            ->where('ppmp.mop', '=', $MOP)
            ->where('wfp_activities.year', '=', $year)
            ->groupBy('general_description', 'year', 'abc', 'itemUnit')
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
        return $arrayName = array(
            'app' => $app,
            'items' => $items,
        );
    }

    function abstractOfBids(Request $request, $year)
    {
        $user = json_decode($request->header('LOGGED_USER'), true);
        $MOP = 1;
        $app = $this->appList($year, $MOP);

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
            // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
            ->where('ppmp.status', '<>', 'deleted')
            ->where('ppmp.MOP', $MOP)
            ->where('wfp_activities.year', '=', $year)
            ->get();

        $users = DB::connection('mysql_2')->table('users')->get();

        return response()->json([
            'app' => $app,
            // 'items' => $items,
            'allppmp' => $allppmp,
            'status' => 200
        ], 200);
    }

    function abstractCanvas(Request $request, $year)
    {
        $user = json_decode($request->header('LOGGED_USER'), true);

        $MOP = 4;
        $app = $this->appList($year, $MOP);
        $appBids = $this->appList($year, 1);

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
            ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.ppmp_id')
            // ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
            ->join('wfp_deliverable', 'wfp_activities.devliverable_id', '=', 'wfp_deliverable.id')
            ->join('annual_budget_programs', 'wfp_activities.annual_budget_program_id', '=', 'annual_budget_programs.id')
            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->Join('interfacev7.programs', 'wfp_activities.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.users', 'wfp_activities.resp_person', '=', 'interfacev7.users.id')
            // ->where('wfp_activities.section_id', '=', $section_id)->where('wfp_activities.division_id', '=', $division_id)
            ->where('ppmp.status', '<>', 'deleted')
            ->where('ppmp.MOP', $MOP)
            ->where('wfp_activities.year', '=', $year)
            ->get();

        $users = DB::connection('mysql_2')->table('users')->get();

        return response()->json([
            'app' => $app,
            // 'items' => $items,
            'allppmp' => $allppmp,
            'appBids' => $appBids,
            'status' => 200
        ], 200);
    }

    function abstractItem($year, $item_id)
    {
        $MOP =1;
        $item = DB::table('ppmp')
            // ->select('ppmp_items.*', 'ppmp.*', 'bids.*','bidders.*', 'ppmp.id AS ppmpTable_id')

            ->join('ppmp_items', 'ppmp.id', '=', 'ppmp_items.id')
            // ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
            // ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
        ->where('ppmp_items.item_id', '=', $item_id)
        ->where('ppmp.MOP', '=', 1)
            // ->where('bidders.bidder_status', '=', 'complete')
            // ->where('bids.year', '=', $year)
            ->get();

        $testBids = DB::table('bids')
            ->select('bids.item_id', 'bids.bid_amount', 'bids.bidder_id','ppmp_items.price','bidders.bidder_name','bidders.bidder_status', 'ppmp_items.unit','ppmp_items.item_name')
            ->join('bidders', 'bids.bidder_id','bidders.id')
            ->join('ppmp_items', 'bids.item_id', 'ppmp_items.item_id')
            // ->join('ppmp', 'ppmp_items.ppmp_id', 'ppmp.id')
            ->where('ppmp_items.item_id', '=', $item_id)
            ->groupBy('bids.item_id' , 'bids.bid_amount', 'bids.bidder_id', 'ppmp_items.price','bidders.bidder_name','bidders.bidder_status', 'ppmp_items.unit','ppmp_items.item_name')
            ->get();


        // $biddersCount = DB::table('ppmp_items')
        //     ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
        //     ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
        //     ->where('ppmp_items.item_id', '=', $item_id)
        //     ->where('bidders.bidder_status', '=', 'complete')
        //     ->where('bids.year', '=', $year)
        //     ->count();

        $app = $this->appList($year, $MOP);

        foreach ($app['app'] as $key => $value) {
            foreach ($testBids as $key2 => $value2) {
                if ($value->id == $value2->item_id) {
                    $value2->totalqty = $value->milestones1 + $value->milestones2 + $value->milestones3 + $value->milestones4 + $value->milestones5 + $value->milestones6 + $value->milestones7 + $value->milestones8 + $value->milestones9 + $value->milestones10 + $value->milestones11 + $value->milestones12;
                    $value2->totalppmpamount =  $value->ppmpq1 + $value->ppmpq2 + $value->ppmpq3 + $value->ppmpq4;
                    $value2->qtyxabc = $value2->bid_amount * $value2->totalqty;
                    $value2->plusminus = $value2->totalppmpamount - $value2->qtyxabc;
                    $value2->itemplusminus = $value2->price - $value2->bid_amount;
                }
            }
        }
        return response()->json([
            'year' => $year,
            'item' => $item,
            'app' => $app,
            'testBids' => $testBids,
            'status' => 200
        ], 200);
    }

    function postQualEvalReport($year)
    {
        $item = DB::table('ppmp_items')
            ->select('*')
            ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
            ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
            ->where('bidders.bidder_status', '=', 'complete')
            ->where('bids.year', '=', $year)
            ->orderBy('ppmp_items.item_id')
            ->get();

        $MOP = 1;
        $app = $this->appList($year, $MOP);

        foreach ($app['app'] as $key => $value) {
            foreach ($item as $key2 => $value2) {
                if ($value->id == $value2->item_id) {
                    $value2->totalqty = $value->milestones1 + $value->milestones2 + $value->milestones3 + $value->milestones4 + $value->milestones5 + $value->milestones6 + $value->milestones7 + $value->milestones8 + $value->milestones9 + $value->milestones10 + $value->milestones11 + $value->milestones12;
                    $value2->totalppmpamount =  $value->ppmpq1 + $value->ppmpq2 + $value->ppmpq3 + $value->ppmpq4;
                    $value2->qtyxabc = $value2->bid_amount * $value2->totalqty;
                    $value2->plusminus = $value2->totalppmpamount - $value2->qtyxabc;
                    $value2->itemplusminus = $value2->price - $value2->bid_amount;
                }
            }
        }

        return response()->json([
            'year' => $year,
            'item' => $item,
            'app' => $app,
            'status' => 200
        ], 200);
    }

    function bidderList(Request $request, $term)
    {
        $list = DB::table('bidders')
            ->select('bidder_name as text', 'id as id')
            ->where('bidder_name', 'like', '%'.$term.'%')
            ->get();

        return response()->json([
            'items' => $list,
            'status' => 200
        ], 200);
    }

    function storeBid(Request $request)
    {
        $input = $request['form_data'];
        $data = array(
            'item_id' => $input['item_id'],
            'year' => $input['year'],
            'bidder_id' => $input['bidder_id'],
            'bid_amount' => $input['bid_amount'],
        );
        DB::beginTransaction();
        try {
            DB::table('bids')->insert($data);
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
            'success' => 'You have added a new bid!',
            'status' => 200
        ], 200);
    }


    function BidderWithBid(Request $request, $year)
    {
            $list = DB::table('bidders')
            ->select('bidder_name', 'bidders.id')
            ->join('bids', 'bidders.id', '=', 'bids.bidder_id')
            ->where('bidders.bidder_status', '=', 'complete')
            ->groupBy('bidder_name','bidders.id')
            ->get();

        // $list = DB::table('ppmp_items')
        //     ->select('*')
        //     ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
        //     ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
        //     ->where('bidders.bidder_status', '=', 'complete')
        //     ->where('bids.year', '=', $year)
        //     ->orderBy('ppmp_items.item_id')
        //     ->get();

        return response()->json([
            'items' => $list,
            'status' => 200
        ], 200);
    }

    function bidderWinStore(Request $request)
    {
        $input = $request['form_data'];
        $selected = explode("||",$input['entry']);
        // $bidder = ;
        $bidder = array(
            'bidderWinner' => $input['awardnow'],
        );

        // return $input['awardnow'];
        // return $selected;
        // return $input['entry'];
            // $data = array(
            //     'item_id' => $input['item_id'],
            //     'year' => $input['year'],
            //     'bidder_id' => $input['bidder_id'],
            //     'bid_amount' => $input['bid_amount'],
            // );
        DB::beginTransaction();
        try {
            DB::table('ppmp')->whereIn('ppmp.id', $selected)->update($bidder);
            DB::commit();
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have awarded the winner',
            'status' => 200
        ], 200);
    }

    function bidderBids($bidder_id, $year) {

        $allppmp = $this->PPMP->POItem($year, $bidder_id);

        $app = DB::table('ppmp')
            ->select( 'general_description AS id', 'wfp_activities.year AS year','abc','ppmp.unit as itemUnit','item_name', 'bids.bid_amount AS bidder_price', 'ppmp.bidderWinner as asdasdasdasdasdasdasdasd', 'bids.bidder_id','ppmp.MOP',
                DB::raw('SUM((milestones1 + milestones2 + milestones3) * bids.bid_amount) AS ppmpq1'),
                DB::raw('SUM((milestones4 + milestones5 + milestones6) * bids.bid_amount) AS ppmpq2'),
                DB::raw('SUM((milestones7 + milestones8 + milestones9) * bids.bid_amount) AS ppmpq3'),
                DB::raw('SUM((milestones10 + milestones11 + milestones12) * bids.bid_amount) AS ppmpq4'),
                DB::raw('SUM(milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) AS qtyTotal'),
                DB::raw('SUM((milestones1 + milestones2 + milestones3 + milestones4 + milestones5 + milestones6 + milestones7 + milestones8 + milestones9 + milestones10 + milestones11 + milestones12) * bids.bid_amount) AS qtyxbidder_price'),
            )
            ->join('wfp_activities', 'ppmp.wfp_id','wfp_activities.devliverable_id')
            ->join('ppmp_items', 'ppmp.id', 'ppmp_items.ppmp_id')
            ->join('bids', 'ppmp_items.item_id', 'bids.item_id')
            ->where('ppmp.status', '<>', 'deleted')
            ->where('ppmp.bidderWinner', '<>', 'none')
            ->where('ppmp.bidderWinner', $bidder_id)
            ->where('bids.bidder_id', $bidder_id)
            // ->where('ppmp.MOP', 1)
            ->where('wfp_activities.year', '=', $year)
            ->groupBy('general_description', 'year', 'abc', 'itemUnit', 'item_name', 'bids.bid_amount', 'ppmp.bidderWinner', 'bids.bidder_id','ppmp.MOP')
        ->get();

        $bids = DB::table('bids')
            // ->select('*', 'ppmp.id as ppmpTable_id', 'ppmp_items.price as ppmp_items_price')
            ->select('*', 'ppmp.id as ppmpTable_id')
            // ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
            ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
            ->join('ppmp', 'bids.item_id', '=', 'ppmp.general_description')
            // ->join('ppmp_items', 'ppmp.general_description', '=', 'ppmp_items.item_id')
            ->where('bidders.id', '=', $bidder_id)
            ->where('ppmp.status', '<>', 'deleted')
            // ->where('wfp_activities.year', '=', 2020)
            ->get();

        $list = DB::table('ppmp_items')
            ->select('*')
            ->join('bids', 'ppmp_items.item_id', '=', 'bids.item_id')
            ->join('bidders', 'bids.bidder_id', '=', 'bidders.id')
            ->where('bidders.bidder_status', '=', 'complete')
            ->where('bidders.id', '=', $bidder_id)
            ->orderBy('ppmp_items.item_id')
            ->get();

        return response()->json([
            'DELbidder_id' => $bidder_id,
            'bids' => $bids,
            'allppmp' => $allppmp,
            'app' => $app,
            'items' => $list,
            'status' => 200
        ], 200);
    }
}

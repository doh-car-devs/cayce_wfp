<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

use App\WFP;
use App\Budget;

class BudgetController extends Controller
{
    /**
     * Class constructor.
     */
    private $Budget;
    private $WFP;
    public function __construct()
    {
        $this->WFP = new WFP();
        $this->Budget = new Budget();
    }


    // public function sidebar()
    // {
    //     $sidebar = array(
    //         // 'Dashboard' => route('planninghome.index'),
    //         // 'Annual Budgets' => route('planning.annbudget'),
    //         // 'Guidelines' => 'planninghome.index',
    //         // 'Reports Generation' => 'planninghome.index',
    //         // 'My Account' => 'planninghome.index',
    //     );
    //     return $sidebar;
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = array(
            // 'users' => Auth::user(),
            // 'sidebar' => $this->sidebar()
        );

        // return view('planning.index')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // END OF RESOURCE FUNCTIONS

    public function annualBudget(Request $request){
        $flash = $request->header('FLASH');
        //GET FUND SOURCES
        //GET FUND SOURCES
        $funds = DB::table('fund_sources')
            ->select('fund_sources.type_id','fund_sources.id AS fund_id','fund_sources.parent_id as parent_id','fund_sources.source_name','fund_sources.source_abbr', 'fund_source_parents.parent_type', 'fund_source_parents.parent_type_abbr', 'fund_source_types.type', 'fund_source_types.type_abbr')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->get();
        $parentFunds = DB::table('fund_source_parents')->select('parent_type_abbr AS parent_abbr', 'parent_type AS parent', 'id')->get();
        $typeFunds = DB::table('fund_source_types')->select('type_abbr AS type_abbr', 'type as type', 'id')->get();

        // GET ANNUAL BUDGETS

        $annual = DB::table('annual_budgets')
            ->select('*', 'annual_budgets.id as annual_id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('annual_budgets.year', '=', $flash)
            // ->where('annual_budgets.program_id', '=', 99)
            ->get();

        $annualPrograms = DB::table('annual_budget_programs')
            ->select('*', 'annual_budget_programs.id as annual_budget_programs_id')
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')
            ->join('interfacev7.sections', 'programs.section_id', '=', 'interfacev7.sections.id')
            ->join('interfacev7.divisions', 'programs.division_id', '=', 'interfacev7.divisions.id')

            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            ->where('annual_budgets.year', '=', $flash)
            ->where('annual_budget_programs.program_id', '!=', 99)
            ->get();

        $allocatedTotals = DB::table('annual_budget_programs')
            ->select(DB::raw('SUM(annual_budget_programs.allocatedNEP) AS annualAllocatedNEP'), 'annual_budgets.fund_source_id AS fund_source_id',
            DB::raw('SUM(annual_budget_programs.allocatedAmount) AS annualAllocatedAmount')
            )
            ->join('interfacev7.programs', 'annual_budget_programs.program_id', '=', 'interfacev7.programs.id')

            ->join('annual_budgets', 'annual_budget_programs.annual_budget_id', 'annual_budgets.id')
            ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
            ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
            ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
            // ->where('annual_budgets.year', '=', $flash)
            ->where('annual_budget_programs.program_id', '!=', 99)
            ->groupBy('fund_source_id')
            ->get();

        // if (!empty($allocatedTotals)) {
        //     $allocatedTotals[0]->fund_source_id = '0';
        // }
        // $allocatedTotals[count($allocatedTotals)] = ['annualAllocatedNep'=> 0, 'fund_source_id' => 0];
        // (object)$allocatedTotals[12] = ['annualAllocatedNep'=> 0, 'fund_source_id' => 0];
            foreach ($annual as $key2 =>  $value2) {
                if(!isset($value2->remainingNEP)) {$value2->remainingNEP = $value2->NEP;}
                if(!isset($value2->remainingAmount)) {$value2->remainingAmount = $value2->amount;}
                foreach ($allocatedTotals as $key => $value) {
                    if ($value2->fund_source_id == $value->fund_source_id) {
                        $value2->remainingNEP = $value2->NEP - $value->annualAllocatedNEP;
                        $value2->remainingAmount = $value2->amount - $value->annualAllocatedAmount;
                    }
                }
            }
        // $annualTotals = DB::table('annual_budgets')
        //     // ->select('*',
        //     //     DB::raw('(milestones1 + milestones2 + milestones3) * abc AS ppmpq1'),
        //     // )
        //     ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
        //     ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
        //     ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
        //     ->where('annual_budgets.year', '=', $flash)
        //     ->where('annual_budgets.division', '=', 99)
        //     // ->where('annual_budgets.NEP',)
        //     // ->sum('annual_budgets.amount')
        //     ->sum('annual_budgets.NEP');

        // $array1 = [1,2,3,4];
        // $sum= [100, 200, 400];
        // foreach($array1 as $res)
        // {
        //     array_push(DB::table())
        // }

        // $divisionAnnualTotals = DB::table('annual_budgets')
        //     ->join('interfacev7.divisions', 'annual_budgets.division', '=', 'interfacev7.divisions.id')

        //     ->join('fund_sources', 'annual_budgets.fund_source_id', 'fund_sources.id')
        //     ->join('fund_source_parents', 'fund_sources.parent_id', 'fund_source_parents.id')
        //     ->join('fund_source_types', 'fund_sources.type_id', 'fund_source_types.id')
        //     ->where('annual_budgets.year', '=', $flash)
        //     ->where('annual_budgets.division', '!=', 99)
        //     ->get();





// $divisionAnnualTotals = [];
// foreach ($divisionAnnual as $key => $value) {
//     // foreach ($annual as $key2 => $value2) {
//         if ($value->fund_source_id == $value2->fund_source_id) {
//             $divisionAnnualTotals[$value2->fund_source_id.$key2] = $value->NEP;
//         }
//     // }
// }

        // $budgetdivision = DB::table('annual_budget')
        //     ->join('divisions', 'annual_budget.division', '=', 'divisions.id')->where('annual_budget.division', '<>', 99)
        //     ->get();

        $year = DB::table('annual_budgets')
            ->select('year')->distinct()->orderBy('year', 'desc')
            ->get();

        // $budgetSource = DB::table('annual_budget')
        //     ->select('budgetSource')->distinct()
        //     ->get();

        // $budget = DB::table('annual_budget')
        //     ->where('annual_budget.division', '=', 99)
        //     ->get();

        $divisions = DB::connection('mysql_2')->table('programs')
            ->select('sections.id AS sec_id', 'programs.id', 'program_abbr AS program', 'division_name', 'section_abbr', 'program_name AS description', 'divisions.id AS division_id')
            ->Join('divisions', 'programs.division_id', '=', 'divisions.id')
            ->Join('sections', 'programs.section_id', '=', 'sections.id')
            ->where('divisions.division_name', '<>', 'DOH-CAR')
            ->orderBy('divisions.id', 'ASC')
            ->get();

        $data = array(
            'annual' => $annual,
            'annualPrograms' => $annualPrograms,
            'allocatedTotals' => $allocatedTotals,
            'typeFunds' => $typeFunds,
            'funds' => $funds,
            'parentFunds' => $parentFunds,
            'selectedyear' => $year,
            'divisions' => $divisions,
            // 'budgetdivision' => $budgetdivision,
        );
        // return response (['success' => 'Successfully added budget for year '.$data['year'], 'data' => $data]);
        // return response (['success' => 'Successfully added budget for year '.$data['year'], 'data' => $data]);
        return $data;
    }

    function programBudget($year, $section_id)
    {
        $totalGAABD = $this->WFP->totalGAASAA($section_id, $year,'GAA');
        $totalSAABD = $this->WFP->totalGAASAA($section_id, $year,'SAA');

        $DivisionBudgetAllocation = $this->Budget->DivisionBudgetAllocation($year, $section_id);

        $annualProgramBudget = array(
            'annual_budget_programs' => $DivisionBudgetAllocation,
            'SAA' => null,                  //Total Allocated Amount for Section
            'GAA' => null,                  //Total Allocated Amount for Section
            'totalGAA' => null,             //Total Allocated Amount in WFP
            'totalSAA' => null,             //Total Allocated Amount in WFP
            'totalGAABD' => $totalGAABD,    //Total Allocated Amount in WFP BreakDown
            'totalSAABD' => $totalSAABD,    //Total Allocated Amount in WFP BreakDown
            'DivisionBudgetAllocation' => $DivisionBudgetAllocation,
        );
        foreach ($DivisionBudgetAllocation as $key => $i) {
            if ($i->parent_type_abbr == 'GAA' && $i->allocatedNEP !== null)
                $annualProgramBudget['GAA'] = $annualProgramBudget['GAA']+ $i->allocatedNEP;

            if ($i->parent_type_abbr == 'SAA' && $i->allocatedAmount !== null)
                $annualProgramBudget['SAA'] = $annualProgramBudget['SAA']+ $i->allocatedAmount;
        }

        foreach ($totalGAABD as $key => $j) {
            $annualProgramBudget['totalGAA'] = $annualProgramBudget['totalGAA']+ $j->cost;
        }

        foreach ($totalSAABD as $key => $k) {
            $annualProgramBudget['totalSAA'] = $annualProgramBudget['totalSAA']+ $k->cost;
        }
        return $annualProgramBudget;
    }

    public function storeAnnualBudget(Request $request)
    {
        $input = $request['form_data'];
        $data = array(
            'year' => $input['selected-year'],
            'fund_source_id' => $input['fundSource_id'],
            'NEP' => $input['projectedTA'],
            'amount' => $input['actualTA'],
        );

        DB::beginTransaction();
        try {
            $inputID = DB::table('annual_budgets')->insertGetId($data);
            foreach ($input['budget_description'] as $key => $value) {
                $input2 = array(
                    'annual_budgets_id' => $inputID,
                    'description' => $input['budget_description'][$key],
                    'account_code' => $input['budget_account_code'][$key],
                    'amount' => $input['budget_breakdown'][$key],
                );
                DB::table('annual_budget_breakdowns')->insert($input2);
            }
            DB::commit();
        } catch (\Illuminate\Database\QueryException  $e) {
            DB::rollback();
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }

        return response()->json([
            'success' => 'You have created a new budget for year '. $data['year'],
            'status' => 200
        ], 200);
    }

    public function storeDivisionBudget(Request $request)
    {
        $input = $request['form_data'];
        $input = array(
            // 'year' => $input['year'],
            'budget_program_name' => $input['purpose'],
            'annual_budget_id' => $input['annual_budget_id'],
            'program_id' => $input['program_id'],
            // 'amount' => $input['actualTA'],
            // 'NEP' => $input['perdivisionbudget'],
            'allocatedAmount' => $input['actualTA'],
            'allocatedNEP' => $input['perdivisionbudget'],
        );
        try {
            DB::table('annual_budget_programs')->insert($input);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have allocated a budget',
            'status' => 200
        ], 200);
    }

    public function editDivisionBudget(Request $request)
    {
        $inputt = $request['form_data'];
        // return response()->json([
        //     'error'=> $inputt,
        //     'status'=> 400
        // ], 400);

        $input = array(
            // 'year' => $input['year'],
            'annual_budget_id' => $inputt['annualllll_id'],
            'budget_program_name' => $inputt['purpose'],
            // 'annual_budget_id' => $inputt['annual_budget_id'],
            'program_id' => $inputt['program_id'],
            // 'amount' => $input['actualTA'],
            // 'NEP' => $input['perdivisionbudget'],
            'allocatedAmount' => $inputt['actualTA'],
            'allocatedNEP' => $inputt['perdivisionbudget'],
        );
        try {
            // DB::table('annual_budget_programs')->update($input);

            DB::table('annual_budget_programs')
                ->where('id', $inputt['iddddd'])
                ->update($input);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }
        return response()->json([
            'success' => 'You have allocated a budget',
            'status' => 200
        ], 200);
    }

    public function sourceStore(Request $request)
    {
        $input = $request['form_data'];
        $input1 = array(
            'parent_id' => $input['budget_parent_id'],
            'type_id' => $input['budget_type_id'],
            'saa_number' => $input['budget_saa_number'],
            'source_abbr' => $input['budget_fund_name_abbr'],
            'source_name' => $input['budget_fund_name'],
            'purpose' => $input['budget_purpose'],
        );
        try {
            $inputID = DB::table('fund_sources')->insertGetId($input1);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response()->json([
                'error'=> $e->getMessage(),
                'status'=> 400
            ], 400);
        }

        // try {
        //     foreach ($input['budget_description'] as $key => $value) {
        //         $input2 = array(
        //             'fund_sources_id' => $inputID,
        //             'description' => $input['budget_description'][$key],
        //             'account_code' => $input['budget_account_code'][$key],
        //             'amount' => $input['budget_breakdown'][$key],
        //         );
        //         DB::table('fund_source_breakdowns')->insert($input2);
        //     }
        // } catch (\Illuminate\Database\QueryException  $e) {
        //     return response()->json([
        //         'error'=> $e->getMessage(),
        //         'status'=> 400
        //     ], 400);
        // }
        return response()->json([
            'success' => 'You have created a  new budget source',
            'status' => 200
        ], 200);
    }
}

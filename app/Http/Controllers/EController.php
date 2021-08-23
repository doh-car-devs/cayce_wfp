<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class EController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = unserialize(base64_decode($request->header('LOGGED_USER')));
        $users = DB::select('select id, name, name_middle, name_family from users');
        
        
        $data = array(
            // 'total' => $total, 
            // 'lhsdsection' => $lhsdsection, 
            // 'program' => $program, 
            // 'funds' => $funds, 
            // 'allwfp' => $allwfp, 
            // 'wfpCategory' => $wfpCategory, 
            // 'allppmp' => $allppmp, 
            // 'months' => $months, 
            // 'monthsShort' => $monthsShort, 
            'users' => $users, 
        );
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = unserialize(base64_decode($request->header('LOGGED_USER')));
        // $users = DB::select('select id, name, name_middle, name_family from users');
        $pui = DB::table('covid19')
            ->where('finalclass', '=', 'Patient Under Investigation (PUI)')
            ->count();        
        $pum = DB::table('covid19')
            ->where('finalclass', '=', 'Patient Under Monitoring (PUM)')
            ->count();    
        $covid = DB::table('covid19')
            ->where('finalclass', '=', 'Confirmed COViD-19 Case')
            ->count(); 
               
        $all = DB::table('covid19')
            // ->where('finalclass', '=', 'Confirmed COViD-19 Case')
            ->count(); 

        $data = array(
            // 'total' => $total, 
            // 'lhsdsection' => $lhsdsection, 
            // 'program' => $program, 
            // 'funds' => $funds, 
            // 'allwfp' => $allwfp, 
            // 'wfpCategory' => $wfpCategory, 
            // 'allppmp' => $allppmp, 
            // 'months' => $months, 
            // 'monthsShort' => $monthsShort, 
            'users' => $user, 
            'all' => $all, 
            'pui' => $pui, 
            'pum' => $pum, 
            'covid' => $covid, 
        );
        return $data;
        // return $request;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $input;
        $input = array(
            'division' => $request->input('division'),
            'dateofinterview' => $request->input('dateofinterview'),
            'connum' => $request->input('connum'),
            'lnofinvest' => $request->input('lnofinvest'),
            'fnofinvest' => $request->input('fnofinvest'),
            'mnofinvest' => $request->input('mnofinvest'),

            'lnp' => $request->input('lnp'),
            'fnp' => $request->input('fnp'),
            'mnp' => $request->input('mnp'),
            'bday' => $request->input('bday'),
            'sex' => $request->input('sex'),
            'civilstat' => $request->input('civilstat'),
            'occupation' => $request->input('occupation'),
            'religion' => $request->input('religion'),
            'nationality' => $request->input('nationality'),
            'passnum' => $request->input('passnum'),
            'housenum' => $request->input('housenum'),
            'street' => $request->input('street'),
            'munorcity' => $request->input('munorcity'),
            'province' => $request->input('province'),
            'region' => $request->input('region'),
            'homenum' => $request->input('homenum'),
            'cellnum' => $request->input('cellnum'),
            'email' => $request->input('email'),

            'ename' => $request->input('ename'),
            'eoccupation' => $request->input('eoccupation'),
            'placeofwork' => $request->input('placeofwork'),
            'ehousenum' => $request->input('ehousenum'),
            'estreet' => $request->input('estreet'),
            'ecityormun' => $request->input('ecityormun'),
            'eprovorstate' => $request->input('eprovorstate'),
            'country' => $request->input('country'),
            'officenum' => $request->input('officenum'),
            'ecellnum' => $request->input('ecellnum'),

            'histoftravel' => $request->input('histoftravel'),
            'portofexit' => $request->input('portofexit'),
            'airorsea' => $request->input('airorsea'),
            'forvnum' => $request->input('forvnum'),
            'dateofdep' => $request->input('dateofdep'),
            'dateofarr' => $request->input('dateofarr'),
            
            'histofexpo' => $request->input('histofexpo'),
            'dateofcontact' => $request->input('dateofcontact'),
            
            'clinicstatus' => $request->input('clinicstatus'),
            'dateofonset' => $request->input('dateofonset'),
            'dateofadmit' => $request->input('dateofadmit'),
            'temp' => $request->input('temp'),
            
            'cough' => $request->input('cough'),
            'sorethroat' => $request->input('sorethroat'),
            'colds' => $request->input('colds'),
            'shortofbreath' => $request->input('shortofbreath'),
            
            'othersymp' => $request->input('othersymp'),
            'otherill' => $request->input('otherill'),
            'xray' => $request->input('xray'),
            'pregnant' => $request->input('pregnant'),
            'lmp' => $request->input('lmp'),
            'cxr' => $request->input('cxr'),
            'otherrad' => $request->input('otherrad'),
            
            'serum' => $request->input('serum'),
            'serumdate' => $request->input('serumdate'),
            'serumdate2' => $request->input('serumdate2'),
            'serumdate3' => $request->input('serumdate3'),
            'serumresult' => $request->input('serumresult'),
            'serumresult2' => $request->input('serumresult2'),
            'swab' => $request->input('swab'),
            'swabdate' => $request->input('swabdate'),
            'swabdate2' => $request->input('swabdate2'),
            'swabdate3' => $request->input('swabdate3'),
            'swabresult' => $request->input('swabresult'),
            'swabresult2' => $request->input('swabresult2'),

            'finalclass' => $request->input('finalclass'),
            'datedischarge' => $request->input('datedischarge'),
            'ofresult' => $request->input('ofresult'),
            'relationship' => $request->input('relationship'),
            'phonenumrel' => $request->input('phonenumrel'),
        );
        try {
            DB::table('covid19')->insert($input);
        } catch (\Illuminate\Database\QueryException  $e) {
            return response(['error'=> $e->getMessage()]);
        }

        return response(['success'=> 'Successfully added new entry']);

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
}

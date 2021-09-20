<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// fix api key
// fix api key
// fix api key
Route::group(['prefix' => 'peek', 'as' => 'peek'], function() {
    Route::get('/ppmpinwfp/{wfp_id?}', 'PPMPController@peekPPMP')->name('.ppmpinwfp');
});

Route::group(['prefix' => 'twg', 'as' => 'twg'], function() {
    Route::get('/export/postqualevalreport/{year?}', 'BidsAwardsController@postQualEvalReport')->name('.export.postqualevalreport');
});

Route::group(['prefix' => 'export', 'as' => 'export'], function() {
    Route::post('/rpr/{year?}/{secDiv?}/{value?}', 'ProcurementController@requestPurchaseRequest')->name('.rpr');
    Route::post('/rpo/{year?}/{secDiv?}/{value?}', 'ProcurementController@requestPurchaseOrder')->name('.rpo');
    Route::get('/pr/{year?}', 'ProcurementController@generatePurchaseRequest')->name('.pr');
    Route::get('/po/{year?}', 'ProcurementController@generatePurchaseOrder')->name('.po');
    Route::get('/wfp/', 'WFPController@generateWFPReport')->name('.wfp');
    Route::get('/generateConsolidatedWFPReport/', 'WFPController@generateConsolidatedWFPReport')->name('.generateConsolidatedWFPReport');
    Route::get('/generateMasterConsolidatedWFPReport/', 'WFPController@generateMasterConsolidatedWFPReport')->name('.generateMasterConsolidatedWFPReport');
    Route::get('/ppmp/', 'PPMPController@generatePPMPReport')->name('.ppmp');
    Route::get('/APPOffice/', 'PPMPController@generateAPPOffice')->name('.APPOffice');
    Route::get('/app/{fund_type?}/{year?}/{division?}/{section?}/{program?}' , 'PPMPController@createapp')->name('.app');

    // delete
    Route::get('dont/open/here/delete' , 'PPMPController@formatdelete')->name('.dont.open.here');
});

Route::group(['prefix' => 'pt', 'as' => 'pt'], function() {
    Route::get('/app/public/{fund_type?}/{year?}/{division?}/{section?}/{program?}' , 'PPMPController@createapp')->name('.app');
});
Route::group(['middleware' => ['apikey']], function() {

    // Budget
    Route::group(['prefix' => 'budget', 'as' => 'budget'], function() {
        Route::resource('/home', 'BudgetController');
        Route::get('/annbudget', 'BudgetController@annualBudget')->name('.annualbudget');
        Route::post('/storeannbudget', 'BudgetController@storeAnnualBudget')->name('.storeannbudget');
        Route::post('/storedivbudget', 'BudgetController@storeDivisionBudget')->name('.storedivbudget');
        Route::post('/editDivisionBudget', 'BudgetController@editDivisionBudget')->name('.editDivisionBudget');
        Route::post('/sourceStore', 'BudgetController@sourceStore')->name('.source.store');
    });

    // Bids and Awards
    Route::group(['prefix' => 'bac', 'as' => 'bac'], function() {
        Route::get('/abstract/{year?}', 'BidsAwardsController@abstractOfBids')->name('.abstract');
        Route::get('/abstractCanvas/{year?}', 'BidsAwardsController@abstractCanvas')->name('.abstractCanvas');
        Route::get('/bidderWithBid/{year?}', 'BidsAwardsController@BidderWithBid')->name('.BidderWithBid');
        Route::get('/bidderBids/{bidder_id?}/{year?}', 'BidsAwardsController@bidderBids')->name('.bidderBids');
        Route::get('/abstract/item/{year?}/{item_id}', 'BidsAwardsController@abstractItem')->name('.abstract.item');
        Route::post('/bidder/win/{bidderID?}', 'BidsAwardsController@bidderWinStore')->name('.bidderWin.store');
    });





    // MSD
    Route::group(['prefix' => 'msd', 'as' => 'msd'], function() {
        Route::resource('/home', 'MSDController');
    });

    // SYSTEM ADMIN CONTROLLER
    Route::group(['prefix' => 'system'], function() {
        Route::resource('/system', 'SystemAdmin');
        Route::get('/divisions', 'SystemAdmin@divisions')->name('system.divisions');
    });
    //Division Head
    Route::group(['prefix' => 'manage', 'as' => 'manage'], function() {
        Route::get('/divisionwfp/{year?}/{division?}/{section?}/{program?}' , 'WFPController@divisionWFP')->name('.approvewfp');
        Route::post('/dhcomment' , 'WFPController@dhComment')->name('.dhComment');
        Route::post('/wfpapprove' , 'WFPController@wfpApprove')->name('.wfpApprove');
        Route::post('/wfpapproveYear' , 'WFPController@wfpApproveYear')->name('.wfpapproveYear');
    });

    // PT
    Route::group(['prefix' => 'pt', 'as' => 'pt'], function() {
        Route::get('/app/{fund_type?}/{year?}/{division?}/{section?}/{program?}' , 'PPMPController@createapp')->name('.app');
    });

    ////////////////////////////////Public Protected Pages//////////////////////////////

    Route::group(['prefix' => 'ajax', 'as' => 'ajax'], function() {
        Route::get('/items/{term?}' , 'WFPController@itemList')->name('.items/{term?}');
        Route::get('/deliverable/{division_id?}/{section_id?}/{term?}' , 'WFPController@deliverableList')->name('.deliverable.{division_id?}.{section_id?}.{term?}');
        Route::get('/bidder/{term?}' , 'BidsAwardsController@bidderList')->name('.bidder.{term?}');
    });

    // Route::resource('/covid', 'EController');
    // Route::get('/covid/' , 'WFPController@createwfp')->name('.wfp');
    Route::group(['prefix' => 'em', 'as' => 'em'], function() {
        Route::get('/covid' , 'EController@create')->name('.create');
        Route::post('/covid/store' , 'EController@store')->name('.store');
    });

    // Show create blades
    Route::group(['prefix' => 'create', 'as' => 'create'], function() {
        Route::get('/wfp/' , 'WFPController@createWFP')->name('.wfp');
        Route::get('/pr/{year?}', 'ProcurementController@purchaseRequest')->name('.pr');
        Route::get('/po/{year?}', 'ProcurementController@purchaseOrder')->name('.po');
        // Route::get('/divisionwfp' , 'WFPController@divisionWFP')->name('.approvewfp');
    });

    // GET API
    Route::group(['prefix' => 'get', 'as' => 'get'], function() {
        Route::get('/programBudget/{year?}/{section_id?}' , 'BudgetController@programBudget')->name('.programBudget');
    });

    // Store routes
    Route::group(['prefix' => 'store', 'as' => 'store'], function() {
        Route::post('/wfp' , 'WFPController@storeWFP')->name('.wfp');
        Route::post('/deliverable' , 'WFPController@storeDeliverable')->name('.wfp');
        Route::post('/ppmp' , 'PPMPController@storePPMP')->name('.ppmp');
        Route::post('/bid' , 'BidsAwardsController@storeBid')->name('.bid');
    });

    // Update routes
    Route::group(['prefix' => 'edit', 'as' => 'edit'], function() {
        Route::post('/wfp' , 'WFPController@editWfp')->name('.wfp');
        Route::post('/ppmp/{id}' , 'PPMPController@editPpmp')->name('.ppmp');
        Route::post('/ppmpApprove/approve' , 'PPMPController@ppmpApprove')->name('.ppmpApprove');
        Route::post('/prn/store/{prid?}/{prnumber?}' , 'ProcurementController@purchaseRequestNumber')->name('.prn.store');
    });


    // Delete routes
    Route::group(['prefix' => 'rm', 'as' => 'rm'], function() {
        Route::post('/wfp/{id}' , 'WFPController@deleteWfp')->name('.wfp');
        Route::post('/ppmp/{id}' , 'PPMPController@deletePPMP')->name('.ppmp');
    });

    // Export routes
    // Route::group(['prefix' => 'export', 'as' => 'export'], function() {
    //     Route::post('/wfp/{id}' , 'WFPController@deleteWfp')->name('.wfp');
    // });

    // Route::group(['prefix' => 'twg', 'as' => 'twg'], function() {
    //     Route::get('/export/postqualevalreport/{year?}', 'BidsAwardsController@postQualEvalReport')->name('.export.postqualevalreport');
    // });

});

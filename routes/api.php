<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Registration\RegistrationAmountController;
use App\Http\Controllers\Registration\RegistrationNumberDetails\MutationDetailsController;
use App\Http\Controllers\Registration\RegistrationNumberDetails\PlotOrFlatDetailsController;
use App\Http\Controllers\Registration\RegistrationNumberDetails\PowerOfAttorneyDetailsController;
use Illuminate\Support\Facades\Route;

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'total']);

        Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show']);
        Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update']);

    });

    Route::prefix('projects')->group(function () {

        // project list and view-details
        Route::get('/', [\App\Http\Controllers\Project\AllController::class, 'view']);
        Route::get('/viewDetails/{id}', [\App\Http\Controllers\Project\AllController::class, 'viewDetails']);
        Route::get('/customerDetails/{id}', [\App\Http\Controllers\Project\AllController::class, 'projectCustomerDetails']);

        // building add,data show,data update
        Route::post('/building/store', [\App\Http\Controllers\Project\BuildingController::class, 'store']);
        Route::get('/building/data/{id}', [\App\Http\Controllers\Project\BuildingController::class, 'data']);
        Route::post('/building/update/{id}', [\App\Http\Controllers\Project\BuildingController::class, 'update']);

        // land add,data show,data update
        Route::post('/land/store', [\App\Http\Controllers\Project\LandController::class, 'store']);
        Route::get('/land/data/{id}', [\App\Http\Controllers\Project\LandController::class, 'data']);
        Route::post('/land/update/{id}', [\App\Http\Controllers\Project\LandController::class, 'update']);

//    Route::get('/search/{id}',[ProjectController::class,'search']);
    });

    Route::prefix('flat_or_plot')->group(function () {

        // flat or plot add, detail show ,update according to specific building or land
        Route::post('/store', [\App\Http\Controllers\Project\FlatOrPlotController::class, 'store']);
        Route::get('/detail/{id}', [\App\Http\Controllers\Project\FlatOrPlotController::class, 'detail']);
        Route::post('/update/{id}', [\App\Http\Controllers\Project\FlatOrPlotController::class, 'update']);
        // Route::get('search/{id}', [\App\Http\Controllers\Project\FlatOrPlotController::class,'search']);

    });

    //Reports API Start

    Route::prefix('reports')->group(function () {
        Route::get('/additionalAmountList', [\App\Http\Controllers\Reports\AdditionalAmountController::class, 'getAdditionalAmountList']);
        Route::get('/installmentDueList', [\App\Http\Controllers\Reports\InstallmentDueController::class, 'getInstallmentDueList']);
        Route::get('/downpaymentDueList', [\App\Http\Controllers\Reports\DownpaymentDueController::class, 'getDownpaymentDueList']);
        Route::get('/utilityAndCarParkingDueList', [\App\Http\Controllers\Reports\UtilityAndCarParkingDueListController::class, 'getUtilityAndCarParkingDueList']);
        Route::get('/registrationAndMutationDueList', [\App\Http\Controllers\Reports\RegistrationAndMutationDueListController::class, 'getRegistrationAndMutationDueList']);
        Route::get('/registrationCompleteButPaymentDueList', [\App\Http\Controllers\Reports\RegistrationCompleteButPaymentDueListController::class, 'getRegistrationCompleteButPaymentDueList']);
        Route::group(['prefix' => 'cancelCustomerList'], function () {
            Route::get('/getAllFileInfo', [\App\Http\Controllers\Reports\CancelCustomerListController::class, 'getAllFileInfo']);
            Route::post('/addCancelCustomer', [\App\Http\Controllers\Reports\CancelCustomerListController:: class, 'storeCanceledCustomer']);
            Route::post('/editCanceledCustomer', [\App\Http\Controllers\Reports\CancelCustomerListController:: class, 'updateCanceledCustomer']);
            Route::get('/viewCanceledCustomerInfo', [\App\Http\Controllers\Reports\CancelCustomerListController:: class, 'getCanceledCustomerInfo']);
            Route::post('/addPayment', [\App\Http\Controllers\Reports\CancelCustomerListController:: class, 'addPayment']);
        });
        Route::group(['prefix' => 'afterRegCancelCustomerList'], function () { 
            Route::get('/getAllFileInfo', [\App\Http\Controllers\Reports\AfterRegCancelCustomerListController::class, 'getAllFileInfo']); 
            Route::post('/addCancelCustomer', [\App\Http\Controllers\Reports\AfterRegCancelCustomerListController:: class, 'storeCanceledCustomer']);
            Route::post('/editCanceledCustomer', [\App\Http\Controllers\Reports\AfterRegCancelCustomerListController:: class, 'updateCanceledCustomer']);
            Route::get('/viewCanceledCustomerInfo', [\App\Http\Controllers\Reports\AfterRegCancelCustomerListController:: class, 'getCanceledCustomerInfo']);
            Route::post('/addPayment', [\App\Http\Controllers\Reports\AfterRegCancelCustomerListController:: class, 'addPayment']);
            Route::post('/addBuyBackData', [\App\Http\Controllers\Reports\AfterRegCancelCustomerListController:: class, 'addBuyBackData']);
            Route::post('/editBuyBackData', [\App\Http\Controllers\Reports\AfterRegCancelCustomerListController:: class, 'updateBuyBackData']);
            }); 
    });

    //Reports API End

    Route::prefix('customer')->group(function () {

        //Client Information API start
        Route::post('/information/store', [\App\Http\Controllers\Customer\InformationController::class, 'store']);
        Route::get('/information/view', [\App\Http\Controllers\Customer\InformationController::class, 'view']);
        Route::post('/information/store', [\App\Http\Controllers\Customer\InformationController::class, 'store']);
        Route::post('/information/details', [\App\Http\Controllers\Customer\InformationController::class, 'details']);
        Route::get('/information/edit/{flat_id}', [\App\Http\Controllers\Customer\InformationController::class, 'edit']);

        Route::post('/information/update', [\App\Http\Controllers\Customer\InformationController::class, 'update']);

        Route::post('/status/active', [\App\Http\Controllers\Customer\InformationController::class, 'active']);
        Route::post('/status/deactive', [\App\Http\Controllers\Customer\InformationController::class, 'deactive']);

        Route::post('/building_land/all', [\App\Http\Controllers\Customer\InformationController::class, 'getBuildingLand']);
        Route::get('/building/flat/{id}/{flat_or_plot_id?}', [\App\Http\Controllers\Customer\InformationController::class, 'flatSelect']);
        Route::get('/land/plot/{id}/{flat_or_plot_id?}', [\App\Http\Controllers\Customer\InformationController::class, 'plotSelect']);

        /* API START::folder create for a file*/
        Route::get('/information/folder/list/{flatOrPlotID}', [\App\Http\Controllers\Customer\InformationController::class, 'getFolderList']);
        Route::post('/information/folder/create', [\App\Http\Controllers\Customer\InformationController::class, 'createFolder']);
        /* API END::folder create for a file*/

        /* API START::document upload into a folder and get the document*/
        Route::get('/information/folder/document/list/{folder_id}', [\App\Http\Controllers\Customer\InformationController::class, 'folderDocumentList']);
        Route::post('/information/folder/document/store', [\App\Http\Controllers\Customer\InformationController::class, 'folderDocumentStore']);
        /* API End::document upload into a folder and get the document*/

        //Client Information API end

        // Client Price Information start
        Route::prefix('price')->group(function () {
            Route::get('/list', [\App\Http\Controllers\Customer\PriceInformationController::class, 'list']);
            Route::post('/store', [\App\Http\Controllers\Customer\PriceInformationController::class, 'store']);
            Route::get('/flatorplot/{id}', [\App\Http\Controllers\Customer\PriceInformationController::class, 'getFlatOrPlotData']);
            Route::get('/detail/{id}', [\App\Http\Controllers\Customer\PriceInformationController::class, 'detail']);

            Route::post('/update', [\App\Http\Controllers\Customer\PriceInformationController::class, 'update']);

            //handle status
            Route::get('/handleActive/{id}', [\App\Http\Controllers\Customer\PriceInformationController::class, 'handleActive']);
            Route::get('/handleDeactive/{id}', [\App\Http\Controllers\Customer\PriceInformationController::class, 'handleDeactive']);
        });
        // Client Price Information end

        // Client Payment start
        Route::prefix('payment')->group(function () {
            Route::get('/list', [\App\Http\Controllers\Customer\PaymentController::class, 'list']);
            Route::post('/store', [\App\Http\Controllers\Customer\PaymentController::class, 'store']);
            Route::get('/details/{id}', [\App\Http\Controllers\Customer\PaymentController::class, 'details']);

            Route::get('/downpayment/{id}', [\App\Http\Controllers\Customer\PaymentController::class, 'downpayment']);
            Route::get('/installment/{id}', [\App\Http\Controllers\Customer\PaymentController::class, 'installment']);
            //new
            Route::get('/additional_amount/{id}', [\App\Http\Controllers\Customer\PaymentController::class, 'additional_amount']);
            // route for update given amount
            Route::post('/getEditData', [\App\Http\Controllers\Customer\PaymentController::class, 'getEditData']);
            Route::get('/getUpgradeableDownpayment/{id}', [\App\Http\Controllers\Customer\PaymentController::class, 'getUpgradeableDownpayment']);
            //new
            Route::get('/getUpgradeableAdditionalAmount/{id}', [\App\Http\Controllers\Customer\PaymentController::class, 'getUpgradeableAdditionalAmount']);
            Route::get('/getUpgradeableInstallment/{id}', [\App\Http\Controllers\Customer\PaymentController::class, 'getUpgradeableInstallment']);
            Route::post('/update', [\App\Http\Controllers\Customer\PaymentController::class, 'update']);

            // Payment folder routes
            Route::get('/folder/list/{price_information_id}', [\App\Http\Controllers\Customer\PaymentController::class, 'folderList']);
            Route::post('/folder/create', [\App\Http\Controllers\Customer\PaymentController::class, 'createFolder']);

            Route::post('/folder/document/store', [\App\Http\Controllers\Customer\PaymentController::class, 'storeDocument']);
            Route::get('/folder/document/list/{folder_id}', [\App\Http\Controllers\Customer\PaymentController::class, 'documentList']);

        });
        // Client Payment end

        //due amount start
        Route::get('/due/amount/list', [\App\Http\Controllers\Customer\DueAmountController::class, 'list']);
        //due amount end
    });

//----------------------------------------- Registration Amount Details -------------------------------------------------
    Route::prefix('registration')->group(function () {

        //----------------------------------------- Registration -------------------------------------------------
        Route::get('/amount/view', [RegistrationAmountController::class, 'amountView']);
        Route::get('/amount/due_details/{id}', [RegistrationAmountController::class, 'dueDetails']);
        Route::post('/amount/add_price', [RegistrationAmountController::class, 'addPrice']);
        Route::post('/amount/add_payment', [RegistrationAmountController::class, 'addPayment']);

        //------------------------------------- Registration Status --------------------------------------------------------
        Route::post('/status/list', [RegistrationAmountController::class, 'statusListView']);
        Route::post('/status/update/{id}/{status_type}/{value}', [RegistrationAmountController::class, 'updateStatus']);
        Route::post('/status/search', [RegistrationAmountController::class, 'searchStatus']);

        //----------------------------------------- Registration : Plot/Flat Details -------------------------------------------------
        Route::group(['prefix' => 'plotOrFlat'], function () {
            Route::get('/manage', [PlotOrFlatDetailsController::class, 'index']);
            Route::post('/store', [PlotOrFlatDetailsController::class, 'store']);
            Route::get('/addCustomerToPlotOrFlat', [PlotOrFlatDetailsController::class, 'addCustomer']);
            Route::get('/getCustomerInfo/{id}', [PlotOrFlatDetailsController::class, 'getCustomerInfo']);
            Route::post('/assignCustomer/{id}', [PlotOrFlatDetailsController::class, 'assignCustomer']);
            Route::post('/update/{id}', [PlotOrFlatDetailsController::class, 'update']);
            Route::get('/plotOrFlatDetails/{id}', [PlotOrFlatDetailsController::class, 'plotOrFlatDetails']);
            Route::post('/plotOrFlatDetailsView', [PlotOrFlatDetailsController::class, 'plotOrFlatDetailsView']);
            Route::post('/plotOrFlatDetailsSearch', [PlotOrFlatDetailsController::class, 'plotOrFlatDetailsSearch']);
            // Route::get('/delete/{id}', [PlotOrFlatDetailsController::class, 'destroy']);
        });

        //----------------------------------------- Registration : Mutation Details -------------------------------------------------
        Route::group(['prefix' => 'mutationDetails'], function () {
            Route::get('/manage', [MutationDetailsController::class, 'index']);
            Route::post('/store', [MutationDetailsController::class, 'store']);
            Route::get('/addCustomerToMutation', [MutationDetailsController::class, 'addCustomerToMutation']);
            Route::get('/getCustomerInfo/{id}', [MutationDetailsController::class, 'getCustomerInfo']);
            Route::post('/assignCustomer/{id}', [MutationDetailsController::class, 'assignCustomer']);
            Route::post('/update/{id}', [MutationDetailsController::class, 'update']);
            Route::get('/mutationDetails/{id}', [MutationDetailsController::class, 'mutationDetails']);
            Route::post('/mutationDetailsView', [MutationDetailsController::class, 'mutationDetailsView']);
//        Route::get('/delete/{id}', [MutationDetailsController::class, 'destroy']);
        });

        //----------------------------------------- Registration : Power Of Attorney Details -------------------------------------------------
        Route::group(['prefix' => 'powerOfAttorneyDetails'], function () {
            Route::get('/manage', [PowerOfAttorneyDetailsController::class, 'index']);
            Route::post('/store', [PowerOfAttorneyDetailsController::class, 'store']);
            Route::get('/addCustomerToPowerOfAttorney', [PowerOfAttorneyDetailsController::class, 'addCustomerToPowerOfAttorney']);
            Route::get('/getCustomerInfo/{id}', [PowerOfAttorneyDetailsController::class, 'getCustomerInfo']);
            Route::post('/assignCustomer/{id}', [PowerOfAttorneyDetailsController::class, 'assignCustomer']);
            Route::post('/update/{id}', [PowerOfAttorneyDetailsController::class, 'update']);
            Route::get('/powerOfAttorney/{id}', [PowerOfAttorneyDetailsController::class, 'powerOfAttorney']);
            Route::post('/powerOfAttorneyView', [PowerOfAttorneyDetailsController::class, 'powerOfAttorneyDetailsView']);
//        Route::get('/delete/{id}', [PlotOrFlatDetailsController::class, 'destroy'])->name('powerOfAttorneyDetails.delete');
        });

    });

});

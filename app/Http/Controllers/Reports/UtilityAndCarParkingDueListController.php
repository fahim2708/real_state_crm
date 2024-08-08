<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\FlatOrPlot;
use Illuminate\Http\Request;

class UtilityAndCarParkingDueListController extends Controller
{
    public function getUtilityAndCarParkingDueList(Request $request)
    {
        $data = FlatOrPlot::whereHas('customers')->with(['customers.soldItem','project','priceInformation'])->get();
        return response()->json([
           'data' => $data
        ]);
    }
}
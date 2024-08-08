<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\FlatOrPlot;
use Illuminate\Http\Request;

class DownpaymentDueController extends Controller
{
    public function getDownpaymentDueList(Request $request)
    {
        $data = FlatOrPlot::whereHas('customers')->with(['customers.soldItem','project','priceInformation' => function($query){
            $query->with(['downpayment']);
        }])->get();
        return response()->json([
           'data' => $data
        ]);
    }
}

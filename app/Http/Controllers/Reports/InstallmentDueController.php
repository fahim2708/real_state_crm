<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\FlatOrPlot;
use Illuminate\Http\Request;

class InstallmentDueController extends Controller
{
    public function getInstallmentDueList(Request $request) 
        {
            $data = FlatOrPlot::whereHas('customers')->with(['customers.soldItem','project','priceInformation' => function($query){
                $query->with(['installment']);
            }])->get();
            return response()->json([
               'data' => $data
            ]);
        }
    
}

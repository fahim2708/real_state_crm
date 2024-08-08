<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\PlotOrFlatRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationAndMutationDueListController extends Controller
{
    public function getRegistrationAndMutationDueList(Request $request)
    {
        $data = PlotOrFlatRegistration::with('plotOrFlatDetails', 'mutationDetails', 'customers', 'registration_amount', 'flatOrPlotDetails', 'project')->get();
        
        return response()->json([
           'data' => $data,
        ]);
        
    }
    
}

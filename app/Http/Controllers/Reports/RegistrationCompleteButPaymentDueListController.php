<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\PlotOrFlatRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationCompleteButPaymentDueListController extends Controller
{
    public function getRegistrationCompleteButPaymentDueList(Request $request)
    {

        $data = PlotOrFlatRegistration::with('project', 'plotOrFlatDetails', 'mutationDetails', 'customers', 'registration_amount', 'flatOrPlotDetails', 'priceInformation')->get();
        return response()->json([
            'data' => $data,
        ]);

    }

}
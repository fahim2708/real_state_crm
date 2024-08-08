<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FlatOrPlot;
use App\Models\Project;

class DashboardController extends Controller
{

    public function total()
    {
        $customer = Customer::count();

        $soldFlat = FlatOrPlot::whereHas('project',function ($q)
        {
            $q->where('type',Project::$BUILDING);
        })->where('status',1)->count();

        $unsoldFlat = FlatOrPlot::whereHas('project',function ($q)
        {
            $q->where('type',Project::$BUILDING);
        })->where('status',0)->count();


        $soldPlot = FlatOrPlot::whereHas('project',function ($q)
        {
            $q->where('type',Project::$LAND);
        })->where('status',1)->count();

        $unsoldPlot = FlatOrPlot::whereHas('project',function ($q)
        {
            $q->where('type',Project::$LAND);
        })->where('status',0)->count();

        return response()->json([
           'status' => 'success',
           'data' => [
               'total_customer'     => $customer,
               'total_sold_flat'    => $soldFlat,
               'total_unsold_flat'  => $unsoldFlat,
               'total_sold_plot'    => $soldPlot,
               'total_unsold_plot'  => $unsoldPlot,
           ]
        ]);
    }

}

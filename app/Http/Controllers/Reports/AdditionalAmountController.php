<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\FlatOrPlot;
use Illuminate\Http\Request;

class AdditionalAmountController extends Controller
{

    public function getAdditionalAmountList(Request $request)
    {

        $limit = $request->post('no_of_rows');
        $offset = ($request->post('page', 1) - 1) * $limit;

        $list = FlatOrPlot::whereHas('customers')->with(['customers', 'project' => function ($q) {
            $q->select('id', 'project_no');
        }, 'priceInformation' => function ($query) {
            $query->select('id', 'flat_or_plot_id',
                'total_money as flat_or_plot_price',
                'total_additional_amount',
                'project_type'
            )->with(['additional_amount']);

        }]);

        $search = $request->search;
        if ($search) {
            $list = $list->where('file_no', 'like', '%' . $search . '%');
        }

        $total_list = $list->count();

        $list = $list->orderBy('id', 'desc')->take($limit)->get();


        return response()->json([
            'data' => [
                'total' => $total_list,
                'page' => $request->post('page', 1),
                'no_of_rows' => count($list),
                'data' => $list,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PriceInformation;
use Carbon\Carbon;
use Illuminate\Http\Request;


class DueAmountController extends Controller
{
    // due list
    public function list(Request $request)
    {
        $limit = $request->post('no_of_rows');
        //$offset = ($request->post('page', 1) - 1) * $limit;
        $search = $request->search;

        $dueList = PriceInformation::with(['flatOrPlot' => function($q) use ($search) {
            $q->with('customers');
            if($search)
            {
                $q->where('file_no','like','%'.$search.'%');
            }
        }])
        ->whereHas('flatOrPlot',function ($query) use ($search)
        {
            if($search)
            {
                $query->where('file_no','like','%'.$search.'%');
            }
        });


        $total_list = $dueList->count();

        $dueList = $dueList->orderBy('id','desc')->take($limit)
        ->selectRaw('
        id,
        flat_or_plot_id,
        (total_money+car_parking+utility_charge+total_additional_amount) as total_money,
        (total_money - (total_booking_money_paid + total_car_parking_paid + total_utility_charge_paid + total_additional_amount_paid + total_installment_amount_paid + total_downpayment_amount_paid)) as total_due_payment,
        (booking_money - total_booking_money_paid )   as booking_money_due_amount,
        (car_parking - total_car_parking_paid )       as car_parking_due_amount,
        (utility_charge - total_utility_charge_paid ) as utility_charge_due_amount,
        (total_additional_amount - total_additional_amount_paid ) as additional_amount_due
        ')
            ->withSum(['installment as total_installment_money'=>function($query)
            {
                $query->where('start_date','<=',Carbon::today());
            }],'amount')
            ->withSum(['installment as paid_installment_money'=>function($query)
            {
                $query->where('start_date','<=',Carbon::today());
            }],'paid')
            ->withSum(['downPayment as total_downPayment_money'=>function($query)
            {
                $query->where('start_date','<=',Carbon::today());
            }],'amount')
            ->withSum(['downPayment as paid_downPayment_money'=>function($query)
            {
                $query->where('start_date','<=',Carbon::today());
            }],'paid')->get();


        return response()->json([
            'data' => [
                'total' => $total_list,
                'page' => $request->post('page', 1),
                'no_of_rows' => count($dueList),
                'due' => $dueList
            ]

        ]);
    }
}

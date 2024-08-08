<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Downpayment;
use App\Models\AdditionalAmount;
use App\Models\Flat;
use App\Models\FlatOrPlot;
use App\Models\Installment;
use App\Models\PaymentHistory;
use App\Models\PriceInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PriceInformationController extends Controller
{
    // all list and view
    public function list(Request $request)
    {

        //$limit = $request->post('no_of_rows');
        //$offset = ($request->post('page', 1) - 1) * $limit;

        $list = FlatOrPlot::whereHas('customers')->with(['customers','project'=>function($q){
            $q->select('id','project_no');
        },'priceInformation' =>function($query)
        {
            $query->select('id','flat_or_plot_id',
            'total_money as flat_or_plot_price',
            'booking_money',
            'car_parking',
            'utility_charge',
            'additional_work_amount',
            'total_installment_amount',
            'per_month_installment_amount',
            'total_downpayment_amount',
            'total_additional_amount',
            'number_of_installment',
            'project_type'
            )->with(['downPayment','installment','additional_amount']);

        }]);

        $search = $request->search;
        if($search)
        {
            $list = $list->where('file_no','like','%'.$search.'%');
        }

        $list = $list->orderBy('id','desc')->get();


        return response()->json([
            'data' => [
                'data' => $list
            ]

        ]);
    }

    //price info details
    public  function  detail($id)
    {
        $detail = FlatOrPlot::whereHas('customers')->with(['customers.soldItem','project','priceInformation' => function($query){
            $query->with(['downPayment','installment','additional_amount']);
        }])->find($id);
        return response()->json([
           'detail' => $detail
        ]);
    }

    //   add price information to a flat or plot method
    public function getFlatOrPlotData($flatOrPlotId)
    {
        $getId = FlatOrPlot::whereHas('customers')->with('project','customers')->find($flatOrPlotId);
        return response([
            'message'   => 'Success',
            'data'      => $getId
        ],200);
    }

    // price information store
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'flat_or_plot_id'=>'required',
            'project_type' => 'required',

            'total_money' => 'required|numeric',
            'booking_money' => 'required|numeric',
            'booking_money_date' => 'required',

            'total_installment_amount' => 'required|numeric',
            'per_month_installment_amount' => 'required|numeric',
            'number_of_installment' => 'required|numeric',
            'total_downpayment_amount' => 'required|numeric',
            'total_additional_amount' => 'required|numeric',

            'downpayment.*.amount' => 'required|numeric',
            'downpayment.*.start_date' => 'required',

            'additional_amount.*.amount' => 'nullable|numeric',
            'additional_amount.*.start_date' => 'nullable',

            'installment.*.amount' => 'required|numeric',
            'installment.*.start_date' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }

        DB::beginTransaction();

        try{

            $downpaymentArray = array();
            $additionalAmountArray = array();
            $installmentArray = array();

            $price_info = new PriceInformation();

            $price_info->flat_or_plot_id                   = $request->flat_or_plot_id;
            $price_info->project_type                      = $request->project_type;

            $price_info->total_money                       = $request->total_money ?? 0;
            $price_info->booking_money                     = $request->booking_money ?? 0;
            $price_info->booking_money_date                = $request->booking_money_date ?? null;
            $price_info->car_parking                       = $request->car_parking ?? 0;
            $price_info->car_parking_date                  = $request->car_parking_date ?? null;
            $price_info->utility_charge                    = $request->utility_charge ?? 0;
            $price_info->utility_charge_date               = $request->utility_charge_date ?? null;
            $price_info->additional_work_amount            = $request->additional_work_amount ?? 0;
           $price_info->additional_work_amount_date       = $request->additional_work_amount_date ?? null;

            $price_info->total_installment_amount          = $request->total_installment_amount ?? 0;
            $price_info->per_month_installment_amount      = $request->per_month_installment_amount ?? 0;
            $price_info->number_of_installment             = $request->number_of_installment ?? 0;

            $price_info->total_downpayment_amount          = $request->total_downpayment_amount ?? 0;
            $price_info->total_additional_amount          = $request->total_additional_amount ?? 0;

            $price_info->total_booking_money_paid          =  0;
            $price_info->total_car_parking_paid            =  0;
            $price_info->total_utility_charge_paid         =  0;
            $price_info->total_additional_work_amount_paid =  0;
            $price_info->total_installment_amount_paid     =  0;
            $price_info->total_downpayment_amount_paid     =  0;
            $price_info->total_additional_amount_paid     =  0;

            $price_info->save();

            foreach ($request->downpayment as $key => $downpayment) {

                $downpaymentAmount = array(
                    'price_information_id' => $price_info->id,
                    'amount' => $downpayment['amount'] ?? null,
                    'paid' => 0,
                    'downpayment_no' => $downpayment['downpayment_no'] ?? null,
                    'start_date' => $downpayment['start_date'] ?? null
                );

                $downpayments = Downpayment::create($downpaymentAmount);

                array_push($downpaymentArray, $downpayments);

            }

            //new
            foreach ($request->additional_amount as $key => $additional_amount) {

                $additionalAmount = array(
                    'price_information_id' => $price_info->id,
                    'amount' => $additional_amount['amount'] ?? null,
                    'paid' => 0,
                    'additional_amount_for' => $additional_amount['type'],
                    'amount_name' => $additional_amount['fieldTitle'],
                    'start_date' => $additional_amount['start_date'] ?? null
                );

                $additional_amount = AdditionalAmount::create($additionalAmount);

                array_push($additionalAmountArray, $additional_amount);

            }
            //newend


            foreach ($request->installment as $key => $installment) {

                $installmentAmount = array(
                    'price_information_id' => $price_info->id,
                    'amount' => $installment['amount'] ?? null,
                    'paid' => 0,
                    'start_date' => $installment['start_date'] ?? null
                );

                $installments = Installment::create($installmentAmount);

                array_push($installmentArray, $installments);

            }

            DB::commit();

            return response()->json([

                'priceInfo'   => $price_info,
                'downpayment' => $downpaymentArray,
                'additional_amount'=> $additionalAmountArray,
                'installment' => $installmentArray
            ]);

        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Data  couldn\'t be loaded',
                'data' => []
            ]);
        }


    }

    public function handleActive($id)
    {
        $priceInformation = PriceInformation::find($id);

        $priceInformation->isActive = 1;

        $priceInformation->save();

        return response([
            'message' => 'Status Activated',
        ],200);

    }

    public function handleDeactive($id)
    {
        $priceInformation = PriceInformation::find($id);

        $priceInformation->isActive = 0;

        $priceInformation->save();

        return response([
            'message' => 'Status Deactivated',
        ],200);
    }


    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'flat_or_plot_id'=>'required',
            'project_type' => 'required',

            'total_money' => 'required|numeric',
            'booking_money' => 'required|numeric',
            'booking_money_date' => 'required',


            'total_installment_amount' => 'required|numeric',
            'per_month_installment_amount' => 'required|numeric',
            'number_of_installment' => 'required|numeric',
            'total_downpayment_amount' => 'required|numeric',
            'total_additional_amount' => 'numeric',

            'downpayment.*.amount' => 'required|numeric',
            'downpayment.*.start_date' => 'required',

            'additional_amount.*.amount' => 'nullable|numeric',
            'additional_amount.*.start_date' => 'nullable',

            'installment.*.amount' => 'required|numeric',
            'installment.*.start_date' => 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }
        DB::beginTransaction();
        try{
            $price_info = PriceInformation::where('flat_or_plot_id',$request->flat_or_plot_id)->first();

            $price_info->total_money                       = $request->total_money ?? 0;
            $price_info->booking_money                     = $request->booking_money ?? 0;
            $price_info->booking_money_date                = $request->booking_money_date ?? null;
            $price_info->car_parking                       = $request->car_parking ?? 0;
            $price_info->car_parking_date                  = $request->car_parking_date ?? null;
            $price_info->utility_charge                    = $request->utility_charge ?? 0;
            $price_info->utility_charge_date               = $request->utility_charge_date ?? null;
            $price_info->additional_work_amount            = $request->additional_work_amount ?? 0;
            $price_info->additional_work_amount_date       = $request->additional_work_amount_date ?? null;

            $price_info->total_installment_amount          = $request->total_installment_amount ?? 0;
            $price_info->per_month_installment_amount      = $request->per_month_installment_amount ?? 0;
            $price_info->number_of_installment             = $request->number_of_installment ?? 0;

            $price_info->total_downpayment_amount          = $request->total_downpayment_amount ?? 0;
            $price_info->total_additional_amount          = $request->total_additional_amount ?? 0;


            $price_info->save();


            foreach ($request->downpayment as $key => $downpayment) {

                if($downpayment['id'] != null && $downpayment['id'] != 'null')
                {
                    $item = Downpayment::find($downpayment['id']);

                    $item->amount = $downpayment['amount'];
                    $item->downpayment_no = $downpayment['downpayment_no'];
                    $item->start_date = $downpayment['start_date'];


                    $item->save();

                }
                else
                {
                    $downpaymentAmount = array(
                        'price_information_id' => $price_info->id,
                        'amount' => $downpayment['amount'] ?? null,
                        'paid' => 0,
                        'downpayment_no' => $downpayment['downpayment_no'] ?? null,
                        'start_date' => $downpayment['start_date'] ?? null
                    );

                    $downpayments = Downpayment::create($downpaymentAmount);
                }
            }

            //new
            foreach ($request->additional_amount as $key => $additional_amount) {

                if($additional_amount['id'] != null && $additional_amount['id'] != 'null')
                {
                    $item = AdditionalAmount::find($additional_amount['id']);
                    $item->amount = $additional_amount['amount'];
                    $item->start_date = $additional_amount['start_date'];
                    $item->amount_name = $additional_amount['fieldTitle'];
                    //$item->additional_amount_for = $additional_amount['type'];
                    $item->save();

                }
                else
                {
                    $additionalAmount = array(
                        'price_information_id' => $price_info->id,
                        'amount' => $additional_amount['amount'] ?? null,
                        'paid' => 0,
                        'additional_amount_for' => $additional_amount['type'],
                        'amount_name' => $additional_amount['fieldTitle'],
                        'start_date' => $additional_amount['start_date'] ?? null
                    );

                    $additional_amount = AdditionalAmount::create($additionalAmount);
                }
            }
            //newend

            foreach ($request->installment as $key => $installment) {

                if($installment['id'] != null && $installment['id'] != 'null')
                {
                    $item = Installment::find($installment['id']);


                    $item->amount = $installment['amount'];
                    $item->start_date = $installment['start_date'];
                    $item->save();

                }
                else{
                    $installmentAmount = array(
                        'price_information_id' => $price_info->id,
                        'amount' => $installment['amount'] ?? null,
                        'paid' => 0,
                        'start_date' => $installment['start_date'] ?? null
                    );

                    $installments = Installment::create($installmentAmount);
                }

            }
            DB::commit();

            return response([
                'status' => 'success',
                'message' => "Successfully Updated",
            ],200);

        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->get_message(), //'Data couldn\'t be loaded',
                'data' => []
            ]);
        }

    }


}

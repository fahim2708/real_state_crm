<?php

namespace App\Http\Controllers\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AfterRegCanceledCustomer;
use App\Models\AfterRegCancelCustomerPayment;
use App\Models\FlatOrPlot;
use App\Models\RegistrationBuyBack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AfterRegCancelCustomerListController extends Controller
{
    public function getAllFileInfo()
    {
        //Show with exclude file no. which already addded to after_reg canceled customer
        $data = FlatOrPlot::with(['customers', 'project'])
        ->whereNotIn('id', function ($query) {
            $query->select('flat_or_plot_id')
                ->from('after_reg_canceled_customers');
        })->get();

        return response()->json([
        'data' => $data,
        ]);
    }
    
    public function storeCanceledCustomer(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'flat_or_plot_id' => 'required',
            'new_address' => 'nullable',
            'canceled_application_date' => 'nullable',
            'total_amount' => 'numeric',
            'original_amount' => 'numeric',
            'extra_amount' => 'numeric',
            'canceled_file_reg_date' => 'nullable',
            'canceled_file_deed_no' => 'nullable',
            'canceled_file_land_size' => 'nullable',
            'canceled_payment_start_date' => 'nullable',
            'authorized_person_name' => 'nullable',
            'authorized_phone_number' => 'nullable',
            'description' => 'nullable',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
            ]);
        }

        DB::beginTransaction();

        try {

            $canceled_customer = new AfterRegCanceledCustomer();

            $canceled_customer->flat_or_plot_id = $request->flat_or_plot_id;
            $canceled_customer->new_address = $request->new_address ?? null;
            $canceled_customer->canceled_application_date = $request->canceled_application_date ?? null;
            $canceled_customer->total_amount = $request->total_amount ?? 0;
            $canceled_customer->original_amount = $request->original_amount ?? 0;
            $canceled_customer->extra_amount = $request->extra_amount ?? 0;
            $canceled_customer->canceled_file_reg_date = $request->canceled_file_reg_date ?? null;
            $canceled_customer->canceled_file_reg_deed_no = $request->canceled_file_deed_no ?? null;
            $canceled_customer->canceled_file_reg_land_size = $request->canceled_file_land_size ?? null;
            $canceled_customer->canceled_payment_start_date = $request->canceled_payment_start_date ?? null;
            $canceled_customer->authorized_person_name = $request->authorized_person_name ?? null;
            $canceled_customer->authorized_phone_number = $request->authorized_phone_number ?? null;
            $canceled_customer->description = $request->description ?? null;
            $canceled_customer->total_canceled_amount_paid = 0;
            $canceled_customer->save();

            DB::commit();

            return response()->json([

                'canceled_customer' => $canceled_customer,
                'message'=> "Successfully Added",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->get_message(),
                'data' => [],
            ]);
        }

    }

    public function updateCanceledCustomer(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'flat_or_plot_id' => 'required',
                'new_address' => 'nullable',
                'canceled_application_date' => 'nullable',
                'total_amount' => 'numeric',
                'original_amount' => 'numeric',
                'extra_amount' => 'numeric',
                'canceled_file_reg_date' => 'nullable',
                'canceled_file_deed_no' => 'nullable',
                'canceled_file_land_size' => 'nullable',
                'canceled_payment_start_date' => 'nullable',
                'authorized_person_name' => 'nullable',
                'authorized_phone_number' => 'nullable',
                'description' => 'nullable',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->messages()->all(),
                ]);
            }
            DB::beginTransaction();
             try {
                $canceled_customer = AfterRegCanceledCustomer::where('flat_or_plot_id', $request->flat_or_plot_id)->first();

                $canceled_customer->flat_or_plot_id = $request->flat_or_plot_id;
                $canceled_customer->new_address = $request->new_address ?? null;
                $canceled_customer->canceled_application_date = $request->canceled_application_date ?? null;
                $canceled_customer->total_amount = $request->total_amount ?? 0;
                $canceled_customer->original_amount = $request->original_amount ?? 0;
                $canceled_customer->extra_amount = $request->extra_amount ?? 0;
                $canceled_customer->canceled_file_reg_date = $request->canceled_file_reg_date ?? null;
                $canceled_customer->canceled_file_reg_deed_no = $request->canceled_file_deed_no ?? null;
                $canceled_customer->canceled_file_reg_land_size = $request->canceled_file_land_size ?? null;
                $canceled_customer->canceled_payment_start_date = $request->canceled_payment_start_date ?? null;
                $canceled_customer->authorized_person_name = $request->authorized_person_name ?? null;
                $canceled_customer->authorized_phone_number = $request->authorized_phone_number ?? null;
                $canceled_customer->description = $request->description ?? null;
                

                $canceled_customer->save();

                DB::commit();

                return response([
                    'status' => 'success',
                    'message' => "Successfully Updated",
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => $e->get_message(),
                    'data' => [],
                ]);
            } 
    }

    public function getCanceledCustomerInfo()
    {
        $data = AfterRegCanceledCustomer::with(['customer_payment', 'buyBackData', 'flat_or_plot' => function ($query) {
            $query->select('id', 'file_no', 'size')->with(['customers','projectInfo', 'soldItems']);
        }])->get();

        return response()->json([
            'data' => $data,
        ], 200);

    }

    public function addPayment(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'flat_or_plot_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
                'data' => [],
            ]);
        }

        DB::beginTransaction();
         try {
            $canceled_customer = AfterRegCanceledCustomer::where('flat_or_plot_id', $request->flat_or_plot_id)->first();

            
                if (($canceled_customer->total_canceled_amount_paid + $request->payment_amount) > $canceled_customer->total_amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $canceled_customer->total_amount - $canceled_customer->total_canceled_amount_paid,
                    ]);
                }
                $canceled_customer->total_canceled_amount_paid += $request->payment_amount;

            
            $canceled_customer->save();

            // Payment History Save
            $cancel_customer_payment = new AfterRegCancelCustomerPayment();

            $cancel_customer_payment->after_reg_cancel_customer_id = $request->after_reg_cancel_customer_id;
            $cancel_customer_payment->new_address = $request->new_address;
            $cancel_customer_payment->payment_date = $request->payment_date;
            $cancel_customer_payment->payment_amount = $request->payment_amount;
            $cancel_customer_payment->amount_in_words = $request->amount_in_words;
            $cancel_customer_payment->payment_method = $request->payment_method;
            $cancel_customer_payment->invoice_no = $request->invoice_no;
            $cancel_customer_payment->received_by = $request->received_by;
            $cancel_customer_payment->staff_name = $request->staff_name;

            $cancel_customer_payment->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment Stored Successfully',
                'data' => $canceled_customer,
            ], 200);
        } catch (\Exception$e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment Stored Unsuccessful',
            ]);

       }

    }

    public function addBuyBackData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'after_reg_cancel_customer_id' => 'required',
            'buy_back_date' => 'required',
            'buy_back_deed_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
                'data' => [],
            ]);
        }

        DB::beginTransaction();
        try {            
            $addBuyBack = new RegistrationBuyBack();

            $addBuyBack->after_reg_cancel_customer_id	 = $request->after_reg_cancel_customer_id	;
            $addBuyBack->buy_back_date = $request->buy_back_date;
            $addBuyBack->buy_back_deed_no = $request->buy_back_deed_no;
            

            $addBuyBack->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Registration Buy Back stored Successfully',
                'data' => $addBuyBack,
            ], 200);
        } catch (\Exception$e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Registration Buy Back Stored Unsuccessful',
            ]);

       }

    }

    public function updateBuyBackData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'after_reg_cancel_customer_id' => 'required',
            'buy_back_date' => 'required',
            'buy_back_deed_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
                'data' => [],
            ]);
        }

        DB::beginTransaction();
        try {            
           
            $addBuyBack = RegistrationBuyBack::where('after_reg_cancel_customer_id', $request->after_reg_cancel_customer_id)->first();

            $addBuyBack->after_reg_cancel_customer_id	 = $request->after_reg_cancel_customer_id;
            $addBuyBack->buy_back_date = $request->buy_back_date;
            $addBuyBack->buy_back_deed_no = $request->buy_back_deed_no;
            

            $addBuyBack->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Registration Buy Back updated Successfully',
                'data' => $addBuyBack,
            ], 200);
        } catch (\Exception$e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Registration Buy Back update Unsuccessful',
            ]);

       }

    }
}

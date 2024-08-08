<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Canceled_Customer;
use App\Models\FlatOrPlot;
use App\Models\Cancel_Customer_Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CancelCustomerListController extends Controller
{
    public function getAllFileInfo()
    {

        //Show with exclude file no. which already addded to canceled customer
        $data = FlatOrPlot::with(['customers', 'project'])
        ->whereNotIn('id', function ($query) {
            $query->select('flat_or_plot_id')
                ->from('canceled__customers');
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

            $canceled_customer = new Canceled_Customer();

            $canceled_customer->flat_or_plot_id = $request->flat_or_plot_id;

            $canceled_customer->new_address = $request->new_address ?? null;
            $canceled_customer->canceled_application_date = $request->canceled_application_date ?? null;
            $canceled_customer->total_amount = $request->total_amount ?? 0;
            $canceled_customer->original_amount = $request->original_amount ?? 0;
            $canceled_customer->extra_amount = $request->extra_amount ?? 0;
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
                'message' => $e->get_message(), //'Data  couldn\'t be loaded',
                'data' => [],
            ]);
        }

    }

    public function updateCanceledCustomer(Request $request)
    {
        {

            $validator = Validator::make($request->all(), [
                'flat_or_plot_id' => 'required',
                'new_address' => 'nullable',
                'canceled_application_date' => 'nullable',
                'total_amount' => 'numeric',
                'original_amount' => 'numeric',
                'extra_amount' => 'numeric',
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
                $canceled_customer = Canceled_Customer::where('flat_or_plot_id', $request->flat_or_plot_id)->first();

                $canceled_customer->flat_or_plot_id = $request->flat_or_plot_id;
                $canceled_customer->new_address = $request->new_address ?? null;
                $canceled_customer->canceled_application_date = $request->canceled_application_date ?? null;
                $canceled_customer->total_amount = $request->total_amount ?? 0;
                $canceled_customer->original_amount = $request->original_amount ?? 0;
                $canceled_customer->extra_amount = $request->extra_amount ?? 0;
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
    }

    public function getCanceledCustomerInfo()
    {
        $amount = Canceled_Customer::with(['customer_payment', 'flat_or_plot' => function ($query) {
            $query->select('id', 'file_no', 'size')->with(['customers','projectInfo', 'soldItems']);
        }])->get();

        return response()->json([
            'data' => $amount,
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
            $canceled_customer = Canceled_Customer::where('flat_or_plot_id', $request->flat_or_plot_id)->first();

            
                if (($canceled_customer->total_canceled_amount_paid + $request->payment_amount) > $canceled_customer->total_amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $canceled_customer->total_amount - $canceled_customer->total_canceled_amount_paid,
                    ]);
                }
                $canceled_customer->total_canceled_amount_paid += $request->payment_amount;

            
            $canceled_customer->save();

            // Payment History Save
            $cancel_customer_payment = new Cancel_Customer_Payment();

            $cancel_customer_payment->cancel_customer_id = $request->canceled_customer_id;
            $cancel_customer_payment->new_address = $request->new_address;
            $cancel_customer_payment->payment_date = $request->payment_date;
            $cancel_customer_payment->payment_amount = $request->payment_amount;
            $cancel_customer_payment->amount_in_words = $request->amount_in_words;
            $cancel_customer_payment->payment_method = $request->payment_method;
            $cancel_customer_payment->invoice__no = $request->invoice_no;
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

    //Add folder or Documents
    public function createFolder(){

    }
}

<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Models\FlatOrPlot;
use App\Models\PaymentHistory;
use App\Models\PriceInformation;
use App\Models\RegistrationAmount;
use App\Models\RegistrationPaymentHistory;
use App\Models\RegistrationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationAmountController extends Controller
{
    public function amountView(Request $request)
    {
        $amount  = RegistrationAmount::with(['flat_or_plot' => function ($query){
            $query->with('customers');
        }])->get();

        return response()->json([
            'status' => 'success',
            'data' => $amount,
        ], 200);
    }

    public function dueDetails($id)
    {
        $amount = RegistrationAmount::with(['flat_or_plot' => function ($query) {
            $query->select('id', 'file_no')->with(['customers' => function ($query) {
                $query->select('flat_or_plot_id', 'nid_number', 'name');
            }]);
        }])->with(['registration_payment_history' => function ($query) {
            $query->with(['registration_amount' => function ($query) {
                $query->select('id', 'registry_amount_schedule_date', 'mutation_cost_schedule_date', 'power_of_attorney_cost_schedule_date');
            }]);
        }])->find($id);

        $total_reg_amount = $amount->registry_amount + $amount->mutation_cost_amount + $amount->power_of_attorney_cost_amount;
        $total_reg_payment = $amount->registry_payment + $amount->mutation_cost_payment + $amount->power_of_attorney_cost_payment;
        $total_reg_dues = $total_reg_amount - $total_reg_payment;

        $due_details = [
            'customers' => $amount->flat_or_plot->customers,
            'file_no' => $amount->flat_or_plot->file_no,
            'total_reg_amount' => $total_reg_amount,
            'total_reg_payment' => $total_reg_payment,
            'total_reg_dues' => $total_reg_dues,
            'registry_amount' => $amount->registry_amount,
            'registry_payment' => $amount->registry_payment,
            'registry_amount_schedule_date' => $amount->registry_amount_schedule_date,
            'registry_due' => $amount->registry_amount - $amount->registry_payment,
            'mutation_cost_amount' => $amount->mutation_cost_amount,
            'mutation_cost_payment' => $amount->mutation_cost_payment,
            'mutation_cost_due' => $amount->mutation_cost_amount - $amount->mutation_cost_payment,
            'mutation_cost_schedule_date' => $amount->mutation_cost_schedule_date,
            'power_of_attorney_cost_amount' => $amount->power_of_attorney_cost_amount,
            'power_of_attorney_cost_payment' => $amount->power_of_attorney_cost_payment,
            'power_of_attorney_cost_due' => $amount->power_of_attorney_cost_amount - $amount->power_of_attorney_cost_payment,
            'power_of_attorney_cost_schedule_date' => $amount->power_of_attorney_cost_schedule_date,
            'payment_histories' => $amount->registration_payment_history ? $amount->registration_payment_history : "No History",
        ];

        return response()->json([
            'status' => 'success',
            'data' => $due_details,
        ], 200);
    }

    public function addPrice(Request $request)
    {
        $request->validate([
            'registry_amount' => 'required',
            'registry_amount_schedule_date' => 'required|date',
            'mutation_cost_amount' => 'required',
            'mutation_cost_schedule_date' => 'required|date',
            'power_of_attorney_cost_amount' => 'required',
            'power_of_attorney_cost_schedule_date' => 'required|date',
        ]);

        $reg_amount = RegistrationAmount::find($request->id);
        $reg_amount->registry_amount = $request->registry_amount;
        $reg_amount->registry_amount_schedule_date = $request->registry_amount_schedule_date;
        $reg_amount->mutation_cost_amount = $request->mutation_cost_amount;
        $reg_amount->mutation_cost_schedule_date = $request->mutation_cost_schedule_date;
        $reg_amount->power_of_attorney_cost_amount = $request->power_of_attorney_cost_amount;
        $reg_amount->power_of_attorney_cost_schedule_date = $request->power_of_attorney_cost_schedule_date;
        $price = $reg_amount->save();

        if ($price) {
            return response()->json([
                'status' => 'success',
                'message' => 'data has been saved successfully!',
                'amount_details' => $reg_amount,
            ], 200);
        }
    }

    public function addPayment(Request $request)
    {
        $request->validate([
            'payment_amount' => 'required',
            'payment_date' => 'required|date',
            'pay_by' => 'required',
            'money_receipt_no' => 'required',
        ]);

        $reg_amount = RegistrationAmount::find($request->id);
        $reg_payment_history = new RegistrationPaymentHistory();

        if ($request->payment_against == RegistrationAmount::$REGISTRY) {
            if(($reg_amount->registry_payment + $request->payment_amount ) <= $reg_amount-> registry_amount)
            {
                $reg_amount->registry_payment = $reg_amount->registry_payment + $request->payment_amount;
            }
            else
            {
                return response([
                    'status' => 'failed',
                    'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Due is:'. $reg_amount->registry_amount - $reg_amount->registry_payment,
                ]);
            }

            $reg_payment_history->registration_amount_id = $reg_amount->id;
            $reg_payment_history->payment_date = $request->payment_date;
            $reg_payment_history->pay_by = $request->pay_by;
            $reg_payment_history->money_receipt_no = $request->money_receipt_no;
            $reg_payment_history->payment_against = RegistrationAmount::$REGISTRY;
            $reg_payment_history->payment_amount = $request->payment_amount;
            $reg_payment_history->payment_due = ($reg_amount->registry_amount - $reg_amount->registry_payment);

        } elseif ($request->payment_against == RegistrationAmount::$MUTATION) {

            if(($reg_amount->mutation_cost_payment + $request->payment_amount ) <= $reg_amount-> mutation_cost_amount)
            {
                $reg_amount->mutation_cost_payment = $reg_amount->mutation_cost_payment + $request->payment_amount;
            }
            else
            {
                return response([
                    'status' => 'failed',
                    'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:'. $reg_amount->mutation_cost_amount - $reg_amount->mutation_cost_payment,
                ]);
            }

            $reg_payment_history->registration_amount_id = $reg_amount->id;
            $reg_payment_history->payment_date = $request->payment_date;
            $reg_payment_history->pay_by = $request->pay_by;
            $reg_payment_history->money_receipt_no = $request->money_receipt_no;
            $reg_payment_history->payment_against = RegistrationAmount::$MUTATION;
            $reg_payment_history->payment_amount = $request->payment_amount;
            $reg_payment_history->payment_due = ($reg_amount->mutation_cost_amount - $reg_amount->mutation_cost_payment);
        } else {

            if(($reg_amount->power_of_attorney_cost_payment + $request->payment_amount ) <= $reg_amount-> power_of_attorney_cost_amount)
            {
                $reg_amount->power_of_attorney_cost_payment = $reg_amount->power_of_attorney_cost_payment + $request->payment_amount;
            }
            else
            {
                return response([
                    'status' => 'failed',
                    'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:'. $reg_amount->power_of_attorney_cost_amount - $reg_amount->power_of_attorney_cost_payment,
                ]);
            }

            $reg_payment_history->registration_amount_id = $reg_amount->id;
            $reg_payment_history->payment_date = $request->payment_date;
            $reg_payment_history->pay_by = $request->pay_by;
            $reg_payment_history->money_receipt_no = $request->money_receipt_no;
            $reg_payment_history->payment_against = RegistrationAmount::$POWER_OF_ATTORNEY;
            $reg_payment_history->payment_amount = $request->payment_amount;
            $reg_payment_history->payment_due = ($reg_amount->power_of_attorney_cost_amount - $reg_amount->power_of_attorney_cost_payment);
        }


        $payment = $reg_amount->save();
        $history = $reg_payment_history->save();

        if ($payment && $history) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data has been saved successfully!',
                'amount_details' => $reg_amount,
            ], 200);
        }
    }

    public function statusListView(Request $request)
    {
        $limit = $request->post('no_of_rows');
        $offset = ($request->post('page', 1) - 1) * $limit;

        $total_rows = RegistrationStatus::count();

        $reg_statuses = RegistrationStatus::with(['flat_or_plot' => function ($query) {
            $query->select('id', 'file_no', 'booking_date')->with(['customers']);
        }])->take($limit)->get();

        return response()->json([
            'status' => 'success',
            'registration_statuses' => $reg_statuses,
            'page' => $request->post('page', 1),
            'no_of_rows' => count($reg_statuses),
            'total_rows' => $total_rows,
        ], 200);
    }

    public function updateStatus($id, $status_type, $value)
    {
        $reg_status = RegistrationStatus::find($id);

        if ($status_type == RegistrationStatus::$REGISTRATION) {
            $reg_status->flat_or_plot_registration_status = $value;
        } else if ($status_type == RegistrationStatus::$MUTATION) {
            $reg_status->mutation_cost_status = $value;
        } else {
            $reg_status->power_of_attorney_cost_status = $value;
        }

        $save_status = $reg_status->save();
        if ($save_status) {
            return response()->json([
                'status' => 'success',
                'message' => 'data has been saved successfully!',
                'updated_data' => $reg_status,
            ], 200);
        }
    }

    public function searchStatus(Request $request)
    {
        $selectedRows = [];
        $file_no = $request->file_no;
        if ($request->status_value && !$request->status_type) {

            $selectedRows = RegistrationStatus::where('flat_or_plot_registration_status', $request->status_value)
                ->orWhere('mutation_cost_status', $request->status_value)
                ->orWhere('power_of_attorney_cost_status', $request->status_value)
                ->with(['flat_or_plot' => function($query) use ($file_no){
                    $query->select('id', 'file_no', 'booking_date')->with(['customers' => function($query){
                        $query->select('flat_or_plot_id', 'nid_number', 'name', 'mailing_address', 'country');
                    }]);
                    if($file_no)
                    {
                        $query->where('file_no',$file_no);
                    }
                }])
                ->whereHas('flat_or_plot',function ($q) use($file_no)
                {
                    if($file_no)
                    {
                        $q->where('file_no',$file_no);
                    }
                })
                ->get();

        } else if ($request->status_type && !$request->status_value) {
            $selectedRows = RegistrationStatus::with(['flat_or_plot' => function ($query) use ($file_no) {
                $query->select('id', 'file_no', 'booking_date')->with(['customers' => function ($query) {
                    $query->select('flat_or_plot_id', 'nid_number', 'name', 'mailing_address', 'country');
                }]);
                if($file_no)
                {
                    $query->where('file_no',$file_no);
                }
            }])
            ->whereHas('flat_or_plot',function ($q) use($file_no)
            {
                if($file_no)
                {
                    $q->where('file_no',$file_no);
                }
            })
            ->get();
        } else {
            $selectedRows = RegistrationStatus::where($request->status_type, $request->status_value)->with(['flat_or_plot' => function ($query) use ($file_no) {
                $query->select('id', 'file_no', 'booking_date')->with(['customers' => function ($query) {
                    $query->select('flat_or_plot_id', 'nid_number', 'name', 'mailing_address', 'country');
                }]);
                if($file_no)
                {
                    $query->where('file_no',$file_no);
                }
            }])
            ->whereHas('flat_or_plot',function ($q) use($file_no)
            {
                if($file_no)
                {
                    $q->where('file_no',$file_no);
                }
            })
            ->get();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Searched!',
            'results' => $selectedRows,
        ], 200);
    }
}

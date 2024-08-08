<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Downpayment;

use App\Models\AdditionalAmount;
use App\Models\FlatOrPlot;
use App\Models\Installment;
use App\Models\MoneyReceiptDocument;
use App\Models\MoneyReceiptFolder;
use App\Models\PaymentHistory;
use App\Models\PriceInformation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    function list(Request $request) {

        $limit = $request->post('no_of_rows');
        $offset = ($request->post('page', 1) - 1) * $limit;
        $search = $request->search;

        $list = PriceInformation::with(['flatOrPlot' => function ($query) use ($search) {
            $query->with('customers');
            if ($search) {
                $query->where('file_no', 'like', '%' . $search . '%');
            }

        }])
            ->select(
                'id',
                'flat_or_plot_id',
                'total_booking_money_paid as booking_money',
                'total_car_parking_paid as car_parking',
                'total_utility_charge_paid as utility_charge',
                //'total_additional_work_amount_paid as additional_work_amount',
                'total_installment_amount_paid as installment_amount',
                'total_downpayment_amount_paid as downpayment_amount',
                'total_additional_amount_paid as additional_amount', //new

            )
            ->whereHas('flatOrPlot', function ($query) use ($search) {
                if ($search) {
                    $query->where('file_no', 'like', '%' . $search . '%');
                }
            });

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

    // select down-payment
    public function downpayment($id)
    {
        $data = PriceInformation::with('downPayment')->select('id')->find($id);

        return response()->json([
            'data' => $data,
        ]);
    }

    // select additional amount-new
    public function additional_amount($id)
    {
        $data = PriceInformation::with('additional_amount')->select('id')->find($id);

        return response()->json([
            'data' => $data,
        ]);
    }

    // select installment
    public function installment($id)
    {
        $data = PriceInformation::with('installment')->select('id')->find($id);

        return response()->json([
            'data' => $data,
        ]);
    }

    // payment paid store
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'flat_or_plot_id' => 'required',
            'payment_against_id' => 'required',
            'amount' => 'required',
            'complete_date' => 'required',
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
            $price_information = PriceInformation::where('flat_or_plot_id', $request->flat_or_plot_id)->first();

            if ($request->payment_against_id == PriceInformation::$BOOKING_MONEY) {
                if (($price_information->total_booking_money_paid + $request->amount) > $price_information->booking_money) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $price_information->booking_money - $price_information->total_booking_money_paid,
                    ]);
                }
                $price_information->total_booking_money_paid += $request->amount;

            } else if ($request->payment_against_id == PriceInformation::$CAR_PARKING) {
                if (($price_information->total_car_parking_paid + $request->amount) > $price_information->car_parking) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $price_information->car_parking - $price_information->total_car_parking_paid,
                    ]);
                }
                $price_information->total_car_parking_paid += $request->amount;

            } else if ($request->payment_against_id == PriceInformation::$UTILITY_CHARGE) {
                if (($price_information->total_utility_charge_paid + $request->amount) > $price_information->utility_charge) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $price_information->utility_charge - $price_information->total_utility_charge_paid,
                    ]);
                }
                $price_information->total_utility_charge_paid += $request->amount;

            } 
            // else if ($request->payment_against_id == PriceInformation::$ADDITIONAL_WORK_AMOUNT) {
            //     if (($price_information->total_additional_work_amount_paid + $request->amount) > $price_information->additional_work_amount) {
            //         return response([
            //             'status' => 'failed',
            //             'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $price_information->additional_work_amount - $price_information->total_additional_work_amount_paid,
            //         ]);
            //     }
            //     $price_information->total_additional_work_amount_paid += $request->amount;

            // } 
            else if ($request->payment_against_id == PriceInformation::$DOWNPAYMENT_AMOUNT) {
                $price_information->total_downpayment_amount_paid += $request->amount;

                $downPayment = Downpayment::find($request->down_payment_id);

                if (($downPayment->paid + $request->amount) > $downPayment->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $downPayment->amount - $downPayment->paid,
                    ]);
                }

                $downPayment->paid += $request->amount;

                $downPayment->save();

            }
            //new
            else if ($request->payment_against_id == PriceInformation::$ADDITIONAL_AMOUNT) {
                $price_information->total_additional_amount_paid += $request->amount;

                $additional_amount = AdditionalAmount::find($request->additional_amount_id);

                if (($additional_amount->paid + $request->amount) > $additional_amount->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $additional_amount->amount - $additional_amount->paid,
                    ]);
                } 

                $additional_amount->paid += $request->amount;

                $additional_amount->save();

            } 
            //newend
            
            else if ($request->payment_against_id == PriceInformation::$INSTALLMENT_AMOUNT) {
                $price_information->total_installment_amount_paid += $request->amount;

                $installment = Installment::find($request->installment_id);

                if (($installment->paid + $request->amount) > $installment->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $installment->amount - $installment->paid,
                    ]);
                }

                $installment->paid += $request->amount;

                $installment->save();
            }

            $price_information->save();

//       Payment History Save.
            $payment_history = new PaymentHistory();

            $payment_history->price_information_id = $price_information->id;
            $payment_history->payment_against = $request->payment_against_name;
            $payment_history->money_receipt_id = $request->money_receipt_id;
            $payment_history->paid_by = $request->paid_by;
            $payment_history->amount = $request->amount;
            $payment_history->complete_date = $request->complete_date;

            $payment_history->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment Stored Successfully',
                'data' => $price_information,
            ], 200);
        } catch (\Exception$e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment Stored Unsuccessful',
            ]);

        }

    }

    // each paid payment details
    public function details($id)
    {
        $paymentDetails = PriceInformation::with(['paymentHistory', 'flatOrPlot' => function ($query) {
            $query->with('customers');
        }])
            ->selectRaw('id,
            flat_or_plot_id,
            total_money,
            booking_money as total_booking_money,
            total_booking_money_paid as booking_money_paid,
            (booking_money - total_booking_money_paid ) as booking_money_due,
            car_parking as total_car_parking,
            total_car_parking_paid as car_parking_paid,
            (car_parking - total_car_parking_paid ) as car_parking_due,
            utility_charge as total_utility_charge,
            total_utility_charge_paid as utility_charge_paid,
            (utility_charge - total_utility_charge_paid ) as utility_charge_due,
            total_downpayment_amount,
            total_downpayment_amount_paid,
            (total_downpayment_amount - total_downpayment_amount_paid ) as downpayment_amount_due,

            
            total_additional_amount,
            total_additional_amount_paid,
            (total_additional_amount - total_additional_amount_paid ) as additional_amount_due,
            
            total_installment_amount,
            total_installment_amount_paid,
            (total_installment_amount - total_installment_amount_paid ) as installment_amount_due,
            additional_work_amount as total_additional_work_amount,
            total_additional_work_amount_paid,
            (additional_work_amount - total_additional_work_amount_paid ) as additional_work_amount_due
        ')->find($id);

        $totalInstallmentAmountTodate = Installment::where('price_information_id', $id)->where('start_date', '<=', Carbon::today())->sum('amount');
        $totalInstallmentCompleteTodate = Installment::where('price_information_id', $id)->where('start_date', '<=', Carbon::today())->sum('paid');

        $totalDownpaymentAmountTodate = Downpayment::where('price_information_id', $id)->where('start_date', '<=', Carbon::today())->sum('amount');
        $totalDownpaymentCompleteTodate = Downpayment::where('price_information_id', $id)->where('start_date', '<=', Carbon::today())->sum('paid');

        $paymentNeedToComplete = $paymentDetails->total_booking_money
         + $paymentDetails->total_car_parking
         + $paymentDetails->total_utility_charge
         + $paymentDetails->total_additional_amount
             + $totalInstallmentAmountTodate
             + $totalDownpaymentAmountTodate;

        $paymentCompleted = $paymentDetails->booking_money_paid
         + $paymentDetails->car_parking_paid
         + $paymentDetails->utility_charge_paid
         + $paymentDetails->total_additional_amount_paid
             + $totalInstallmentCompleteTodate
             + $totalDownpaymentCompleteTodate;

        $asOfTodayPayment = [

            'needToComplete' => $paymentNeedToComplete,
            'completed' => $paymentCompleted,
            'due' => $paymentNeedToComplete - $paymentCompleted,

        ];

        $asOfTodayDownpayment = [

            'needToComplete' => $totalDownpaymentAmountTodate,
            'completed' => $totalDownpaymentCompleteTodate,
            'due' => $totalDownpaymentAmountTodate - $totalDownpaymentCompleteTodate,
        ];

        $asOfTodayInstallmentpayment = [

            'needToComplete' => $totalInstallmentAmountTodate,
            'completed' => $totalInstallmentCompleteTodate,
            'due' => $totalInstallmentAmountTodate - $totalInstallmentCompleteTodate,
        ];
        $detail = FlatOrPlot::whereHas('customers')->with(['customers.soldItem','project','priceInformation' => function($query){
            $query->with(['additional_amount']);
        }])->get();

        return response()->json([
            'statement' => $paymentDetails,
            'asOfTodayPayment' => $asOfTodayPayment,
            'asOfTodayDownpayment' => $asOfTodayDownpayment,
            'asOfTodayInstallmentpayment' => $asOfTodayInstallmentpayment,
            'detail' => $detail
        ]
    );
    }

    // update payment
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flat_or_plot_id' => 'required',
            'payment_against_id' => 'required',
            'amount' => 'required',
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
            $price_information = PriceInformation::where('flat_or_plot_id', $request->flat_or_plot_id)->first();

            if ($request->payment_against_id == PriceInformation::$BOOKING_MONEY) {
                if ($request->amount > $price_information->booking_money) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:' . $price_information->booking_money,
                    ]);
                }
                $price_information->total_booking_money_paid = $request->amount;

            } else if ($request->payment_against_id == PriceInformation::$CAR_PARKING) {
                if ($request->amount > $price_information->car_parking) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:' . $price_information->car_parking,
                    ]);
                }
                $price_information->total_car_parking_paid = $request->amount;

            } else if ($request->payment_against_id == PriceInformation::$UTILITY_CHARGE) {
                if ($request->amount > $price_information->utility_charge) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:' . $price_information->utility_charge,
                    ]);
                }
                $price_information->total_utility_charge_paid = $request->amount;

            } 
            // else if ($request->payment_against_id == PriceInformation::$ADDITIONAL_WORK_AMOUNT) {
            //     if ($request->amount > $price_information->additional_work_amount) {
            //         return response([
            //             'status' => 'failed',
            //             'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:' . $price_information->additional_work_amount,
            //         ]);
            //     }
            //     $price_information->total_additional_work_amount_paid = $request->amount;

            // } 
            else if ($request->payment_against_id == PriceInformation::$DOWNPAYMENT_AMOUNT) {

                $downPayment = Downpayment::find($request->down_payment_id);

                $price_information->total_downpayment_amount_paid -= $downPayment->paid;

                if ($request->amount > $downPayment->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:' . $downPayment->amount,
                    ]);
                }

                $downPayment->paid = $request->amount;

                $price_information->total_downpayment_amount_paid += $request->amount;

                $downPayment->save();

            } 
            //new
            else if ($request->payment_against_id == PriceInformation::$ADDITIONAL_AMOUNT) {

                $additional_amount = AdditionalAmount::find($request->additional_amount_id);

                $price_information->total_additional_amount_paid -= $additional_amount->paid;

                if ($request->amount > $additional_amount->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:' . $additional_amount->amount,
                    ]);
                }

                $additional_amount->paid = $request->amount;

                $price_information->total_additional_amount_paid += $request->amount;

                $additional_amount->save();

            }
            //newend
            
            
            
            
            
            else if ($request->payment_against_id == PriceInformation::$INSTALLMENT_AMOUNT) {
                $installment = Installment::find($request->installment_id);

                $price_information->total_installment_amount_paid -= $installment->paid;

                if ($request->amount > $installment->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Total Amount. Total Amount is:' . $installment->amount,
                    ]);
                }

                $installment->paid = $request->amount;
                $price_information->total_installment_amount_paid += $request->amount;

                $installment->save();
            }

            $price_information->save();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Price Updated Successfully',
                'data' => $price_information,
            ], 200);

        } catch (\Exception$e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Data couldn\'t be loaded',
                'data' => [],
            ]);
        }

    }

    public function getEditData(Request $request)
    {
        $priceInformation = PriceInformation::find($request->id);
        $amount = 0;
        if ($request->payment_against_id == PriceInformation::$BOOKING_MONEY) {
            $amount = $priceInformation->total_booking_money_paid;
        } elseif ($request->payment_against_id == PriceInformation::$CAR_PARKING) {
            $amount = $priceInformation->total_car_parking_paid;
        } elseif ($request->payment_against_id == PriceInformation::$UTILITY_CHARGE) {
            $amount = $priceInformation->total_utility_charge_paid;
        } 
        // elseif ($request->payment_against_id == PriceInformation::$ADDITIONAL_WORK_AMOUNT) {
        //     $amount = $priceInformation->total_additional_work_amount_paid;
        // }

        return response([
            'data' => [
                'price_information_id' => $priceInformation->id,
                'amount' => $amount,
            ],
        ]);

    }

    public function getUpgradeableDownpayment($id)
    {
        $downpayment = Downpayment::where('price_information_id', $id)->where('amount', '>', 'paid')->where('paid', '!=', 0)->get();

        return response([
            'data' => $downpayment,
        ]);
    }


    //new
    public function getUpgradeableAdditionalAmount($id)
    {
        $additional_amount = AdditionalAmount::where('price_information_id', $id)->where('amount', '>', 'paid')->where('paid', '!=', 0)->get();

        return response([
            'data' => $additional_amount,
        ]);
    }
    //newend
    public function getUpgradeableInstallment($id)
    {
        $installment = Installment::where('price_information_id', $id)->where('amount', '>', 'paid')->where('paid', '!=', 0)->get();

        return response([
            'data' => $installment,
        ]);
    }

    // Folder create and list methods //
    public function createFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'price_information_id' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
                'data' => [],
            ]);
        }
        $receipt = new MoneyReceiptFolder();

        $receipt->price_information_id = $request->price_information_id;
        $receipt->name = $request->name;

        $receipt->save();

        return response([
            'message' => 'Folder Created Successfully',
        ]);
    }

    public function folderList($price_information_id)
    {
        $list = MoneyReceiptFolder::where('price_information_id', $price_information_id)->get();

        return response([
            'data' => $list,
        ]);
    }

    public function storeDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|mimes:jpg,png,jpeg,pdf,xlsx',
            'money_receipt_folders_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
                'data' => [],
            ]);
        }

        $document = new MoneyReceiptDocument();

        $document->money_receipt_folders_id = $request->money_receipt_folders_id;

        $file = $request->document;

        if ($file && $file !== 'null') {

            $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'money_receipt/' . $file_name;

            $file->move(public_path('money_receipt/'), $destinationPath);

            $file_path = $destinationPath;
            $file_name = $file->getClientOriginalName();

            $document->file_path = $file_path;
            $document->file_name = $file_name;

        }

        $document->save();

        return response([
            'message' => 'Document Stored Successfully',
        ]);

    }

    public function documentList($folder_id)
    {
        $list = MoneyReceiptDocument::where('money_receipt_folders_id', $folder_id)->get();

        return response([
            'data' => $list,
        ]);
    }

}
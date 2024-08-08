<?php

namespace App\Http\Controllers\Registration\RegistrationNumberDetails;

use App\Http\Controllers\Controller;
use App\Models\{PowerOfAttorneyDetails, FlatOrPlot};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PowerOfAttorneyDetailsController extends Controller
{

    public function index(Request $request)
    {
        //$limit = $request->post('no_of_rows');
        //$offset = ($request->post('page', 1) - 1 ) * $limit;

       // $total_data = PowerOfAttorneyDetails::count();

        $data = PowerOfAttorneyDetails::all();

        return response()->json([
            'status' => 'success',
            'data' => [
                //'total' => $total_data,
                //'page' => $request->post('page', 1),
                //'no_of_rows' => count($data),
                'powerOfAttorneyDetails' => $data,
            ]
        ],200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'registration_date' =>  'required',
            'sub_deed_no'       =>  'required|max:11',
            'land_size'         =>  'required|max:50',
            'mouza_name'        =>  'required|max:11',
            'cs_daag_no'        =>  'required|max:11',
            'sa_daag_no'        =>  'required|max:11',
            'rs_daag_no'        =>  'required|max:11',
            'bs_daag_no'        =>  'required|max:11',
            'cs_khatian_no'     =>  'required|max:11',
            'sa_khatian_no'     =>  'required|max:11',
            'rs_khatian_no'     =>  'required|max:11',
            'bs_khatian_no'     =>  'required|max:11',
        ]);

        $data = [
            'registration_date' =>  $request->registration_date,
            'sub_deed_no'       =>  $request->sub_deed_no,
            'land_size'         =>  $request->land_size,
            'mouza_name'        =>  $request->mouza_name,
            'cs_daag_no'        =>  $request->cs_daag_no,
            'sa_daag_no'        =>  $request->sa_daag_no,
            'rs_daag_no'        =>  $request->rs_daag_no,
            'bs_daag_no'        =>  $request->bs_daag_no,
            'cs_khatian_no'     =>  $request->cs_khatian_no,
            'sa_khatian_no'     =>  $request->sa_khatian_no,
            'rs_khatian_no'     =>  $request->rs_khatian_no,
            'bs_khatian_no'     =>  $request->bs_khatian_no,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('power_of_attorney_details')->insert($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Power of Attorney Details Added Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Power of Attorney Details not Added'
            ],200);
        }

    }


    public function addCustomerToPowerOfAttorney()
    {
        $fileInfo = \DB::table('flat_or_plots')
            ->select(
                'id',
                'file_no'
            )
            ->whereNotExists( function ($query) {
                $query->select(DB::raw(1))
                    ->from('plot_or_flat_registrations')
                    ->whereRaw('flat_or_plots.id = plot_or_flat_registrations.flat_or_plots_id')
                    ->whereNotNull('plot_or_flat_registrations.power_of_attorney_details_id');
            })
            ->get();

        return response()->json([
            'status' => 'success',
            'file_info' => $fileInfo,
        ]);
    }


    public function getCustomerInfo($id)
    {
        $customerInfo = FlatOrPlot::where('id', $id)->with([
            'customers' => function ($q){
                $q->select('name', 'nid_number');
            },
            'project' => function($q){
                $q->select('id', 'project_no');
            }])->get();

        return response()->json([
            'status' => 'success',
            'customerInfo' =>  $customerInfo,
        ]);
    }

    public function assignCustomer(Request $request, $id)
    {
        $data = [
            'power_of_attorney_details_id' => $request->power_of_attorney_details_id,
            'created_at' => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('plot_or_flat_registrations')->where('flat_or_plots_id', $id)->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Customer Added Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Data couldn\'t be loaded',
            ]);
        }
    }


    public function powerOfAttorney($id)
    {
        $powerOfAttorney = PowerOfAttorneyDetails::with(['powerOfAttorneyRegistration' => function($query){
            $query->with(['flatOrPlotForPowerOfAttorney' => function($query){
                $query->with(['customers']);
            }]);
        }])->find($id);

        return response()->json([
            'status' => 'success',
            'powerOfAttorney' => $powerOfAttorney,
        ]);
    }

    public function powerOfAttorneyDetailsView(Request $request)
    {

        $data = PowerOfAttorneyDetails::has('powerOfAttorneyRegistration.flatOrPlotForPowerOfAttorney')->with(['powerOfAttorneyRegistration' => function ($query) {
            $query->with(['flatOrPlotForPowerOfAttorney' => function ($query) {
                $query->with('customers');
            }]);
        }])->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'powerOfAttorneyRegistrationDetails' =>  $data,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'registration_date' =>  'required',
            'sub_deed_no'       =>  'required|max:11',
            'land_size'         =>  'required|max:50',
            'mouza_name'        =>  'required|max:11',
            'cs_daag_no'        =>  'required|max:11',
            'sa_daag_no'        =>  'required|max:11',
            'rs_daag_no'        =>  'required|max:11',
            'bs_daag_no'        =>  'required|max:11',
            'cs_khatian_no'     =>  'required|max:11',
            'sa_khatian_no'     =>  'required|max:11',
            'rs_khatian_no'     =>  'required|max:11',
            'bs_khatian_no'     =>  'required|max:11',
        ]);

        $data = [
            'registration_date' =>  $request->registration_date,
            'sub_deed_no'       =>  $request->sub_deed_no,
            'land_size'         =>  $request->land_size,
            'mouza_name'        =>  $request->mouza_name,
            'cs_daag_no'        =>  $request->cs_daag_no,
            'sa_daag_no'        =>  $request->sa_daag_no,
            'rs_daag_no'        =>  $request->rs_daag_no,
            'bs_daag_no'        =>  $request->bs_daag_no,
            'cs_khatian_no'     =>  $request->cs_khatian_no,
            'sa_khatian_no'     =>  $request->sa_khatian_no,
            'rs_khatian_no'     =>  $request->rs_khatian_no,
            'bs_khatian_no'     =>  $request->bs_khatian_no,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('power_of_attorney_details')->where('id', $id)->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Power of Attorney Details Updated Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to Update'
            ],200);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try{
            DB::delete('delete from power_of_attorney_details where id = ?',[$id]);
            DB::commit();
            return response()->json([
                'message' => 'Power of Attorney Details Deleted Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
            ],200);
        }
    }
}

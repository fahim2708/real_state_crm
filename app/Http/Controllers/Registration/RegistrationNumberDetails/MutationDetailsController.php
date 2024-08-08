<?php

namespace App\Http\Controllers\Registration\RegistrationNumberDetails;

use App\Http\Controllers\Controller;
use App\Models\FlatOrPlot;
use App\Models\MutationDetails;
use App\Models\PlotOrFlatDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MutationDetailsController extends Controller
{

    public function index(Request $request)
    {

        $data = MutationDetails::orderBy('id','DESC')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'mutationDetails' => $data,
            ]
        ],200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'registration_date'=>'required',
            'misscase_no'=>'required|max:11',
            'land_size'=>'required|max:50',
            'cs_daag_no'=>'required|max:11',
            'sa_daag_no'=>'required|max:11',
            'rs_daag_no'=>'required|max:11',
            'bs_daag_no'=>'required|max:11',
            'cs_khatian_no'=>'required|max:11',
            'sa_khatian_no'=>'required|max:11',
            'rs_khatian_no'=>'required|max:11',
            'bs_khatian_no'=>'required|max:11',
        ]);

        $data = [
            'registration_date' => $request->registration_date,
            'misscase_no' =>  $request->misscase_no,
            'land_size' =>  $request->land_size,
            'mouza_name' =>  $request->mouza_name,
            'cs_daag_no' =>  $request->cs_daag_no,
            'sa_daag_no' =>  $request->sa_daag_no,
            'rs_daag_no' =>  $request->rs_daag_no,
            'bs_daag_no' =>  $request->bs_daag_no,
            'cs_khatian_no' =>  $request->cs_khatian_no,
            'sa_khatian_no' =>  $request->sa_khatian_no,
            'rs_khatian_no' =>  $request->rs_khatian_no,
            'bs_khatian_no' =>  $request->bs_khatian_no,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('mutation_details')->insert($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Mutation Details Added Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed'
            ],200);
        }
    }


    public function addCustomerToMutation()
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
                    ->whereNotNull('plot_or_flat_registrations.mutation_detailes_id');
            })
            ->get();

        return response()->json([
            'file_info' => $fileInfo,
            'status' => 'success'
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
            'customerInfo' =>  $customerInfo,
            'status' => 'success'
        ]);
    }

    public function assignCustomer(Request $request, $id)
    {
        $data = [
            'mutation_detailes_id' => $request->mutation_detailes_id,
            'created_at' => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('plot_or_flat_registrations')->where('flat_or_plots_id', $id)->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Added Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed'
            ],200);
        }
    }

    public function mutationDetails($id)
    {
        $mutationDetails = MutationDetails::with(['plotOrFlatRegistrationForMutation' => function($query){
            $query->with(['flatOrPlotForMutation' => function($query){
                $query->with(['customers']);
            }]);
        }])->find($id);

        return response()->json([
            'status' => 'success',
            'mutationDetails' => $mutationDetails,
        ]);
    }

    public function mutationDetailsView(Request $request)
    {
        $data = MutationDetails::has('plotOrFlatRegistrationForMutation.flatOrPlotForMutation')->with(['plotOrFlatRegistrationForMutation' => function ($query) {
            $query->with(['flatOrPlotForMutation' => function ($query) {
                $query->with('customers');
            }]);
        }])->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'mutationRegistrationDetails' =>  $data,
            ]
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'registration_date'=>'required',
            'misscase_no'=>'required|max:11',
            'land_size'=>'required|max:50',
            'cs_daag_no'=>'required|max:11',
            'sa_daag_no'=>'required|max:11',
            'rs_daag_no'=>'required|max:11',
            'bs_daag_no'=>'required|max:11',
            'cs_khatian_no'=>'required|max:11',
            'sa_khatian_no'=>'required|max:11',
            'rs_khatian_no'=>'required|max:11',
            'bs_khatian_no'=>'required|max:11',
        ]);

        $data = [
            'registration_date' => $request->registration_date,
            'misscase_no'       => $request->misscase_no,
            'land_size'         => $request->land_size,
            'mouza_name' =>     $request->mouza_name,
            'cs_daag_no' =>     $request->cs_daag_no,
            'sa_daag_no' =>     $request->sa_daag_no,
            'rs_daag_no' =>     $request->rs_daag_no,
            'bs_daag_no' =>     $request->bs_daag_no,
            'cs_khatian_no' =>  $request->cs_khatian_no,
            'sa_khatian_no' =>  $request->sa_khatian_no,
            'rs_khatian_no' =>  $request->rs_khatian_no,
            'bs_khatian_no' =>  $request->bs_khatian_no,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('mutation_details')->where('id', $id)->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Mutation Details Updated Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'Failed to Update'
            ],200);
        }
    }



    public function destroy($id)
    {
        DB::beginTransaction();
        try{
            DB::delete('delete from mutation_details where id = ?',[$id]);
            DB::commit();
            return response()->json([
                'status' => 'Mutation Details Deleted Successfully'
            ],200);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed'
            ],200);
        }
    }
}

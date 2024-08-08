<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\FlatOrPlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FlatOrPlotController extends Controller
{
    // Flat or Plot Store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'project_id' =>'required',
            'file_no' =>'required|unique:flat_or_plots,file_no',
            'flat_number'=>'required_if:type,1',
            'floor_no'=>'required_if:type,1',
            'plot_no' => 'required_if:type,2',
            'face_direction' => 'required|min:4',
            'size'=>'required',


        ],[
          'flat_number.required_if'  => "Flat Number is required",
          'floor_no.required_if'  => "Floor No is required",
          'plot_no.required_if'  => "Plot No is required",
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }

        DB::beginTransaction();
        try{

            $flat_or_plot = new FlatOrPlot();
            $flat_or_plot->project_id             = $request->project_id;
            $flat_or_plot->file_no                = $request->file_no;
            $flat_or_plot->flat_number            = $request->flat_number;
            $flat_or_plot->floor_no               = $request->floor_no;
            $flat_or_plot->plot_no                = $request->plot_no;
            $flat_or_plot->face_direction         = $request->face_direction;
            $flat_or_plot->size                   = $request->size;
            $flat_or_plot->status                 = 0;
            $flat_or_plot->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Information Added Successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'data couldn\'t be loaded',
                'data' => []
            ]);
        }
    }

    // flat or plot in details (excess)
    public function detail($id)
    {
        $details = DB::table('flat_or_plots')->where('id',$id)
            ->selectRaw('
            id,
            project_id,
            file_no as flat_or_plot_file_no,
            floor_no as flat_floor_no,
            flat_number,
            plot_no,
            face_direction as flat_or_plot_face_direction,
            size as flat_or_plot_size,
            CASE
                WHEN status = 1 THEN "Sold"
                ELSE "Unsold"
            END as flat_or_plot_status
        ')->get();
        return response()->json([
            'status' => 'success',
            'details' => $details
        ],200);
    }


    // Flat or Plot data Update
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(),[
            'project_id' =>'required',
//            'file_no' =>'required|unique:flat_or_plots,file_no',
            'flat_number'=>'required_if:type,1',
            'floor_no'=>'required_if:type,1',
            'plot_no' => 'required_if:type,2',
            'face_direction' => 'required|min:4',
            'size'=>'required',
        ],[
            'flat_number.required_if'  => "Flat Number is required",
            'floor_no.required_if'  => "Floor No is required",
            'plot_no.required_if'  => "Plot No is required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
            ]);
        }
        DB::beginTransaction();
        try {
            $flat_or_plot = FlatOrPlot::find($id);

            if (isset($request->project_id)) {
                $flat_or_plot->project_id = $request->project_id;
            }
            if (isset($request->file_no)) {
                $flat_or_plot->file_no = $request->file_no;
            }
            if (isset($request->flat_number)) {
                $flat_or_plot->flat_number = $request->flat_number;

            }
            if (isset($request->floor_no)) {
                $flat_or_plot->floor_no = $request->floor_no;

            }
            if (isset($request->plot_no)) {
                $flat_or_plot->plot_no = $request->plot_no;

            }
            if (isset($request->face_direction)) {
                $flat_or_plot->face_direction = $request->face_direction;
            }
            if (isset($request->size)) {
                $flat_or_plot->size = $request->size;

            }
//            if (isset($request->status)) {
//                $flat_or_plot->status = $request->status;
//            }

            $flat_or_plot->save();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Flat or Plot Updated Successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'data couldn\'t be loaded',
                'data' => []
            ]);
        }
    }
}

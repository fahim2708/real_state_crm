<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LandController extends Controller
{
    // land project store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' =>'required',
            'road_no' =>'required|max:12',
            'project_no'=>'required|unique:projects,project_no',
            'land_size'=>'required',
            'face_direction' => 'required|min:4',
            'location' => 'required',
            'number_of_flat_or_plot'=>'required',
            'work_start_date'=>'sometimes|nullable',
            'work_end_date'=>'sometimes|nullable',
            'work_complete_date' => 'sometimes|nullable',

        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }

        DB::beginTransaction();

        try {
            $project = new Project();

            $project->name = $request->name;
            $project->road_no = $request->road_no;
            $project->project_no = $request->project_no;
            $project->face_direction = $request->face_direction;
            $project->location = $request->location;
            $project->land_size = $request->land_size;
            $project->number_of_flat_or_plot = $request->number_of_flat_or_plot;
            $project->work_start_date = $request->work_start_date;
            $project->work_end_date = $request->work_end_date;
            $project->work_complete_date = $request->work_complete_date;
            $project->type = Project::$LAND;

            $project->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Land Created Successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Land data couldn\'t be loaded',
                'data' => []
            ]);
        }


    }

    //only land data show
    public function data($id)
    {
        $project = Project::find($id);

        return response()->json([
            'status' => 'success',
            'data' => $project
        ]);
    }

    // land data update
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(),[
            'name' =>'required',
            'road_no' =>'required|max:12',
            'project_no'=>'required',
            'land_size'=>'required',
            'face_direction' => 'required|min:4',
            'location' => 'required',
            'number_of_flat_or_plot'=>'required',
            'work_start_date'=>'sometimes|nullable',
            'work_end_date'=>'sometimes|nullable',
            'work_complete_date' => 'sometimes|nullable',

        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }

        try {
            $project = Project::find($id);

            if (isset($request->name)) {
                $project->name = $request->name;

                if (isset($request->road_no)) {
                    $project->road_no = $request->road_no;
                }
                if (isset($request->project_no)) {
                    $project->project_no = $request->project_no;

                }
                if (isset($request->face_direction)) {
                    $project->face_direction = $request->face_direction;

                }
                if (isset($request->location)) {
                    $project->location = $request->location;
                }
                if (isset($request->land_size)) {
                    $project->land_size = $request->land_size;
                }

                if (isset($request->number_of_flat_or_plot)) {
                    $project->number_of_flat_or_plot = $request->number_of_flat_or_plot;

                }
                if (isset($request->work_start_date)) {
                    $project->work_start_date = $request->work_start_date;

                }
                if (isset($request->work_end_date)) {
                    $project->work_end_date = $request->work_end_date;

                }
                if (isset($request->work_complete_date)) {
                    $project->work_complete_date = $request->work_complete_date;

                }
                $project->save();

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Land Updated Successfully'
                ], 200);
            }
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Building data couldn\'t be loaded',
                'data' => []
            ]);
        }


    }
}

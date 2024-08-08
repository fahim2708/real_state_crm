<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllController extends Controller
{
    // all project(building/land) list
    public function view(Request $request)
    {

        $projectList = Project::select(
            'id as project_id',
            'type',
            'project_no',
            'name as project_name',
            'location as project_location',
            'road_no as project_road_no',
            'face_direction as project_face_direction',
            'land_size as project_land_size',
            'total_number_of_floor as project_number_of_floor',
            'number_of_flat_or_plot as project_number_of_flat_or_plot',
            'work_start_date',
            'work_end_date',
            'work_complete_date'
            )
            ->withCount(['flatOrPlot as unsold_flat_or_plot_count' => function ($q) {
                    $q->where('status', 0);
                }, 'flatOrPlot as sold_flat_or_plot_count' => function ($q) {
                    $q->where('status', 1);
                }]);



        $search = $request->search;

        if($search)
        {
            $projectList = $projectList->where('name','like','%'.$search.'%')
                            ->orWhere('location','like','%'.$search.'%')
                            ->orWhere('road_no','like','%'.$search.'%');
        }

        $total_project = $projectList->count();

        $projectList = $projectList->orderBy('id','desc')->get();


        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $projectList
            ]

        ], 200);
    }

    // all project's flat/plot view-details
    public function viewDetails($id)
    {

            $List = Project::with(['FlatOrPlot' => function ($q) {
            $q->select(
                DB::raw('(case when status = 0 then "Unsold" else "Sold" end) as status'),
                'id',
                'project_id',
                'file_no',
                'flat_number',
                'floor_no',
                'plot_no',
                'face_direction',
                'size'
            );
            }])->select(
                'id',
                'type as project_type',
                'name',
                'project_no',
                'total_number_of_floor',
                'number_of_flat_or_plot',
                'land_size',
                'road_no',
                'face_direction',
                'location')
                ->find($id);

        return response()->json([
            'status' => 'success',
            'data' => $List
        ], 200);
    }

    // Building, flat and its customer details
    public function projectCustomerDetails($project_id)
    {
         $details = Project::with('flatOrPlot.customers')->find($project_id);

         return response()->json(
             [
                'details' => $details
             ]
         );
    }


}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\CustomerDocumentFolder;
use App\Models\CustomerDocumentFolderItem;
use App\Models\CustomerProfile;
use App\Models\Nominee;

use App\Models\FlatOrPlot;

use App\Models\PlotOrFlatRegistration;
use App\Models\PriceInformation;
use App\Models\Project;
use App\Models\RegistrationAmount;
use App\Models\RegistrationStatus;
use App\Models\SoldItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class InformationController extends Controller
{

    //add customer
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'customer.*.customer_name'=>'required',
            'customer.*.customer_nid_number'=>'required',
            'customer.*.customer_email'=>'nullable|email:rfc,dns|unique:customers,email',
            'customer.*.customer_phone_number' => 'required|numeric|unique:customers,phone_number',
//            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
            'customer.*.customer_image' => 'required|image|mimes:jpg,png,jpeg,svg',
            'customer.*.customer_country'=>'required',
            'customer.*.customer_permanent_address'=>'required',
            'customer.*.customer_office_address'=>'nullable',
            'customer.*.customer_other_file_no'=>'nullable|numeric',
            'customer.*.nominee_image' => 'sometimes|required|image|mimes:jpg,png,jpeg,svg',
            'customer.*.nominee_contact_number' => 'sometimes|required|numeric',
            'customer.*.nominee_gets' => 'sometimes|required',
            'booking_date' => 'required',
            'media_name' => 'nullable',
            'media_phone_number' => 'nullable|numeric',
            'type' => 'required',
            'flat_or_plot_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
                'data' => []
            ]);
        }

        DB::beginTransaction();

        try{

            $customerArray = array();
            $customerProfileArray = array();
            $customerNomineeArray = array();
            $customerDocumentArray = array();

            foreach ( $request->customer  as $data) {

                $file = $data['customer_image'] ?? null;
                $file_path = null;

                if ($file && $file !== 'null') {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = 'customer/image/' . $file_name;
                    Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                    $file_path = $destinationPath;
                }

                $customer = array(
                    'name' => $data['customer_name'] ?? null,
                    'nid_number' => $data['customer_nid_number'] ?? null,
                    'date_of_birth' => $data['customer_date_of_birth'] ?? null,
                    'phone_number' => $data['customer_phone_number'] ?? null,
                    'email' => $data['customer_email'] ?? null,
                    'mailing_address' => $data['customer_mailing_address'] ?? null,
                    'country' => $data['customer_country'] ?? null,
                    'image' => $file_path,
                );

                $customerData = Customer::create($customer);

                array_push($customerArray, $customerData);

                $profile = CustomerProfile::create([
                    'customer_id' => $customerData->id,
                    'father_name' => $data['customer_father_name'] ?? null,
                    'mother_name' => $data['customer_mother_name'] ?? null,
                    'spouse_name' => $data['customer_spouse_name'] ?? null,
                    'profession' => $data['customer_profession'] ?? null,
                    'permanent_address' => $data['customer_permanent_address'] ?? null,
                    'office_address' => $data['customer_office_address'] ?? null,
                    'designation' => $data['customer_designation'] ?? null,
                ]);

                array_push($customerProfileArray, $profile);

                $document = CustomerDocument::create([
                    'customer_id' => $customerData->id,
                    'other_file_no' => $data['customer_other_file_no'] ?? null,
                ]);

                array_push($customerDocumentArray, $document);


                $file = $data['nominee_image'] ?? null;
                $file_path = null;
                if ($file && $file !== 'null') {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = 'nominee/image/' . $file_name;
                    Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                    $file_path = $destinationPath;
                }

                $nominee = Nominee::create([
                    'customer_id' => $customerData->id,
                    'nominee_name' => $data['nominee_name'] ?? null,
                    'relation_with_nominee' => $data['relation_with_nominee'] ?? null,
                    'nominee_contact_number' => $data['nominee_contact_number'] ?? null,
                    'nominee_address' => $data['nominee_address'] ?? null,
                    'nominee_gets' => $data['nominee_gets'] ?? null,
                    'nominee_image' => $file_path,
                ]);

                array_push($customerNomineeArray, $nominee);

                $flat_or_plot = new SoldItem();

                if ($request->type == Project::$BUILDING) {
                    $flat = FlatOrPlot::find($request->flat_or_plot_id);
                    $flat->status = FlatOrPlot::$SOLD;
                    $flat->booking_date = $request->booking_date;
                    $flat->save();

                    $flat_or_plot->flat_or_plot_id = $request->flat_or_plot_id;
                    $flat_or_plot->project_type  = Project::$BUILDING;
                }

                if ($request->type == Project::$LAND) {
                    $plot = FlatOrPlot::find($request->flat_or_plot_id);
                    $plot->status = 1;
                    $plot->booking_date = $request->booking_date;
                    $plot->save();

                    $flat_or_plot->flat_or_plot_id = $request->flat_or_plot_id;
                    $flat_or_plot->project_type = Project::$LAND;
                }

                $flat_or_plot->media_name = $request->media_name;
                $flat_or_plot->media_phone_number = $request->media_phone_number;
                $flat_or_plot->customer_id = $customerData->id;
                $flat_or_plot->save();


            }
            $registration_amount = new RegistrationAmount();
            $registration_amount->flat_or_plot_id = $request->flat_or_plot_id;
            $registration_amount->save();

            $registration_status = new RegistrationStatus();
            $registration_status->flat_or_plot_id = $request->flat_or_plot_id;
            $registration_status->save();

            $plot_or_flat_id = new PlotOrFlatRegistration();
            $plot_or_flat_id->flat_or_plots_id = $request->flat_or_plot_id;
            $plot_or_flat_id->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Customer successfully created',
                'customers' => $customerArray,
                'customerProfile' => $customerProfileArray,
                'customerNominee' => $customerNomineeArray,
                'customerDocument' => $customerDocumentArray,
                'soldItems' => $flat_or_plot,
            ], 200);

        }catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer data couldn\'t be loaded',
                'data' => []
            ]);

        }

    }

    //get all building and land  response data to frontend
    public function getBuildingLand(Request $request)
    {
        $type = $request->type;

        $get_Buildings_Lands = Project::where('type', $type)->with('flatOrPlot');

            if( $type == 1 ){
                $get_Buildings_Lands->select('id', 'name as building_name');
            } else {
                $get_Buildings_Lands->select('id', 'name as land_name');
            }
        $data = $get_Buildings_Lands->get();


        return response()->json([
            'status' => 'success',
            'data' => $data
        ],200);
    }



    //select flat response from dropdown
    public function flatSelect($building_id,$flat_or_plot_id=null)
    {

        $flatfrombuilding = Project::select('id', 'name as building_name')
            ->where([
                ['type', Project::$BUILDING],
                ['id', $building_id],
            ])->with(['flatOrPlot' => function ($q) use($flat_or_plot_id) {
                $q->where('status', 0)->orWhere('id',$flat_or_plot_id) ;
            }])->first();

        return response()->json([
            'status' => 'success',
            'data' => $flatfrombuilding
        ], 200);
    }


    //select plot response from dropdown
    public function plotSelect($id,$flat_or_plot_id=null)
    {
        $land = Project::select('id', 'name as land_name')
            ->where([
                ['type', Project::$LAND],
                ['id', $id],
            ])->with(['flatOrPlot' => function ($q) use($flat_or_plot_id) {
                $q->where('status', 0)->orWhere('id',$flat_or_plot_id);
            }])->first();

        return response()->json([
            'status' => 'success',
            'data' => $land
        ], 200);
    }


    // all view
    public function view(Request $request)
    {

        $search = $request->search;

        $file = FlatOrPlot::with(['customers','project'])
            ->where(function($q) use ($search)
            {
                if($search)
                {
                    $q->where('file_no','like','%'.$search.'%');
                }
            })
            ->whereHas('customers');

        $file = $file->orderBy('id','desc')->get();


        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $file
            ]
        ]);
    }


    // flat/plot active status is related to flats. if status deactive then flat/plot deactive
    public function active(Request $request)
    {
        if($request->type == Project::$BUILDING){
            $status = FlatOrPlot::findorFail($request->id)->update([
               'is_active' => 0
            ]);
        }elseif ($request->type == Project::$LAND){
            $status = FlatOrPlot::findorFail($request->id)->update([
               'is_active' => 0
            ]);
        }

        return response()->json([
            'status' => $status
        ]);
    }


    // flat/plot deactive status is related to flats. if status active then flat/plot active
    public function deactive(Request $request)
    {
        if($request->type == Project::$BUILDING){
            $status = FlatOrPlot::findorFail($request->id)->update([
                'is_active' => 1
            ]);
        }elseif ($request->type == Project::$LAND){
            $status = FlatOrPlot::findorFail($request->id)->update([
                'is_active' => 1
            ]);
        }

        return response()->json([
            'status' => $status
        ],200);
    }


    // the total details of customer information
    public function details(Request $request)
    {
        $data = Project::with(['flatOrPlot'=>function($query)use($request)
        {
            $query->where('id',$request->flat_or_plot_id)->with(['customers'=>function ($q) use($request)
            {
                $q->with(['profile','document','nominee','soldItem']);
            }]);
        }])->find($request->project_id);

        return response()->json([
            'data' => $data
        ],200);
    }


    // customer edit data
    public function edit($flat_id)
    {
        $data = FlatOrPlot::with('customers','project')->find($flat_id);
        return response()->json([
           'data' => $data,
        ]);
    }


    // customer profile Update
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'customer.*.customer_name'=>'required',
            'customer.*.customer_nid_number'=>'required',
            'customer.*.customer_email'=>'nullable|email:rfc,dns',
            'customer.*.customer_phone_number' => 'required|numeric',
//            'customer.*.customer_image' => 'nullable|image|mimes:jpg,png,jpeg,svg|max:2048',
            'customer.*.customer_country'=>'required',
            'customer.*.customer_permanent_address'=>'required',
            'customer.*.customer_office_address'=>'nullable',
            'customer.*.customer_other_file_no'=>'required|numeric',
//            'customer.*.nominee_image' => 'nullable|image|mimes:jpg,png,jpeg,svg|max:2048',
            'customer.*.nominee_contact_number' => 'sometimes|required|numeric',
            'customer.*.nominee_gets' => 'required',
            'booking_date' => 'required',
            'media_name' => 'nullable',
            'media_phone_number' => 'nullable|required|numeric',
            'type' => 'required',
            'flat_or_plot_id' => 'sometimes|nullable',
            'old_flat_or_plot_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
                'data' => []
            ]);
        }

        DB::beginTransaction();

        try {

            $customerIDS = [];

            foreach ($request->customer as $data) {

                if ($data['id'] != null && $data['id'] != 'null') {
                    $id = $this->updateExitingCustomer($data);
                    array_push($customerIDS, $id);

                } else {
                    $id = $this->addNewCustomer($data);
                    array_push($customerIDS, $id);
                }

            }

            $soldItems = SoldItem::where('flat_or_plot_id', $request->old_flat_or_plot_id)->get('customer_id')->pluck('customer_id');

            $unmatchedCustomers = [];

            //  Find Unmatched Customer
            foreach ($soldItems as $item) {
                if (!in_array($item, $customerIDS)) {
                    array_push($unmatchedCustomers, $item);
                }
            }

            //  Delete Unmatched Customer
            foreach ($unmatchedCustomers as $customer) {
                $this->removeCustomer($customer);
            }


            if ($request->old_flat_or_plot_id != $request->flat_or_plot_id) {
                //  Find old flat or plot and make it unsold
                $plot = FlatOrPlot::find($request->old_flat_or_plot_id);
                $plot->status = FlatOrPlot::$UNSOLD;
                $plot->booking_date = null;
                $plot->save();

                //  find price information of old flat or plot by id and replace it with new flat or plot id
                $priceInformation = PriceInformation::where('flat_or_plot_id', $request->old_flat_or_plot_id)->first();

                if (isset($priceInformation)) {
                    $priceInformation->flat_or_plot_id = $request->flat_or_plot_id;
                    $priceInformation->save();
                }

                //  find registration amount of old flat or plot by id and replace it with new flat or plot id
                $registrationAmount = RegistrationAmount::where('flat_or_plot_id', $request->old_flat_or_plot_id)->first();

                if (isset($registrationAmount)) {
                    $registrationAmount->flat_or_plot_id = $request->flat_or_plot_id;
                    $registrationAmount->save();
                }

                //  find registration status of old flat or plot by id and replace it with new flat or plot id
                $registrationStatus = RegistrationStatus::where('flat_or_plot_id', $request->old_flat_or_plot_id)->first();

                if (isset($registrationStatus)) {
                    $registrationStatus->flat_or_plot_id = $request->flat_or_plot_id;
                    $registrationStatus->save();
                }


                //  Find new flat or plot and make it sold
                $flat = FlatOrPlot::find($request->flat_or_plot_id);
                $flat->status = FlatOrPlot::$SOLD;
                $flat->booking_date = $request->booking_date;
                $flat->save();

            } else {
                $flat = FlatOrPlot::find($request->old_flat_or_plot_id);
                $flat->booking_date = $request->booking_date;
                $flat->save();
            }

            //  Delete all old sold item and initialize again.
            $soldItems = SoldItem::where('flat_or_plot_id', $request->old_flat_or_plot_id)->delete();

            foreach ($customerIDS as $newCustomer) {
                $flat_or_plot = new SoldItem();
                $flat_or_plot->flat_or_plot_id = $request->flat_or_plot_id;

                if ($request->type == Project::$BUILDING) {
                    $flat_or_plot->project_type = Project::$BUILDING;
                }
                if ($request->type == Project::$LAND) {
                    $flat_or_plot->project_type = Project::$LAND;
                }
                $flat_or_plot->media_name = $request->media_name;
                $flat_or_plot->media_phone_number = $request->media_phone_number;
                $flat_or_plot->customer_id = $newCustomer;
                $flat_or_plot->save();
            }

            DB::commit();

            return response([
                'status' => 'success',
                'message' => "Customer Data Updated Successfully",
            ], 200);
        }
        catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer Data Update Unsuccessful',
            ]);

        }

    }


    public function updateExitingCustomer($data)
    {
        //        get the customer from id

        $customer = Customer::with('profile','document','nominee')->where('id',$data['id'])->first();

        //        update customer
        $customer->name = $data['customer_name'] ?? null;
        $customer->nid_number = $data['customer_nid_number'] ?? null;
        $customer->date_of_birth = $data['customer_date_of_birth'] ?? null;
        $customer->phone_number = $data['customer_phone_number'] ?? null;
        $customer->email = $data['customer_email'] ?? null;
        $customer->mailing_address = $data['customer_mailing_address'] ?? null;
        $customer->country = $data['customer_country'] ?? null;

        $file = $data['customer_image'] ?? null;
        $file_path = null;

        if ($file && $file !== 'null') {
            $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'customer/image/' . $file_name;
            Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
            $file_path = $destinationPath;

            if(file_exists(public_path($customer->image)) && isset($customer->image))
            {
                unlink(public_path($customer->image));
            }

            $customer->image = $file_path;

        }

        $customer->save();

//        Update customer profile

        $customer->profile->father_name = $data['customer_father_name'] ?? null;
        $customer->profile->mother_name = $data['customer_mother_name'] ?? null;
        $customer->profile->spouse_name = $data['customer_spouse_name'] ?? null;
        $customer->profile->profession = $data['customer_profession'] ?? null;
        $customer->profile->permanent_address = $data['customer_permanent_address'] ?? null;
        $customer->profile->office_address = $data['customer_office_address'] ?? null;
        $customer->profile->designation = $data['customer_designation'] ?? null;

        $customer->profile->save();


//      Update customer document
        $customer->document->other_file_no =  $data['customer_other_file_no'] ?? null;
        $customer->document->save();

//      Update customer nominee information
        $file = $data['nominee_image'] ?? null;
        $file_path = null;

        $customer->nominee->nominee_name = $data['nominee_name'] ?? null;
        $customer->nominee->relation_with_nominee = $data['relation_with_nominee'] ?? null;
        $customer->nominee->nominee_contact_number = $data['nominee_contact_number'] ?? null;
        $customer->nominee->nominee_address = $data['nominee_address'] ?? null;
        $customer->nominee->nominee_gets = $data['nominee_gets'] ?? null;


        if ($file && $file !== 'null') {
            $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'nominee/image/' . $file_name;
            Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
            $file_path = $destinationPath;

            if(file_exists(public_path($customer->nominee->nominee_image)) && isset($customer->nominee->nominee_image))
            {
                unlink(public_path($customer->nominee->nominee_image));
            }

            $customer->nominee->nominee_image = $file_path;
        }

        $customer->nominee->save();

        return $customer->id;
    }

    public function addNewCustomer($data)
    {
        $file = $data['customer_image'] ?? null;
        $file_path = null;

        if ($file && $file !== 'null') {
            $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'customer/image/' . $file_name;
            Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
            $file_path = $destinationPath;
        }

        $customer = array(
            'name' => $data['customer_name'] ?? null,
            'nid_number' => $data['customer_nid_number'] ?? null,
            'date_of_birth' => $data['customer_date_of_birth'] ?? null,
            'phone_number' => $data['customer_phone_number'] ?? null,
            'email' => $data['customer_email'] ?? null,
            'mailing_address' => $data['customer_mailing_address'] ?? null,
            'country' => $data['customer_country'] ?? null,
            'image' => $file_path,
        );

        $customerData = Customer::create($customer);



        $profile = CustomerProfile::create([
            'customer_id' => $customerData->id,
            'father_name' => $data['customer_father_name'] ?? null,
            'mother_name' => $data['customer_mother_name'] ?? null,
            'spouse_name' => $data['customer_spouse_name'] ?? null,
            'profession' => $data['customer_profession'] ?? null,
            'permanent_address' => $data['customer_permanent_address'] ?? null,
            'office_address' => $data['customer_office_address'] ?? null,
            'designation' => $data['customer_designation'] ?? null,
        ]);



        $document = CustomerDocument::create([
            'customer_id' => $customerData->id,
            'other_file_no' => $data['customer_other_file_no'] ?? null,
        ]);




        $file = $data['nominee_image'] ?? null;
        $file_path = null;
        if ($file && $file !== 'null') {
            $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'nominee/image/' . $file_name;
            Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
            $file_path = $destinationPath;
        }

        $nominee = Nominee::create([
            'customer_id' => $customerData->id,
            'nominee_name' => $data['nominee_name'] ?? null,
            'relation_with_nominee' => $data['relation_with_nominee'] ?? null,
            'nominee_contact_number' => $data['nominee_contact_number'] ?? null,
            'nominee_address' => $data['nominee_address'] ?? null,
            'nominee_gets' => $data['nominee_gets'] ?? null,
            'nominee_image' => $file_path,
        ]);

        return $customerData->id;


    }

    public function removeCustomer($data)
    {
        $customer = Customer::with('profile','document','nominee')->find($data);
            if(isset($customer->profile))
            {
                $customer->profile->delete();
            }

            if(isset($customer->document))
            {
                if(file_exists(public_path($customer->document->other_file_no)) && isset($customer->document->other_file_no))
                {
                    unlink(public_path($customer->document->other_file_no));
                }
                $customer->document->delete();
            }

            if(isset($customer->nominee))
            {
                if(file_exists(public_path($customer->nominee->nominee_image)) && isset($customer->nominee->nominee_image))
                {
                    unlink(public_path($customer->nominee->nominee_image));
                }
                $customer->nominee->delete();
            }

            if(isset($customer))
            {
                if(file_exists(public_path($customer->image)) && isset($customer->image))
                {
                    unlink(public_path($customer->image));
                }
            }

            if(isset($customer))
            {
                $customer->delete();
            }

        $customer->delete();

    }

    // customer document folder implement methods
    public function getFolderList($flatOrPlotID)
    {
        $folders = CustomerDocumentFolder::where('flat_or_plot_id',$flatOrPlotID)->select('id','name','flat_or_plot_id')->get();

        return response([
            'status' => 'success',
            'data' => $folders,
        ],200);

    }

    public function createFolder(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'flat_or_plot_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
                'data' => []
            ]);
        }

        try{
            $folder = new CustomerDocumentFolder();

            $folder->flat_or_plot_id = $request->flat_or_plot_id;
            $folder->name = $request->name;

            $folder->save();

            return response([
                'status' => 'success',
                'message' => 'Documents Created Successfully',
                'data' => $folder
            ]);

        }catch (\Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => 'Documents couldn\'t be loaded',
                'data' => []
            ]);
        }
    }

    public function folderDocumentList($folder_id)
    {
        $item = CustomerDocumentFolderItem::where('customer_document_folder_id',$folder_id)->select('id','file_path','file_name')->get();

        return response([
            'status' => 'success',
            'data' => $item,
        ],200);

    }

    public function folderDocumentStore(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'document'  =>'required|mimes:jpg,png,jpeg,pdf,xlsx',
            'folder_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
                'data' => []
            ]);
        }

        try{
            $item = new CustomerDocumentFolderItem();

            $item->customer_document_folder_id = $request->folder_id;

            $file = $request->document;

            if ($file && $file !== 'null') {

                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'flat_or_plot/document/' . $file_name;

                $file->move(public_path('flat_or_plot/document/'),$destinationPath);

                $file_path = $destinationPath;
                $file_name = $file->getClientOriginalName();

                $item->file_path = $file_path;
                $item->file_name = $file_name;

            }

            $item->save();

            return response([
                'status' => 'success',
                'message' => 'Document Upload Successfully',
                'data' => $item
            ]);
        }catch (\Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => 'Documents Upload couldn\'t be loaded',
                'data' => []
            ]);
        }


    }
}

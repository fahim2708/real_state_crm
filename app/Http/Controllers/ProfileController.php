<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    //admin profile show
    public function show()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Profile details',
            'role' => 'Admin',
            'data' => Auth::user()
        ]);
    }

    // admin profile update
    public function update(Request $request)
    {
        $this->validate($request,[
            'phone' => 'required|regex:/^(?:\+?88)?01[13-9]\d{8}$/',
            'password'=>'required|min:8',
        ]);

        try{
            $user = User::find(Auth::id());

            $user->name = trim($request->name);
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = bcrypt($request->password);

//            if ($request->hasFile('image')) {
//                $imagePath = public_path($agent->image);
//                if(file_exists($imagePath) && $agent->image != null){
//                    unlink($imagePath);
//                }
//
//                $image = $request->file('image');
//                $save_url_thumbnail = ImageHelper::imageUpload($image,150,150,'upload/image/');
//                $agent->image = $save_url_thumbnail;
//            }

            if ($user->save()){
                return response()->json([
                    'status' => 'success',
                    'message' => "Profile updated",
                    'data' => []
                ]);
            }
            return response()->json([
                'status' => 'failed',
                'message' => "Failed to update",
                'data' => []
            ]);
        }
        catch (\Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => "Couldn't update profile. Please try again",
                'data' => []
            ]);
        }
    }
}

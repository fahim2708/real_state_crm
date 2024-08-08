<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $fields = $request->validate([
            'name'      => 'required|string',
            'email'     => 'nullable|email:rfc,dns|unique:users,email',
            'phone'     => 'required|regex:/^(?:\+?88)?01[13-9]\d{8}$/|unique:users,phone',
            'password'  => 'required|min:8',
        ]);

        try{
            $user = User::create([
                'name'     => $fields['name'],
                'email'    => $fields['email'],
                'phone'    => $fields['phone'],
                'password' => bcrypt($fields['password']),

            ]);


            $response = [
                'user' => $user,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Registration Successful',
                'data'  => $response
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => "Couldn't be registered. Please try again",
                'data' => []
            ]);
        }

    }

   public function login(Request $request)
    {
        $this->validate($request,[
            'phone' => 'required|regex:/^(?:\+?88)?01[13-9]\d{8}$/',
            'password'=>'required|min:8',
        ]);

        try {
            $user = User::where('phone', $request->phone)->first();
            if (!$user){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid mobile number'
                ]);
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Incorrect password',
                ]);

            }

            else {
                $token = $user->createToken('ShaplaCity-CRM')->plainTextToken;

                if(!$token){
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Failed to login',
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'You have logged in successfully',
                    'user' => $user,
                    'token' => $token
                ]);
            }
        }
        catch (\Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => "Couldn't be logged in. Please try again",
                'data' => []
            ]);
        }
    }

    public function revoke(User $user,$tokenId)
    {
        $user->tokens()->where('id', $tokenId)->delete();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

}

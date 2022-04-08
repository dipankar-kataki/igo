<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    use ApiResponser;
    public function getOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10'
        ]);
        if($validator->fails()){
            return $this->error('Whoops! Something went wrong', $validator->errors(), 'null', 400);
        }else{
            $phone = $request->phone;
            $otp = rand(1000, 9999);
            Cache::put('otp', $otp, now()->addMinutes(5));
            return $this->success('OTP sent successfully', $otp, 'null', 200);
        }
        
    }

    public function verifyOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
            'otp' => 'required | digits:4'
        ]);
        if($validator->fails()){
            return $this->error('Whoops! Something went wrong', $validator->errors(), 'null', 400);
        }else{
            if(Cache::get('otp') != $request->otp){
                return $this->error('Failed to verify OTP. Invalid OTP.', null, 'null', 400);
            }else{
                $details= User::where('phone', $request->phone)->where('role', 1)->first();
                $is_signup_complete = 0;
                if($details != null){

                    if($details->name != 'null' || $details->name != null){
                        $is_signup_complete = 1;
                    }
                    Cache::forget('otp');
                    $token = $details->createToken('auth_token')->plainTextToken;
                    return $this->success('OTP verified successfully.', $is_signup_complete, $token, 200);
                }else{
                    $create = User::create([
                        'phone' => $request->phone,
                        'role' => 1
                    ]);
                    if($create){
                        Cache::forget('otp');
                        $user = User::where('phone', $request->phone)->firstOrFail();
                        $token = $user->createToken('auth_token')->plainTextToken;
                        return $this->success('OTP verified successfully.', $is_signup_complete, $token, 201);
                    }else{
                        return $this->error('Whoops! Something went wrong', null, 'null', 500);
                    }
                }
                
            }

        }

    }

    public function signup(Request $request){

        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
            'name' => 'required',
            'gender' => 'required',
            'email' => 'required | unique:users',
            'photo' => 'required|image|mimes:jpg,png,jpeg|max:1024'
        ]);

        if($validator->fails()){
            return $this->error('Whoops! Something went wrong', $validator->errors(), 'null', 400);
        }else{
            $profilePic = $request->photo;
            $file = '';

            $email_exists = User::where('email', $request->email)->exists();
            if($email_exists){
                return $this->error('Whoops! Email already exist.', null, 'null', 400);
            }else{
                if($request->hasFile('photo')){
                    $new_name = date('d-m-Y-H-i-s') . '_' . $profilePic->getClientOriginalName();
                    $profilePic->move(public_path('customer/files/profile/'), $new_name);
                    $file = 'customer/files/profile/' . $new_name;
                }

                $create = User::where('phone', $request->phone)->where('role', 1)->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'photo' => $file,
                    'gender' => $request->gender,
                ]);

                if($create){

                    $firebaseToken = PersonalAccessToken::whereNotNull('token')->pluck('token')->all();
            
                    $SERVER_API_KEY = env('FCM_KEY');
                
                    $data = [
                        "registration_ids" => $firebaseToken,
                        "notification" => [
                            "title" => 'Welcome To IGO',
                            "body" => 'Account created successfull',  
                        ]
                    ];
                    $dataString = json_encode($data);
                
                    $headers = [
                        'Authorization: key=' . $SERVER_API_KEY,
                        'Content-Type: application/json',
                    ];
                
                    $ch = curl_init();
                    
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                            
                    $response = curl_exec($ch);
                    return $this->success('Signup successfull.', $response, null, 201);
                }else{
                    return $this->error('Whoops! Something went wrong', null, 'null', 500);
                }
            }
            
        }

    }
}

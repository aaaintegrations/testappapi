<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $user = "";
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /*
    * Login common function
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" =>  "required|string",
            "password" =>  "required|string|min:6",
        ]);

        if($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Something went wrong!",
                "errors" => $validator->errors()
            ]);
        }
        $user = User::where("username", $request->username)->first();

        if(is_null($user)) {
            return response()->json([
                "status" => false,
                "message" => "Failed! usename not found"
            ]);
        }

        if(Auth::attempt(["username" => $request->username, "password" => $request->password, "status" => 1])){
            $user = User::where('username', $request->username)->first();
            $token  = $user->createToken('TutsForWeb')->accessToken;

            return response()->json([
                "status" => true,
                "token" => $token,
                "data" => $user
            ]);
        }
        else {
            return response()->json([
                "status" => false,
                "message" => "Whoops! invalid password"
            ]);
        }
    }

    /*
    * Admin invite user
    */
    public function invite_user(Request $request){
        $result = $this->user->getUser($request->id);
        if($result->role == 'admin'){
            $request->code = mt_rand(100000, 999999);
            $subject = 'Invitation to ApiApp';
            Mail::send('email.invite-user', ['name' => $subject, 'data' => $request], function ($message) use ($request, $subject) {
                $message->subject($subject);
                $message->to(trim($request->email));
                $message->from(env('MAIL_FROM_ADDRESS'));
                $message->replyTo(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                // Create a custom header that we can later retrieve
                $message->getHeaders()->addTextHeader('X-Model-ID',substr(md5(microtime()),rand(0,26),5));
            });
            $response = $this->user->saveData($request);
            if($response){
                return response()->json([
                    "status" => true,
                    "message" => "Congrates! invite email sent!"
                ]);
            }
        }else{
            return response()->json([
                "status" => false,
                "message" => "Whoops! you don't have access to this module"
            ]);
        }

    }

    /*
    * Register user
    */
    public function register(Request $request){
        $result = $this->user->getUserByEmail($request->email);
        if($result){
            if($result->code == $request->code){
                $validator = Validator::make($request->all(), [
                    "name" =>  "required|string",
                    "username" =>  "required|string|min:4|max:50|unique:users,username",
                    "phone" =>  "required|string|unique:users,phone",
                    "password" =>  "required|string|min:6",
                    "avatar" => "required|image:jpeg,png,jpg,gif,svg",
                    'avatar' => 'dimensions:width=256,height=256',
                    "gender" => "required|string",
                    "dob" => "required|date",
                    "registered_at" => "required|date"
                ]);
        
                if($validator->fails()) {
                    return response()->json([
                        "status" => false,
                        "message" => "Something went wrong!",
                        "errors" => $validator->errors()
                    ]);
                }
                $request->code = mt_rand(100000, 999999);
                if($request->avatar) {
                    $avatarImage = 'UA-'.mt_rand(100000, 999999).'-'.time().'.'.$request->avatar->extension();  
                    $request->avatar->move(public_path('uploads/users'), $avatarImage);
                    $request->avatar = $avatarImage;
                }
                $response = $this->user->saveData($request, $result->id);
                if($response){
                    $subject = 'Verification PIN';
                    Mail::send('email.verification-pin', ['name' => $subject, 'data' => $request], function ($message) use ($request, $subject) {
                        $message->subject($subject);
                        $message->to(trim($request->email));
                        $message->from(env('MAIL_FROM_ADDRESS'));
                        $message->replyTo(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        // Create a custom header that we can later retrieve
                        $message->getHeaders()->addTextHeader('X-Model-ID',substr(md5(microtime()),rand(0,26),5));
                    });
                    return response()->json([
                        "status" => true,
                        "message" => "Congrates! Pin send to your email please verify to activate account!"
                    ]);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "Whoops! Somethingwent wrong"
                    ]);
                }
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "Whoops! invitation code dosen't match"
                ]);
            }
        }else{
            return response()->json([
                "status" => false,
                "message" => "Whoops! we haven't invited you"
            ]);
        }

    }

    /*
    * Account activate
    */
    public function activate_account(Request $request){
        $result = $this->user->getUserByEmail($request->email);
        if($result){
            if($result->code == $request->code){
                $request->status = 1;
                $response = $this->user->saveData($request, $result->id);
                if($response){
                    return response()->json([
                        "status" => true,
                        "message" => "Congrates! Account activated successfully!"
                    ]);
                }else{
                    return response()->json([
                        "status" => false,
                        "message" => "Whoops! Somethingwent wrong"
                    ]);
                }
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "Whoops! invitation code dosen't match"
                ]);
            }
        }else{
            return response()->json([
                "status" => false,
                "message" => "Whoops! email dosen't match"
            ]);
        }
    }

    /*
    * Update profile
    */
    public function update_profile(Request $request){
        $result = $this->user->getUser($request->id);
        if($result){
            $validator = Validator::make($request->all(), [
                "name" =>  "required|string",
                "gender" => "required|string",
                "dob" => "required|date",
            ]);

            if($request->avatar){
                $validator = Validator::make($request->all(), [
                    "avatar" => "required|image:jpeg,png,jpg,gif,svg",
                    'avatar' => 'dimensions:width=256,height=256',
                ]);
            }

            if($request->username != $result->username){
                $validator = Validator::make($request->all(), [
                    "username" =>  "required|string|min:4|max:50|unique:users,username",
                ]);
            }

            if($request->phone != $result->phone){
                $validator = Validator::make($request->all(), [
                    "phone" =>  "required|string|unique:users,phone",
                ]);
            }

            if($request->password){
                $validator = Validator::make($request->all(), [
                    "password" =>  "required|string|min:6",
                ]);
            }

            if($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Something went wrong!",
                    "errors" => $validator->errors()
                ]);
            }

            if(request()->hasFile('avatar'))
            {
                if(Auth()->user()->avatar != ''){
                    $filePath = 'public/uploads/users/'.Auth()->user()->avatar;
                    if(file_exists($filePath)){
                        unlink($filePath);
                    }
                }
                
                $avatarImage = 'UA-'.mt_rand(100000, 999999).'-'.time().'.'.$request->avatar->extension();  
                $request->avatar->move(public_path('uploads/users'), $avatarImage);
                $request->avatar = $avatarImage;
            }

            $userData = $this->user->saveData($request, $request->id);
            if($userData){
                return response()->json([
                    "status" => true,
                    "message" => "Congrates! Account updated successfully!"
                ]);
            }else{
                return response()->json([
                    "status" => false,
                    "message" => "Whoops! Somethingwent wrong"
                ]);
            }
        }

    }


    /*
    * Logout user
    */
    public function logout()
    {
        Auth()->user()->tokens()->delete();
        return [
            "message" => "Tokens Revoked"
        ];
    }
}

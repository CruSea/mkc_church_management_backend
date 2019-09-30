<?php

namespace App\Http\Controllers\Authenticate;

use App\Role;
use App\User;
use App\User_log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class MobAuthenticate extends Controller
{

    /**
     * MobAuthenticate constructor.
     */
    public function __construct()
    {
    }
    /**
     * Authenticate User
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        try{
            $rules = [
                'email' => 'required|email|max:255',
                'password' => 'required|max:255',
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status'=>false, 'error'=>'validation error'],500);
            }
            $credentials = $request->only('email', 'password');
            try {
                // verify the credentials and create a token for the user
                if (! $token = JWTAuth::attempt($credentials)) {
                    return response()->json(['status'=>false, 'error' => 'invalid_credentials'], 401);
                }
                else{
                    $user= JWTAuth::toUser($token);
                    if($user instanceof User){
                        if($user->status == true){
                            if($user->user_type_id == 2) {
                                $user_info = User::where('id', '=', $user->id)->first();
                                $user_roles = $user->roles()->get();
                                $user_permission = [];
                                foreach ($user_roles as $user_role) {
                                    $user_permission = Role::where('id', '=', $user_role->id)->first()->perms()->select('name')->get();
                                }
                                $user_log = $this->saveLogHistory($user);
                                return response()->json(['status'=>true, 'message'=> 'successfully authenticated', 'token'=>$token, 'user'=>$user_info,
                                    'user_role'=>$user_roles, 'user_permissions'=>$user_permission],200);
                            } else {
                                return response()->json(['status'=>false, 'message'=> 'Whoops! not mobile account credential', 'error'=>'mobile account credential needed'],500);
                            }
                        }else{
                            return response()->json(['status'=>false, 'message'=> 'Inactive Account', 'error'=>'Your account is not active yet!!!'],500);
                        }
                    }
                }
                return response()->json(['status'=>false, 'error'=>'Something went Wrong!!!'],500);
            } catch (JWTException $e) {
                return response()->json(['status'=>false, 'error' => 'could_not_create_token'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'error'=>$exception->getMessage()],500);
        }
    }
    public function saveLogHistory($user){
        $user_log = new User_log();
        $user_log ->user_id = $user->id;
        $user_log ->last_login_at = Carbon::now();
        $user_log ->save();
        return response()->json(['status'=>true, 'message'=> 'successfully registered', 'user_log'=>$user_log],200);
    }
    /**
     * Validate user data and register
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $data = $request->only(['full_name', 'email', 'password', 'phone']);
            $rules = [
                'email' => 'required|email',
                'password' => 'required|max:255',
                'full_name' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['status'=>false, 'error'=>'validation error'],500);
            }
            $old_user = User::where('email', '=', $data['email'])->first();
            if($old_user instanceof User) {
                return response()->json(['status'=>false, 'message'=>'Whoops! Email Already Taken', 'error'=>'email is already taken'],500);
            } else {
                $newUser = new User();
                $newUser->full_name = $data['full_name'];
                $newUser->email = $data['email'];
                $newUser->password = bcrypt($data['password']);
                $newUser->phone = isset($data['phone']) ? $data['phone']: null;
                $newUser->user_type_id = 2;
                $newUser->status = true;
                $newUser->remember_token = str_random(20);
                if($newUser->save()) {
                    $viewer_role = Role::where('id', '=', 6)->first();
                    $newUser->attachRole($viewer_role);
                    return response()->json(['status'=>true, 'message'=> 'successfully registered', 'user'=>$newUser, 'user_role'=>$viewer_role],200);
                } else {
                    return response()->json(['status'=>false, 'error'=>'Something went Wrong! unable to register'],500);
                }
            }
        } catch (\Exception $exception) {
            return response()->json(['status'=>false, 'error'=>$exception->getMessage()],500);
        }
    }
}

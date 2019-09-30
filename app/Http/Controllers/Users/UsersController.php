<?php

namespace App\Http\Controllers\Users;

use App\Role;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{

    /**
     * UsersController constructor.
     */
    public function __construct()
    {
        $this->middleware('ability:,update-user', ['only' => ['update', 'updateUserStatus']]);
        $this->middleware('ability:,delete-user', ['only' => ['delete']]);
        $this->middleware('ability:,create-user', ['only' => ['create']]);
        $this->middleware('ability:,view-users', ['only' => ['getUsers']]);
        $this->middleware('ability:,normal-mobile-user', ['only' => ['getMe','updateMobile']]);
    }
    public function getUserRoles() {
        try{
            $user_roles = Role::where('id', '<=', 4)->get();
            return response()->json(['status'=> true, 'message'=> 'profile info fetched', 'user_roles'=> $user_roles],200);
        }catch (\Exception $exception) {
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function getMe() {
        try{
            $this_user = Auth::user();
            if($this_user instanceof User) {
                return response()->json(['status'=> true, 'message'=> 'profile info fetched', 'users'=> $this_user],200);
            }
        }catch (\Exception $exception) {
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }

    }
    public function getUsers(){
        try{
            $users = User::with('roles')->where('user_type_id', '=', 1)->paginate(10);
            return response()->json(['status'=> true, 'users'=> $users],200);
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function getMobileUsers(){
        try{
            $users = User::with('roles')->where('user_type_id', '=', 2)->paginate(10);
            return response()->json(['status'=> true, 'users'=> $users],200);
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function create() {
        try{
            $credential = request()->only('full_name', 'email','password', 'user_role_id');
            $rules = [
                'full_name' => 'required|max:255',
                'email' => 'required',
                'password' => 'required',
                'user_role_id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['status'=>false, 'error'=> $error],500);
            }
            $oldUser = User::with('roles')->where('email', '=', $credential['email'])->first();
            if($oldUser instanceof User) {
                return response()->json(['status'=>false, 'error'=>'Whoops! email already taken'],500);
            }else {
                $newUser = new User();
                $newUser->full_name = $credential['full_name'];
                $newUser->email = $credential['email'];
                $newUser->user_type_id = 1;
                $newUser->password = bcrypt($credential['password']);
                $newUser->remember_token = str_random(20);
                if($newUser->save()) {
                    $user_role = Role::where('id', '=', $credential['user_role_id'])->where('id', '<=', 4)->first();
                    $newUser->attachRole($user_role);
                    return response()->json(['status'=>true, 'message'=> 'successfully registered', 'user'=>$newUser, 'user_role'=>$user_role],200);
                } else {
                    return response()->json(['status'=>false, 'error'=>'Something went Wrong! unable to register'],500);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function updateUserStatus() {
        try{
            $credential = request()->only('id', 'status');
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['status'=>false, 'error'=> $error],500);
            }
            $this_user = Auth::user();
            if ($this_user instanceof User) {
                $oldUser = User::where('id', '=',  $credential['id'])->where('id', '!=', $this_user->id)->first();
                if($oldUser instanceof User) {
                    $oldUser->status = isset($credential['status'])? $credential['status']: !$oldUser->status;
                    if($oldUser->update()){
                        return response()->json(['status'=> true, 'message'=> 'user successfully updated', 'user'=>$oldUser],200);
                    } else {
                        return response()->json(['status'=> false, 'message'=> 'unable to update user information', 'error'=>'something went wrong! please try again'],500);
                    }
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! this email address is already taken', 'error'=>'email duplication'],500);
                }
            } else {
                return response()->json(['status'=> false, 'message'=> 'unable to update self status', 'error'=>'unable to update self status'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function update() {
        try{
            $credential = request()->only('id', 'full_name','email', 'user_role_id');
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['status'=>false, 'error'=> $error],500);
            }
            $oldUser = User::with('roles')->where('id', '=',  $credential['id'])->first();
            if($oldUser instanceof User) {
                $oldUser->full_name = isset($credential['full_name'])? $credential['full_name']: $oldUser->full_name;
                $oldUser->email = isset($credential['email'])? $credential['email']: $oldUser->email;
                if(isset($credential['user_role_id'])){
                    $role = Role::where('id', '=', $credential['user_role_id'])->first();
                    $oldUser->roles()->detach($oldUser->roles->first());
                    $oldUser->roles()->attach($role);
                }
                if($oldUser->update()){
                    return response()->json(['status'=> true, 'message'=> 'user successfully updated', 'user'=>$oldUser],200);
                } else {
                    return response()->json(['status'=> false, 'message'=> 'unable to update user information', 'error'=>'something went wrong! please try again'],200);
                }
            }else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! this email address is already taken', 'error'=>'email duplication'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function updateMobile() {
        try{
            $credential = request()->only( 'full_name');
            $rules = [
//                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['status'=>false, 'error'=> $error],500);
            }
            $this_user = Auth::user();
            $oldUser = User::with('roles')->where('id', '=',  $this_user->id)->first();
            if($oldUser instanceof User) {
                $oldUser->full_name = isset($credential['full_name'])? $credential['full_name']: $oldUser->full_name;
                $oldUser->phone = isset($credential['phone'])? $credential['phone']: $oldUser->phone;
                if($oldUser->update()){
                    return response()->json(['status'=> true, 'message'=> 'user successfully updated', 'user'=>$oldUser],200);
                } else {
                    return response()->json(['status'=> false, 'message'=> 'unable to update user information', 'error'=>'something went wrong! please try again'],200);
                }
            }else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! this email address is already taken', 'error'=>'email duplication'],500);
            }

        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function delete($id) {
        try{
            $oldUser = User::where('id', '=', $id)->first();
            $this_user = Auth::user();
            if($oldUser instanceof User && $this_user instanceof User){
                if($oldUser->id != $this_user->id){
                    if($oldUser->delete()){
                        return response()->json(['status'=> true, 'message'=> 'user successfully deleted'],200);
                    }else {
                        return response()->json(['status'=>false, 'message'=> 'Whoops! failed to delete the user account', 'error'=>'failed to delete the user account'],500);
                    }
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! self deletion is not valid', 'error'=>'self deletion is not valid'],500);
                }
            }else{
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to find the user information', 'error'=>'failed to find the user information'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
}

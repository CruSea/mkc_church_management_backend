<?php

namespace App\Http\Controllers\Partnership;

use App\Http\Controllers\Controller;
use App\Partnership;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PartnershipController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['create', 'getSinglePartner']]);
    }

    public function getPartners() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $data = Partnership::orderBy('updated_at', 'DESC')
                ->with('user')->where('status', '=', true)
                ->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Partners fetched successfully', 'partnership_data'=>$data], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }
    public function getSinglePartner() {
        try{

            $this_user = Auth::user();
            if ($this_user instanceof User) {
                $partner = Partnership::where('user_id', $this_user->id)->first();
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }

            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$partner], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }
    public function getPartnerShipRequests() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $data = Partnership::orderBy('updated_at', 'DESC')
                ->with('user')->where('status', '=', false)
                ->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Partners fetched successfully', 'partnership_data'=>$data], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }

    public function create() {
        try{
            $credential = request()->only(
                 'city', 'phone', 'country', 'address',
                'membership_church', 'pledge_amount', 'pledge_frequency'
            );

            $rules = [
                'pledge_amount' => 'required',
                'pledge_frequency' => 'required',
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $this_user = Auth::user();
            if($this_user instanceof User){
                $item = new Partnership();
                $item->user_id = $this_user->id;
                $item->full_name = isset($credential['full_name']) ? $credential['full_name']: "";
                $item->pledge_amount = isset($credential['pledge_amount']) ? $credential['pledge_amount']: null;
                $item->pledge_frequency = isset($credential['pledge_frequency']) ? $credential['pledge_frequency']: null;
                $item->city = isset($credential['city']) ? $credential['city']: null;
                $item->phone = isset($credential['phone']) ? $credential['phone']: null;
                $item->country = isset($credential['country']) ? $credential['country']: null;
                $item->address = isset($credential['address']) ? $credential['address']: null;
                $item->membership_church = isset($credential['membership_church']) ? $credential['membership_church']: null;
                $item->status = isset($credential['status']) ? $credential['status']: false;
                if($item->save()){
                    return response()->json(['status'=> true, 'message'=> 'Partner Successfully Created', 'result'=>$item],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Member', 'error'=>'failed to create Partner'],500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }



    public function addPartnerAdmin() {
        try{
            $credential = request()->only(
                'full_name','city', 'phone', 'country', 'address','email',
                'membership_church', 'pledge_amount', 'pledge_frequency'
            );

            $rules = [
                'pledge_amount' => 'required',
                'pledge_frequency' => 'required',
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }
                $item = new Partnership();
//                $item->user_id = $this_user->id;
                $item->full_name = isset($credential['full_name']) ? $credential['full_name']: null;
                $item->city = isset($credential['city']) ? $credential['city']: null;
                $item->phone = isset($credential['phone']) ? $credential['phone']: null;
                $item->email = isset($credential['email']) ? $credential['email']: null;
                $item->country = isset($credential['country']) ? $credential['country']: null;
                $item->address = isset($credential['address']) ? $credential['address']: null;
                $item->pledge_amount = isset($credential['pledge_amount']) ? $credential['pledge_amount']: null;
                $item->pledge_frequency = isset($credential['pledge_frequency']) ? $credential['pledge_frequency']: null;
                $item->membership_church = isset($credential['membership_church']) ? $credential['membership_church']: null;
                $item->status = isset($credential['status']) ? $credential['status']: !$item->status ;
                if($item->save()){
                    return response()->json(['status'=> true, 'message'=> 'Partner Successfully Created', 'result'=>$item],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Member', 'error'=>'failed to create Partner'],500);
                }

        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }




    public function update() {
        try{
            $credential = request()->only(

                'id','full_name','phone','email','city', 'phone', 'country', 'address',
                'membership_church', 'pledge_amount', 'pledge_frequency','status'
            );

            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }
            $oldItem = Partnership::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof Partnership) {
                $oldItem->full_name = isset($credential['full_name']) ? $credential['full_name']:  $oldItem->full_name;
                $oldItem->city = isset($credential['city']) ? $credential['city']: $oldItem->city;
                $oldItem->phone = isset($credential['phone']) ? $credential['phone']:  $oldItem->phone;
                $oldItem->email = isset($credential['email']) ? $credential['email']:  $oldItem->email;
                $oldItem->country = isset($credential['country']) ? $credential['country']:   $oldItem->country;
                $oldItem->address = isset($credential['address']) ? $credential['address']:  $oldItem->address ;
                $oldItem->pledge_amount = isset($credential['pledge_amount']) ? $credential['pledge_amount']:  $oldItem->pledge_amount;
                $oldItem->pledge_frequency = isset($credential['pledge_frequency']) ? $credential['pledge_frequency']:  $oldItem->pledge_frequency ;
                $oldItem->membership_church = isset($credential['membership_church']) ? $credential['membership_church']:  $oldItem->membership_church;
                $oldItem->status = isset($credential['status']) ? $credential['status']:  $oldItem->status;

                if($oldItem->update()){
                    return response()->json(['status'=> true, 'message'=> 'Item Successfully Updated', 'result'=>$oldItem],200);
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to update Item', 'error'=>'failed to update '],500);
                }
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to find item with ID: '.$credential['id'], 'error'=>'Item not found'],500);
            }
        } catch (\Exception $exception) {
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }



//    public function updateUserStatus() {
//        try{
//            $credential = request()->only('id', 'status');
//            $rules = [
//                'id' => 'required',
//            ];
//            $validator = Validator::make($credential, $rules);
//            if($validator->fails()) {
//                $error = $validator->messages();
//                return response()->json(['status'=>false, 'error'=> $error],500);
//            }
//            $this_user = Auth::user();
//            if ($this_user instanceof Partnership) {
//                $oldUser = Partnership::where('id', '=',  $credential['id'])->where('id', '!=', $this_user->id)->first();
//                if($oldUser instanceof User) {
//                    $oldUser->status = isset($credential['status'])? $credential['status']: !$oldUser->status;
//                    if($oldUser->update()){
//                        return response()->json(['status'=> true, 'message'=> 'user successfully updated', 'user'=>$oldUser],200);
//                    } else {
//                        return response()->json(['status'=> false, 'message'=> 'unable to update user information', 'error'=>'something went wrong! please try again'],500);
//                    }
//                }else {
//                    return response()->json(['status'=>false, 'message'=> 'Whoops! this email address is already taken', 'error'=>'email duplication'],500);
//                }
//            } else {
//                return response()->json(['status'=> false, 'message'=> 'unable to update self status', 'error'=>'unable to update self status'],500);
//            }
//        }catch (\Exception $exception){
//            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
//        }
//    }





    public function delete($id) {
        try{
            $item = Partnership::where('id', '=', $id)->first();
            if($item instanceof Partnership) {
                if($item->delete()){
                    return response()->json(['status'=> true, 'message'=> 'Delete Successfully Deleted'],200);
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! failed to delete item', 'error'=>'failed to delete'],500);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

}

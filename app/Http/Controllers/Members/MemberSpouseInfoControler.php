<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Member;
use App\MemberChildren;
use App\MemberSpouseInfo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Monolog\Logger;

/**
 * Class MemberSpouseInfoControler
 * @package App\Http\Controllers
 */
class MemberSpouseInfoControler extends Controller
{
    //


    /**
     * MemberSpouseInfoControler constructor.
     */
    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['create']]);
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaginated() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;

            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }

            $items = MemberSpouseInfo::orderBy('id', 'desc')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberSpouse($id) {
        try{
            $member = Member::where('id', '=', $id)->get()->first();
            if(! $member instanceof Member){
                return response()->json(['status'=>false, 'message'=> 'Member not found'],404);
            }

            $items = MemberSpouseInfo::where('member_id', '=', $id)->get();
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function create() {
        try{
            $credential = request()->only(
                'full_name', 'member_id','city', 'phone_cell', 'phone_work', 'phone_home',
                'email', 'birth_day', 'occupation', 'employment_place', 'employment_position', 'gender', 'nationality' ,'salvation_date','is_baptized','baptized_date', 'address'
            );

            $rules = [
                'full_name' => 'required',
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $this_user = Auth::user();
            if ($this_user instanceof User) {

                $member = Member::where('user_id', '=', $this_user->id)->get()->first();
                if (!$member instanceof Member) {
                    return response()->json(['success' => false, 'error' => "Member not found!"], 500);
                }

            $item = new MemberSpouseInfo();
            $item->member_id = $member->id;
            $item->full_name = isset($credential['full_name']) ? $credential['full_name']: null;
            $item->city = isset($credential['city']) ? $credential['city']: null;
            $item->phone_cell = isset($credential['phone_cell']) ? $credential['phone_cell']: null;
            $item->phone_work = isset($credential['phone_work']) ? $credential['phone_work']: null;
            $item->phone_home = isset($credential['phone_home']) ? $credential['phone_home']: null;
            $item->email = isset($credential['email']) ? $credential['email']: null;
            $item->birth_day = isset($credential['birth_day']) ? $credential['birth_day']: null;
            $item->occupation = isset($credential['occupation']) ? $credential['occupation']: null;
            $item->employment_place = isset($credential['employment_place']) ? $credential['employment_place']: null;
            $item->employment_position = isset($credential['employment_position']) ? $credential['employment_position']: null;
            $item->gender = isset($credential['gender']) ? $credential['gender']: null;
            $item->salvation_date = isset($credential['salvation_date']) ? $credential['salvation_date']: null;
            $item->is_baptized = isset($credential['is_baptized']) ? $credential['is_baptized']: null;
            $item->baptized_date = isset($credential['baptized_date']) ? $credential['baptized_date']: null;
            $item->address = isset($credential['address']) ? $credential['address']: null;
            if($item->save()){
                return response()->json(['status'=> true, 'message'=> 'Member Successfully Created', 'result'=>$item],200);
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Member', 'error'=>'failed to create Member'],500);
            }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function createForAdmin() {
        try{
            $credential = request()->only(
                'member_id', 'full_name', 'city', 'phone_cell', 'phone_work', 'phone_home',
                'email', 'birth_day', 'occupation', 'employment_place', 'employment_position', 'gender', 'address','nationality','salvation_date','is_baptized','baptized_date'
            );

            $rules = [
                'full_name' => 'required',
                'email' =>'required|email|max:255',
                'phone_cell' =>'required|numeric',
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            if(isset($credential['member_id'])){
                $member = Member::where('id', '=', $credential['member_id'])->get()->first();

            $item = new MemberSpouseInfo();
            $item->member_id = $member->id;
            $item->full_name = isset($credential['full_name']) ? $credential['full_name']: null;
            $item->city = isset($credential['city']) ? $credential['city']: null;
            $item->nationality = isset($credential['nationality']) ? $credential['nationality']: null;
            $item->phone_cell = isset($credential['phone_cell']) ? $credential['phone_cell']: null;
            $item->phone_work = isset($credential['phone_work']) ? $credential['phone_work']: null;
            $item->phone_home = isset($credential['phone_home']) ? $credential['phone_home']: null;
            $item->email = isset($credential['email']) ? $credential['email']: null;
            $item->birth_day = isset($credential['birth_day']) ? $credential['birth_day']: null;
            $item->occupation = isset($credential['occupation']) ? $credential['occupation']: null;
            $item->employment_place = isset($credential['employment_place']) ? $credential['employment_place']: null;
            $item->employment_position = isset($credential['employment_position']) ? $credential['employment_position']: null;
            $item->gender = isset($credential['gender']) ? $credential['gender']: null;
            $item->salvation_date = isset($credential['salvation_date']) ? $credential['salvation_date']: null;
            $item->is_baptized = isset($credential['is_baptized']) ? $credential['is_baptized']: null;
            $item->baptized_date = isset($credential['baptized_date']) ? $credential['baptized_date']: null;
            $item->address = isset($credential['address']) ? $credential['address']: null;
            if($item->save()){
                return response()->json(['status'=> true, 'message'=> 'Member Successfully Created', 'result'=>$item],200);
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Member', 'error'=>'failed to create Member'],500);
            }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }

        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }




    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function update() {
        try{
            $credential = request()->only(
                'id','member_id', 'full_name', 'city', 'phone_cell', 'phone_work', 'phone_home',
                'email', 'birth_day', 'occupation', 'employment_place', 'employment_position', 'gender', 'nationality', 'marital_status','salvation_date','is_baptized','baptized_date', 'address'
            );

            $rules = [
                'member_id' => 'required',
                'full_name' => 'required'
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $member = Member::where('id', '=', $credential['member_id'] )->get()->first();
            if(!$member instanceof Member){
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to find item with the given ID.', 'error'=>'Item not found'],500);
            }

            $oldItem = MemberSpouseInfo::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof MemberSpouseInfo) {
                $oldItem->member_id = isset($credential['member_id'])? $credential['member_id']: $oldItem->member_id;
                $oldItem->full_name = isset($credential['full_name'])? $credential['full_name']: $oldItem->full_name;
                $oldItem->city = isset($credential['city'])? $credential['city']: $oldItem->city;
                $oldItem->phone_cell = isset($credential['phone_cell'])? $credential['phone_cell']: $oldItem->phone_cell;
                $oldItem->phone_work = isset($credential['phone_work'])? $credential['phone_work']: $oldItem->phone_work;
                $oldItem->phone_home = isset($credential['phone_home'])? $credential['phone_home']: $oldItem->phone_home;
                $oldItem->email = isset($credential['email'])? $credential['email']: $oldItem->email;
                $oldItem->birth_day = isset($credential['birth_day'])? $credential['birth_day']: $oldItem->birth_day;
                $oldItem->occupation = isset($credential['occupation'])? $credential['occupation']: $oldItem->occupation;
                $oldItem->employment_place = isset($credential['employment_place'])? $credential['employment_place']: $oldItem->employment_place;
                $oldItem->employment_position = isset($credential['employment_position'])? $credential['employment_position']: $oldItem->employment_position;
                $oldItem->gender = isset($credential['gender'])? $credential['gender']: $oldItem->gender;
                $oldItem->nationality = isset($credential['nationality'])? $credential['nationality']: $oldItem->nationality;
//                $oldItem->address = isset($credential['address'])? $credential['address']: $oldItem->adress;
                $oldItem->salvation_date = isset($credential['salvation_date']) ? $credential['salvation_date']: $oldItem->salvation_date;
                $oldItem->is_baptized = isset($credential['is_baptized']) ? $credential['is_baptized']: $oldItem->is_bapptized;
                $oldItem->baptized_date = isset($credential['baptized_date']) ? $credential['baptized_date']: $oldItem->baptized_date;
                $oldItem->address = isset($credential['address']) ? $credential['address']: $oldItem->address;

                if($oldItem->update()){
                    return response()->json(['status'=> true, 'message'=> 'Item Successfully Updated', 'result'=>$oldItem],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to update Item', 'error'=>'failed to update '],500);
                }
            } else {
                $item = new MemberSpouseInfo();
                $item->member_id = isset($credential['member_id'])? $credential['member_id']: null;
                $item->full_name = isset($credential['full_name'])? $credential['full_name']: null;
                $item->city = isset($credential['city'])? $credential['city']: null;
                $item->phone_cell = isset($credential['phone_cell'])? $credential['phone_cell']: null;
                $item->phone_work = isset($credential['phone_work'])? $credential['phone_work']: null;
                $item->phone_home = isset($credential['phone_home'])? $credential['phone_home']: null;
                $item->email = isset($credential['email'])? $credential['email']: null;
                $item->birth_day = isset($credential['birth_day'])? $credential['birth_day']: null;
                $item->occupation = isset($credential['occupation'])? $credential['occupation']: null;
                $item->employment_place = isset($credential['employment_place'])? $credential['employment_place']: null;
                $item->employment_position = isset($credential['employment_position'])? $credential['employment_position']: null;
                $item->gender = isset($credential['gender'])? $credential['gender']: null;
                $item->nationality = isset($credential['nationality'])? $credential['nationality']: null;
//                $oldItem->address = isset($credential['address'])? $credential['address']: $oldItem->adress;
                $item->salvation_date = isset($credential['salvation_date']) ? $credential['salvation_date']: null;
                $item->is_baptized = isset($credential['is_baptized']) ? $credential['is_baptized']: null;
                $item->baptized_date = isset($credential['baptized_date']) ? $credential['baptized_date']: null;
                $item->address = isset($credential['address']) ? $credential['address']: null;

                if($item->save()){
                    return response()->json(['status'=> true, 'message'=> 'Item Successfully Updated', 'result'=>$item],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to update Item', 'error'=>'failed to update '],500);
                }
            }
        } catch (\Exception $exception) {
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id) {
        try{
            $item = MemberSpouseInfo::where('id', '=', $id)->first();
            if($item instanceof MemberSpouseInfo) {
                if($item->delete()){
                    return response()->json(['status'=> true, 'message'=> 'Delete Successfully Deleted'],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! failed to delete item', 'error'=>'failed to delete'],500);
                }
            }
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
}

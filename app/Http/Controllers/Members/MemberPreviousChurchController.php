<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Member;
use App\MemberPreviousChurches;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Monolog\Logger;

class MemberPreviousChurchController extends Controller
{
    //
    /**
     * MemberPreviousChurchController constructor.
     */
    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['create', 'createMass']]);
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

            $items = MemberPreviousChurches::orderBy('id', 'desc')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberChurches($id) {
        try{
            $member = Member::where('id', '=', $id)->get()->first();
            if(! $member instanceof Member){
                return response()->json(['status'=>false, 'message'=> 'Member not found'],404);
            }

            $items = MemberPreviousChurches::where('member_id', '=', $id)->get();
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function createMass(){
        try {
            $credential = request()->only(
                'churches'
            );

            $rules = [
                'churches' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $this_user = Auth::user();
            if($this_user instanceof User) {

                $member = Member::where('user_id', '=', $this_user->id)->get()->first();
                if (!$member instanceof Member) {
                    return response()->json(['success' => false, 'error' => "Member not found!"], 500);
                }

                $data = json_decode($credential["churches"], true);

                $counter = 0;
                for ($i = 0; $i < sizeof($data); $i++) {
                    $church_name = isset($data[$i]['church_name']) ? $data[$i]['church_name'] : null;
                    $leaving_reason = isset($data[$i]['leaving_reason']) ? $data[$i]['leaving_reason'] : null;
                    $was_member = isset($data[$i]['was_member']) ? $data[$i]['was_member'] : null;
                    $uration = isset($data[$i]['duration']) ? $data[$i]['duration'] : null;

                    $newChurch = new MemberPreviousChurches();
                    $newChurch->member_id = $member->id;
                    $newChurch->church_name = $church_name;
                    $newChurch->leaving_reason = $leaving_reason;
                    $newChurch->was_member = $was_member;
                    $newChurch->duration = $uration;

                    $state = $newChurch->save();
                    if ($state) {
                        $counter++;
                    }
                }

                if ($counter == 1) {
                    return response()->json(['status' => true, 'message' => ' 1 Previous Church Successfully Saved', 'result' => $counter], 200);
                } else if ($counter > 1) {
                    return response()->json(['status' => true, 'message' => $counter . ' Previous Churches Successfully Saved', 'result' => $counter], 200);
                } else {
                    return response()->json(['success' => false, 'error' => "No child is provided"]);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        } catch (\Exception $e){
            return response()->json(['success'=>false,'error'=> $e->getMessage() ]);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function create() {
        try{
            $credential = request()->only(

                 'church_name', 'leaving_reason', 'was_member', 'duration', 'image_file'
            );
            $rules = [
                'church_name' => 'required'
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

            $image_file = request()->file('image_file');
            $image_url = null;
            if (isset($image_file)){
                $file_extension = strtolower($image_file->getClientOriginalExtension());

                if($file_extension == "jpg" || $file_extension == "png") {
                    $posted_file_name = str_random(20) . '.' . $file_extension;
                    $destinationPath = public_path('/previous_church_images');
                    $image_file->move($destinationPath, $posted_file_name);
//                    $image_url = '/previous_church_images/' . $posted_file_name;
                    $image_url = Controller::$API_URL .'/previous_church_images/' .$posted_file_name;
                }
                else {
                    return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                }
            }

            $item = new MemberPreviousChurches();
            $item->member_id = $member->id;
            $item->image_url = $image_url;
            $item->church_name = isset($credential['church_name']) ? $credential['church_name']: null;
            $item->leaving_reason = isset($credential['leaving_reason']) ? $credential['leaving_reason']: null;
            $item->was_member = isset($credential['was_member']) ? $credential['was_member']: null;
            $item->duration = isset($credential['duration']) ? $credential['duration']: null;

            if($item->save()){
                return response()->json(['status'=> true, 'message'=> 'Item Successfully Created', 'result'=>$item],200);
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Item', 'error'=>'failed to create item'],500);
            }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function createAdmin() {
        try{
            $credential = request()->only(

                'member_id', 'church_name', 'leaving_reason', 'was_member', 'duration', 'image_file'
            );
            $rules = [
                'church_name' => 'required',
                'member_id' =>'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            if($credential['member_id']) {
                $member = Member::where('id', '=', $credential['member_id'])->get()->first();

                $image_file = request()->file('image_file');
                $image_url = null;
                if (isset($image_file)) {
                    $file_extension = strtolower($image_file->getClientOriginalExtension());

                    if ($file_extension == "jpg" || $file_extension == "png") {
                        $posted_file_name = str_random(20) . '.' . $file_extension;
                        $destinationPath = public_path('/previous_church_images');
                        $image_file->move($destinationPath, $posted_file_name);
//                    $image_url = '/previous_church_images/' . $posted_file_name;
                        $image_url = Controller::$API_URL .'/previous_church_images/' .$posted_file_name;
                    } else {
                        return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                    }
                }

                $item = new MemberPreviousChurches();
                $item->member_id = $member->id;
                $item->image_url = $image_url;
                $item->church_name = isset($credential['church_name']) ? $credential['church_name'] : null;
                $item->leaving_reason = isset($credential['leaving_reason']) ? $credential['leaving_reason'] : null;
                $item->was_member = isset($credential['was_member']) ? $credential['was_member'] : null;
                $item->duration = isset($credential['duration']) ? $credential['duration'] : null;

                if ($item->save()) {
                    return response()->json(['status' => true, 'message' => 'Item Successfully Created', 'result' => $item], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Whoops! unable to create Item', 'error' => 'failed to create item'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! You Need To Add Member Info First'], 500);
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
                'id', 'member_id', 'church_name', 'leaving_reason', 'was_member', 'duration'
            );

            $rules = [
                'member_id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $oldItem = MemberPreviousChurches::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof MemberPreviousChurches) {

                $image_file = request()->file('image_file');
                $image_url = null;
                if (isset($image_file)) {
                    $file_extension = strtolower($image_file->getClientOriginalExtension());

                    if ($file_extension == "jpg" || $file_extension == "png") {
                        $posted_file_name = str_random(20) . '.' . $file_extension;
                        $destinationPath = public_path('/previous_church_images');
                        $image_file->move($destinationPath, $posted_file_name);
//                    $image_url = '/previous_church_images/' . $posted_file_name;
                        $image_url = Controller::$API_URL .'/previous_church_images/' .$posted_file_name;
                    } else {
                        return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                    }
                }

                $oldItem->member_id = isset($credential['member_id'])? $credential['member_id']: $oldItem->member_id;
                $oldItem->church_name = isset($credential['church_name'])? $credential['church_name']: $oldItem->church_name;
                $oldItem->leaving_reason = isset($credential['leaving_reason'])? $credential['leaving_reason']: $oldItem->leaving_reason;
                $oldItem->was_member = isset($credential['was_member'])? $credential['was_member']: $oldItem->was_member;
                $oldItem->duration = isset($credential['duration'])? $credential['duration']: $oldItem->duration;
                $oldItem->image_url = isset($image_url)? $image_url: $oldItem->image_url;

                if($oldItem->update()){
                    return response()->json(['status'=> true, 'message'=> 'Item Successfully Updated', 'result'=>$oldItem],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to update Item', 'error'=>'failed to update '],500);
                }
            } else {
                $image_file = request()->file('image_file');
                $image_url = null;
                if (isset($image_file)) {
                    $file_extension = strtolower($image_file->getClientOriginalExtension());

                    if ($file_extension == "jpg" || $file_extension == "png") {
                        $posted_file_name = str_random(20) . '.' . $file_extension;
                        $destinationPath = public_path('/previous_church_images');
                        $image_file->move($destinationPath, $posted_file_name);
//                    $image_url = '/previous_church_images/' . $posted_file_name;
                        $image_url = Controller::$API_URL .'/previous_church_images/' .$posted_file_name;
                    } else {
                        return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                    }
                }

                $item = new MemberPreviousChurches();
                $item->member_id = isset($credential['member_id'])? $credential['member_id']: null;
                $item->church_name = isset($credential['church_name'])? $credential['church_name']: null;
                $item->leaving_reason = isset($credential['leaving_reason'])? $credential['leaving_reason']: null;
                $item->was_member = isset($credential['was_member'])? $credential['was_member']: null;
                $item->duration = isset($credential['duration'])? $credential['duration']: null;
                $item->image_url = isset($image_url)? $image_url: null;

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
            $item = MemberPreviousChurches::where('id', '=', $id)->first();
            if($item instanceof MemberPreviousChurches) {
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

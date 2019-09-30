<?php

namespace App\Http\Controllers\Members;

use App\Http\Controllers\Controller;
use App\Member;
use App\MemberChildren;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemberChildrenController extends Controller
{
    //


    /**
     * MemberChildrenController constructor.
     */
    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['createMass']]);

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

            $items = MemberChildren::orderBy('id', 'desc')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberChildren($id) {
        try{
            $member = Member::where('id', '=', $id)->get()->first();
            if(! $member instanceof Member){
                return response()->json(['status'=>false, 'message'=> 'Member not found'],404);
            }

            $items = MemberChildren::where('member_id', '=', $id)->get();
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function createMass(){
        try {
            $credential = request()->only(
                'children'
            );

            $rules = [
                'children' => 'required'
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

                $data = json_decode($credential["children"], true);

                $counter = 0;
                for ($i = 0; $i < sizeof($data); $i++) {
                    $full_name = isset($data[$i]['full_name']) ? $data[$i]['full_name'] : null;
                    $gender = isset($data[$i]['gender']) ? $data[$i]['gender'] : null;
                    $birthday = isset($data[$i]['birthday']) ? $data[$i]['birthday'] : null;

                    $newChild = new MemberChildren();
                    $newChild->member_id = $member->id;
                    $newChild->full_name = $full_name;
                    $newChild->gender = $gender;
                    $newChild->birthday = $birthday;

                    $state = $newChild->save();
                    if ($state) {
                        $counter++;
                    }
                }

                if ($counter == 1) {
                    return response()->json(['status' => true, 'message' => ' 1 Child Successfully Saved', 'result' => $counter], 200);
                } else if ($counter > 1) {
                    return response()->json(['status' => true, 'message' => $counter . ' Children Successfully Saved', 'result' => $counter], 200);
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
                 'member_id','full_name', 'gender', 'birthday'
            );

            $rules = [
                'full_name' => 'required',
                'member_id' => 'required',
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }


            if($credential['member_id']) {
                $member = Member::where('id', '=',$credential['member_id'])->get()->first();
            } else{
                $this_user = Auth::user();
                if ($this_user instanceof User) {

                    $member = Member::where('user_id', '=', $this_user->id)->get()->first();
                    if (!$member instanceof Member) {
                        return response()->json(['success' => false, 'error' => "Member not found!"], 500);
                    }
                }
            }

                $item = new MemberChildren();
                $item->member_id = $member->id;
                $item->full_name = isset($credential['full_name']) ? $credential['full_name'] : null;
                $item->gender = isset($credential['gender']) ? $credential['gender'] : null;
                $item->birthday = isset($credential['birthday']) ? $credential['birthday'] : null;

                if ($item->save()) {
                    return response()->json(['status' => true, 'message' => 'Child Successfully Created', 'result' => $item], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Whoops! unable to create Child', 'error' => 'failed to create Child'], 500);
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
                'id', 'full_name', 'gender', 'birthday'
            );

            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }
            $oldItem = MemberChildren::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof MemberChildren) {
                $oldItem->full_name = isset($credential['full_name'])? $credential['full_name']: $oldItem->full_name;
                $oldItem->gender = isset($credential['gender'])? $credential['gender']: $oldItem->gender;
                $oldItem->birthday = isset($credential['birthday'])? $credential['birthday']: $oldItem->birthday;

                if($oldItem->update()){
                    return response()->json(['status'=> true, 'message'=> 'Item Successfully Updated', 'result'=>$oldItem],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to update Item', 'error'=>'failed to update '],500);
                }
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to find item with ID: '.$credential['id'], 'error'=>'Item not found'],500);
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
            $item = MemberChildren::where('id', '=', $id)->first();
            if($item instanceof MemberChildren) {
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

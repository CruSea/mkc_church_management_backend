<?php

namespace App\Http\Controllers\PrayerRequest;

use App\PrayerRequest;
use App\PrayerReply;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PrayerRequestController extends Controller
{

    /**
     * PrayerRequestController constructor.
     */
    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['create', 'getSinglePrayerRequestData']]);
    }

    public function getPrayerRequests() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $requests = PrayerRequest::orderBy('updated_at', 'DESC') ->with('user')->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Prayer Requests fetched successfully', 'prayer_requests'=>$requests], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find Prayer Request ' . $exception->getMessage()], 500);
        }
    }

    public function getReply(){
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $request_reply = PrayerReply::orderBy('updated_at', 'DESC') ->with('user')->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Prayer Requests fetched successfully', 'prayer_requests'=>$request_reply], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find Prayer Request ' . $exception->getMessage()], 500);
        }
}
    public function getSinglePrayerRequestData() {
        try{
            $this_user = Auth::user();
            if ($this_user instanceof User) {
                $prayer_data = PrayerRequest::where('user_id', $this_user->id)->with('prayer_reply')->get();
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }

            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'prayer_data'=>$prayer_data], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }

    public function reply() {
        try{

            $credential = request()->only( 'request_id', 'type', 'reply');
            $rules = [
                'request_id' => 'required',

            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }
                $oldreply = PrayerReply:: where('request_id', '=', $credential['request_id'])->first();
            if ($oldreply instanceof PrayerReply){
                $oldreply->request_id = isset($credential['request_id']) ? $credential['request_id'] : $oldreply->request_id;
                $oldreply->type = isset($credential['type']) ? $credential['type'] : $oldreply->type;
                $oldreply->reply = isset($credential['reply']) ? $credential['reply'] : $oldreply->reply;
                if ($oldreply->update()) {
                    return response()->json(['status' => true, 'message' => 'Prayer reply Successfully sent', 'result' => $oldreply], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Whoops! unable to save prayer request', 'error' => 'failed to create Prayer request'], 500);
                }
            } else {
               $item = new PrayerReply();
                $item->request_id = isset($credential['request_id']) ? $credential['request_id'] : null;
                $item->type = isset($credential['type']) ? $credential['type'] : null;
                $item->reply = isset($credential['reply']) ? $credential['reply'] : null;
                if ($item->save()) {
                    return response()->json(['status' => true, 'message' => 'Prayer reply Successfully sent', 'result' => $item], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Whoops! unable to save prayer request', 'error' => 'failed to create Prayer request'], 500);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
    public function updatePrayerStatus() {
        try{
            $credential = request()->only('id', 'is_replied');
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['status'=>false, 'error'=> $error],500);
            }

                $oldPrayer = PrayerRequest::where('id', '=',  $credential['id'])->first();
                    $oldPrayer->is_replied = true;
                    if($oldPrayer->update()){
                        return response()->json(['status'=> true, 'message'=> 'user successfully updated', 'user'=>$oldPrayer],200);
                    } else {
                        return response()->json(['status'=> false, 'message'=> 'unable to update user information', 'error'=>'something went wrong! please try again'],500);
                    }

        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function create() {
        try{
            $credential = request()->only('title', 'description', 'photo_file', 'type');
            $rules = [
                'description' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $this_user = Auth::user();
            if($this_user instanceof User) {

                $image_file = request()->file('photo_file');
                $image_url = null;

                if (isset($image_file)) {
                    $file_extension = strtolower($image_file->getClientOriginalExtension());

                    if ($file_extension == "jpg" || $file_extension == "png") {
                        $posted_file_name = str_random(20) . '.' . $file_extension;
                        $destinationPath = public_path('/prayer_request_images');
                        $image_file->move($destinationPath, $posted_file_name);
//                        $image_url = 'http://127.0.0.1:8000/prayer_request_images/'. $posted_file_name;
                        $image_url = 'http://api.prophet-jeremiah.agelgel.net/prayer_request_images/' .$posted_file_name;
                    } else {
                        return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                    }
                }


                $item = new PrayerRequest();
                $item->user_id = $this_user->id;
                $item->description = isset($credential['description']) ? $credential['description'] : "";
                $item->type = isset($credential['type']) ? $credential['type'] : null;
                $item->photo_url = $image_url;

                if ($item->save()) {
                    return response()->json(['status' => true, 'message' => 'Prayer request Successfully Created', 'result' => $item], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Whoops! unable to save prayer request', 'error' => 'failed to create Prayer request'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function delete($id) {
        try{
            $item = PrayerRequest::where('id', '=', $id)->first();
            if($item instanceof PrayerRequest) {
                if($item->delete()){
                    return response()->json(['status'=> true, 'message'=> 'Prayer Successfully Deleted'],200);
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! failed to delete item', 'error'=>'failed to delete'],500);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
}

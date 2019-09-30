<?php

namespace App\Http\Controllers\Donation;

use App\DonationReport;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DonationReportsController extends Controller
{
    //
    /**
     * Donation Report Controller constructor.
     */
    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['create']]);
    }

    public function getDonationReports() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $items = DonationReport::orderBy('updated_at', 'DESC')->with('user')->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> 'Items fetched successfully', 'result'=>$items], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find items ' . $exception->getMessage()], 500);
        }
    }

    public function create() {
        try{
            $credential = request()->only('note', 'donation_method', 'photo_file');
            $rules = [
                'donation_method' => 'required'
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
                        $destinationPath = public_path('/donation_report_images');
                        $image_file->move($destinationPath, $posted_file_name);
//                        $image_url = '/donation_report_images/' . $posted_file_name;
                        $image_url = 'http://api.prophet-jeremiah.agelgel.net/donation_report_images/' .$posted_file_name;
                    } else {
                        return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                    }
                }


                $item = new DonationReport();
                $item->user_id = $this_user->id;
                $item->note = isset($credential['note']) ? $credential['note'] : "";
                $item->donation_method = isset($credential['donation_method']) ? $credential['donation_method'] : null;
                $item->photo_url = $image_url;

                if ($item->save()) {
                    return response()->json(['status' => true, 'message' => 'Donation Report Successfully Created', 'result' => $item], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Whoops! unable to save donation report', 'error' => 'failed to create Donation'], 500);
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
            $item = DonationReport::where('id', '=', $id)->first();
            if($item instanceof DonationReport) {
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

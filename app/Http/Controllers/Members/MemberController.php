<?php

namespace App\Http\Controllers\Members;

use App\Exports\MembersExport;
use App\Imports\MembersImport;
use App\Member;
use App\TeamMember;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Excel;
//use Maatwebsite\Excel\Excel;
use Barryvdh\DomPDF\PDF;
use Monolog\Logger;

class MemberController extends Controller
{

    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        $this->middleware('ability:,create-user', ['only' => ['create','getSingleMobileMember']]);
    }

    public function exportMember()
    {
        return $this->excel->download(new MembersExport, 'members.xlsx');
    }

    public function importMember( Request $request )
    {
        $members = $this->excel->toCollection(new MembersImport, $request->file('import_file'));
             foreach ( $members[0] as $member){
               Member::where('id', $member[0])->update([
                     'user_id' => $member[1],
                     'full_name'  => $member[2],
                     'photo_url' => $member[3],
                     'application_type' => $member[4],
                     'city' => $member[5],
                     'phone_cell' => $member[6],
                     'phone_work' => $member[7],
                     'phone_home' => $member[8],
                     'email' => $member[9],
                     'birth_day' => $member[10],
                     'occupation' => $member[11],
                     'employment_place'=> $member[12],
                     'employment_position' => $member[13],
                     'gender' => $member[14],
                     'nationality' => $member[15],
                     'address' => $member[16],
                     'salvation_date' => $member[17],
                     'is_baptized' => $member[18],
                     'baptized_date' => $member[19],
                     'marital_status' => $member[20],
                 ]);
                 return response()->json(['members' => $members]);
             }


    }
    public function get_member_data(){
        $members = Member::all();
        return $members;
    }


    public function pdf(){

        $pdf = \PDF::loadHTML($this->convert_member_data_to_html())->stream('pdf.pdf');
        return $pdf;

//        $pdf = App::make('dompdf.wrapper');
//        $pdf->loadHTML('<h1>Test ሮጀር</h1>');
//        return $pdf->stream();

      /*  \PDF::setOptions(['dpi' => 150, 'defaultFont' => 'Nyala']);
*/
//        $pdf = \PDF2::loadHTML($this->convert_member_data_to_html())->setPaper('a4', 'landscape');
//        return $pdf ->stream();
    }

    function convert_member_data_to_html()
    {
        $member_data = $this->get_member_data();
        $output = '
       <html>
       <head
       <title>የአባላት መረጃ</title>
       <style type="text/css">
       body {
	font-family: \'examplefont\', sans-serif;
    }
</style>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
       
        <head>
       
        </head>
        <body>
  
     <h3 align="center">የአባላት መረጃ</h3>
     <table width="100%" style="border-collapse: collapse; border: 0px;">
      <tr>
    <th style="border: 1px solid; padding:12px;" width="6%">ቁጥር</th>
    <th style="border: 1px solid; padding:12px;" width="20%">ሙሉ ስም</th>
    <th style="border: 1px solid; padding:12px;" width="17%">ኢሜይል</th>
    <th style="border: 1px solid; padding:12px;" width="7%">ፆታ</th>
    <th style="border: 1px solid; padding:12px;" width="10%">ስልክ</th>
    <th style="border: 1px solid; padding:12px;" width="10%">ዜግነት</th>
    <th style="border: 1px solid; padding:12px;" width="10%">ሴል ቡድን</th>
    <th style="border: 1px solid; padding:12px;" width="8%">ወረዳ</th>
    <th style="border: 1px solid; padding:12px;" width="8%">ክፍለ ከተማ</th>
    <th style="border: 1px solid; padding:12px;" width="10%">የጋብቻ ሁኔታ</th>
   </tr>
     ';
        $num = 0;
        foreach($member_data as $member)
        {
            $num++;
            $output .= '
      <tr>
       <td style="border: 1px solid; padding:12px;">'.$num.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->full_name.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->email.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->gender.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->phone_cell.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->nationality.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->church_group_place.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->wereda.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->sub_city.'</td>
       <td style="border: 1px solid; padding:12px;">'.$member->marital_status.'</td>
      </tr>
      ';
        }
        $output .= '
      </table>
        </body>
        </html>';
        return $output;
    }




public function getMembers() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 5;

            $members = Member::orderBy('updated_at', 'DESC')->with('children','spouse','member_previous_church')->withCount('spouse','children')->where('status','=',true)->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }



    public function getMembersNotInTeam() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 5;

            $credential = request()->only('team_id');

            $rules = [
                'team_id' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $members = Member::orderBy('updated_at', 'DESC')
                ->WhereNotIn('id', TeamMember::select('member_id')->where('team_id', '=', $credential['team_id'])->get())
                ->with('children','spouse','member_previous_church')
                ->where('status','=',true)->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }

    public function getPaginatedSearch(Request $request) {
        try{
               $search_item = $request->get('search');

            $members = Member::where("full_name", "LIKE", "%$search_item%")
                ->orWhere("city", "LIKE", "%$search_item%")
                ->orWhere("phone_cell", "LIKE", "%$search_item%")
                ->orWhere("email", "LIKE", "%$search_item%")
                ->orWhere("gender", "LIKE", "%$search_item%")
                ->orWhere("sub_city", "LIKE", "%$search_item%")
                ->orWhere("wereda", "LIKE", "%$search_item%")
                ->orWhere("occupation", "LIKE", "%$search_item%")
                ->paginate(5);
            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }

    public function getPaginatedSearchDetail(Request $request) {
        try{
            $search_item = $request->get('search');
            $searchType = $request->get('search_type');

            if($searchType == 'name'){
                $members = Member::where("full_name", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }

            else if($searchType == 'phone'){
                $members = Member::where("phone_cell", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }

            else if($searchType == 'email'){
                $members = Member::where("email", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }
            else if($searchType == 'gender'){
                $members = Member::where("gender", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }
            else if($searchType == 'city'){
                $members = Member::where("city", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }
            else if($searchType == 'subcity'){
                $members = Member::where("sub_city", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }
            else if($searchType == 'educational_status'){
                $members = Member::where("educational_status", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }
            else if($searchType == 'marital_status'){
                $members = Member::where("marital_status", "LIKE", "%$search_item%")
                    ->paginate(5);
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }
            else{
                $members = [];
                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
            }

        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }


    public function getPaginatedSearchNotInTeam(Request $request) {
        try{
            $credential = request()->only('team_id');

            $rules = [
                'team_id' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $search_item = $request->get('search');

            $members = Member::WhereNotIn('id', TeamMember::select('member_id')->where('team_id', '=', $credential['team_id'])->get())
                ->where(function($q) use ($search_item) {
                    $q->where(function($query) use ($search_item){
                        $query->where("full_name", "LIKE", "%$search_item%");
                    })
                        ->orWhere(function($query) use ($search_item) {
                            $query->where("city", "LIKE", "%$search_item%");
                        })
                        ->orWhere(function($query) use ($search_item) {
                            $query->where("phone_cell", "LIKE", "%$search_item%");
                        })
                        ->orWhere(function($query) use ($search_item) {
                            $query->where("email", "LIKE", "%$search_item%");
                        })
                        ->orWhere(function($query) use ($search_item) {
                            $query->where("gender", "LIKE", "%$search_item%");
                        })
                        ->orWhere(function($query) use ($search_item) {
                            $query->where("sub_city", "LIKE", "%$search_item%");
                        })
                        ->orWhere(function($query) use ($search_item) {
                            $query->where("wereda", "LIKE", "%$search_item%");
                        })
                        ->orWhere(function($query) use ($search_item) {
                            $query->where("occupation", "LIKE", "%$search_item%");
                        })
                    ;
                })
                ->paginate(5);
            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }


    public function getMemberRequests() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $members = Member::orderBy('updated_at', 'DESC')->where('status','=',false)->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$members], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }
    public function getSingleMobileMember() {
        try{

            $this_user = Auth::user();
            if ($this_user instanceof User) {
            $member = Member::where('user_id', $this_user->id)->first();
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }

            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'members_data'=>$member], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }
    public function getPaginated() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;

            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }

            $members = Member::orderBy('id', 'desc')->paginate($per_page);
            return response()->json(['status'=> true, 'members_data'=> $members],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function create() {
        try{
            $credential = request()->only(
                'member_id','full_name','photo_file', 'application_type', 'city', 'phone_cell', 'phone_work', 'phone_home',
                'email', 'birth_day', 'occupation', 'address','education_level', 'employment_position', 'gender', 'nationality', 'marital_status','salvation_date','is_baptized','baptized_date',
                'sub_city','wereda','house_number','baptized_church','church_group_place','birth_place','emergency_contact_name','emergency_contact_phone','emergency_contact_subcity','emergency_contact_house_no',
                'have_family_fellowship', 'salvation_church'
            );
            $rules = [
                'full_name' => 'required',
//                'email' =>'required|email|max:255',
                'phone_cell' =>'required|numeric',
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $this_user = Auth::user();
            if($this_user instanceof User){
                $image_file = request()->file('photo_file');
                $image_url = null;

                if (isset($image_file)){
                    $file_extension = strtolower($image_file->getClientOriginalExtension());

                    if($file_extension == "jpg" || $file_extension == "png") {
                        $posted_file_name = str_random(20) . '.' . $file_extension;
                        $destinationPath = public_path('/member_images');
                        $image_file->move($destinationPath, $posted_file_name);
                        $image_url = Controller::$API_URL . '/member_images/' . $posted_file_name;
                    }
                    else {
                        return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                    }
                }


                $credential = request()->only(
                    'full_name', 'photo_file', 'city', 'phone_cell', 'phone_work', 'phone_home',
                    'email', 'birth_day', 'occupation', 'address','education_level', 'employment_position', 'gender', 'nationality', 'marital_status','salvation_date','is_baptized','baptized_date',
                    'sub_city','wereda','house_number','church_group_place','birth_place','emergency_contact_name',
                    'emergency_contact_phone', 'baptized_church', 'emergency_contact_wereda','emergency_contact_subcity','emergency_contact_house_no',
                    'have_family_fellowship', 'salvation_church'
                );

                $item = new Member();
                $item->user_id = $this_user->id;
                $item->member_id = $this->getUniqueCode();
                $item->full_name = $credential['full_name'];
                $item->photo_url = $image_url;
                $item->city = isset($credential['city']) ? $credential['city']: null;
                $item->sub_city = isset($credential['sub_city']) ? $credential['sub_city']: null;
                $item->wereda = isset($credential['wereda']) ? $credential['wereda']: null;
                $item->house_number = isset($credential['house_number']) ? $credential['house_number']: null;
                $item->church_group_place = isset($credential['church_group_place']) ? $credential['church_group_place']: null;
                $item->phone_cell = isset($credential['phone_cell']) ? $credential['phone_cell']: null;
                $item->phone_work = isset($credential['phone_work']) ? $credential['phone_work']: null;
                $item->phone_home = isset($credential['phone_home']) ? $credential['phone_home']: null;
                $item->email = isset($credential['email']) ? $credential['email']: null;
                $item->birth_day = isset($credential['birth_day']) ? $credential['birth_day']: null;
                $item->birth_place = isset($credential['birth_place']) ? $credential['birth_place']: null;
                $item->nationality = isset($credential['nationality']) ? $credential['nationality']: null;
                $item->occupation = isset($credential['occupation']) ? $credential['occupation']: null;
                $item->education_level = isset($credential['education_level']) ? $credential['education_level']: null;
                $item->employment_position = isset($credential['employment_position']) ? $credential['employment_position']: null;
                $item->gender = isset($credential['gender']) ? $credential['gender']: null;
                $item->address = isset($credential['address']) ? $credential['address']: null;
                $item->salvation_date = isset($credential['salvation_date']) ? $credential['salvation_date']: null;
                $item->salvation_church = isset($credential['salvation_church']) ? $credential['salvation_church']: null;
                $item->is_baptized = isset($credential['is_baptized']) ? $credential['is_baptized']: null;
                $item->baptized_date = isset($credential['baptized_date']) ? $credential['baptized_date']: null;
                $item->baptized_church = isset($credential['baptized_church']) ? $credential['baptized_church']: null;
                $item->marital_status = isset($credential['marital_status']) ? $credential['marital_status']: null;
                $item->emergency_contact_name = isset($credential['emergency_contact_name']) ? $credential['emergency_contact_name']: null;
                $item->emergency_contact_phone = isset($credential['emergency_contact_phone']) ? $credential['emergency_contact_phone']: null;
                $item->emergency_contact_subcity = isset($credential['emergency_contact_subcity']) ? $credential['emergency_contact_subcity']: null;
                $item->emergency_contact_wereda = isset($credential['emergency_contact_wereda']) ? $credential['emergency_contact_wereda']: null;
                $item->emergency_contact_house_no = isset($credential['emergency_contact_house_no']) ? $credential['emergency_contact_house_no']: null;
                $item->have_family_fellowship = isset($credential['have_family_fellowship']) ? $credential['have_family_fellowship']: false;
                $item->remark = isset($credential['remark']) ? $credential['remark']: null;
                $item->status = isset($credential['status']) ? $credential['status']: false ;

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
//    public function createImageFromBase64( $file_data){
////        $file_data = $request->input('photo_url');
//        $file_name = 'image_'.time().'.png'; //generating unique file name;
//        @list($type, $file_data) = explode(';', $file_data);
//        @list(, $file_data) = explode(',', $file_data);
//        if($file_data!=""){ // storing image in storage/app/public Folder
//            \Storage::disk('public')->put($file_name,base64_decode($file_data));
//        }
//    }
    public function addMembearAdmin() {
        try{
            $credential = request()->only(
                'full_name', 'photo_file', 'city', 'phone_cell', 'phone_work', 'phone_home',
                'email', 'birth_day', 'occupation', 'address','education_level', 'employment_position', 'gender', 'nationality', 'marital_status','salvation_date','is_baptized','baptized_date',
                'sub_city','wereda','house_number', 'baptized_church', 'church_group_place','birth_place','emergency_contact_name','emergency_contact_phone','emergency_contact_wereda', 'emergency_contact_subcity','emergency_contact_house_no',
                'have_family_fellowship', 'salvation_church'
            );
            $rules = [
                'full_name' => 'required',
//                'email' =>'required|unique:members,email|max:255',
                'phone_cell' =>'required|numeric',
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $image_file = request()->file('image_file');

            $photo_url = request()->file('photo_url');

            $image_url = null;
            if (isset($photo_url)){
                $image = $credential['photo_url'];  // your base64 encoded
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = str_random(10) . '.' . 'png';
                \File::put(public_path('/member_images/') . $imageName, base64_decode($image));
                $image_url = Controller::$API_URL . '/member_images/' . $imageName;
            }
            else if (isset($image_file)){
                $file_extension = strtolower($image_file->getClientOriginalExtension());

                if($file_extension == "jpg" || $file_extension == "png") {
                    $posted_file_name = str_random(20) . '.' . $file_extension;
                    $destinationPath = public_path('/member_images');
                    $image_file->move($destinationPath, $posted_file_name);
                    $image_url = Controller::$API_URL . '/member_images/' . $posted_file_name;
                }
                else {
                    return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                }
            }

            $item = new Member();
            $item->member_id = $this->getUniqueCode();
            $item->full_name = $credential['full_name'];
            $item->photo_url = $image_url;
            $item->city = isset($credential['city']) ? $credential['city']: null;
            $item->sub_city = isset($credential['sub_city']) ? $credential['sub_city']: null;
            $item->wereda = isset($credential['wereda']) ? $credential['wereda']: null;
            $item->house_number = isset($credential['house_number']) ? $credential['house_number']: null;
            $item->church_group_place = isset($credential['church_group_place']) ? $credential['church_group_place']: null;
            $item->phone_cell = isset($credential['phone_cell']) ? $credential['phone_cell']: null;
            $item->phone_work = isset($credential['phone_work']) ? $credential['phone_work']: null;
            $item->phone_home = isset($credential['phone_home']) ? $credential['phone_home']: null;
            $item->email = isset($credential['email']) ? $credential['email']: null;
            $item->birth_day = isset($credential['birth_day']) ? $credential['birth_day']: null;
            $item->birth_place = isset($credential['birth_place']) ? $credential['birth_place']: null;
            $item->nationality = isset($credential['nationality']) ? $credential['nationality']: null;
            $item->occupation = isset($credential['occupation']) ? $credential['occupation']: null;
            $item->education_level = isset($credential['education_level']) ? $credential['education_level']: null;
            $item->employment_position = isset($credential['employment_position']) ? $credential['employment_position']: null;
            $item->gender = isset($credential['gender']) ? $credential['gender']: null;
            $item->address = isset($credential['address']) ? $credential['address']: null;
            $item->salvation_date = isset($credential['salvation_date']) ? $credential['salvation_date']: null;
            $item->salvation_church = isset($credential['salvation_church']) ? $credential['salvation_church']: null;
            $item->is_baptized = isset($credential['is_baptized']) ? $credential['is_baptized']: null;
            $item->baptized_date = isset($credential['baptized_date']) ? $credential['baptized_date']: null;
            $item->baptized_church = isset($credential['baptized_church']) ? $credential['baptized_church']: null;
            $item->marital_status = isset($credential['marital_status']) ? $credential['marital_status']: null;
            $item->emergency_contact_name = isset($credential['emergency_contact_name']) ? $credential['emergency_contact_name']: null;
            $item->emergency_contact_phone = isset($credential['emergency_contact_phone']) ? $credential['emergency_contact_phone']: null;
            $item->emergency_contact_wereda = isset($credential['emergency_contact_wereda']) ? $credential['emergency_contact_wereda']: null;
            $item->emergency_contact_subcity = isset($credential['emergency_contact_subcity']) ? $credential['emergency_contact_subcity']: null;
            $item->emergency_contact_house_no = isset($credential['emergency_contact_house_no']) ? $credential['emergency_contact_house_no']: null;
            $item->have_family_fellowship = isset($credential['have_family_fellowship']) ? $credential['have_family_fellowship']: null;
            $item->remark = isset($credential['remark']) ? $credential['remark']: null;
            $item->status = isset($credential['status']) ? $credential['status']: true ;

            if($item->save()){
                    return response()->json(['status'=> true, 'message'=> 'Member Successfully Created', 'result'=>$item],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Member', 'error'=>'failed to create Member'],500);
                }

        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function update() {
        try{
            $credential = request()->only(
                'full_name', 'id','photo_file', 'city', 'phone_cell', 'phone_work', 'phone_home',
                'email', 'birth_day', 'occupation', 'address','education_level', 'employment_position', 'gender', 'nationality', 'marital_status','salvation_date','is_baptized','baptized_date',
                'sub_city','wereda','house_number','baptized_church','church_group_place','birth_place', 'emergency_contact_wereda','emergency_contact_name','emergency_contact_phone','emergency_contact_subcity','emergency_contact_house_no'
            );


            $rules = [
                'id' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }
            $oldItem = Member::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof Member) {

                $image_file = request()->file('image_file');

                $image_url = null;
                if (isset($image_file)){
                    $file_extension = strtolower($image_file->getClientOriginalExtension());
                    if($file_extension == "jpg" || $file_extension == "png") {
                        $posted_file_name = str_random(20) . '.' . $file_extension;
                        $destinationPath = public_path('/member_images');
                        $image_file->move($destinationPath, $posted_file_name);
                        $image_url = Controller::$API_URL . '/member_images/' .$posted_file_name;
                    }
                }

                $oldItem->full_name = isset($credential['full_name'])? $credential['full_name']: $oldItem->full_name;
                $oldItem->photo_url = isset($image_url)? $image_url: $oldItem->photo_url;
                $oldItem->city = isset($credential['city'])? $credential['city']: $oldItem->city;
                $oldItem->sub_city = isset($credential['sub_city'])? $credential['sub_city']: $oldItem->sub_city;
                $oldItem->wereda = isset($credential['wereda'])? $credential['wereda']: $oldItem->wereda;
                $oldItem->house_number = isset($credential['house_number'])? $credential['house_number']: $oldItem->house_number;
                $oldItem->church_group_place = isset($credential['church_group_place'])? $credential['church_group_place']: $oldItem->church_group_place;

                $oldItem->phone_cell = isset($credential['phone_cell'])? $credential['phone_cell']: $oldItem->phone_cell;
                $oldItem->phone_work = isset($credential['phone_work'])? $credential['phone_work']: $oldItem->phone_work;
                $oldItem->phone_home = isset($credential['phone_home'])? $credential['phone_home']: $oldItem->phone_home;
                $oldItem->email = isset($credential['email'])? $credential['email']: $oldItem->email;
                $oldItem->birth_day = isset($credential['birth_day'])? $credential['birth_day']: $oldItem->birth_day;
                $oldItem->birth_place = isset($credential['birth_place'])? $credential['birth_place']: $oldItem->birth_place;
                $oldItem->occupation = isset($credential['occupation'])? $credential['occupation']: $oldItem->occupation;
                $oldItem->employment_position = isset($credential['employment_position'])? $credential['employment_position']: $oldItem->employment_position;
                $oldItem->gender = isset($credential['gender'])? $credential['gender']: $oldItem->gender;
                $oldItem->address = isset($credential['address'])? $credential['address']: $oldItem->address;
                $oldItem->nationality = isset($credential['nationality'])? $credential['nationality']: $oldItem->nationality;
                $oldItem->marital_status = isset($credential['marital_status'])? $credential['marital_status']: $oldItem->marital_status;
                $oldItem->salvation_date = isset($credential['salvation_date']) ? $credential['salvation_date']: $oldItem->salvation_date;
                $oldItem->is_baptized = isset($credential['is_baptized']) ? $credential['is_baptized']: $oldItem->is_baptized;
                $oldItem->baptized_date = isset($credential['baptized_date']) ? $credential['baptized_date']: $oldItem->baptized_date;
                $oldItem->baptized_church = isset($credential['baptized_church']) ? $credential['baptized_church']: $oldItem->baptized_church;
                $oldItem->emergency_contact_name = isset($credential['emergency_contact_name']) ? $credential['emergency_contact_name']: $oldItem->emergency_contact_name;
                $oldItem->emergency_contact_phone = isset($credential['emergency_contact_phone']) ? $credential['emergency_contact_phone']: $oldItem->emergency_contact_phone;
                $oldItem->emergency_contact_subcity = isset($credential['emergency_contact_subcity']) ? $credential['emergency_contact_subcity']: $oldItem->emergency_contact_subcity;
                $oldItem->emergency_contact_wereda = isset($credential['emergency_contact_wereda']) ? $credential['emergency_contact_wereda']: $oldItem->emergency_contact_wereda;
                $oldItem->emergency_contact_house_no = isset($credential['emergency_contact_house_no']) ? $credential['emergency_contact_house_no']: $oldItem->emergency_contact_house_no;
                $oldItem->remark = isset($credential['remark']) ? $credential['remark']: $oldItem->remark;
                $oldItem->status = isset($credential['status'])? $credential['status']: $oldItem->status;

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

    public function delete($id) {
        try{
            $item = Member::where('id', '=', $id)->first();
            if($item instanceof Member) {
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

    public function show($id) {
        try{
            $item = Member::where('id', '=', $id)
                ->with('children','spouse','member_previous_church')
                ->first();
            if($item instanceof Member) {
                return response()->json(['status'=> true, 'message'=> 'Item found!', 'result'=> $item],200);
            }
            else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! failed to find item', 'error'=>'failed to find item'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function getUniqueCode(){
        $length = 5;
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
//        $codeAlphabet= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }
        $token ="MKC-".$token;
        return $token;
    }

}

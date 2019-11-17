<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Member;
use App\Team;
use App\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Monolog\Logger;

class TeamMembersController extends Controller
{

    public function __construct()
    {
        $this->middleware('ability:,create-teams', ['only' => ['create']]);
    }

    public function getTeamMembers() {
        try {
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;

            $credential = request()->only(
                'team_id'
            );
            $rules = [
                'team_id' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $team = Team::where('id', '=', $credential['team_id'])->get()->first();
            if($team instanceof Team){
                $members = TeamMember::where('team_id', '=', $team->id)
                    ->orderBy('updated_at', 'DESC')
                    ->with('teams','members')
                    ->where('status','=',true)
                    ->paginate($paginate_num);

                return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'result'=>$members], 200);
            }
            else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! failed to find team', 'error'=>'failed to find team'],500);
            }
        } catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }

    public function getPaginatedSearch() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;

            $credential = request()->only(
                'team_id', 'search'
            );
            $search_item = $credential['search'];

            $items = TeamMember::where('team_id', '=', $credential['team_id'])
                ->whereIn('member_id', Member::select('id')
                                        ->where("full_name", "LIKE", "%$search_item%")
                                        ->orWhere("phone_cell", "LIKE", "%$search_item%")
                                        ->orWhere("email", "LIKE", "%$search_item%")
                                        ->orWhere("marital_status", "LIKE", "%$search_item%")
                                        ->orWhere("gender", "LIKE", "%$search_item%")
                                        ->orWhere("church_group_place", "LIKE", "%$search_item%")
                                        ->orWhere("sub_city", "LIKE", "%$search_item%")
                                        ->orWhere("wereda", "LIKE", "%$search_item%")
                                        ->orWhere("city", "LIKE", "%$search_item%"))
                ->with('teams', 'members')
                ->paginate($paginate_num);

            return response()->json(['status'=> true,'message'=> ' Item fetched successfully', 'result'=>$items], 200);
        } catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }

    public function getTeamLeaders() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;

            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }

            $credential = request()->only(
                'team_id'
            );

            $team = Team::where('id', '=', $credential['team_id'])->get()->first();
            if($team instanceof Team){
                $items = TeamMember::where('team_id', '=', $team->id)
                    ->where('is_leader', '=', true)
                    ->with('teams', 'members')
                    ->orderBy('id', 'desc')->paginate($per_page);
                return response()->json(['status'=> true, 'result'=> $items],200);
            }
            else{
                return response()->json(['status'=>false, 'message'=> 'Whoops! failed to find team', 'error'=>'failed to find team'],500);
            }
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function getTeamLeadersAll() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;

            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }

            $items = TeamMember::where('is_leader', '=', true)
                ->with('teams', 'members')
                ->orderBy('id', 'desc')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $items],200);

        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }




    public function getTeamMainLeaders() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;

            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }

            $credential = request()->only(
                'team_id'
            );

            $team = Team::where('id', '=', $credential['team_id'])->get()->first();
            if($team instanceof Team){
                $items = TeamMember::where('team_id', '=', $team->id)
                    ->where('is_leader', '=', true)
                    ->where('is_main_leader', '=', true)
                    ->with('teams', 'members')
                    ->orderBy('id', 'desc')->paginate($per_page);
                return response()->json(['status'=> true, 'result'=> $items],200);
            }
            else{
                return response()->json(['status'=>false, 'message'=> 'Whoops! failed to find team', 'error'=>'failed to find team'],500);
            }
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function getTeamMainLeadersAll() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;

            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }

            $items = TeamMember::where('is_leader', '=', true)
                ->where('is_main_leader', '=', true)
                ->with('teams', 'members')
                ->orderBy('id', 'desc')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $items],200);

        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function create() {
        $log = new Logger('Test');
        try{
            $credential = request()->only(
                'team_id', 'member_id', 'is_leader', 'is_main_leader', 'status'
            );
            $rules = [
                'team_id' => 'required',
                'member_id' => 'required'
            ];

            $log->log(Logger::INFO, "Requests", [$credential]);

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $item = new TeamMember();

            $team = Team::where('id', '=', $credential['team_id'])->get()->first();
            if($team instanceof Team){
                $item->team_id = $team->id;
            }
            else{
                return response()->json(['status'=>false, 'message'=> 'Whoops! Team not found', 'error'=>'failed to find Team!'],500);
            }

            $member = Member::where('id', '=', $credential['member_id'])->get()->first();
            if($member instanceof Member){
                $item->member_id = $member->id;
                $log->log(Logger::INFO, "Requests", ["Memeber found"]);

            }
            else{
                return response()->json(['status'=>false, 'message'=> 'Whoops! Member not found', 'error'=>'failed to find Member!'],500);
            }

            $item->is_leader = isset($credential['is_leader']) ? $credential['is_leader']: false;
            $item->is_main_leader = isset($credential['is_main_leader']) ? $credential['is_main_leader']: false;
            $item->status = isset($credential['status']) ? $credential['status']: true ;

            $log->log(Logger::INFO, "Requests", [$item]);

            if($item->save()){
                return response()->json(['status'=> true, 'message'=> 'Team Member Successfully Created', 'result'=>$item],200);
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Team Member', 'error'=>'failed to create Team Member'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function update() {
        try{
            $credential = request()->only(
                'id', 'is_leader', 'is_main_leader', 'status'
            );

            $rules = [
                'id' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $oldItem = TeamMember::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof TeamMember) {

                $oldItem->is_leader = isset($credential['is_leader'])? $credential['is_leader']: $oldItem->is_leader;
                $oldItem->is_main_leader = isset($credential['is_main_leader'])? $credential['is_main_leader']: $oldItem->is_main_leader;
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

    public function show($id) {
        try{
            $item = TeamMember::where('id', '=', $id)->with('teams', 'members')->first();
            if($item instanceof TeamMember) {
                return response()->json(['status'=> true, 'message'=> 'Item found!', 'result'=> $item],200);
            }
            else{
                return response()->json(['status'=>false, 'message'=> 'Whoops! failed to find item', 'error'=>'failed to find item'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function delete($id) {
        try{
            $item = TeamMember::where('id', '=', $id)->first();
            if($item instanceof TeamMember) {
                if($item->delete()){
                    return response()->json(['status'=> true, 'message'=> 'Delete Successfully Deleted', 'result'=> $item],200);
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! failed to delete item', 'error'=>'failed to delete'],500);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }
}

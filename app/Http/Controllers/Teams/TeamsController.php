<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Member;
use App\Team;
use App\TeamCategory;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class TeamsController extends Controller
{

    public function __construct()
    {
        $this->middleware('ability:,create-teams', ['only' => ['create']]);
    }

    public function getMembers() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;

            $members = Member::orderBy('updated_at', 'DESC')->with('children','spouse','member_previous_church')->withCount('spouse','children')->where('status','=',true)->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> ' Member fetched successfully', 'result'=>$members], 200);
        } catch (\Exception $exception){
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

            $teams = Team::orderBy('id', 'desc')->with('user', 'category', 'parent_team', 'team_member', 'team_member.members')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $teams],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function getPaginatedSearch(Request $request) {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $search_item = $request->get('search');

            $teams = Team::where("name", "LIKE", "%$search_item%")
                ->orWhere("description", "LIKE", "%$search_item%")
                ->with('user', 'parent_team', 'category')
                ->paginate($paginate_num);

            return response()->json(['status'=> true,'message'=> ' Teams fetched successfully', 'result'=>$teams], 200);
        } catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }

    public function create() {
        try{
            $credential = request()->only(
                'name', 'description', 'parent_team_id', 'category_id'
            );
            $rules = [
                'name' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $this_user = Auth::user();
            if($this_user instanceof User){

                $item = new Team();
                $item->created_by = $this_user->id;

                $item->name = $credential['name'];
                $item->description = isset($credential['description']) ? $credential['description']: null;
                if(isset($credential['parent_team_id'])){
                    $parent_team = Team::where('id', '=', $credential['parent_team_id'])->get()->first();
                    if($parent_team instanceof Team){
                        $item->parent_team_id = $parent_team->id;
                    }
                }

                if(isset($credential['category_id'])){
                    $category = TeamCategory::where('id', '=', $credential['category_id'])->get()->first();
                    if($category instanceof TeamCategory){
                        $item->category_id = $category->id;
                    }
                }

                $item->status = isset($credential['status']) ? $credential['status']: true ;

                if($item->save()){
                    return response()->json(['status'=> true, 'message'=> 'Team Successfully Created', 'result'=>$item],200);
                } else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Team', 'error'=>'failed to create Team'],500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function update() {

        try{
            $credential = request()->only(
                'id','name', 'description', 'parent_team_id', 'category_id'
            );

            $rules = [
                'id' => 'required'
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $oldItem = Team::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof Team) {

                $oldItem->name = isset($credential['name'])? $credential['name']: $oldItem->name;
                $oldItem->description = isset($credential['description'])? $credential['description']: $oldItem->description;
                $oldItem->parent_team_id = isset($credential['parent_team_id'])? $credential['parent_team_id']: $oldItem->parent_team_id;
                $oldItem->category_id = isset($credential['category_id'])? $credential['category_id']: $oldItem->category_id;
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
            $item = Team::where('id', '=', $id)->with('category', 'parent_team')->first();
            if($item instanceof Team) {
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
            $item = Team::where('id', '=', $id)->first();
            if($item instanceof Team) {
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


    public function getCategories() {
        try{
            $team_categories = TeamCategory::orderBy('id', 'desc')->get();
            return response()->json(['status'=> true, 'result'=> $team_categories],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }



    public function getCategoriesPaginated() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;

            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }

            $team_categories = TeamCategory::orderBy('id', 'desc')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $team_categories],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    /**
     * Categories Controller
     */

    /**
     * @return \Illuminate\Http\JsonResponsed
     *
     */
    public function createCategory() {
        try{
            $credential = request()->only(
                'name', 'description'
            );
            $rules = [
                'name' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $item = new TeamCategory();
            $item->name = $credential['name'];
            $item->description = isset($credential['description']) ? $credential['description']: null;

            if($item->save()){
                return response()->json(['status'=> true, 'message'=> 'ITem Successfully Created', 'result'=>$item],200);
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create item', 'error'=>'failed to create item'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }


    public function updateCategory(){

        try{
            $credential = request()->only(
                'id','name', 'description'
            );

            $rules = [
                'id' => 'required'
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $oldItem = TeamCategory::where('id', '=', $credential['id'])->first();
            if($oldItem instanceof TeamCategory) {

                $oldItem->name = isset($credential['name'])? $credential['name']: $oldItem->name;
                $oldItem->description = isset($credential['description'])? $credential['description']: $oldItem->description;

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

    public function deleteCategory($id) {
        try{
            $item = TeamCategory::where('id', '=', $id)->first();
            if($item instanceof TeamCategory) {
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

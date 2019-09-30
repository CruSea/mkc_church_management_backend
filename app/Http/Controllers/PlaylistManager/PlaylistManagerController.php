<?php

namespace App\Http\Controllers\PlaylistManager;

use App\PlaylistManager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PlaylistManagerController extends Controller
{


    /**
     * PlaylistManagerController constructor.
     */
    public function __construct()
    {
//        $this->middleware('ability:,normal-mobile-user', ['only' => ['getAll']]);
//        $this->middleware('ability:,normal-mobile-user', ['only' => ['getPaginated']]);

    }

    public function getAll() {
        try{
            $items = PlaylistManager::all();
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function getPaginated() {
        try{
            $pages = request()->only('len');
            $per_page = $pages != null ? (int)$pages['len'] : 10;
            if ($per_page > 50) {
                return response()->json(['success' => false, 'error' => 'Maximum page length is 50.'], 401);
            }
            $items = PlaylistManager::orderBy('id', 'DESC')->paginate($per_page);
            return response()->json(['status'=> true, 'result'=> $items],200);
        } catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function create() {
        try{
            $credential = request()->only('title', 'description', 'playlist_id', 'image_file');
            $rules = [
                'title' => 'required',
                'playlist_id' => 'required',
                'image_file' => 'required'
            ];

            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }

            $image_file = request()->file('image_file');
            $image_url = null;

            if (isset($image_file)){
                $file_extension = strtolower($image_file->getClientOriginalExtension());

                if($file_extension == "jpg" || $file_extension == "png") {
                    $posted_file_name = str_random(20) . '.' . $file_extension;
                    $destinationPath = public_path('/playlist_images');
                    $image_file->move($destinationPath, $posted_file_name);
                    $image_url = 'http://api.prophet-jeremiah.agelgel.net/playlist_images/' . $posted_file_name;
                }
                else {
                    return response()->json(['success' => false, 'error' => "The uploaded file does not have a valid image extension."], 500);
                }
            }

            $oldItem = PlaylistManager::where('title', '=', $credential['title'])->get()->first();
            if($oldItem instanceof PlaylistManager){
                return response()->json(['status'=>false, 'message'=> 'The playlist title already taken. Please enter another title. ', 'error'=>'failed to create playlist'],500);
            }

            $item = new PlaylistManager();
            $item->title = $credential['title'];
            $item->playlist_id = $credential['playlist_id'];
            $item->image_url = $image_url;
            $item->description = isset($credential['description']) ? $credential['description']: "";

            if($item->save()){
                return response()->json(['status'=> true, 'message'=> 'Playlist Successfully Created', 'result'=>$item],200);
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to create Playlist', 'error'=>'failed to create Playlist'],500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function update() {
        try{
            $credential = request()->only('id', 'title', 'description','playlist_id');
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['error'=> $error],500);
            }
            $oldPlaylist = PlaylistManager::where('id', '=', $credential['id'])->first();
            if($oldPlaylist instanceof PlaylistManager) {
                $oldPlaylist->title = isset($credential['title'])? $credential['title']: $oldPlaylist->title;
                $oldPlaylist->description = isset($credential['description'])? $credential['description']: $oldPlaylist->title;
                $oldPlaylist->playlist_id = isset($credential['playlist_id'])? $credential['playlist_id']: $oldPlaylist->playlist_id;
                if($oldPlaylist->update()){
                    return response()->json(['status'=> true, 'message'=> 'Playlist Successfully Updated', 'result'=>$oldPlaylist],200);
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! unable to update Playlist', 'error'=>'failed to update '],500);
                }
            } else {
                return response()->json(['status'=>false, 'message'=> 'Whoops! unable to find playlist with ID: '.$credential['id'], 'error'=>'Playlist not found'],500);
            }
        } catch (\Exception $exception) {
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

    public function delete($id) {
        try{
            $item = PlaylistManager::where('id', '=', $id)->first();
            if($item instanceof PlaylistManager) {
                if($item->delete()){
                    return response()->json(['status'=> true, 'message'=> 'Delete Successfully Deleted'],200);
                }else {
                    return response()->json(['status'=>false, 'message'=> 'Whoops! failed to delete playlist', 'error'=>'failed to delete'],500);
                }
            }
        }catch (\Exception $exception){
            return response()->json(['status'=>false, 'message'=> 'Whoops! something went wrong', 'error'=>$exception->getMessage()],500);
        }
    }

}

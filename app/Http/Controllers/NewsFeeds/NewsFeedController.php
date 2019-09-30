<?php

namespace App\Http\Controllers\NewsFeeds;

use App\NewsFeed;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsFeedController extends Controller
{

    /**
     * NewsFeedController constructor.
     */
    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['getPublishedNewsFeedForMobile']]);
        $this->middleware('ability:,create-news-feed', ['only' => ['create', 'update']]);
        $this->middleware('ability:,update-news-feed', ['only' => []]);
        $this->middleware('ability:,delete-news-feed', ['only' => ['delete']]);
        $this->middleware('ability:,view-news-feed', ['only' => ['getAllNewsFeed', 'getPublishedNewsFeed', 'getNotPublishedNewsFeed']]);
    }
    public function getAllNewsFeed() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $news_feeds = NewsFeed::with('post_type', 'created_by', 'updated_by')->withCount('likes')->withCount('comments')->orderBy('updated_at', 'DESC')->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> 'news_feeds fetched successfully', 'news_feeds'=>$news_feeds], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds'], 500);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublishedNewsFeed() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $news_feeds = NewsFeed::with('post_type', 'created_by', 'updated_by')->withCount('likes', 'comments')->orderBy('updated_at', 'DESC')->where("is_published", '=', true)->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> 'published news_feeds fetched successfully', 'news_feeds'=>$news_feeds], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds ' . $exception->getMessage()], 500);
        }
    }
    public function getPublishedNewsFeedForMobile() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $news_feeds = NewsFeed::with('post_type', 'created_by', 'updated_by')
                ->withCount('likes', 'comments')->orderBy('updated_at', 'DESC')->where("is_published", '=', true)->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> 'published news_feeds fetched successfully', 'news_feed'=>$news_feeds], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds', 'error'=>$exception->getMessage()], 500);
        }
    }
    public function getNotPublishedNewsFeed() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $news_feeds = NewsFeed::with('post_type', 'created_by', 'updated_by')->withCount('likes', 'comments')->orderBy('updated_at', 'DESC')->where("is_published", '=', false)->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> 'not published news_feeds fetched successfully', 'news_feeds'=>$news_feeds], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds', 'error' => $exception->getMessage()], 500);
        }
    }
    public function create() {
        try{
            $credential =  request()->only('title', 'detail', 'image_url', 'video_url', 'is_published', 'post_type_id', 'image_file');
            $rules = [
                'title' => 'required',
                'detail' => 'required',
                'post_type_id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['success'=> false, 'error'=> $error, 'message'=>"Invalid format for new news_feed"]);
            }
            $this_user = Auth::user();
            if($this_user instanceof User){
                $image_url = isset($credential['image_url'])? $credential['image_url']: null;
                $image_file = request()->file('image_file');
                if ($image_file) {
                    $file_extension = strtolower($image_file->getClientOriginalExtension());
                    if($file_extension == "jpg" || $file_extension == "png") {
                        $posted_file_name = str_random(30) . '.' . $file_extension;
                        $destinationPath = public_path('/news_feeds_images');
                        $image_file->move($destinationPath, $posted_file_name);
//                        $image_url = 'http://127.0.0.1:8000/news_feeds_images/' .$posted_file_name;
                        $image_url = 'http://api.prophet-jeremiah.agelgel.net/news_feeds_images/' .$posted_file_name;
                    } else {
                        return response()->json(['status'=> false,'message'=> 'Whoops! unable to find the image url'], 500);
                    }
                } else {
                    $image_url = isset($credential['image_url'])? $credential['image_url']: null;
                }
                $newNewsFeed = new NewsFeed();
                $newNewsFeed->title = isset($credential['title'])? $credential['title']: null;
                $newNewsFeed->detail = isset($credential['detail'])? $credential['detail']: null;
                $newNewsFeed->image_url = $image_url;
                $newNewsFeed->video_url = isset($credential['video_url'])? $credential['video_url']: null;
                $newNewsFeed->is_published = isset($credential['is_published'])? $credential['is_published']: true;
                $newNewsFeed->post_type_id = isset($credential['post_type_id'])? $credential['post_type_id']: 1;
                $newNewsFeed->created_by = $this_user->id;
                $newNewsFeed->updated_by = $this_user->id;
                if($newNewsFeed->save()){
                    return response()->json(['status'=> true,'message'=> 'news_feed saved successfully', 'news_feed'=>$newNewsFeed], 200);
                } else {
                    return response()->json(['status'=> false,'message'=> 'Whoops! failed to save contact'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to create news_feed', 'error'=>$exception->getMessage()], 500);
        }
    }
    public function update() {
        try{
            $credential =  request()->only('id', 'title', 'detail', 'image_url', 'video_url', 'is_published', 'post_type_id', 'image_file');
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['success'=> false, 'error'=> $error, 'message'=>"Invalid format for new news_feed"]);
            }
            $this_user = Auth::user();
            if($this_user instanceof User){
                $old_news_feed = NewsFeed::where('id', '=', $credential['id'])->first();
                if($old_news_feed instanceof NewsFeed) {
                    $image_url = isset($credential['image_url'])? $credential['image_url']: null;
                    $image_file = request()->file('image_file');
                    if ($image_file) {
                        $file_extension = strtolower($image_file->getClientOriginalExtension());
                        if($file_extension == "jpg" || $file_extension == "png") {
                            $posted_file_name = str_random(30) . '.' . $file_extension;
                            $destinationPath = public_path('/news_feeds_images');
                            $image_file->move($destinationPath, $posted_file_name);
                            $image_url = 'http://api.prophet-jeremiah.agelgel.net/news_feeds_images/' . $posted_file_name;
                        } else {
                            return response()->json(['status'=> false,'message'=> 'Whoops! unable to find the image url'], 500);
                        }
                    }
                    $old_news_feed->title = isset($credential['title'])? $credential['title']:  $old_news_feed->title;
                    $old_news_feed->detail = isset($credential['detail'])? $credential['detail']:   $old_news_feed->detail;
                    $old_news_feed->image_url = $image_url;
                    $old_news_feed->video_url = isset($credential['video_url'])? $credential['video_url']:  $old_news_feed->video_url;
                    $old_news_feed->is_published = isset($credential['is_published'])? $credential['is_published']: $old_news_feed->is_published;
                    $old_news_feed->post_type_id = isset($credential['post_type_id'])? $credential['post_type_id']: $old_news_feed->post_type_id;
                    $old_news_feed->updated_by = $this_user->id;
                    if($old_news_feed->update()){
                        return response()->json(['status'=> true,'message'=> 'news_feed updated successfully', 'news_feed'=>$old_news_feed], 200);
                    } else {
                        return response()->json(['status'=> false,'message'=> 'Whoops! failed to update contact'], 500);
                    }
                } else {
                    return response()->json(['status'=> false,'message'=> 'Whoops! unable to find news feed with this id'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to create news_feed', 'error'=>$exception->getMessage()], 500);
        }
    }
    public function delete($id) {
        try{
            $old_news_feed = NewsFeed::where('id', '=', $id)->first();
            if($old_news_feed instanceof NewsFeed){
                if($old_news_feed->delete()){
                    return response()->json(["status" => true, "message"=>'news_feed deleted successfully']);
                }
            } else {
                return response()->json(["status" => false, "message"=>'whoops! unable to delete news_feed'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(["status" => false, "message"=>'Whoops! Failed to delete', "error"=>$exception->getMessage()]);
        }
    }
}

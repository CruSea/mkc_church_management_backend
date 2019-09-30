<?php

namespace App\Http\Controllers\NewsFeeds;

use App\NewsFeed;
use App\NewsFeedComment;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsFeedCommentsController extends Controller
{

    /**
     * NewsFeedCommentsControler constructor.
     */
    public function __construct()
    {
//        $this->middleware('ability:,view-news-feed', ['only' => ['getAll', 'getCommentsList']]);
//        $this->middleware('ability:,normal-mobile-user', ['only' => ['getAll', 'getCommentsList']]);
        $this->middleware('ability:,verified-mobile-user', ['only' => ['create', 'update', 'delete']]);
    }
    public function getAll() {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $news_feed_comments = NewsFeedComment::with('user')->orderBy('id', 'DESC')->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> 'news_feeds fetched successfully', 'news_feed_comments'=>$news_feed_comments], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feed_comments'], 500);
        }
    }
    public function getCommentsList($id) {
        try{
            $paginate_num = request()->input('PAGINATE_SIZE')? request()->input('PAGINATE_SIZE') : 10;
            $news_feed_comments = NewsFeedComment::with('user')->where('news_feed_id', '=', $id)->orderBy('id', 'DESC')->paginate($paginate_num);
            return response()->json(['status'=> true,'message'=> 'news_feeds fetched successfully', 'news_feed_comments'=>$news_feed_comments], 200);
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to find news_feeds'], 500);
        }
    }
    public function create() {
        try{
            $credential =  request()->only('news_feed_id', 'comment');
            $rules = [
                'news_feed_id' => 'required',
                'comment' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['success'=> false, 'error'=> $error, 'message'=>"Invalid format for new news_feed"]);
            }
            $this_user = Auth::user();
            if($this_user instanceof User){
                $old_news_feed = NewsFeed::where('id', '=', $credential['news_feed_id'])->first();
                if($old_news_feed instanceof NewsFeed) {
                    $newNewsFeedComment = new NewsFeedComment();
                    $newNewsFeedComment->comment = isset($credential['comment'])? $credential['comment']: null;
                    $newNewsFeedComment->news_feed_id = $old_news_feed->id;
                    $newNewsFeedComment->user_id = $this_user->id;
                    if($newNewsFeedComment->save()){
                        return response()->json(['status'=> true,'message'=> 'news_feed_comment saved successfully', 'news_feed_comment'=>$newNewsFeedComment], 200);
                    } else {
                        return response()->json(['status'=> false,'message'=> 'Whoops! failed to save news_feed_comment'], 500);
                    }
                } else {
                    return response()->json(['status'=> false,'message'=> 'Whoops! unable to find the news_feed'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to create contact', 'error'=>$exception->getMessage()], 500);
        }
    }
    public function update() {
        try{
            $credential =  request()->only('id', 'news_feed_id', 'comment');
            $rules = [
                'id' => 'required',
                'news_feed_id' => 'required',
                'comment' => 'required',
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['success'=> false, 'error'=> $error, 'message'=>"Invalid format for new news_feed"]);
            }
            $this_user = Auth::user();
            if($this_user instanceof User){
                $old_news_feed = NewsFeed::where('id', '=', $credential['news_feed_id'])->first();
                if($old_news_feed instanceof NewsFeed) {
                    $oldNewsFeedComment = NewsFeedComment::where('id', '=', $credential['id'])->where('user_id', '=', $this_user->id)->first();
                    if($oldNewsFeedComment instanceof NewsFeedComment) {
                        $oldNewsFeedComment->comment = isset($credential['title'])? $credential['title']: null;
                        $oldNewsFeedComment->news_feed_id = $old_news_feed->id;
                        $oldNewsFeedComment->user_id = $this_user->id;
                        if($oldNewsFeedComment->update()){
                            return response()->json(['status'=> true,'message'=> 'news_feed_comment updates successfully', 'news_feed_comment'=>$oldNewsFeedComment], 200);
                        } else {
                            return response()->json(['status'=> false,'message'=> 'Whoops! failed to updated news_feed_comment'], 500);
                        }
                    } else {
                        return response()->json(['status'=> false,'message'=> 'Whoops! unable to find this news_feed_comment'], 500);
                    }
                } else {
                    return response()->json(['status'=> false,'message'=> 'Whoops! unable to find this news_feed'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception) {

        }
    }
    public function delete($id) {
        try{
            $old_news_feed_comment = NewsFeedComment::where('id', '=', $id)->first();
            if($old_news_feed_comment instanceof NewsFeed){
                if($old_news_feed_comment->delete()){
                    return response()->json(["status" => true, "message"=>'news_feed_comment deleted successfully']);
                }
            } else {
                return response()->json(["status" => false, "message"=>'whoops! unable to delete news_feed_comment'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(["status" => false, "message"=>'Whoops! Failed to delete', "error"=>$exception->getMessage()]);
        }
    }
}

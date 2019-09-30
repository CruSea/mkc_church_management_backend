<?php

namespace App\Http\Controllers\NewsFeeds;

use App\NewsFeed;
use App\NewsFeedLike;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsFeedLikesController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('ability:,normal-mobile-user', ['only' => ['like', 'unLike']]);
    }

    public function like() {
        try{
            $credential =  request()->only('news_feed_id');
            $rules = [
                'news_feed_id' => 'required'
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['success'=> false, 'error'=> $error, 'message'=>"Invalid format for feed like"]);
            }
            $this_user = Auth::user();
            if($this_user instanceof User){
                $news_feed = NewsFeed::where('id', '=', $credential['news_feed_id'])->first();
                if($news_feed instanceof NewsFeed) {
                    $old_like = NewsFeedLike::where('news_feed_id', '=', $news_feed->id)->where('user_id', '=', $this_user->id)->get()->first();
                    if(! $old_like instanceof NewsFeedLike){
                        $new_like = new NewsFeedLike();
                        $new_like->news_feed_id = $news_feed->id;
                        $new_like->user_id = $this_user->id;
                        $state = $new_like->save();
                        if($state){
                            return response()->json(['status'=> true,'message'=> 'like saved successfully', 'result'=>$new_like], 200);
                        }
                        else {
                            return response()->json(['status'=> false,'message'=> 'Whoops! failed to save feed like'], 500);
                        }
                    } {
                        return response()->json(['status'=> false,'message'=> 'Whoops! You have already liked this feed.'], 500);
                    }
                } else {
                    return response()->json(['status'=> false,'message'=> 'Whoops! unable to find the news_feed'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to create feed like', 'error'=>$exception->getMessage()], 500);
        }
    }


    public function unLike() {
        try{
            $credential =  request()->only('news_feed_id');
            $rules = [
                'news_feed_id' => 'required'
            ];
            $validator = Validator::make($credential, $rules);
            if($validator->fails()) {
                $error = $validator->messages();
                return response()->json(['success'=> false, 'error'=> $error, 'message'=>"Invalid format for feed like"]);
            }

            $this_user = Auth::user();
            if($this_user instanceof User){
                $news_feed = NewsFeed::where('id', '=', $credential['news_feed_id'])->first();
                if($news_feed instanceof NewsFeed) {
                    $old_like = NewsFeedLike::where('news_feed_id', '=', $news_feed->id)->where('user_id', '=', $this_user->id)->get()->first();
                    if($old_like instanceof NewsFeedLike){
                        $state = $old_like->delete();
                        if($state){
                            return response()->json(['status'=> true,'message'=> 'Unlike successfully', 'result'=>$old_like], 200);
                        }
                        else {
                            return response()->json(['status'=> false,'message'=> 'Whoops! failed to unlike feed'], 500);
                        }
                    } {
                        return response()->json(['status'=> false,'message'=> 'Whoops! You cannot unlike what you have not liked previously.'], 500);
                    }
                } else {
                    return response()->json(['status'=> false,'message'=> 'Whoops! unable to find the news_feed'], 500);
                }
            } else {
                return response()->json(['status'=> false,'message'=> 'Whoops! failed to authorize this request'], 500);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=> false,'message'=> 'Whoops! failed to create feed like', 'error'=>$exception->getMessage()], 500);
        }
    }

}

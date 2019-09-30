<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NewsFeed extends Model
{
    public function post_type() {
        return $this->belongsTo(PostType::class, 'post_type_id');
    }
    public function comments() {
        return $this->hasMany(NewsFeedComment::class);
    }
    public function likes() {
        return $this->hasMany(NewsFeedLike::class, 'news_feed_id');
    }
    public function created_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updated_by() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected $appends = ['liked_by_auth_user'];
    public function getLikedByAuthUserAttribute()
    {
        $user = Auth::user();
        if($user instanceof User){
            $userId = $user->id;
            $myLike = $this->likes->first(function ($key) use ($userId){
                return $key->user_id === $userId;
            });
            if($myLike){
                return true;
            }
        }
        return false;
    }
}

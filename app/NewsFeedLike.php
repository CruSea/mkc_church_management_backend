<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsFeedLike extends Model
{
    //

    public function feed()
    {
        return $this->belongsTo('App\NewsFeed','news_feed_id','id');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }
}

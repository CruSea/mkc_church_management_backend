<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrayerRequest extends Model
{
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function prayer_reply() {
        return $this->hasOne(PrayerReply::class, 'request_id', 'id');
    }
}

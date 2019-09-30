<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsFeedComment extends Model
{
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function created_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updated_by() {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

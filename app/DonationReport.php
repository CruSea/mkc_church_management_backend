<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DonationReport extends Model
{
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

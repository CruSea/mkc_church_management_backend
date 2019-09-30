<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    public function spouse() {
        return $this->hasOne(MemberSpouseInfo::class, 'member_id', 'id');
    }
    public function children() {
        return $this->hasMany(MemberChildren::class, 'member_id', 'id');
    }

    public function member_previous_church() {
        return $this->hasOne(MemberPreviousChurches::class, 'member_id', 'id');
    }

}

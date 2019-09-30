<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    //

    public function parent_team() {
        return $this->hasOne(Team::class, 'id', 'parent_team_id');
    }

    public function category() {
        return $this->hasOne(TeamCategory::class, 'id', 'category_id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function member() {
        return $this->belongsTo(Member::class, 'team_id', 'id', 'member_id');
    }

    public function team_member() {
        return $this->hasMany(TeamMember::class, 'team_id', 'id');
    }
}

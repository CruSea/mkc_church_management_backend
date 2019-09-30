<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{

    public function teams() {
        return $this->hasMany(Team::class, 'id', 'team_id');
    }

    public function members() {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }
}

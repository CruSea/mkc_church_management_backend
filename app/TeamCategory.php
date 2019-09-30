<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamCategory extends Model
{
    //

    public function teams() {
        return $this->hasMany(Team::class, 'category_id', 'id');
    }
}

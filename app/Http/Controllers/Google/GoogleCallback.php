<?php

namespace App\Http\Controllers\Google;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GoogleCallback extends Controller
{
    public function googleCallback() {
        return "THIS IS GOOGLE";
    }
}

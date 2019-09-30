<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PostType;

class PostTypeController extends Controller
{
    public function getPostType() {
        $postType = PostType::all();

        $response = [
            'postTypes' => $postType
        ];
        return response()->json($response, 200);
    }
}

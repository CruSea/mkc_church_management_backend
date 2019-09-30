<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Monolog\Logger;

class UssdController extends Controller
{
    //


    public function handleTest(){
        $logger = new Logger("TestLog");
        $logger->log(Logger::INFO, "Incoming Message", request()->all());
        return response()->json(['status'=> true, 'requests'=> request()->all()],200);

    }
}

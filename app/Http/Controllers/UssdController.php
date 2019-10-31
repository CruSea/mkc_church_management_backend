<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Monolog\Logger;

class UssdController extends Controller
{
    //


    public function getData(){
        $logger = new Logger("TestLog");
        $logger->log(Logger::INFO, "Incoming Data", request()->all());

    }

    public function handleTest(){
        $logger = new Logger("TestLog");
        $logger->log(Logger::INFO, "Incoming Message", request()->all());
        return response()->json(['status'=> true, 'requests'=> request()->all()],200);

    }
}

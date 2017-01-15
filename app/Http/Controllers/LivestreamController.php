<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;


class LivestreamController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }




   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchLivestream(Request $request)
    {
    	$streams = Redis::lrange('livestream.list', 0, -1);

        $livestreams = [];

        foreach($streams as $stream) {
            $stream = json_decode($stream, true);
            $timezone = 'Asia/Kolkata';        
            
            $ride_time = $stream['ride_time']['date'];
            $ride_time = Carbon::createFromFormat('Y-m-d H:i:s.u', $ride_time)->timezone($timezone)->format('H:i A');
            $stream['ride_time_tz'] = $ride_time;
        
            $created_at = $stream['created_at']['date'];
            $created_at = Carbon::createFromFormat('Y-m-d H:i:s.u', $created_at)->diffForHumans();   
            $stream['created_at_humans'] = $created_at;
                               
            $livestreams[] = $stream;
        }

        return response()->json(['status' => 'success', 'livestreams' => $livestreams], 200); 
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OpruutRequest;
use App\User;
use App\Events\OpruutRequestLivestreamArrived;
use Carbon\Carbon;

use App\Lib\RideTimeLibrary;
use App\Lib\AvatarsLibrary;
use App\Lib\CityImagesLibrary;
use App\Jobs\OpruutProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

Log::useFiles('php://stdout', config('app.log_level'));




class OpruutController extends Controller
{
    
	// $incrementing = true;
	// $primaryKey = 'id';
	// $timestamps = true;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        
        
    }







    public function findOpruut(Request $request) {

        $user = null;
        $opruut = null;


        // first validate the request
        $this->validate($request, [
            
            'source_id' => 'bail|required|integer',
            'destination_id' => 'bail|required|integer',
            'source' => 'bail|required|string',
            'destination' => 'bail|required|string',
            'preference' => 'bail|required|integer',
            'ride_time' => 'bail|required|integer',

        ]);

        // parse source and destination and city
        $city= trim(explode(',', $request->input('source'))[1]);
        $city = trim(explode('|', $city)[0]);

        $city_snake_case = strtolower(preg_replace('/\s+/', '_', $city));

        $request['source'] = trim(explode(',', $request->input('source'))[0]);
        $request['destination'] = trim(explode(',', $request->input('destination'))[0]);
        $request['city'] = $city;
        $request['cityImg'] = CityImagesLibrary::$paths[$city_snake_case].CityImagesLibrary::$images[rand(1,47)];



        $ride_time = $request->input('ride_time');

        // detect the user's timezone 
        // or if not present use the applications timezone (config('app.timezone'))
        // or a hard coded one for now 'Asia/Kolkata';
        $timezone = 'Asia/Kolkata';

        if ($ride_time === 0) {
            $request['ride_time'] = Carbon::now($timezone);
        }
        else {
            $request['ride_time'] = Carbon::createFromTime(RideTimeLibrary::$rideTimes[$ride_time]['hours'], RideTimeLibrary::$rideTimes[$ride_time]['mins'], 0, $timezone);
        }


        // now convert the time to 'UTC' (or Application's timezone) so as to be saved in database in an uniform format
        $request['ride_time'] = $request['ride_time']->timezone(config('app.timezone'));

        // dd('request final ', $request->all());

        // Check if User is Authenticated or not
        if (Auth::check()) {
            // user is logged in.
            $user = Auth::user();

            $opruut = $user->add_opruut_request($request->all());
        
        }
        else {
            
            $opruut = OpruutRequest::create($request->all());

        }

        $opruut = $opruut->load('user');

        // Increment the Global REquests counter
        $globalRequests = intval(Cache::increment('globalRequests'));


        $imgUrl = null;
        $link = AvatarsLibrary::$links[rand(1,2)];

        if ($link['type'] === 'adorable') {

            $imgUrl = $link['prefix'].'48/'.rand(1,5000);
        
        }
        else if ($link['type'] === 'local') {
            $choice = ['male', 'female'];

            $gender = $choice[rand(0,1)];

            if ($gender === 'male') {
                $id = rand(1,12);
            }
            else {
                $id = rand(1,9);
            }

            $imgUrl = $link['prefix'].AvatarsLibrary::$avatar[$gender][$id];
        
        }
        else if ($link['type'] === 'randomuser') {

            $choice = ['men', 'women'];

            $gender = $choice[rand(0,1)];

            if ($gender === 'men') {
                $id = rand(1,99);
            }
            else {
                $id = rand(1,99);
            }

            $imgUrl = $link['prefix'].'thumb/'.$gender.'/'.$id.'.jpg';
        }
        else if ($link['type'] === 'lorempixel') {

            $colors = ['', 'g/'];

            $color = $colors[rand(0,1)];

            $imgUrl = $link['prefix'].$color.'48/48/people';
        }
        else if ($link['type'] === 'loremflickr') {

            $colors = ['', 'g/', 'blue/'];

            $color = $colors[rand(0,2)];

            $categories = ['girl', 'boy'];

            $category = $categories[rand(0,1)];

            $imgUrl = $link['prefix'].$color.'48/48/'.$category;
        }
        else if ($link['type'] === 'unsplash') {

            $colors = ['', 'g/'];

            $color = $colors[rand(0,1)];

            $imgUrl = $link['prefix'].$color.'48/?random';
        }
        else if ($link['type'] === 'uinames') {

            $choice = ['male', 'female'];

            $gender = $choice[rand(0,1)];

            if ($gender === 'male') {
                $id = rand(1,43);
            }
            else {
                $id = rand(1,32);
            }

            $imgUrl = $link['prefix'].$gender.'/'.$id.'.jpg';
        }
    	


        $avatar_random = $imgUrl;   

        // test response
        // return response()->json(['status' => 'processing', 'request' => $opruut->toJson()], 200);   

        // broadcast the live stream event of the request to all users
        // Log::debug(' [OpruutController] : broadcasting OpruutRequestLivestreamArrived ');
    	broadcast(new OpruutRequestLivestreamArrived($opruut, $globalRequests, $avatar_random));
        // Log::debug(' [OpruutController] : OpruutRequestLivestreamArrived broadcasted'); 


        $livestream =  [

            "id" => $opruut->id,
            "source" => $opruut->source,
            "source_id" => $opruut->source_id,
            "destination" => $opruut->destination,
            "destination_id" => $opruut->destination_id,
            "preference" => $opruut->preference,
            "created_at" => $opruut->created_at, // get the current created_at day in a humanly format
            "ride_time" => $opruut->ride_time,
            "userId" => $opruut->user ? $opruut->user->id : null,
            "userName" => $opruut->user ? $opruut->user->name : 'Anonymous',
            "userUsername" => $opruut->user ? $opruut->user->username : 'iHaventRegistered',
            "userAvatar" => $opruut->user ? $opruut->user->avatar : $avatar_random,
            "type" => "opruut_request"

        ];


        // add new request to livestream cache
        // Log::debug(' [OpruutController] : adding livestream to Redis');
        $livestreamCache = Redis::lpush('livestream.list', json_encode($livestream));
        $livestreamLtrim = Redis::ltrim('livestream.list', 0, 100);

        $opruut->userAvatar = $opruut->user ? $opruut->user->avatar : $avatar_random;

        // send the request to a Job Queue by dispatch the Job
        // Log::debug('[OpruutController] : creating opruutProcessingJob ');
    	$opruutProcessingJob = (new OpruutProcessing($opruut))->onQueue('opruut_processing');

        // Log::debug('[OpruutController] :  opruutProcessingJob Created');
        // Log::debug('[OpruutController] :  dispatching opruutProcessingJob');
        dispatch($opruutProcessingJob);

        // Log::info('[OpruutController] : opruutProcessingJob  Dispatched with OpruutRequest id : '.$opruut->id);

        return response()->json(['status' => 'processing', 'opruut_id' => $opruut->id], 200);	

    }





    public function fetchOpruuts(Request $request) {

        // first validate the request

        $cursor = $request->input('cursor');
        $limit = $request->input('limit');
        $fetch_type = $request->input('fetch_type');

        $cursor_up = null;
        $cursor_down = null;

        if ($cursor === null) {
            $cursor_up = -1;
            $cursor_down = -1;
        }
        else {
            $cursors = explode('_', $cursor);
            $cursor_up = intval($cursors[0]);
            $cursor_down = intval($cursors[1]);
        }
        
        if ($limit === null) {
            $limit = 15;
        }
        else {
            $limit = intval($limit);
        }


        if ($limit > 50) {
            $limit = 50;
        }

        if ($fetch_type === null || ($fetch_type !== 'up' && $fetch_type !== 'down')) {
            $fetch_type === 'down';
        }

        $opruuts = null;

        // Log::debug('cursor_up : '.$cursor_up.' cursor_down : '.$cursor_down.' limit : '.$limit.' fetch_type : '.$fetch_type);

        // check if opruut request has been favorited by user if authenticated or not
        $user = null;
        if (Auth::check()) {
            $user = Auth::user(); 
        } 
        

        // Return general request regardless of authentication 
        if ($cursor === null) {
            // Log::debug('cursor === null : ');
            // first time request 
            // means no condition on id or cursor
            // use skip and limit
            if ($user) {
                // Log::debug('$user !== null : ');
                $opruuts = OpruutRequest::with(['user' => function($q1) { 
                        
                    $q1->select('id', 'name', 'username', 'avatar'); 
                        
                }, 'opruut_results'=> function($q2) { 
                        
                    $q2->orderBy('rank')
                    ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                    
                }])
                ->withCount(['favorites' => function($q3) { 

                    $q3->where('liked', true); 

                }])
                ->with(['favorites' => function($q4)  use($user) { 

                    $q4->where('user_id', $user->id)
                    ->where('liked', true)
                    ->select('opruut_request_id', 'user_id', 'liked'); 

                }])
                ->latest()
                ->offset(0)->limit($limit)
                // ->select('id', 'user_id', 'source', 'source_id', 'destination', 'destination_id', 'preference', 'city', 'cityImg', 'ride_time', 'created_at')
                ->get();
                
                // Log::debug('111111**********opruuts**********111111 : '.json_encode($opruuts));

            }
            else {
                // Log::debug('$user === null : ');
                $opruuts = OpruutRequest::with(['user' => function($q1) { 
                    
                    $q1->select('id', 'name', 'username', 'avatar'); 
                        
                }, 'opruut_results'=> function($q2) { 
                        
                    $q2->orderBy('rank')
                    ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                    
                }])
                ->withCount(['favorites' => function($q3) { 

                    $q3->where('liked', true); 

                }])
                ->latest()
                ->offset(0)->limit($limit)
                // ->select('id', 'user_id', 'source', 'source_id', 'destination', 'destination_id', 'preference', 'city', 'cityImg', 'ride_time', 'created_at')
                ->get();

                // Log::debug('2222222**********opruuts**********222222 : '.json_encode($opruuts));
            }

        }
        else {
            // Log::debug('cursor !== null : ');
            // not first time request 
            // means cursor to be used and also to be used
            // according to fetch_type
            // hence use where clause with limit, don't use skip
            if ($fetch_type === 'down') {
                // Log::debug('fetch_type === down ');
                // fetch favorites with id < the cursor_down value
                if ($user) {
                    // Log::debug('$user !== null : ');
                    $opruuts = OpruutRequest::with(['user' => function($q1) { 
                            
                        $q1->select('id', 'name', 'username', 'avatar'); 
                            
                    }, 'opruut_results'=> function($q2) { 
                            
                        $q2->orderBy('rank')
                        ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                        
                    }])
                    ->withCount(['favorites' => function($q3) { 
                    
                        $q3->where('liked', true); 

                    }])
                    ->with(['favorites' => function($q4) use($user) { 

                        $q4->where('user_id', $user->id)
                        ->where('liked', true)
                        ->select('opruut_request_id', 'user_id', 'liked');

                    }])
                    ->where('id', '<', $cursor_down)
                    ->latest()
                    ->limit($limit)
                    // ->select('id', 'user_id', 'source', 'source_id', 'destination', 'destination_id', 'preference', 'city', 'cityImg', 'ride_time', 'created_at')
                    ->get();

                    // Log::debug('333333**********opruuts**********333333 : '.json_encode($opruuts));
                
                }
                else {
                    // Log::debug('$user === null : ');
                    $opruuts = OpruutRequest::with(['user' => function($q1) { 
                            
                        $q1->select('id', 'name', 'username', 'avatar'); 
                            
                    }, 'opruut_results'=> function($q2) { 
                            
                        $q2->orderBy('rank')
                        ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                        
                    }])
                    ->withCount(['favorites' => function($q3) { 

                        $q3->where('liked', true); 

                    }])
                    ->where('id', '<', $cursor_down)
                    ->latest()
                    ->limit($limit)
                    // ->select('id', 'user_id', 'source', 'source_id', 'destination', 'destination_id', 'preference', 'city', 'cityImg', 'ride_time', 'created_at')
                    ->get();

                    // Log::debug('444444**********opruuts**********44444444 : '.json_encode($opruuts));

                }

            }
            else if ($fetch_type === 'up') {
                // Log::debug('fetch_type === up ');
                // fetch favorites with id > the cursor_up value
                if ($user) {
                    // Log::debug('$user !== null : ');
                    $opruuts = OpruutRequest::with(['user' => function($q1) { 
                            
                        $q1->select('id', 'name', 'username', 'avatar'); 
                            
                    }, 'opruut_results'=> function($q2) { 
                            
                        $q2->orderBy('rank')
                        ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                        
                    }])
                    ->withCount(['favorites' => function($q3) {

                        $q3->where('liked', true); 

                    }])
                    ->with(['favorites' => function($q4) use($user) { 

                        $q4->where('user_id', $user->id)
                        ->where('liked', true)
                        ->select('opruut_request_id', 'user_id', 'liked'); 

                    }])
                    ->where('id', '>', $cursor_up)
                    ->latest()
                    ->limit($limit)
                    // ->select('id', 'user_id', 'source', 'source_id', 'destination', 'destination_id', 'preference', 'city', 'cityImg', 'ride_time', 'created_at')
                    ->get();

                    // Log::debug('5555555**********opruuts**********555555 : '.json_encode($opruuts));
                
                }
                else {
                    // Log::debug('$user === null : ');
                    $opruuts = OpruutRequest::with(['user' => function($q1) { 
                            
                        $q1->select('id', 'name', 'username', 'avatar'); 
                            
                    }, 'opruut_results'=> function($q2) { 
                            
                        $q2->orderBy('rank')
                        ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                        
                    }])
                    ->withCount(['favorites' => function($q3) { 

                        $q3->where('liked', true); 

                    }])
                    ->where('id', '>', $cursor_up)
                    ->latest()
                    ->limit($limit)
                    // ->select('id', 'user_id', 'source', 'source_id', 'destination', 'destination_id', 'preference', 'city', 'cityImg', 'ride_time', 'created_at')
                    ->get();

                    // Log::debug('66666666**********opruuts**********66666 : '.json_encode($opruuts));
                }

            }

        }


          
        // Log::debug('FINAL **********opruuts********** FINAL : '.json_encode($opruuts));
        // Log::debug('opruuts : '.json_encode($opruuts));
        
        $opruuts_formatted = [];
    
        $firstResult = true;

        // add isLIked for each opruutREquest
        foreach($opruuts as $op_request) {

            if ($firstResult && $cursor_up === -1) {
                $cursor_up = $op_request->id;
                $firstResult = false;
            }
            else if ($firstResult && $fetch_type === 'up') {
                $cursor_up = $op_request->id;
                $firstResult = false;
            }

            $imgUrl = null;
            $link = AvatarsLibrary::$links[rand(1,2)];
            $avatar_random = null;

            if ($op_request->user) {
                $avatar_random = $op_request->user->avatar; 

            }
            else {               

                if ($link['type'] === 'adorable') {

                    $imgUrl = $link['prefix'].'48/'.rand(1,5000);
                
                }
                else if ($link['type'] === 'local') {
                    $choice = ['male', 'female'];

                    $gender = $choice[rand(0,1)];

                    if ($gender === 'male') {
                        $id = rand(1,12);
                    }
                    else {
                        $id = rand(1,9);
                    }

                    $imgUrl = $link['prefix'].AvatarsLibrary::$avatar[$gender][$id];
                
                }
                else if ($link['type'] === 'randomuser') {

                    $choice = ['men', 'women'];

                    $gender = $choice[rand(0,1)];

                    if ($gender === 'men') {
                        $id = rand(1,99);
                    }
                    else {
                        $id = rand(1,99);
                    }

                    $imgUrl = $link['prefix'].'thumb/'.$gender.'/'.$id.'.jpg';
                }
                else if ($link['type'] === 'lorempixel') {

                    $colors = ['', 'g/'];

                    $color = $colors[rand(0,1)];

                    $imgUrl = $link['prefix'].$color.'48/48/people';
                }
                else if ($link['type'] === 'loremflickr') {

                    $colors = ['', 'g/', 'blue/'];

                    $color = $colors[rand(0,2)];

                    $categories = ['girl', 'boy'];

                    $category = $categories[rand(0,1)];

                    $imgUrl = $link['prefix'].$color.'48/48/'.$category;
                }
                else if ($link['type'] === 'unsplash') {

                    $colors = ['', 'g/'];

                    $color = $colors[rand(0,1)];

                    $imgUrl = $link['prefix'].$color.'48/?random';
                }
                else if ($link['type'] === 'uinames') {

                    $choice = ['male', 'female'];

                    $gender = $choice[rand(0,1)];

                    if ($gender === 'male') {
                        $id = rand(1,43);
                    }
                    else {
                        $id = rand(1,32);
                    }

                    $imgUrl = $link['prefix'].$gender.'/'.$id.'.jpg';
                }

                $avatar_random = $imgUrl;

            }

            // Log::debug('[OpruutController:fetchOpruuts] - $user : '.json_encode($user));
            $op_request->isFavorited = false;

            // check if user is authenticated or not
            if ($user) {
                // Log::debug('[OpruutController:fetchOpruuts] - $user is true: ');
                //check if opruut is favorited by user or noot
                $op_request->isFavorited = (count($op_request->favorites) === 0) ? false : true;
                
            }

            // Log::debug('[OpruutController:fetchOpruuts] - $isFavorited : '.$op_request->isFavorited);

            $ride_time = $op_request->ride_time;
            // convert the ride time to appropriate ride time in user's timezone
            // get timezone for user if present then okay otherwise 
            // use the application's timezone (config('app.timezone')) or 
            // use a local hardcode timezone 'Asia/Kolkata'
            $timezone = 'Asia/Kolkata';
            // change ride time to new timezone and only return the 
            // time in 12 hour formal
            $ride_time = $ride_time->copy()->timezone($timezone)->format('H:i A');  

            // add a fuzzy likes count to the original count to show non zero count values
            $op_request->favorites_count = $op_request->favorites_count ? $op_request->favorites_count + rand(5, 50) : rand(5, 50);
            $op_request->created_at_humans = $op_request->created_at->diffForHumans(); // get the current created_at day in a humanly format
            $op_request->ride_time_tz = $ride_time;
            $op_request->userName = $op_request->user ? $op_request->user->name : 'Anonymous';
            $op_request->userUsername = $op_request->user ? $op_request->user->username : 'iHaventRegistered';
            $op_request->userAvatar = $avatar_random; 
            $op_request->from = $op_request->source;
            $op_request->to = $op_request->destination;

            if ($fetch_type === 'down') {
                // only change the cursor_down if fetch type is down
                // else don't change cursor_down, just change cursor_up
                // which is done at the beginning of the loop 
                $cursor_down = $op_request->id;
            }
        
            // $opruuts_formatted[$op_request->id] = $op_request;

        }


        $cursor_updated = null;

        if ($cursor_up !== -1 && $cursor_down !== -1) {
            $cursor_updated = $cursor_up.'_'.$cursor_down;
        }

        // Log::debug('Final cursor_up : '.$cursor_up.' Final cursor_down : '.$cursor_down.' Fial cursor updated : '.$cursor_updated.' Final limit : '.$limit); 

        return response()->json(['status' => 'success', 'opruuts' => $opruuts,  'cursor' => $cursor_updated, 'limit' => $limit], 200); 

    }



    


}

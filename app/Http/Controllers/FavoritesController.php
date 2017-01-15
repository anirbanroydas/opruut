<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\OpruutRequest;
use App\Favorite;
use App\Lib\AvatarsLibrary;
use App\Jobs\OpruutProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Events\FavoriteActionsLivestreamArrived;

Log::useFiles('php://stdout', config('app.log_level'));





class FavoritesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }




    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function showPage()
    {
        $user = null;
        $isAuthenticated = false;
        $globalRequests = Cache::get('globalRequests');

        $user = Auth::user();
        $isAuthenticated = true; 

        return view('index', ['userinfo' => $user, 'authenticated' => $isAuthenticated, 'globalRequests' => intval($globalRequests), 'error' => null]);
    }






    public function toggleFavorite(Request $request) 
    {
        $opruutId = $request->input('opruutId');
        $user = Auth::user();

        $this->validate($request, [
            'opruutId' => 'bail|required|integer',
        ]);

        $opruutId = intval($opruutId);

        $opruut = OpruutRequest::withCount('favorites')->find($opruutId);
        $favorites = Favorite::where('user_id', $user->id)->where('opruut_request_id', $opruutId)->get();
        $isFavoriting = 'true';
        $favorite = null;

        if (count($favorites) > 0) {
            // favorite row already there, thus toggle the favorite value
            $favorites[0]->liked = !$favorites[0]->liked;
            $isFavoriting = $favorites[0]->liked;
            $favorites[0]->save();
        }
        else {
            // favorite table not present, hence create a new one
            $favorite = $opruut->add_favorite(['user_id'=> $user->id, 'liked' => true]);
        }

        // to check the throttling of the favoriting live stream and do all of this only if the action is for favoriting
        // skip the entire process if it was for unfavoriting
        if ($isFavoriting) {
            
            $get_last_favorited_stream_timestamp = intval(Cache::get('favorited:stream:last_timestamp'));
            $current_timestamp = intval(Carbon::now()->timestamp);

            if ($current_timestamp - $get_last_favorited_stream_timestamp >= 1) {
                // first save the current timestamp to Cache
                Cache::put('favorited:stream:last_timestamp', $current_timestamp);

                $favorite = Favorite::with('user', 'opruut_request')
                    ->where('user_id', $user->id)
                    ->where('opruut_request_id', $opruutId)
                    ->get();

                // send the livestream by sending an event
                broadcast(new FavoriteActionsLivestreamArrived($favorite[0]));


                $avatar_random = null;

                if ($favorite[0]->user && $favorite[0]->user->avatar) {
                    $avatar_random = $favorite[0]->user->avatar;
                } 
                else {

                    $imgUrl = null;
                    $link = AvatarsLibrary::$links[rand(1,2)];
                    

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
                } 



                $livestream =  [

                    "id" => $favorite[0]->id,
                    "source" => $favorite[0]->opruut_request->source,
                    "source_id" => $favorite[0]->opruut_request->source_id,
                    "destination" => $favorite[0]->opruut_request->destination,
                    "destination_id" => $favorite[0]->opruut_request->destination_id,
                    "preference" => $favorite[0]->opruut_request->preference,
                    "created_at" => $favorite[0]->updated_at, // get the current created_at day in a humanly format
                    "ride_time" => $favorite[0]->opruut_request->ride_time,
                    "userId" => $favorite[0]->user ? $favorite[0]->user->id : null,
                    "userName" => $favorite[0]->user ? $favorite[0]->user->name : 'Anonymous',
                    "userUsername" => $favorite[0]->user ? $favorite[0]->user->username : 'iHaventRegistered',
                    "userAvatar" => $avatar_random,
                    "type" => "favorites"

                ];

                $livestreamCache = Redis::lpush('livestream.list', json_encode($livestream));
                $livestreamLtrim = Redis::ltrim('livestream.list', 0, 100);
                
            }
            else {
                // dont sent any live stream just send the response
            }
        }

        return response()->json(['status' => 'success', 'favorites_count' => $opruut->favorites_count + 1], 200); 

    }






    public function fetchFavorites(Request $request) 
    {

        $cursor = $request->input('cursor');
        $limit = $request->input('limit');
        $fetch_type = $request->input('fetch_type');

        $cursor_up = null;
        $cursor_down = null;
        $cursor_up_timestamp = null;
        $cursor_down_timestamp = null;

        // Log::debug('cursor : '.$cursor);

        if ($cursor === null) {
            $cursor_up = -1;
            $cursor_down = -1;
        }
        else {
            $cursors = explode('_', $cursor);
            
            if (count($cursors) !== 2) {
                return response()->json(['status' => 'failure', 'message' => 'cursor of wrong format'], 422);  
            }

            // Log::debug('cursors : '.json_encode($cursors));

            $cursor_up_timestamp = intval($cursors[0]); // cursor_up in timestamp
            $cursor_down_timestamp = intval($cursors[1]);  // cursor_down in timestamp
            // Log::debug('Before : cursor_up_timestamp : '.$cursor_up_timestamp.' cursor_down_timestamp : '.$cursor_down_timestamp);

            // chagne to get the Carbon datetimestring
            $cursor_up = Carbon::createFromTimestamp($cursor_up_timestamp)->toDateTimeString();
            // chagne to get the Carbon datetimestring
            $cursor_down = Carbon::createFromTimestamp($cursor_down_timestamp)->toDateTimeString();
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


        // Log::debug('After : cursor_up_timestamp : '.$cursor_up_timestamp.' cursor_up : '.$cursor_up.' cursor_down_timestamp : '.$cursor_down_timestamp.' cursor_down : '.$cursor_down.' limit : '.$limit.' fetch_type : '.$fetch_type);


        $opruuts = null;
        $favorites = null;
        $user = Auth::user();

        // Log::debug('user : '.json_encode($user));


        if ($cursor === null) {
            // Log::debug('cursor === null : ');
            // first time request 
            // means no condition on id or cursor or fetch_type
            // use skip and limit
            $favorites = User::with(['favorites' => function($q1) use($limit) {

                $q1->with(['opruut_request' => function($q2) { 
                    
                    $q2->with(['user'  => function($q3) { 
                    
                        $q3->select('id', 'name', 'username', 'avatar'); 
                            
                    }, 'opruut_results' => function($q4) { 
                
                        $q4->orderBy('rank')
                        ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                
                    }])
                    ->withCount(['favorites' => function($q5) { 

                        $q5->where('liked', true); 

                    }]);
                }])
                ->where('liked', true)
                // ->latest()
                ->orderBy('updated_at', 'desc')
                ->offset(0)->limit($limit)
                ->select('opruut_request_id', 'user_id', 'liked', 'updated_at');
                
            }])
            ->find($user->id);

            // Log::debug('**********favorites********** : '.json_encode($favorites));

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
                $favorites = User::with(['favorites' => function($q1) use($cursor_down, $limit) {

                    $q1->with(['opruut_request' => function($q2) { 
                        
                        $q2->with(['user'  => function($q3) { 
                        
                            $q3->select('id', 'name', 'username', 'avatar'); 
                                
                        }, 'opruut_results' => function($q4) { 
                    
                            $q4->orderBy('rank')
                            ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                    
                        }])
                        ->withCount(['favorites' => function($q5) { 

                            $q5->where('liked', true); 

                        }]);
                    }])
                    ->where('updated_at', '<', $cursor_down)
                    ->where('liked', true)
                    // ->latest()
                    ->orderBy('updated_at', 'desc')
                    ->limit($limit)
                    ->select('opruut_request_id', 'user_id', 'liked', 'updated_at');
                    
                }])
                ->find($user->id);

                // Log::debug('2222**********favorites**********2222 : '.json_encode($favorites));

            }
            else if ($fetch_type === 'up') {
                // Log::debug('fetch_type === up ');
                // fetch favorites with id > the cursor_up value
                $favorites = User::with(['favorites' => function($q1) use($cursor_up, $limit) {

                    $q1->with(['opruut_request' => function($q2) { 
                        
                        $q2->with(['user'  => function($q3) { 
                        
                            $q3->select('id', 'name', 'username', 'avatar'); 
                                
                        }, 'opruut_results' => function($q4) { 
                    
                            $q4->orderBy('rank')
                            ->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 
                    
                        }])
                        ->withCount(['favorites' => function($q5) { 

                            $q5->where('liked', true); 

                        }]);
                    }])
                    ->where('updated_at', '>', $cursor_up)
                    ->where('liked', true)
                    // ->latest()
                    ->orderBy('updated_at', 'desc')
                    ->limit($limit)
                    ->select('opruut_request_id', 'user_id', 'liked', 'updated_at');
                    
                }])
                ->find($user->id);

                // Log::debug('33333**********favorites**********3333 : '.json_encode($favorites));
            }

        }



        // Log::debug('favorites : '.json_encode($favorites));
        $favorites_formatted = [];

        $firstResult = true;

        // Log::debug('4444444**********favorites**********4444 : '.json_encode($favorites));
        // Log::debug('COUNT : favorites : '.count($favorites->favorites));
        

        // add isLIked for each opruutREquest
        foreach($favorites->favorites as $favorite) {

            if ($firstResult && $cursor_up === -1) {
                // Log::debug('$firstResult && $cursor_up === -1');
                
                $cursor_up = intval($favorite->updated_at->timestamp);
                $firstResult = false;
                // Log::debug('thus $cursor_up : '.$cursor_up);
            }
            else if ($firstResult && $fetch_type === 'up') {
                // Log::debug('$firstResult && $fetch_type === up');

                $cursor_up = intval($favorite->updated_at->timestamp);
                $firstResult = false;
                // Log::debug('thus $cursor_up : '.$cursor_up);
            }

            $op_request = $favorite->opruut_request;

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



            $ride_time = $op_request->ride_time;
            // convert the ride time to appropriate ride time in user's timezone
            // get timezone for user if present then okay otherwise 
            // use the application's timezone (config('app.timezone')) or 
            // use a local hardcode timezone 'Asia/Kolkata'
            $timezone = 'Asia/Kolkata';
            // change ride time to new timezone and only return the 
            // time in 12 hour formal
            $ride_time = $ride_time->copy()->timezone($timezone)->format('H:i A');  

            $op_request->created_at_humans = $op_request->created_at->diffForHumans(); // get the current created_at day in a humanly format
            $op_request->ride_time_tz = $ride_time;

            // add a fuzzy likes count to the original count to show non zero count values
            $op_request->favorites_count = $op_request->favorites_count ? $op_request->favorites_count + rand(5, 50) : rand(5, 50);

            $op_request->isFavorited = true;
            $op_request->userName = $op_request->user ? $op_request->user->name : 'Anonymous';
            $op_request->userUsername = $op_request->user ? $op_request->user->username : 'iHaventRegistered';
            $op_request->userAvatar = $avatar_random;
            $op_request->from = $op_request->source;
            $op_request->to = $op_request->destination; 

            // Log::debug(' $fetch_type : '.$fetch_type);

            if ($fetch_type === 'down') {
                // Log::debug(' $fetch_type === down');
                // only change the cursor_down if fetch type is down
                // else don't change cursor_down, just change cursor_up
                // which is done at the beginning of the loop 
                $cursor_down = intval($favorite->updated_at->timestamp);
                // Log::debug('thus $cursor_down : '.$cursor_down);
            }

            $favorites_formatted[] = $op_request; 
        }



        // Log::debug('Before update : cursor_up : '.$cursor_up.' cursor_down : '.$cursor_down);

        if (gettype($cursor_up) === 'string') {
            
            // Log::debug('gettype($cursor_up) === string ');
            $cursor_up = intval($cursor_up_timestamp);
            // Log::debug('thus $cursor_up : '.$cursor_up);
        }
        if (gettype($cursor_down) === 'string') {
            
            // Log::debug('gettype($cursor_down) === string ');
            $cursor_down = intval($cursor_down_timestamp);
            // Log::debug('thus $cursor_down : '.$cursor_down);
        }

        // Log::debug('After update : cursor_up : '.$cursor_up.' cursor_down : '.$cursor_down);

        $cursor_updated = null;

        if ($cursor_up !== -1 && $cursor_down !== -1) {
            // Log::debug(' $cursor_up !== -1 && $cursor_down !== -1 ');
            $cursor_updated = $cursor_up.'_'.$cursor_down;
        }

        // Log::debug('final opruuts : '.json_encode($opruuts));
            

        // only return the opruuts withpout the user info
        // $favorites = $favorites_formatted;

        // Log::debug('Final cursor_up : '.$cursor_up.' Final cursor_down : '.$cursor_down.' Fial cursor updated : '.$cursor_updated.' Final limit : '.$limit);        

        return response()->json(['status' => 'success', 'favorites' => $favorites_formatted,  'cursor' => $cursor_updated, 'limit' => $limit], 200); 


    }


}

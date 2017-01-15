<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Lib\AvatarsLibrary;


class IndexController extends Controller
{
	
    protected $opruut;


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
    public function showPage()
    {
    	$imgUrl = null;
        $link = AvatarsLibrary::$links[rand(1,2)];

        if ($link['type'] === 'adorable') {

            $imgUrl = $link['prefix'].'260/'.rand(1,5000);
        }
        else {
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

        $user = ['avatar' => $imgUrl ];
        $isAuthenticated = false;
        $globalRequests = Cache::get('globalRequests');

    	// Check if User is Authenticated or not
    	if (Auth::check()) {
    		// user is logged in.
    		// render the logged in index page
    		
    		// add user info to index page
    			
    		$user = Auth::user();
            $isAuthenticated = true;

    	}
    	else {
    		// user not logged in
    		// render the non logged in index page

    	}  


        return view('index', ['userinfo' => $user, 'authenticated' => $isAuthenticated, 'globalRequests' => intval($globalRequests), 'error' => null]);
    }



}

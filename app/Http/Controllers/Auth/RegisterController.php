<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

use App\Lib\AvatarsLibrary;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


Log::useFiles('php://stdout', config('app.log_level'));

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }



    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm(Request $request)
    {

        Log::debug('showRegistration');
        Log::debug('request url : '.$request->url());

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

        return view('index', ['userinfo' => $user, 'authenticated' => $isAuthenticated, 'globalRequests' => intval($globalRequests), 'error' => null]);
    }



    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        Log::debug('register Cotroller');
        Log::debug('request url : '.$request->url());
        Log::debug('accept type is json :  '.$request->expectsJson());

        $this->validator($request->all())->validate();

        Log::debug('user validated: ');

        $gender = $request->input('gender');

        $imgUrl = null;

        if ($gender === 'other') {
           $link =  AvatarsLibrary::$links[1];
        }
        else {
            $link = AvatarsLibrary::$links[2];
        }

        if ($link['type'] === 'adorable') {

            $imgUrl = $link['prefix'].'260/'.rand(1,5000);
        }
        else {

            if ($gender === 'male') {
                $id = rand(1,12);
            }
            else {
                $id = rand(1,9);
            }


            $imgUrl = $link['prefix'].AvatarsLibrary::$avatar[$gender][$id];
        }


        $request['avatar'] = $imgUrl;

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);


        Log::debug('user :  '.$user);

        if ($request->expectsJson()) {
            return response()->json($user, 200);
        }
       
        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }


    

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        
        Log::debug('register validator');

        return Validator::make($data, [
            'name' => 'bail|required|max:100',
            'username' => 'bail|required|max:100|unique:users',
            'email' => 'bail|required|email|max:255|unique:users',
            'gender' => 'bail|required|in:male,female,other',
            'password' => 'bail|required|min:6|confirmed'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        Log::debug('register create');

        return User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'avatar' => $data['avatar'],
            'gender' => $data['gender']
        ]);
    }




    public function validateEmailAllowed(Request $request) {

        // Log::debug('email : '.$request->input('email'));
        // Log::debug('request->all : '.json_encode($request->all()));

        $validator =  Validator::make($request->all(), [
            'email' => 'bail|required|email|max:255|unique:users',
        ]);

        $validator->validate();

        if ($request->expectsJson()) {
            return response()->json(['isAllowed' => true], 200);
        }
    }


}

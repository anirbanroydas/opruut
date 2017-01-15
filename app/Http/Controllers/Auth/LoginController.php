<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use App\Lib\AvatarsLibrary;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;

Log::useFiles('php://stdout', config('app.log_level'));

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.5
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('cors', ['except' => 'showLoginForm']);
        $this->middleware('guest', ['except' => 'logout']);
    }




    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {
        
        Log::debug('showLoginForm');
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
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {

        Log::debug('login handler');
        Log::debug('request url : '.$request->url());

        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }


       
        if ($this->attemptLogin($request)) {

            Log::debug('attemp login value : true ');
            return $this->sendLoginResponse($request);
        }

        Log::debug('attemp login value : false ');


        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }



    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        Log::debug('sendLoginResponse');
        Log::debug('accept type is json :  '.$request->expectsJson());

        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($request->expectsJson()) {
            return response()->json($this->guard()->user(), 200);
        }

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }



    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        Log::debug('sendFailedLoginResponse');
        Log::debug('accept type is json :  '.$request->expectsJson());
        
        if ($request->expectsJson()) {
            return response()->json([$this->username() => Lang::get('auth.failed')], 401);
        }


        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => Lang::get('auth.failed'),
            ]);
    }





    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json(['success' => true], 200);
        }

        return redirect('/');
    }




}

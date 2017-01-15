<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;




class Favorite extends Model
{
    /**
     * The attributes that are mass assignable.
     *adx
     * @var array
     */
    protected $fillable = [
        'user_id', 'liked'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // 
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 
        'updated_at', 
    ];



// public function getCreatedAtAttribute($value) {

//     $user = Auth::user();
//     // If no user is logged in, we'll just default to the 
//     // application's timezone (config('app.timezone')) or our custom timezone, 
//     // for now hardcoded to Asia/Kolkata
//     $timezone = $user ? $user->timezone : 'Asia/Kolkata';

//     return Carbon::createFromTimestamp(strtotime($value))
//         ->timezone($timezone)
//         // Leave this part off if you want to keep the property as 
//         // a Carbon object rather than always just returning a string
//         ->toDayDateTimeString()
//     ;
// }


    /**
     * Get the user that issues the opruut request.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }


    /**
     * Get the user that issues the opruut request.
     */
    public function opruut_request()
    {
        return $this->belongsTo('App\OpruutRequest');
    }


}

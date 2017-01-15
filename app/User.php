<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\OpruutRequest;
use App\Favorite;


class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'avatar', 'gender'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    /**
     * Get the opruut requests for the user.
     */
    public function opruut_requests()
    {
        return $this->hasMany('App\OpruutRequest');
    }


    public function add_opruut_request($opruut) {

        return $this->opruut_requests()->create($opruut);
    }



    /**
     * Get the opruut requests for the user.
     */
    public function favorites()
    {
        return $this->hasMany('App\Favorite');
    }


    public function add_favorite($favorite) {

        return $this->favorites()->create($favorite);
    }


}

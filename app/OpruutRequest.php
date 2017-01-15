<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class OpruutRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source', 'source_id', 'destination', 'destination_id', 'preference', 'ride_time', 'city', 'cityImg'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *s
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
        'ride_time'
    ];


    // protected $casts = [];


    /**
     * Get the user that issues the opruut request.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }






    /**
     * Get the opruut_results for the opruut_request.
     */
    public function opruut_results()
    {
        return $this->hasMany('App\OpruutResult');
    }


    public function add_opruut_result($opruut_result) {

        return $this->opruut_results()->create($opruut_result);
    }


    /**
     * Get the opruut_results for the opruut_request.
     */
    public function favorites()
    {
        return $this->hasMany('App\Favorite');
    }


    public function add_favorite($favorite) {

        return $this->favorites()->create($favorite);
    }







}

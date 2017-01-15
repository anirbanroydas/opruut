<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpruutResult extends Model
{
    /**
     * The attributes that are mass assignable.
     *adx
     * @var array
     */
    protected $fillable = [
        'stations', 'routes', 'station_count', 'interchanges', 'interchanges_stations', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor', 'rank'
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


    protected $casts = [
        'stations' => 'array',
        'routes' => 'array',
        'travel_time' => 'array',
        'interchanges_stations' => 'array'
    ];


    /**
     * Get the opruut_request that has this opruut result.
     */
    public function opruut_request()
    {
        return $this->belongsTo('App\OpruutRequest');
    }




}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\OpruutRequest;
use App\Lib\AvatarsLibrary;


class OpruutCalculated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $opruut;
    // public $opruut_result;

    public $broadcastQueue = 'opruut_broadcast_opruut_calculated';



    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OpruutRequest $opruut)
    {
        $this->opruut = $opruut;
        // $this->opruut_results = $opruut_results;
    }



    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {

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

        $ride_time = $this->opruut->ride_time;
        // convert the ride time to appropriate ride time in user's timezone
        // get timezone for user if present then okay otherwise 
        // use the application's timezone (config('app.timezone')) or 
        // use a local hardcode timezone 'Asia/Kolkata'
        $timezone = 'Asia/Kolkata';
        // change ride time to new timezone and only return the 
        // time in 12 hour formal
        $ride_time = $ride_time->copy()->timezone($timezone)->format('H:i A');  


        return [

            "id" => $this->opruut->id,
            "source" => $this->opruut->source,
            "destination" => $this->opruut->destination,
            "source_id" => $this->opruut->source_id,
            "destination_id" => $this->opruut->destination_id,
            "created_at_humans" => $this->opruut->created_at->diffForHumans(), // get the current created_at day in a humanly format
            "ride_time_tz" => $ride_time,
            "preference" => $this->opruut->preference,
            "user" => $this->opruut->user,
            "opruut_results" => $this->opruut->opruut_results,
            "isFavorited" => false,
            "favorites_count" => 0,
            "userName" => $this->opruut->user ? $this->opruut->user->name : 'Anonymous',
            "userUsername" => $this->opruut->user ? $this->opruut->user->username : 'iHaventRegistered',
            "userAvatar" => $this->opruut->userAvatar ? $this->opruut->userAvatar : $this->opruut->user ? $this->opruut->user->avatar : $avatar_random,
            "from" => $this->opruut->source,
            "to" => $this->opruut->destination,
            "city" => $this->opruut->city,
            "cityImg" => $this->opruut->cityImg

        ];

    }



    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('opruut.request.'.$this->opruut->id);
    }
}

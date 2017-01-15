<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Favorite;
use App\Lib\AvatarsLibrary;


class FavoriteActionsLivestreamArrived  implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $favorite;

    public $broadcastQueue = 'opruut_broadcast_livestreaming_favorite_actions';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Favorite $favorite)
    {
        $this->favorite = $favorite;
    }


    


    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $ride_time = $this->favorite->opruut_request->ride_time;
        // convert the ride time to appropriate ride time in user's timezone
        // get timezone for user if present then okay otherwise 
        // use the application's timezone (config('app.timezone')) or 
        // use a local hardcode timezone 'Asia/Kolkata'
        $timezone = 'Asia/Kolkata';
        // change ride time to new timezone and only return the 
        // time in 12 hour formal
        $ride_time = $ride_time->copy()->timezone($timezone)->format('H:i A');

        $avatar_random = null;

        if ($this->favorite->user && $this->favorite->user->avatar) {
            $avatar_random = $this->favorite->user->avatar;
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




        return [

            "id" => $this->favorite->id,
            "source" => $this->favorite->opruut_request->source,
            "source_id" => $this->favorite->opruut_request->source_id,
            "destination" => $this->favorite->opruut_request->destination,
            "destination_id" => $this->favorite->opruut_request->destination_id,
            "preference" => $this->favorite->opruut_request->preference,
            "created_at_humans" => $this->favorite->updated_at->diffForHumans(), // get the current created_at day in a humanly format
            "ride_time_tz" => $ride_time,
            "userId" => $this->favorite->user ? $this->favorite->user->id : null,
            "userName" => $this->favorite->user ? $this->favorite->user->name : 'Anonymous',
            "userUsername" => $this->favorite->user ? $this->favorite->user->username : 'iHaventRegistered',
            "userAvatar" => $avatar_random,
            "type" => "favorites"

        ];

    }



    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('opruut.livestream.favorites');
    }
}

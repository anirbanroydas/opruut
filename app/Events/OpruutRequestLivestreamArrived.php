<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\OpruutRequest;


class OpruutRequestLivestreamArrived  implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $opruut;
    public $globalRequests;
    public $avatar_random;

    public $broadcastQueue = 'opruut_broadcast_livestreaming_opruut_requests';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OpruutRequest $opruut, $globalRequests, $avatar_random)
    {
        $this->opruut = $opruut;
        $this->globalRequests = $globalRequests;
        $this->avatar_random = $avatar_random;
    }


    


    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
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
            "source_id" => $this->opruut->source_id,
            "destination" => $this->opruut->destination,
            "destination_id" => $this->opruut->destination_id,
            "preference" => $this->opruut->preference,
            "created_at_humans" => $this->opruut->created_at->diffForHumans(), // get the current created_at day in a humanly format
            "ride_time_tz" => $ride_time,
            "userId" => $this->opruut->user ? $this->opruut->user->id : null,
            "userName" => $this->opruut->user ? $this->opruut->user->name : 'Anonymous',
            "userUsername" => $this->opruut->user ? $this->opruut->user->username : 'iHaventRegistered',
            "userAvatar" => $this->opruut->user ? $this->opruut->user->avatar : $this->avatar_random,
            "globalRequests" => $this->globalRequests,
            "type" => "opruut_request"

        ];

    }



    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('opruut.livestream');
    }
}

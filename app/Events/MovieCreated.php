<?php

namespace App\Events;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MovieCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Movie $movie, public array $request)
    {
    }

}

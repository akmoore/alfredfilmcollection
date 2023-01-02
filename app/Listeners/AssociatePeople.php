<?php

namespace App\Listeners;

use App\Actions\People\Cast;
use App\Events\MovieCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssociatePeople implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\MovieCreated  $event
     * @return void
     */
    public function handle(MovieCreated $event)
    {
        (new Cast($event->request['movie_full_data']['credits']['crew'], $event->movie, 'crew'))();
        (new Cast($event->request['movie_full_data']['credits']['cast'], $event->movie, 'cast'))();
    }
}

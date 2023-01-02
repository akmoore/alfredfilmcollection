<?php

namespace App\Listeners;

use App\Models\Genre;
use App\Events\MovieCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssociateGenre
{
    
    public function handle(MovieCreated $event)
    {
        $genres = $event->request['movie_full_data']['genres'];

        foreach ($genres as $genre ) {
            $g = Genre::firstOrCreate(
                ['movie_db_id' => $genre['id']],
                ['name' => $genre['name']]
            );

            $g->movies()->attach($event->movie->id);
        }

        $event->movie->movie_genres = $genres;
        $event->movie->save();
    }
}

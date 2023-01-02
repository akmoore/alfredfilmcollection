<?php

namespace App\Listeners;

use Illuminate\Support\Str;
use App\Events\MovieCreated;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreMovieBackdrop
{
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }

    public function handle(MovieCreated $event)
    {
        $url = 'https://image.tmdb.org/t/p/w1280';

        //Download the image
        $contents = file_get_contents($url . $event->request['movie_data']['backdrop_id']); //sizes: w200, w1280
        $filename = "image_" . Str::uuid() . ".jpg";
        Storage::put("/public/backdrops/{$filename}", $contents);

        //Associate the image with the movie record
        $event->movie->paths['backdrop'] = $filename;
        $event->movie->save();
    }
}

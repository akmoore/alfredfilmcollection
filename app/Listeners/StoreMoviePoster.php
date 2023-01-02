<?php

namespace App\Listeners;

use Illuminate\Support\Str;
use App\Events\MovieCreated;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreMoviePoster
{
    public function retryUntil()
    {
        return now()->addMinutes(5);
    }
    
    public function handle(MovieCreated $event)
    {
        $sizes = [
            ['url' => 'https://image.tmdb.org/t/p/w400', 'size' =>  'grid'],
            ['url' => 'https://image.tmdb.org/t/p/w1280', 'size' =>  'large'],
        ];

        foreach ($sizes as $image) {
            //Download the image
            $contents = file_get_contents($image['url'] . $event->request['form']['image']); //sizes: w200, w1280
            $filename = "image_" . Str::uuid() . "_" . $image['size'] . ".jpg";
            Storage::put("/public/posters/{$filename}", $contents);

            //Associate the image with the movie record
            $event->movie->paths['poster_' . $image['size']] = $filename;
        }

        $event->movie->save();
    }
}

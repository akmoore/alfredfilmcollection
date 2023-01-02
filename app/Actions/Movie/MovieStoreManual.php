<?php
namespace App\Actions\Movie;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\Movies\MovieTrait;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class MovieStoreManual {
    use MovieTrait;

    public function __construct(public Request $request)
    {   
    }

    public function __invoke()
    {
        // return $this->request->all();
        $movie = Movie::create($this->fillableManualArray($this->request));
        $this->addToFilterable($movie, null);
        $this->handleGenres($movie);
        $this->handleImage($movie);
        return $movie;
    }

    private function handleGenres($movie){
        foreach ($this->request->genres as $genre ) {
            $g = Genre::firstOrCreate(
                ['movie_db_id' => $genre['id']],
                ['name' => $genre['name']]
            );

            $g->movies()->attach($movie->id);
        }

        $movie->movie_genres = $this->request->genres;
        $movie->save();
    }

    private function handleImage($movie){
        if(!$this->request->file('poster')) return;

        $img = Image::make($this->request->file('poster'));
        $extension = $this->request->file('poster')->getClientOriginalExtension();
        $gridImage = "image_" . Str::uuid() . "_grid." . $extension;
        $largeImage = "image_" . Str::uuid() . "_large." . $extension;
        
        $img_grid = $img->resize(400, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save(storage_path("app/public/posters/{$gridImage}"));

        $img_large = $img->resize(1280, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save(storage_path("app/public/posters/{$largeImage}"));

        // Storage::put("/public/posters/{$gridImage}", $img_grid->__toString());
        // Storage::put("/public/posters/{$largeImage}", $img_large->__toString());

        $paths = [
            "poster_grid" => $gridImage, 
            "poster_large" => $largeImage,
        ];

        $movie->paths = $paths;
        $movie->save();
    }
}
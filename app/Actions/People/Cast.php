<?php
namespace App\Actions\People;

use App\Models\Movie;
use App\Models\People;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Cast {
    public function __construct(public array $cast, public Movie $movie, public string $type)
    {
    }

    public function __invoke()
    {
        $this->addCast();
    }

    private function addCast(){
        foreach ($this->cast as $person) {
            if(
                $this->type == 'cast' || 
                ($this->type == 'crew' && $person['job'] == "Director") ||
                ($this->type == 'crew' && $person['job'] == "Director of Photography") ||
                ($this->type == 'crew' && $person['job'] == "Screenplay") ||
                ($this->type == 'crew' && $person['job'] == "Writer") ||
                ($this->type == 'crew' && $person['job'] == "Original Music Composer") ||
                ($this->type == 'crew' && $person['job'] == "Editor")
            ) {
                $this->createPerson($person);
            }

        }
    }

    private function createPerson($person){
        
        $p = People::firstOrCreate(
            ['movie_db_id' => $person['id']],
            [
                'name' => $person['name'],
            ]
        );

        $this->storeProfileImage($p, $person);
        $this->associateMovie($p, $person);
    }

    private function storeProfileImage(People $person, $original_person){
        if(!is_null($person['profile_pic'])) return;
        if(is_null($original_person['profile_path'])) return;

        //Download the image
        $start_url = "https://www.themoviedb.org/t/p/w276_and_h350_face";
        $contents = file_get_contents($start_url . $original_person['profile_path']); //sizes: w200, w1280
        $filename = "image_" . Str::uuid() . ".jpg";
        $storage = Storage::put("/public/credits/{$filename}", $contents);

        //Associate the image with the person record
        $person['profile_pic'] = $filename;
        $person->save();
    }

    private function associateMovie(People $person, $original_person){
        if($this->type == 'cast'){
            $this->movie->people()->attach($person->id, [
                'role' => $original_person['known_for_department'], 
                'cast' => true, 
                'character' => $original_person['character'], 
                'order' => $original_person['order']
            ]);
        }

        if($this->type == 'crew'){
            $this->movie->people()->attach($person->id, [
                'role' => $original_person['job'], 
                'cast' => false, 
            ]);
        }
    }
}
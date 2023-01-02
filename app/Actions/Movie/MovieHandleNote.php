<?php
namespace App\Actions\Movie;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieHandleNote {
    public function __construct(public Request $request, public Movie $movie)
    {
    }

    public function __invoke()
    {
        match($this->request->action){
            'add' => $this->handleAdd(),
            'update' => $this->handleUpdate(),
            'delete' => $this->handleDelete(),
        };

        $this->movie->fresh();
        return $this->movie->notes;
    }

    private function handleAdd(){
        $originalNotesArray = $this->movie->notes;
        array_push($originalNotesArray, $this->request->note);
        $this->movie->notes = $originalNotesArray;
        $this->movie->save();
    }

    private function handleUpdate(){
        $this->movie->notes = collect($this->movie->notes)->map(function($note){
            if($note['id'] == $this->request->note['id']){
                return $this->request->note;
            }

            return $note;
        })->toArray();

        $this->movie->save();
    }

    private function handleDelete(){
        $notesArray = collect($this->movie->notes)->filter(function($note){
            return $note['id'] != $this->request->note['id'];
        })->values()->all();

        // dd($notesArray);

        $this->movie->notes = $notesArray;
        $this->movie->save();
    }
}
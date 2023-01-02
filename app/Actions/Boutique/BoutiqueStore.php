<?php
namespace App\Actions\Boutique;

use App\Models\Boutique;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BoutiqueStore {
    public function __construct(public Request $request)
    {
    }

    public function __invoke()
    {
        // dd($this->request->all());

        $boutique = Boutique::create([
            'display' => $this->request->boutique,
            'value' => strtolower(Str::snake($this->request->boutique)) 
        ]);

        if($this->request->hasFile('image')){
            $this->storeImage($boutique);
        }

        // $this->storeImage($boutique);
        return $boutique;

    }

    private function storeImage($boutique)
    {
        //Download the image
        // $contents = file_get_contents($this->request->image_url); //sizes: w200, w1280
        $image = $this->request->file('image');
        $extension = $image->getClientOriginalExtension();
        $filename = "boutique_" . Str::uuid() . ".{$extension}";
        // Storage::put("/public/boutiques/{$filename}", $image);
        $this->request->image->storeAs('/public/boutiques', $filename);

        //Associate the image with the boutique record
        $boutique->image = $filename;
        $boutique->save();
    }
}
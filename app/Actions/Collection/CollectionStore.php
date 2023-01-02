<?php
namespace App\Actions\Collection;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionStore {
    public function __construct(public Request $request)
    {
    }

    public function __invoke()
    {
        return Collection::create([
            'name' => $this->request->name,
            'boutique_id' => $this->request->boutique,
        ]);
    }
}
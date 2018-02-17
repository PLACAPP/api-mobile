<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;

class BreedController extends Controller {

  
    public function filterBreeds($petType = "", $value = "") {

        $breedList = \Plac\Breed::where('pet_type', $petType)
                ->where('breed_name', 'like', '%' . $value . '%')
                ->orderBy('breed_name', 'asc')
                ->get();
        return \Plac\Helpers\JsonObjects::createJsonObjectsList("breeds", $breedList);
    }

}

<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;


class CategoriesCompanyController extends Controller {

    public function getCategories() {
        return \Plac\PlaceCategories::all();
    }

    public function getSubCategoriesFilter($categoryId) {
        return \Plac\PlaceSubCategories::where("category_id", $categoryId)->get();
    }
    
    public function getSubcategories() {
        $categories = \Plac\PlaceSubCategories::orderBy('subcategory_name', 'asc')->get();

        for ($i = 0; $i < count($categories); $i++) {
            $categories[$i]['isSelected'] = 0;
            $categories[$i]['categoryPosition'] = $i;
        }
        return $categories;
    }

}

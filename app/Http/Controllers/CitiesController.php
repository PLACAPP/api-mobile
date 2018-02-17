<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;

use Plac\Http\Requests;

class CitiesController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return \Plac\City::where("country_id", "COL")->get();
    }

    /**
     * Search city by value
     * 
     * @params String value
     * @return \Illuminate\Http\Response
     */
    public function searchCity($value) {
        $cities = \Plac\City::whereRaw("lower(city_name) like '$value%'")->where("country_id", "COL")->get();
       
        if (count($cities)>0) {
            for ($i = 0; $i < count($cities); $i++) {
                $cities[$i]['isSelected'] = 0;
                $cities[$i]['cityPosition'] = $i;
            }
        }
          return $cities;
    }

}

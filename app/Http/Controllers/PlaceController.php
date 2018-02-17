<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaceController extends Controller {

    // ACTIVE NEW VERSION getPlacesSearch($value)
    public function getPlacesSearched($value, $cityId) {

        $subcategories = \Plac\PlaceSubCategories::whereRaw('LOWER(`subcategory_name`) like ' . "'%$value%'")
                ->get();
        $places = \Plac\PlaceLocation::where('state', '=', 1);
        if ($cityId != 'NA') {
            $places->where('city_id', $cityId);
        }
        $places->where(function($query) use ($value, $subcategories) {
            $query->orwhereRaw('LOWER(`place_location_name`) like ' . "'%$value%'")
                    ->orWhereRaw('LOWER(`place_description`) like ' . "'%$value%'");
            if (count($subcategories) > 0) {
                foreach ($subcategories as $subcategory) {
                    $subcategoryId = $subcategory->subcategory_id;
                    $query->orWhereRaw('categories like ' . "'%$subcategoryId%'");
                }
            }
        });
        return $this->getPlacesSubCategories($places->with('city', 'place')->paginate(20));
    }

    // ACTIVE IT USED INTERNALY
    public function getPlacesSubCategories($placeList) {
        $subCategories = \Plac\PlaceSubCategories::all();
        $i = 0;
        if (count($placeList) > 0) {
            foreach ($placeList as $place) {
                $categoriesName = array();
                foreach ($subCategories as $subCategory) {
                    $placeCategories = json_decode($place->categories, true);
                    if ($placeCategories) {
                        foreach ($placeCategories as $placeCategory) {
                            if ($placeCategory == $subCategory->subcategory_id) {
                                array_push($categoriesName, $subCategory->subcategory_name);
                            }
                        }
                    }
                }
                $placeList[$i]->categoryName = implode(", ", $categoriesName);
                $i++;
            }
        }
        return $placeList;
    }

    // ACTIVE NEW METHOD REPLACE getInitPlacesAround 
    // GET PLACES NEAR TO CURRENT POSITION
    public function getPlacesNear(Request $request) {
        if (!isset($request->coords)) {
            return 'Request empty';
        }
        // COORDS
        $coords = $request->coords;
        $latitude = $coords['latitude'];
        $longitude = $coords['longitude'];

        // FILTERS
        $filters = $request->filters;
        $distance = $filters['distance'];
        $categories = $filters['categories'];

        //VALUE
        $value = $filters['value'];
        $places = DB::table('place_locations')
                ->select(DB::raw("*,(((acos(sin(($latitude *pi()/180)) * sin((`place_latitude`*pi()/180))+cos(( $latitude * pi()/180)) * cos((`place_latitude`*pi()/180)) * cos((($longitude - `place_longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance"))
                ->join('places', 'place_locations.place_id', '=', 'places.place_id')
                ->join("cities", "place_locations.city_id", "=", "cities.city_id");
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $categoryId = $category['subcategory_id'];
                $places->orWhereRaw("categories like '%$categoryId%'");
            }
        }

        if ($value != '') {
            $places = $this->getPlaceSearchContinue($places, $value);
        }

        $places = $places->where('state', 1)
                ->having('distance', '<', $distance)
                ->get();
        $places = $this->getObjectsPlace($places);
        return $this->getPlacesSubCategories($places);
    }

    //ACTIVE IT USED INTERNALY
    public function getPlaceSearchContinue($places, $value) {
        $subcategories = \Plac\PlaceSubCategories::whereRaw('LOWER(`subcategory_name`) like ' . "'%$value%'")
                ->get();
        $places->where(function($query) use ($value, $subcategories) {
            $query->orwhereRaw('LOWER(`place_location_name`) like ' . "'%$value%'")
                    ->orWhereRaw('LOWER(`place_description`) like ' . "'%$value%'");
            if (count($subcategories) > 0) {
                foreach ($subcategories as $subcategory) {
                    $subcategoryId = $subcategory->subcategory_id;
                    $query->orWhereRaw('categories like ' . "'%$subcategoryId%'");
                }
            }
        });
        return $places;
    }

    //ACTIVE GET PLACE ASSESSMENT
    public function getPlaceAssessment($placeId) {
        $assessment = \Plac\Assessment::join("orders", "orders.order_id", "=", "assessment_place.order_id")
                        ->where("place_id", $placeId)->get();
        $sumAssessmentAll = 0;
        if ($assessment != null) {
            foreach ($assessment as $assessmentPlace) {
                $sumAssessmentAll += intval($assessmentPlace->assessment_quantity);
            }
        }
        $totalAssessmentCompany = 0;
        if (count($assessment) > 0) {
            $totalAssessmentCompany = ($sumAssessmentAll / (count($assessment) * 5)) * 5;
        }

        return $totalAssessmentCompany;
    }

    // GET PLACES FILTER BY THE NEXT CITIES BOGOTA, MEDELLIN, BUCARAMANGA 
    public function getMainPlacesByCity(Request $request) {
        $categories = $request->categories;
        $cities = \Plac\City::where('country_id', 'COL')->get();
        $arrayPlacesMain = array();
        foreach ($cities as $city) {
            $places = \Plac\PlaceLocation::where('city_id', $city->city_id)->where('state', 1)->with('city', 'place');
            if (count($categories) > 0) {
                foreach ($categories as $category) {
                    $categoryId = $category['subcategory_id'];
                    $places->whereRaw("categories like '%$categoryId%'");
                }
            }
            $places = $places->latest()->take(10)->get();
            $places = $this->getPlacesSubCategories($places);
            if (count($places) > 0) {
                $cityName = $city->city_name;
                $arrayPlacesMain[$cityName] = $places;
            }
        }
        return $arrayPlacesMain;
    }

    //ACTIVE GET PLACES  BY CITY AND FILTER BY CATEGORIES
    public function getPlacesByCity(Request $request) {
        $categories = $request->categories;
        $cityId = $request->cityId;

        $places = \Plac\PlaceLocation::where('city_id', $cityId)->where('state', 1);
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $categoryId = $category['subcategory_id'];
                $places->whereRaw("categories like '%$categoryId%'");
            }
        }
        $places = $places->with('city', 'place')->latest()->paginate(20);
        $places = $this->getPlacesSubCategories($places);
        return $places;
    }

    //ACTIVE IT USED INTERNALY
    function getObjectsPlace($places) {
        for ($i = 0; $i < count($places); $i++) {
            $city['city_id'] = $places[$i]->city_id;
            $city['city_name'] = $places[$i]->city_name;
            $places[$i]->city = $city;

            $place['path_image_logo'] = $places[$i]->path_image_logo;
            $places[$i]->place = $place;
        }

        return $places;
    }

    //DEPRECATED
    public function getPlacesSearch($search) {

        $subcategories = DB::select('SELECT ps.*'
                        . ' FROM  place_subcategories  ps'
                        . " WHERE  lower(ps.subcategory_name) like '%$search%'");

        $subcategoriesFilters = "";
        foreach ($subcategories as $subcategory) {
            $subcategoryId = $subcategory->subcategory_id;
            $subcategoriesFilters .= " OR categories like '%$subcategoryId%' \n";
        }

        $query = " SELECT pl.*,cit.*,pla.path_image_logo,pla.place_id"
                . " FROM place_locations  pl "
                . " INNER JOIN  cities cit ON cit.city_id=pl.city_id"
                . " INNER JOIN  places pla ON pla.place_id=pl.place_id"
                . " WHERE  pl.state=1"
                . " AND (lower(place_location_name) like '%$search%' "
                . " OR lower(place_description) like '%$search%' "
                . " $subcategoriesFilters )";

        $placeList = DB::select($query);
        return $this->getPlacesFinal($placeList);
    }

    //DEPRECATED
    public function getInitPlacesAround($latitude, $longitude, $distance) {

        $place = DB::table('place_locations')
                ->select(DB::raw("*,(((acos(sin(($latitude *pi()/180)) * sin((`place_latitude`*pi()/180))+cos(( $latitude * pi()/180)) * cos((`place_latitude`*pi()/180)) * cos((($longitude - `place_longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance"))
                ->join('places', 'place_locations.place_id', '=', 'places.place_id')
                ->join("cities", "place_locations.city_id", "=", "cities.city_id")
                ->where('state', 1)
                ->having('distance', '<', $distance)
                ->get();

        return $this->getPlacesFinal($place);
    }

    //DEPRECATED
    public function getInitPlacesAroundPaginate($latitude, $longitude, $distance) {

        $place = \Plac\PlaceLocation::select('place_location.*', DB::raw("(((acos(sin(($latitude *pi()/180)) * sin((`place_latitude`*pi()/180))+cos(( $latitude * pi()/180)) * cos((`place_latitude`*pi()/180)) * cos((($longitude - `place_longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance"))
                ->join('places', 'place_locations.place_id', '=', 'places.place_id')
                ->join("cities", "place_locations.city_id", "=", "cities.city_id")
                ->where('state', 1)
                ->having('distance', '<', $distance)
                ->paginate(10);

        return $this->getPlacesFinal($place);
    }

    // DEPRECATED
    public function getPlacesFinal($placeList) {
        $subCategories = \Plac\PlaceSubCategories::all();

        $categoryName = "";
        $categories = "";
        $i = 0;
        if (count($placeList) > 0) {


            foreach ($placeList as $place) {
                $categoryName = "";
                foreach ($subCategories as $subCategory) {

                    $placeCategories = json_decode($place->categories, true);
                    $y = 0;
                    foreach ($placeCategories as $placeCategory) {

                        if ($placeCategory == $subCategory->subcategory_id) {

                            $categoryName .= $subCategory->subcategory_name;

                            if (count($placeCategories) - 1 != $y) {
                                $categoryName .= ", ";
                            }
                        }
                        $y++;
                    }
                }
                $placeList[$i]->categoryName = $categoryName;

                $i++;
            }
        }
        return $placeList;
    }

    // DEPRECATED
    public function getPlacesFiltered(Request $request) {
        $filters = $request["filters"];
        //FILTER LOCATIONS
        $latLong = $filters["latLong"];
        //FILTER CITY
        $city = $filters["city"];
        //FILTER DISTANCE
        $distance = $filters["distance"];

        //FILTER CATEGORIES
        $categories = $filters["categories"];


        if ($city != "") {
            $latitude = $city["latitude"];
            $longitude = $city["longitude"];
        } else {
            $locationEnabled = $latLong["locationEnabled"];
            if ($locationEnabled == true) {
                $latitude = $latLong["coords"]["latitude"];
                $longitude = $latLong["coords"]["longitude"];
            }
        }

        $query = "SELECT pl.*,ct.*,p.*,(((acos(sin(($latitude *pi()/180)) * sin((`place_latitude`*pi()/180))+cos(( $latitude * pi()/180)) * cos((`place_latitude`*pi()/180)) * cos((($longitude - `place_longitude`)*pi()/180))))*180/pi())*60*1.1515*1.609344) as distance " .
                " FROM place_locations pl " .
                " INNER JOIN cities ct ON ct.city_id=pl.city_id " .
                " INNER JOIN places p ON p.place_id=pl.place_id ";


        if (count($categories) > 0) {

            $query .= " WHERE pl.categories like  '%$categories[0]%' ";

            for ($i = 1; $i < count($categories); $i++) {

                $query .= " OR pl.categories  like  '%$categories[$i]%'   ";
            }
        }

        if ($city != "") {
            $city_id = $city["city_id"];
            $query .= " AND ct.city_id=$city_id ";
        }

        $query .= " AND pl.state=1
               HAVING distance < $distance " .
                " ORDER BY distance,pl.created_at ";
        $places = DB::select(DB::raw($query));
        return $this->getPlacesFinal($places);
    }

}

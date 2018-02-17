<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Follower;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;
use Plac\Profile;
use DB;

class ProfileController extends Controller {

    public function getRecentProfiles($profileId) {
        $profilesTo = Follower::where("profile_from_id", $profileId)->pluck('profile_to_id');
        //GET PROFILE FOR GET plac_user_id
        $profile = Profile::where('profile_id', $profileId)->first();
        $placUserId = $profile->plac_user_id;
        // QUERY TO BLACK LIST FOR CHECK WHO BLOCK ME AND  WHO I BLOCK
        $placUserBlackList = new PlacUserBlackListController();
        $placUsersTo = $placUserBlackList->getPlacUserListBlackListFrom($placUserId);
        $placUsersFrom = $placUserBlackList->getPlacUserListBlackListTo($placUserId);
        $profiles = Profile::whereNotIn('profile_id', $profilesTo)->whereNotIn('plac_user_id', $placUsersTo)->whereNotIn('plac_user_id', $placUsersFrom)->whereNotNull("breed_id")->with("breed", "placUser")->orderBy('created_at', 'desc')->take(12)->get();
        return PlacUserBlackListController::removeProfilesByBlackList($profileId, $profiles);
    }

    public function getRecentProfiles2($profileId) {
        $profilesTo = Follower::where("profile_from_id", $profileId)->pluck('profile_to_id');
        //GET PROFILE FOR GET plac_user_id
        $profile = Profile::where('profile_id', $profileId)->first();
        $placUserId = $profile->plac_user_id;
        // QUERY TO BLACK LIST FOR CHECK WHO BLOCK ME AND  WHO I BLOCK
        $placUserBlackList = new PlacUserBlackListController();
        $placUsersTo = $placUserBlackList->getPlacUserListBlackListFrom($placUserId);
        $placUsersFrom = $placUserBlackList->getPlacUserListBlackListTo($placUserId);
        //QUERY TO DISMISSED PROFFILES FOR CHECK WHO I DISMISSED       
        $profileDismissController = new ProfileDismissController();
        $profilesDismissed = $profileDismissController->getProfilesDismissed($profileId);

        $profiles = Profile::whereNotIn('profile_id', $profilesTo)->whereNotIn('profile_id', $profilesDismissed)->whereNotIn('plac_user_id', $placUsersTo)->whereNotIn('plac_user_id', $placUsersFrom)->with("breed", "placUser")->orderBy('created_at', 'desc')->take(21)->get();
        $profiles = self::getCategoriesNameFromCompany($profiles);
        return PlacUserBlackListController::removeProfilesByBlackList($profileId, $profiles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function storeReact(Request $request) {


        $profile = new Profile();
        $profileType = $request->profileType;
        $placUserId = $request->plac_user_id;
        $pathCloudinary = "";
        try {

            $profileId = $this->generateUniqueId();

            $profile->profile_id = $profileId;
            if ($profileType == "pet") {
                $pet = $request->pet;
                if (is_array($pet)) {
                    $arrayPet = $pet;
                } else {
                    $arrayPet = json_decode($pet, true);
                }
                $profile->profile_type = "pet";
                $profile->profile_name = $arrayPet["pet_name"];
                $profile->breed_id = $arrayPet["pet_breed"]["breed_id"];
                $profile->pet_type = $arrayPet["pet_type"];
                $profile->pet_gender = $arrayPet["pet_gender"];
                $pathCloudinary = "profiles/pets/pet";
            } else if ($profileType == "company") {

                $company = $request->company;
                $arrayCompany;
                if (is_array($company)) {
                    $arrayCompany = $company;
                } else {
                    $arrayCompany = json_decode($company, true);
                }

                $profile->profile_type = "company";
                $profile->profile_name = $arrayCompany["company_name"];
                $profile->company_categories = json_encode($arrayCompany["company_categories"]);
                $pathCloudinary = "profiles/companies/company";
            }

            $profileImage = $request->profile_image;
            if ($profileImage != null) {
                $pathImage = ImageController::base64_to_jpeg($profileImage, public_path('images/profile/profile' . $profileId . "name.jpg"));
                $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $profile->profile_id, $pathCloudinary);
                unlink($pathImage);
                $profile->profile_image = $imagePathCloudinary;
            }
            $profile->plac_user_id = $placUserId;
            $profile->save();

            $follower = new FollowersController();
            $follower->addRelationProfileCreated($profileId);

            if ($profile->profile_type == "company") {
                $profileList = array($profile);
                $profileList = $this->getCategoriesNameFromCompany($profileList);
                $profile = $profileList[0];
            } else {
                $profile = Profile::where("profile_id", $profileId)->with("breed")->first();
            }

            return JsonObjects::responseJsonObject("profile", "created", $profile, "profile_created");
        } catch (Exception $e) {
            return JsonObjects::responseJsonObject("profile", "exception", $e, "error");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $profile = Profile::where("profile_id", $id)->with("placuser")->first();
        return $profile;
    }

    public function getProfileFrom($profileFromId, $profileToId) {
        $profile = Profile::where("profile_id", $profileFromId)->with("placuser", "breed")->first();
        if (FollowersController::checkProfileIsFollowing($profileFromId, $profileToId)) {
            $profile["isFollowing"] = 1;
        } else {
            $profile["isFollowing"] = 0;
        }

        return $profile;
    }

    public function getProfileData($profileFromId, $profileToId) {
        $profile = Profile::where("profile_id", $profileToId)->with("placuser", "breed")->first();
        if (FollowersController::checkProfileIsFollowing($profileFromId, $profileToId)) {
            $profile["isFollowing"] = 1;
        } else {
            $profile["isFollowing"] = 0;
        }

        return $profile;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {


        $requestProfile;
        if (is_array($request["profile"])) {
            $requestProfile = $request["profile"];
        } else {
            $requestProfile = json_decode($request["profile"], true);
        }


        $profile = Profile::where('profile_id', $id)->first();
        $pathCloudinary = "";


        if ($profile->profile_type == "pet") {


            $breed_id = $requestProfile["breed_id"];
            if ($breed_id != null) {
                $profile->breed_id = $breed_id;
            }

            $pet_birthday = $requestProfile["pet_birthday"];
            if ($pet_birthday != null) {
                $profile->pet_birthday = $pet_birthday;
            }
            $pet_gender = $requestProfile["pet_gender"];
            if ($pet_gender != null) {
                $profile->pet_gender = $pet_gender;
            }

            $pet_type = $requestProfile["pet_type"];
            if ($pet_type != null) {
                $profile->pet_type = $pet_type;
            }

            if (isset($requestProfile["pet_image_vaccine"])) {

                $petImageVaccine = $requestProfile["pet_image_vaccine"];
                $pathCloudinaryVaccine = "profiles/pets/vaccine/pet";
                $pathImage = ImageController::base64_to_jpeg($petImageVaccine, public_path('images/profile/vaccine' . $profile->profile_id . ".jpg"));

                $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $profile->profile_id, $pathCloudinaryVaccine);
                unlink($pathImage);
                $profile->pet_image_vaccine = $imagePathCloudinary;
            }



            if (isset($requestProfile["pet_image_pedigree"])) {
                $petImagePedigree = $requestProfile["pet_image_pedigree"];


                $pathCloudinaryPedigree = "profiles/pets/pedigree/pet";
                $pathImage = ImageController::base64_to_jpeg($petImagePedigree, public_path('images/profile/pedigree' . $profile->profile_id . ".jpg"));

                $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $profile->profile_id, $pathCloudinaryPedigree);
                unlink($pathImage);
                $profile->pet_image_pedigree = $imagePathCloudinary;
            }
            $pathCloudinary = "profiles/pets/pet";
        } else {

            if (isset($requestProfile["company_categoriesa"])) {
                $profile->company_categories = json_encode($requestProfile["company_categoriesa"]);
            }

            if (isset($requestProfile["company_address"])) {
                $profile->company_address = $requestProfile["company_address"];
            }

            if (isset($requestProfile["company_description"])) {
                $profile->company_description = $requestProfile["company_description"];
            }

            if (isset($requestProfile["company_email"])) {
                $profile->company_email = $requestProfile["company_email"];
            }

            if (isset($requestProfile["company_hour_office_start"])) {
                $profile->company_hour_office_start = $requestProfile["company_hour_office_start"];
            }
            if (isset($requestProfile["company_hour_office_end"])) {
                $profile->company_hour_office_end = $requestProfile["company_hour_office_end"];
            }

            $pathCloudinary = "profiles/companies/company";
        }

        if (isset($requestProfile["profile_image"])) {
            $profileImage = $requestProfile["profile_image"];
            $pathImage = ImageController::base64_to_jpeg($profileImage, public_path('images/profile/profile' . $profile->profile_id . ".jpg"));
            $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $profile->profile_id, $pathCloudinary);
            unlink($pathImage);
            $profile->profile_image = $imagePathCloudinary;
        }



        $profile_name = $requestProfile["profile_name"];

        $profile->profile_name = $profile_name;

        $profile->save();
        $profileNew = Profile::where('profile_id', $id)->with("breed")->first();

        return JsonObjects::responseJsonObject("profile", 'profile_udpated', $profileNew, 'Profile sucessfully updated');
    }

    public function getProfilesFacebookFriends(Request $request) {

        $requestFriends = json_decode($request->requestFriends, true);
        $profileFromId = $request->profileFromId;
        $friendData = $requestFriends["friends"]["data"];
        $facebookIdList = array();
        $i = 0;
        foreach ($friendData as $friendata) {
            $facebookIdList[$i] = $friendata["id"];
            $i++;
        }

        $profiles = Profile::join('plac_users', 'profiles.plac_user_id', '=', 'plac_users.plac_user_id')
                ->whereIn("plac_users.facebook_id", $facebookIdList)
                ->with("placUser")
                ->get();
        $i = 0;
        foreach ($profiles as $profile) {
            $following = Follower::where("profile_from_id", $profileFromId)->where("profile_to_id", $profile->profile_id)->first();
            $isFollowing = 0;
            if ($following != null) {
                $isFollowing = 1;
            }

            $profiles[$i]['isFollowing'] = $isFollowing;
            $i++;
        }



        return $profiles;
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = Profile::where('profile_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    public static function getPlacUserProfiles($placUserId) {
        $profiles = Profile::where("plac_user_id", $placUserId)->with("breed")->get();

        return self::getCategoriesNameFromCompany($profiles);
    }

    public static function getCategoriesNamePost($posts) {
        $categories = \Plac\PlaceCategories::all();
        $i = 0;
        $categoryName = '';

        foreach ($posts as $post) {

            $profile = $post['profile'];
            $categoriesName = array();
            if ($profile['profile_type'] == 'company') {
                foreach ($categories as $category) {
                    $companyCategories = json_decode($profile['company_categories'], true);
                    if ($companyCategories != null) {
                        foreach ($companyCategories as $companyCategory) {
                            if ($companyCategory == $category->category_id) {
                                array_push($categoriesName, $category->category_name);
                            }
                        }
                    }
                }
            }
            $posts[$i]['profile']['company_categories_name'] = implode(", ", $categoriesName);

            $i++;
        }

        return $posts;
    }

    public static function getCategoriesNameFromCompany($profiles) {
        $categories = \Plac\PlaceCategories::all();
        $i = 0;
        foreach ($profiles as $profile) {
            $categoriesName = array();
            foreach ($categories as $category) {
                $companyCategories = json_decode($profile['company_categories'], true);
                if ($companyCategories != null) {

                    foreach ($companyCategories as $companyCategory) {
                        if ($companyCategory == $category->category_id) {
                            array_push($categoriesName, $category->category_name);
                        }
                    }
                }
            }
            $profiles[$i]->company_categories_name = implode(", ", $categoriesName);
            $i++;
        }
        return $profiles;
    }

    public function getPetType($petType) {
        $response = "";
        switch ($petType) {
            case "DOG":
                $response = "Perro";
                break;
            case "CAT":
                $response = "Gato";
                break;
            case "MINIPIG":
                $response = "Mini Pig";
                break;
            case "HAMSTER":
                $response = "Hamster";
                break;
            case "RABBIT":
                $response = "Conejo";
                break;
        }
        return $response;
    }

    public function getProfilesSearched($profileFromId, $value) {
        $value = strtolower($value);
        $profile = Profile::where('profile_id', $profileFromId)->first();
        $placUserId = $profile->plac_user_id;
        $breeds = \Plac\Breed::whereRaw('LOWER(`breed_name`) like ' . "'%$value%'")->get();
        $profiles = Profile::join('plac_users', 'profiles.plac_user_id', '=', 'plac_users.plac_user_id')
                ->whereRaw('LOWER(`plac_user_name`) like ' . "'%$value%'")
                ->orWhereRaw('LOWER(`profile_name`) like ' . "'%$value%'");

        if (count($breeds) > 0) {
            for ($i = 0; $i < count($breeds); $i++) {
                $breedId = $breeds[$i]['breed_id'];
                $profiles->orWhere("breed_id", "$breedId");
            }
        }
        $profiles = $profiles->select('profiles.*')
                ->with("placUser", "breed")
                ->paginate(20);
        $profiles = self::getCategoriesNameFromCompany($profiles);
        $profiles = \GuzzleHttp\json_encode($profiles);
        $profiles = \GuzzleHttp\json_decode($profiles, true);
        $i = 0;
        foreach ($profiles["data"] as $profile) {
            if (FollowersController::checkProfileIsFollowing($profileFromId, $profile["profile_id"])) {
                $profiles["data"][$i]["isFollowing"] = 1;
            } else {
                $profiles["data"][$i]["isFollowing"] = 0;
            }
            $i++;
        }
        $responseProfiles = PlacUserBlackListController::removeProfilesByBlackList($profileFromId, $profiles['data']);
        $profiles['data'] = $responseProfiles;
        return $profiles;
    }

    public function getSuggestProfilesWithFilters(Request $request) {

        $filters = $request->filters;

        $breed = $filters['breed'];
        $profileType = $filters['profile_type'];
        $petType = $filters['pet_type'];
 



        $users = Profile::select('profiles.*', DB::raw('count(posts.post_id) as total_posts'))
                ->join('posts', 'posts.profile_id', '=', 'profiles.profile_id')
                ->whereRaw('posts.created_at >=DATE_SUB( now(), INTERVAL 4 MONTH)')
                ->whereRaw("posts.post_state='ACTIVE'");

        if ($breed['breed_id'] != "") {
            $simbol = ($breed['exclude']) ? "<>" : "=";
            $users = $users->where('profiles.breed_id', $simbol, $breed['breed_id']);
        }
        if ($profileType['type'] != "") {
            $simbol = ($profileType['exclude']) ? "<>" : '=';
            $users = $users->where('profiles.profile_type', $simbol, $profileType['type']);
        }
        if ($petType['type'] != "") {
            $simbol = ($petType['exclude']) ? "<>" : '=';
            $users = $users->where('profiles.pet_type', $simbol, $petType['type'] );
        }

        $users = $users->groupBy('profiles.profile_id')
                ->havingRaw('total_posts >=1')
                ->orderBy('total_posts', 'desc')
                ->with('placUser','breed')
                ->get();
        return $users;
    }

}

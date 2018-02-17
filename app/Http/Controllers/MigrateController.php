<?php

namespace Plac\Http\Controllers;

use Parse\ParseQuery;
use Plac\Follower;
use Plac\Helpers\HelperIDs;
use Plac\Http\Requests;
use Plac\PlacUser;
use Plac\Profile;

class MigrateController extends ParseTemporalController
{

    const NUMBER_USER_LAST_MIGRATE = 584;
    const NUMBER_PETS_LAST_MIGRATE = 488;
    const NUMBER_FOLLOWERS_LAST_MIGRATE = 488;


    public function migrateUsers()
    {

        $queryUser = new ParseQuery("_User");
        $queryUser->ascending("createdAt");
        $queryUser->limit(100);

        $userList = $queryUser->find();


    }


    public function migratecompanies()
    {
        $queryCompanies = new ParseQuery("companies");
        $queryCompanies->ascending("createdAt");
        $queryCompanies->includeKey("categories_company");
        $queryCompanies->limit(20);
        $queryCompanies->skip(0);
        $companiesList = $queryCompanies->find();


        foreach ($companiesList as $company) {


            $fileImageUser = $company->get("image_company");
            $urlImageCompany = $fileImageUser->getUrl();
            $ruta_imagen = $this->compressimage($urlImageCompany, HelperIDs::generateID() . "_" . $this->replaceSpace($company->get("name_company")));

            if ($ruta_imagen != "na") {
                $dataImage = \Cloudinary\Uploader::upload($ruta_imagen, array("use_filename" => TRUE));
            } else {
                $dataImage['url'] = "na";
            }

            $profile = new Profile();
            $profile->id_profile = $company->getObjectId();
            $profile->name_profile = utf8_encode($company->get("name_company"));
            $profile->image_profile = $dataImage['url'];
            $profile->type_profile = 'company';
            $profile->select_profile = 1;
            $array = $company->get("categories_company");
            $var = $array[count($array) - 1];
            $profile->categories_company = json_encode(array($var->get("name_subcategory")));
            $profile->address_company = $company->get("address_company");
            $profile->email_company = $company->get("email_company");
            $profile->hour_office_start_company = $company->get("hour_office_start_company");
            $profile->hour_office_end_company = $company->get("hour_office_end_company");
            $profile->id_user = $company->get("user_company")->getObjectId();
            $profile->created_at = $company->getCreatedAt()->format("Y-m-d H:i:s");
            $profile->updated_at = $company->getUpdatedAt()->format("Y-m-d H:i:s");
            $profile->save();
        }

    }


    public function migratepets()
    {


        $queryPets = new ParseQuery("pets");
        $queryPets->descending("createdAt");
        $queryPets->limit(30);
        $petList = $queryPets->find();
        $array = array();
        $i = 0;
        foreach ($petList as $pet) {

            $fileImagePet = $pet->get("image_pet");
            $urlImagePet = $fileImagePet->getUrl();
            $ruta_imagen = $this->compressimage($urlImagePet, HelperIDs::generateID() . "_" . $this->replaceSpace($pet->get("name_pet")));

            if ($ruta_imagen != "na") {
                $dataImage = \Cloudinary\Uploader::upload($ruta_imagen, array("use_filename" => TRUE));
            } else {
                $dataImage['url'] = "na";
            }

            $profile = new Profile();
            $profile->id_profile = $pet->getObjectId();
            $profile->name_profile = $pet->get("name_pet");

            $profile->image_profile = $dataImage['url'];
            $profile->type_profile = 'pet';
            $profile->select_profile = $pet->get("select_profile");
            $profile->gender_pet = $pet->get("gender_pet");
            $profile->type_pet = $pet->get("type_pet");
            $profile->birthday_pet = $pet->get("birthday_pet");
            $objectBreed = $pet->get("breed_pet");

            if ($objectBreed != "" || $objectBreed != null) {
                $profile->id_breed = $objectBreed->getObjectId();
            }

            $profile->id_user = $pet->get("user_pet")->getObjectId();
            $profile->created_at = $pet->getCreatedAt()->format("Y-m-d H:i:s");
            $profile->updated_at = $pet->getUpdatedAt()->format("Y-m-d H:i:s");
            $profile->save();
            $array[$i] = $dataImage;
            $i++;
        }
        echo json_encode($array);

    }


    public function migratefollowers()
    {
        $queryFollowers = new ParseQuery("followers");
        $queryFollowers->ascending("createdAt");
        $queryFollowers->limit(1000);
        $queryFollowers->skip(76);
        $followersList = $queryFollowers->find();
        foreach ($followersList as $follower) {
            $follower_mysql = new Follower();
            $array_user_from = $follower->get("user_from");
            $array_user_to = $follower->get("user_to");
            $profile_from_id = $array_user_from[0]->getObjectId();
            $profile_to_id = $array_user_to[0]->getObjectId();
            $follower_mysql->id_follower = $follower->getObjectId();
            $follower_mysql->id_profile_from = $profile_from_id;
            $follower_mysql->id_profile_to = $profile_to_id;
            $follower_mysql->created_at = $follower->getCreatedAt()->format("Y-m-d H:i:s");
            $follower_mysql->updated_at = $follower->getUpdatedAt()->format("Y-m-d H:i:s");
            $follower_mysql->save();
        }

        echo "registros insertados";
    }

    public function compressimage($originalFile, $name_image)
    {
        $format = substr($originalFile, -4);
        $ruta_imagen = base_path() . '/public/images/' . $name_image . '.jpg';
        $newImage = "";
        if ($format == ".jpg") {
            $newImage = imagecreatefromjpeg($originalFile);
        } else if ($format == ".png") {
            $newImage = imagecreatefrompng($originalFile);
        } else {
            return "na";
        }
        //Convert to jpg
        imagejpeg($newImage, $ruta_imagen, 80);
        // liberar la imagen de la memoria
        imagedestroy($newImage);

        return $ruta_imagen;
    }

    public function replaceSpace($value)
    {
        return str_replace(' ', '_', $value);
    }
}

<?php

namespace Plac\Http\Controllers;


use Parse\ParseQuery;
use Plac\Follower;
use Plac\Http\Requests;
use Plac\PlacUser;
use Plac\Post;
use Plac\PostComments;
use Plac\PostLikes;
use Plac\Profile;


class MigrationController extends ParseTemporalController

{

    public function migrateUsers()
    {
        $queryUser = new ParseQuery("_User");
        $queryUser->descending("createdAt");
        $queryUser->limit(1);

        //   $queryUser->skip(800);
        $userList = $queryUser->find();
        foreach ($userList as $user) {
            $placUser = new PlacUser();
            $placUser->plac_user_id = $user->getObjectId();
            $placUser->plac_user_name = $user->get("name_user");
            $placUser->plac_user_email = $user->get("email");
            $placUser->account_type = $user->get("type_account");

            if ($user->get("id_facebook") != "") {
                $placUser->sign_up_type = "facebook";
                $placUser->facebook_id = $user->get("id_facebook");
            } else {
                $placUser->sign_up_type = "form";
            }

            $placUser->encrypted_password = $user->get("email");
            $placUser->salt = $user->get("email");

            $placUser->isMigration = true;
            $placUser->email_confirmed = $user->get("emailVerified");
            $placUser->created_at = $user->getCreatedAt()->format('Y-m-d H:i:s');
            $placUser->updated_at = $user->getUpdatedAt()->format('Y-m-d H:i:s');
            $placUser->save();

        }


    }


    public function migratePets()
    {
        new CloudinaryController();
        $queryPets = new ParseQuery("pets");
        $queryPets->descending("createdAt");
        $queryPets->limit(1);
        // $queryPets->skip(950);  //-- after 950
        $petList = $queryPets->find();
        $array = array();
        $i = 0;
        foreach ($petList as $pet) {


            $profile = new Profile();


            $profile->profile_id = $pet->getObjectId();
            $profile->profile_name = $pet->get("name_pet");

            $profile->profile_type = 'pet';
            $profile->profile_selected = $pet->get("select_pet");
            $profile->pet_gender = $pet->get("gender_pet");
            $profile->pet_type = $pet->get("type_pet");
            $profile->pet_birthday = $pet->get("birthday_pet");
            $objectBreed = $pet->get("breed_pet");

            if ($objectBreed != null) {
                $profile->breed_id = $objectBreed->getObjectId();
            }
            $fileImagePet = $pet->get("image_pet");


            if ($fileImagePet != null) {

                $urlImagePet = $fileImagePet->getUrl();
                $ruta_imagen = $this->compressimage($urlImagePet, $pet->getObjectId());

                if ($ruta_imagen != "na") {
                    $dataImage = \Cloudinary\Uploader::upload($ruta_imagen, array("public_id" => "profiles/profile" . $pet->getObjectId()));
                } else {
                    $dataImage['url'] = "na";
                }
            } else {
                $dataImage['url'] = "na";
            }

            $profile->profile_image = $dataImage['url'];


            $profile->plac_user_id = $pet->get("user_pet")->getObjectId();
            $profile->created_at = $pet->getCreatedAt()->format("Y-m-d H:i:s");
            $profile->updated_at = $pet->getUpdatedAt()->format("Y-m-d H:i:s");
            $profile->save();
            $array[$i] = $dataImage;
            $i++;
        }
        echo json_encode($array);

    }


    public function migrateCompanies()
    {

        new CloudinaryController();
        $queryCompanies = new ParseQuery("companies");
        $queryCompanies->descending("createdAt");
        $queryCompanies->includeKey("categories_company.categoria");
        $queryCompanies->limit(100);
        $queryCompanies->skip(0);
        $companiesList = $queryCompanies->find();


        foreach ($companiesList as $company) {


            $profile = new Profile();
            $profile->profile_id = $company->getObjectId();
            $profile->profile_name = utf8_encode($company->get("name_company"));


            $fileImageUser = $company->get("image_company");
            $urlImageCompany = $fileImageUser->getUrl();
            $ruta_imagen = $this->compressimage($urlImageCompany, $company->getObjectId());

            if ($ruta_imagen != "na") {
                $dataImage = \Cloudinary\Uploader::upload($ruta_imagen, array("public_id" => "profiles/profile" . $company->getObjectId()));
            } else {
                $dataImage['url'] = "";
            }

            $profile->profile_image = $dataImage['url'];
            $profile->profile_type = 'company';
            $profile->profile_selected = 1;


            $subcategories = $company->get("categories_company");

            $array = array();
            $i = 0;
            foreach ($subcategories as $subcategory) {
                $array[$i]["subcategory_id"] = $subcategory->getObjectId();
                $array[$i]["subcategory_name"] = $subcategory->get("name_subcategory");
                $category = $subcategory->get("categoria");
                $array[$i]["category"]["category_id"] = $category->getObjectId();
                $array[$i]["category"]["category_name"] = $category->get("name_category");

                $i++;

            }
            $categories = json_encode($array);

            $profile->company_categories = $categories;
            $profile->company_address = $company->get("address_company");
            $profile->company_email = $company->get("email_company");
            $profile->company_hour_office_start = $company->get("hour_office_start_company");
            $profile->company_hour_office_end = $company->get("hour_office_end_company");
            $profile->plac_user_id = $company->get("user_company")->getObjectId();
            $profile->created_at = $company->getCreatedAt()->format("Y-m-d H:i:s");
            $profile->updated_at = $company->getUpdatedAt()->format("Y-m-d H:i:s");
            $profile->save();
        }

    }

    public function testCompanies()
    {

        new CloudinaryController();
        $queryCompanies = new ParseQuery("companies");
        $queryCompanies->descending("createdAt");
        $queryCompanies->includeKey("categories_company.categoria");
        $queryCompanies->limit(100);
        $companiesList = $queryCompanies->find();

        $array = array();

        foreach ($companiesList as $company) {


            $subcategories = $company->get("categories_company");
            $i = 0;
            foreach ($subcategories as $subcategory) {
                $array[$i]["subcategory_id"] = $subcategory->getObjectId();
                $array[$i]["subcategory_name"] = $subcategory->get("name_subcategory");
                $category = $subcategory->get("categoria");
                $array[$i]["category"]["category_id"] = $category->getObjectId();
                $array[$i]["category"]["category_name"] = $category->get("name_category");

                $i++;

            }

            echo json_encode($array);

        }


    }


    public function migrateFollowers()
    {
        $queryFollowers = new ParseQuery("followers");
        $queryFollowers->descending("createdAt");
        $queryFollowers->limit(3);
        //$queryFollowers->skip(6000);
        $followersList = $queryFollowers->find();
        foreach ($followersList as $follower) {
            $follower_mysql = new Follower();
            $array_user_from = $follower->get("user_from");
            $array_user_to = $follower->get("user_to");
            $profile_from_id = $array_user_from[0]->getObjectId();
            $profile_to_id = $array_user_to[0]->getObjectId();
            $follower_mysql->follower_id = $follower->getObjectId();
            $follower_mysql->profile_from_id = $profile_from_id;
            $follower_mysql->profile_to_id = $profile_to_id;
            $follower_mysql->created_at = $follower->getCreatedAt()->format("Y-m-d H:i:s");
            $follower_mysql->updated_at = $follower->getUpdatedAt()->format("Y-m-d H:i:s");
            $follower_mysql->save();
        }

        echo "registros insertados";
    }

    public function migratePosts()
    {
        new CloudinaryController();
        $queryPostTimeLine = new ParseQuery("post_time_line");
        $queryPostTimeLine->descending("createdAt");
        $queryPostTimeLine->limit(50);
        $queryPostTimeLine->skip(698);
        $postList = $queryPostTimeLine->find();
        foreach ($postList as $post) {
            $post_mysql = new Post();

            echo "Object Id: " . $post->getObjectId();

            $post_mysql->post_id = $post->getObjectId();
            $post_mysql->message = $post->get("txt_content_post");
            $arrayProfile = $post->get("user_post");
            $profile = $arrayProfile[0];
            $post_mysql->profile_id = $profile->getObjectId();

            $postImage = $post->get("image_post");

            if ($postImage != null) {
                $dataImage = "";
                $urlImageCompany = $postImage->getUrl();
                $ruta_imagen = $this->compressimage($urlImageCompany, $post->getObjectId());

                if ($ruta_imagen != "na") {
                    $dataImage = \Cloudinary\Uploader::upload($ruta_imagen, array("public_id" => "posts/post" . $post->getObjectId()));
                } else {
                    $dataImage['url'] = NULL;
                }
                $post_mysql->post_path_image = $dataImage['url'];
            }

            $post_mysql->created_at = $post->getCreatedAt()->format("Y-m-d H:i:s");
            $post_mysql->updated_at = $post->getUpdatedAt()->format("Y-m-d H:i:s");
            $post_mysql->save();

        }

        echo "registros insertados";
    }

    public function migratePostLikes()
    {
        new CloudinaryController();
        $queryPostTimeLine = new ParseQuery("post_time_line");
        $queryPostTimeLine->descending("createdAt");
        $queryPostTimeLine->limit(1000);

        $postList = $queryPostTimeLine->find();


        foreach ($postList as $post) {


            $postLikesList = $post->get("likes_post");
            if ($postLikesList != null) {
                foreach ($postLikesList as $postLike) {
                    $postLikesController = new PostLikesController();

                    $postLikesPlac = new PostLikes();
                    $postLikesPlac->post_id = $post->getObjectId();
                    $postLikesPlac->post_like_id = $postLikesController->generateUniqueId();
                    $postLikesPlac->profile_id = $postLike->getObjectId();
                    $postLikesPlac->save();
                }
            }


        }


    }

    public function migratePostComments()
    {
        $queryPostComments = new ParseQuery("post_comments");
        $queryPostComments->descending("createdAt");
        $queryPostComments->limit(500);
        $queryPostComments->skip(127);
        $postCommentsList = $queryPostComments->find();
        foreach ($postCommentsList as $comment) {

            $postCommentsController = new PostCommentsController();
            $postCommentsPlac = new PostComments();
            $postCommentsPlac->post_comment_id = $postCommentsController->generateUniqueId();
            $arrayProfile = $comment->get("user_comment");
            $profile = $arrayProfile[0];
            $postCommentsPlac->profile_id = $profile->getObjectId();
            $postComment = $comment->get("post_comment");
            echo "postID:" . $postComment->getObjectId();
            $postCommentsPlac->post_id = $postComment->getObjectId();
            $comment = $comment->get("comment_post");
            $postCommentsPlac->message = $comment;
            $postCommentsPlac->save();
        }


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
}

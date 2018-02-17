<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;
use Plac\Helpers\Sanitize;
use Plac\Follower;
use Plac\PlacUser;
use Plac\Http\Controllers\ProfileController;

class PlacUserController extends Controller {

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {


        try {
            $user = $request->user;
            $device = $request->deviceInfo;
            $signUpType = $request->signUpType;
            if (is_array($user)) {
                $userArray = $user;
            } else {
                $userArray = json_decode($user, true);
            }

            if (is_array($device)) {
                $deviceInfoArray = $device;
            } else {
                $deviceInfoArray = json_decode($device, true);
            }




            $email = "";


            if (isset($userArray['email'])) {
                $email = $userArray['email'];
            }

            $placUser = $this->verifyEmailExist($email);
            if ($placUser == "") {
                if ($signUpType == "facebook") {
                    $facebookId = $userArray['id'];
                    $placUser = PlacUser::where('facebook_id', $facebookId)->first();
                }
            }
            if ($placUser == "") {

                $placUser = new PlacUser();
                if ($signUpType == "google") {
                    $placUser->plac_user_image = $userArray['photo'];
                    //$placUser->plac_user_birthday=$userArray['birthday'];
                    $placUser->google_id = $userArray['id'];
                    $placUser->sign_up_type = "google";
                } else if ($signUpType == "facebook") {
                    $placUser->sign_up_type = "facebook";
                    if (isset($userArray['picture'])) {
                        $placUser->plac_user_image = $userArray['picture']['data']['url'];
                    } else {
                        $placUser->plac_user_image = 'http://res.cloudinary.com/plac/image/upload/v1487874124/placusers/image_empty.png';
                    }

                    if (isset($userArray['id'])) {
                        $placUser->plac_user_facebook_link = "https://www.facebook.com/" . $userArray['id'];
                        $placUser->facebook_id = $userArray['id'];
                    }

                    if (isset($userArray['gender'])) {
                        $placUser->plac_user_gender = $userArray['gender'];
                    }

                    if (isset($userArray['birthday'])) {
                        $birthdayFacebookFormat = strtotime($userArray['birthday']);
                        $birthdayMySqlFormat = date('Y-m-d', $birthdayFacebookFormat);
                        $placUser->plac_user_birthday = $birthdayMySqlFormat;
                    }

                    if (isset($userArray['location'])) {
                        $placUser->plac_user_location = $userArray['location']['name'];
                    }

                    if (isset($userArray['work'])) {
                        $placUser->plac_user_work = json_encode($userArray['work'][0]);
                    }
                    if (isset($userArray['books'])) {
                        $placUser->plac_user_books = json_encode($userArray['books']);
                    }
                    if (isset($userArray['movies'])) {
                        $placUser->plac_user_movies = json_encode($userArray['movies']);
                    }
                }

                $confirmation_code = str_random(30);
                if (isset($userArray['name'])) {
                    $placUser->plac_user_name = $userArray['name'];
                } else {
                    $placUser->plac_user_name = 'No registrado';
                }

                $placUserId = $this->generateUniqueId();
                $email = $placUserId . "@placapp.com";
                if (isset($userArray['email'])) {
                    $email = $userArray['email'];
                }

                $placUser->plac_user_email = $email;
                $placUser->confirmation_code = $confirmation_code;

                $pathCloudinary = "placusers/user";
                $placUserImage = $placUser->plac_user_image;
                $imagePathCloudinary = "http://res.cloudinary.com/plac/image/upload/v1487009571/placusers/image_empty.png";

                if (isset($placUserImage)) {
                    if ($placUserImage != "") {
                        $imagePathCloudinary = ImageController::saveImageOnCloudinary($placUser->plac_user_image, $placUserId, $pathCloudinary);
                    }
                }

                $placUser->plac_user_accepted_terms = true;
                $placUser->plac_user_image = $imagePathCloudinary;
                $placUser->plac_user_id = $placUserId;
                $placUser->save();
                $this->sendConfirmationNewUser($placUser, $confirmation_code);
                $installationController = new InstallationController();
                $installationController->store($deviceInfoArray, $placUserId);
                $placUser = PlacUser::where("plac_user_id", $placUserId)->first();


                return JsonObjects::responseJsonObject("plac_user", "created", $placUser, "plac_user_created");
            } else {
                if ($this->updateFromRegister($userArray, $deviceInfoArray, $signUpType, $placUser)) {
                    $profiles = ProfileController::getPlacUserProfiles($placUser->plac_user_id);
                    if (count($profiles) > 0) {
                        $placUser["profiles"] = $profiles;
                        return JsonObjects::responseJsonObject("plac_user", "updated_exist_profiles", $placUser, "plac_user_udpated");
                    } else {
                        return JsonObjects::responseJsonObject("plac_user", "updated_without_profiles", $placUser, "plac_user_udpated");
                    }
                } else {
                    return JsonObjects::responseJsonObject("plac_user", "error", null, "error_udpated");
                }
            }
        } catch (Exception $e) {
            return JsonObjects::responseJsonObject("plac_user", "error", null, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        $requestPlacUser = json_decode($request["placUser"], true);
        $placUser = PlacUser::where('plac_user_id', $id)->first();

        if (isset($requestPlacUser["plac_user_name"])) {
            $placUser->plac_user_name = $requestPlacUser["plac_user_name"];
        }

        if (isset($requestPlacUser["plac_user_work"])) {
            $placUser->plac_user_work = $requestPlacUser["plac_user_work"];
        }
        if (isset($requestPlacUser["plac_user_facebook_link"])) {
            $placUser->plac_user_facebook_link = $requestPlacUser["plac_user_facebook_link"];
        }
        if (isset($requestPlacUser["plac_user_instagram_link"])) {
            $placUser->plac_user_instagram_link = $requestPlacUser["plac_user_instagram_link"];
        }

        if (isset($requestPlacUser["plac_user_twitter_link"])) {
            $placUser->plac_user_twitter_link = $requestPlacUser["plac_user_twitter_link"];
        }

        if (isset($requestPlacUser["plac_user_location"])) {
            $placUser->plac_user_location = $requestPlacUser["plac_user_location"];
        }

        if (isset($requestPlacUser["plac_user_image"])) {
            $placUserImage = $requestPlacUser["plac_user_image"];
            $pathCloudinary = "placusers/user";
            $pathServer = "images/placuser/image" . $id . ".jpg";
            $pathImage = ImageController::base64_to_jpeg($placUserImage, public_path($pathServer));

            $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $id, $pathCloudinary);
            unlink($pathImage);
            $placUser->plac_user_image = $imagePathCloudinary;
        }

        if (isset($requestPlacUser["plac_user_image2"])) {
            $placUserImage = $requestPlacUser["plac_user_image2"];
            $pathCloudinary = "placusers/user2";
            $pathServer = "images/placuser/image2" . $id . ".jpg";
            $pathImage = ImageController::base64_to_jpeg($placUserImage, public_path($pathServer));

            $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $id, $pathCloudinary);
            unlink($pathImage);
            $placUser->plac_user_image2 = $imagePathCloudinary;
        }
        if (isset($requestPlacUser["plac_user_image3"])) {
            $placUserImage = $requestPlacUser["plac_user_image3"];
            $pathCloudinary = "placusers/user3";
            $pathServer = "images/placuser/image3" . $id . ".jpg";
            $pathImage = ImageController::base64_to_jpeg($placUserImage, public_path($pathServer));

            $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $id, $pathCloudinary);
            unlink($pathImage);
            $placUser->plac_user_image3 = $imagePathCloudinary;
        }



        $placUser->save();

        return $placUser;
    }

    /**
     *  Update the accepted terms and conditions from user
     *
     * @param  Request $request
     * @return string 'updated_terms'
     */
    public function updateTerms(Request $request) {

        if (config("app.key") == $request->header("access-key")) {
            $placUser = PlacUser::where('plac_user_id', $request['plac_user_id'])->first();
            if ($placUser != null) {
                $placUser->plac_user_accepted_terms = true;
                $placUser->save();
                return 'updated_terms';
            } else {
                return 'No tiene acceso para realizar esta acción';
            }
        }

        return 'No tiene acceso para realizar esta acción';
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id_user = PlacUser::where('plac_user_id', $idGenerated)->count();
        if ($count_exist_id_user == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    public function sendConfirmationNewUser($placUser, $confirmation_code) {
        Mail::send('emails.user.confirmation.newuser', ['email_user' => $placUser->plac_user_email, 'name_user' => strtoupper($placUser->plac_user_name), 'confirmation_code' => $confirmation_code], function ($m) use ($placUser) {
            $m->from('no-reply@placapp.com', 'PLAC BIENVENIDO');
            $m->to($placUser->plac_user_email, $placUser->plac_user_email)->subject('Confirmar correo electronico');
        });
    }

    public function confirmNewUser($email_user, $confirmation_code) {
        $email_user_clean = Sanitize::sanitize_html_string($email_user);
        $confirmation_code_clean = Sanitize::sanitize_html_string($confirmation_code);
        $placUser = PlacUser::where('plac_user_email', $email_user_clean)
                        ->where('confirmation_code', $confirmation_code_clean)->first();
        if ($placUser != null || $placUser != "") {
            $email_verified = $placUser->email_confirmed;
            if (!$email_verified) {
                $placUser->email_confirmed = true;
                $placUser->save();
//definir vistas
                return "email correctamente verificado";
            } else {

                //definir vistas
                return "email ya   verificado";
            }
        } else {
            return " no existe el usuario  verificado";
        }
    }

    public function verifyEmailExist($email) {
        $userEmail = PlacUser::where("plac_user_email", $email)->first();
        return $userEmail;
    }

    public function updateFromRegister($userArray, $deviceInfoArray, $signUpType, $object) {
        $placUser = $object;

        if ($signUpType == "google") {

            $placUser->plac_user_image = $userArray['photo'];
            //$placUser->plac_user_birthday=$userArray['birthday'];
            $placUser->google_id = $userArray['id'];
            $placUser->sign_up_type = "google";
        } else if ($signUpType == "facebook") {
            $placUser->sign_up_type = "facebook";
            $placUser->facebook_id = $userArray['id'];

            if (isset($userArray['gender'])) {
                $placUser->plac_user_gender = $userArray['gender'];
            }

            if ($placUser->plac_user_image != '') {
                if (isset($userArray['picture'])) {
                    $placUser->plac_user_image = $userArray['picture']['data']['url'];
                }
            }


            if (isset($userArray['birthday'])) {
                $birthdayFacebookFormat = strtotime($userArray['birthday']);
                $birthdayMySqlFormat = date('Y-m-d', $birthdayFacebookFormat);
                $placUser->plac_user_birthday = $birthdayMySqlFormat;
            }
            if (isset($userArray['location'])) {
                $placUser->plac_user_location = $userArray['location']['name'];
            }

            if (isset($userArray['work'])) {
                $placUser->plac_user_work = json_encode($userArray['work'][0]);
            }
            if (isset($userArray['books'])) {
                $placUser->plac_user_books = json_encode($userArray['books']);
            }
            if (isset($userArray['movies'])) {
                $placUser->plac_user_movies = json_encode($userArray['movies']);
            }
            $placUser->plac_user_facebook_link = "https://www.facebook.com/" . $userArray['id'];
        }

        $placUser->plac_user_name = $userArray['name'];
        $placUser->email_confirmed = true;
        $placUser->plac_user_accepted_terms = true;
        $imagePathCloudinary = "http://res.cloudinary.com/plac/image/upload/v1487009571/placusers/image_empty.png";

        if ($placUser->plac_user_image != "") {
            $pathCloudinary = "placusers/user";
            $imagePathCloudinary = ImageController::saveImageOnCloudinary($placUser->plac_user_image, $placUser->plac_user_id, $pathCloudinary);
            $placUser->plac_user_image = $imagePathCloudinary;
        }


        $placUser->save();
        $installationController = new InstallationController();
        $installationController->store($deviceInfoArray, $placUser->plac_user_id);



        return true;
    }

    public function getPlacUserProfiles($placUserId) {
        $profiles = PlacUser::where("plac_user_id", $placUserId)->with("profiles.breed")->get();
        return $profiles;
    }

    public function getPlacUserProfiles2($profileFromId, $placUserId) {
        $placUser = PlacUser::where("plac_user_id", $placUserId)->with("profiles.breed")->first();
        $profiles = $placUser['profiles'];
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
        $profiles = ProfileController::getCategoriesNameFromCompany($profiles);
        $placUser['profiles'] = $profiles;
        return $placUser;
    }

}

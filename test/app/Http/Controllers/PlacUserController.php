<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Plac\Helpers\Encrypt;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonErrors;
use Plac\Helpers\JsonObjects;
use Plac\Helpers\Sanitize;
use Plac\Http\Requests;
use Plac\PlacUser;
use Plac\ResetPasswordPlacUser;
use Redirect;
use View;

class PlacUserController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        
        
        
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        //
    }

    public function signUp(Request $request)
    {

        $email_user = $request->email_user;
        $id_facebook = $request->id_facebook;
        $password_user = $id_facebook;
        $type_sign_up_in = $request->type_sign_up;


        $placUserGet = PlacUser::where('email_user', $email_user)->first();
        if ($placUserGet != "" || $placUserGet != null) {


            if ($type_sign_up_in == "facebook") {

                if ($placUserGet->isMigration) {

                    $type_sign_up_get = $placUserGet->type_sign_up;
                    if ($type_sign_up_get == "facebook") {

                        $hash = Encrypt::hashSSHA($id_facebook);
                        $encrypted_password = $hash["encrypted"]; // encrypted password
                        $salt = $hash["salt"]; // salt
                        $placUserGet->encrypted_password = $encrypted_password;
                        $placUserGet->salt = $salt;
                        $placUserGet->id_facebook = $id_facebook;
                        $placUserGet->isMigration = false;
                        $placUserGet->save();
                        $password_user = $id_facebook;


                    } else if ($type_sign_up_get == "form") {

                        $hash = Encrypt::hashSSHA($id_facebook);
                        $encrypted_password = $hash["encrypted"]; // encrypted password
                        $salt = $hash["salt"]; // salt
                        $placUserGet->encrypted_password = $encrypted_password;
                        $placUserGet->salt = $salt;
                        $placUserGet->isMigration = false;
                        $placUserGet->type_sign_up = "facebook";
                        $placUserGet->id_facebook = $id_facebook;
                        $placUserGet->save();
                        $password_user = $id_facebook;
                    }


                    $this->initLogin($email_user, $password_user);


                } else {

                    $this->initLogin($email_user, $id_facebook);
                }

            } else if ($type_sign_up_in == "form") {
                return JsonErrors::getErrorEmailAlreadyExist();
            }
        } else {

            $name_user = $request->name_user;
            $type_account = $request->type_account;
            $id_firebase = $request->id_firebase;
            $accept_condition = $request->accept_condition;
            //entity  object plac_user
            $confirmation_code = str_random(30);
            $placUser = new PlacUser();
            $idGenerated = $this->generateUniqueId();
            $placUser->plac_user_id = $idGenerated;
            $placUser->plac_user_name = $name_user;
            $placUser->plac_user_email= $email_user;
            if ($type_sign_up_in == "facebook") {
                $placUser->id_facebook = $id_facebook;
                $password_user = $id_facebook;
            } else if ($type_sign_up_in == "form") {
                $password_user = $request->password_user;
            }
            $hash = Encrypt::hashSSHA($password_user);
            $encrypted_password = $hash["encrypted"]; // encrypted password
            $salt = $hash["salt"]; // salt
            $placUser->encrypted_password = $encrypted_password;
            $placUser->salt = $salt;
            $placUser->type_account = $type_account;
            $placUser->type_sign_up = $type_sign_up_in;
            $placUser->confirmation_code = $confirmation_code;
            $placUser->id_firebase = $id_firebase;
            $placUser->accept_condition = $accept_condition;
            $placUser->save();
            $this->sendConfirmationNewUser($placUser, $confirmation_code);
            return JsonObjects::createJsonObjectModel("plac_user_created", $idGenerated, $placUser);

        }


    }

    public function generateUniqueId()
    {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id_user = PlacUser::where('id_user', $idGenerated)->count();

        if ($count_exist_id_user == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }


    public function initLogin($email_user, $id_facebook)
    {
        $request = new Request();
        $request->email_user = $email_user;
        $request->password_user = $id_facebook;
        $this->logIn($request);
    }

    public function logIn(Request $request)
    {

        $email_user = $request->email_user;
        $password_user = $request->password_user;

        $placUser = PlacUser::where('email_user', $email_user)->with('profiles')->first();
        if ($placUser != "" || $placUser != null) {


            $encrypted_password = $placUser->encrypted_password;
            $salt = $placUser->salt;
            $hash = Encrypt::checkhashSSHA($salt, $password_user);

            if ($encrypted_password == $hash) {
                echo JsonObjects::createJsonObjectModel("login_plac_user", $placUser->id_user, $placUser);
            } else {
                echo JsonErrors::getErrorPasswordDontMatch();
            }


        } else {
            echo JsonErrors::getErrorEmailNoExist();
        }


    }

    public function requestResetPasswordForgot(Request $request)
    {

        $email_user_clean = Sanitize::sanitize_html_string($request->email_user);
        $token = str_random(50);
        $created_at = date("Y-m-d H:i:s");
        $resetPassword = ResetPasswordPlacUser::create(['email_user' => $email_user_clean, 'token' => $token, 'created_at' => $created_at]);
        if ($resetPassword != null || $resetPassword != "") {


            Mail::send('emails.user.confirmation.resetpassword', ['email_user' => $email_user_clean, "token" => $token], function ($m) use ($email_user_clean) {

                $m->from('no-reply@placapp.com', 'PLAC CAMBIAR CONTRASEÑA');
                $m->to($email_user_clean, "PLAC Restablecer contraseña");
                $m->subject('Reestablecer contraseña');
            });

            return JsonObjects::createJsonObjectModel("resetpassword", $token, $resetPassword);

        } else {
            return JsonErrors::getErrorEmailNoExist();
        }


    }

    public function confirmResetPassword($email, $token)
    {

        $resetPasswordObject = ResetPasswordPlacUser::where("email_user", $email)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($resetPasswordObject != null || $resetPasswordObject != "") {


            $created_at = $resetPasswordObject->created_at;
            $created_at = \DateTime::createFromFormat("Y-m-d H:i:s", $created_at);
            $timeNow = date("Y-m-d H:i:s");
            $timeNow = \DateTime::createFromFormat("Y-m-d H:i:s", $timeNow);
            $interval = date_diff($created_at, $timeNow);

            $hours_spent = $interval->format('%h');

            if ($hours_spent < 2) {
                if ($resetPasswordObject->token == $token) {
                    return View::make('placapp.user.formchangepasword', ['email_user' => $email]);
                } else {
                    return "No match";
                }
            } else {
                return "Tiempo agotado";
            }
        } else {
            return JsonErrors::getErrorEmailNoExist();
        }

    }


    public function confirmChangePasswordReset(Request $request)
    {


        $email_user = $request->email_user;
        $password = $request->password;
        $repassword = $request->repassword;

        $resetPassword = ResetPasswordPlacUser::where('email_user', $email_user)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($resetPassword->isUsed) {
            return "contraseña ya cambiada";
        } else {
            $resetPassword->isUsed = true;
            $resetPassword->save();
            $placUser = PlacUser::where('email_user', $email_user)->first();
            if ($placUser != null || $placUser != "") {
                $hash = Encrypt::hashSSHA($repassword);
                $encrypted_password = $hash["encrypted"]; // encrypted password
                $salt = $hash["salt"]; // salt
                $placUser->encrypted_password = $encrypted_password;
                $placUser->salt = $salt;
                $placUser->isMigration = false;
                $placUser->save();
                return "contraseña cambiada";
            }
        }
    }


    public function sendConfirmationNewUser($placUser, $confirmation_code)
    {


        Mail::send('emails.user.confirmation.newuser', ['email_user' => $placUser->email_user, 'name_user' => strtoupper($placUser->name_user), 'confirmation_code' => $confirmation_code], function ($m) use ($placUser) {

            $m->from('no-reply@placapp.com', 'PLAC BIENVENIDO');
            $m->to($placUser->email_user, $placUser->email_user)->subject('Confirmar correo electronico');
        });
    }


    public function confirmNewUser($email_user, $confirmation_code)
    {
        $email_user_clean = Sanitize::sanitize_html_string($email_user);
        $confirmation_code_clean = Sanitize::sanitize_html_string($confirmation_code);
        $placUser = PlacUser::where('email_user', $email_user_clean)
            ->where('confirmation_code', $confirmation_code_clean)->first();
        if ($placUser != null || $placUser != "") {
            $email_verified = $placUser->email_verified;
            if (!$email_verified) {
                $placUser->email_verified = true;
                $placUser->save();
//definir vistas
                return "email correctamente verificado";
            } else {
                return "email ya   verificado";
            }

        } else {
            return " no existe el usaurio  verificado";
        }

    }

}

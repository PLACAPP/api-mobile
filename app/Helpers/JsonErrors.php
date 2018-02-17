<?php
/**
 * Created by PhpStorm.
 * User: arang
 * Date: 03/06/2016
 * Time: 15:41
 */

namespace Plac\Helpers;


class JsonErrors
{


    static public function getErrorEmailAlreadyExist()
    {
        $error_email = array();
        $error_email['error']['code'] = 225;
        $error_email['error']['title'] = "Email already exist";
        $error_email['error']['detail'] = "El usuario ya existe en la base de datos, por favor inicie sesión ";
        return json_encode($error_email);
    }
    
    
      static public function getErrorEmailAlreadyExistForm()
    {
        $error_email = array();
        $error_email['error']['code'] = 224;
        $error_email['error']['title'] = "Email already exist";
        $error_email['error']['detail'] = "El usuario tiene una cuenta creada a traves del formulario inicia sesi";
        return json_encode($error_email);
    }

    static public function getErrorEmailNoExist()
    {
        $error_email = array();
        $error_email['error']['code'] = 225;
        $error_email['error']['title'] = "Email not exist";
        $error_email['error']['detail'] = "El correo electronico que ingresaste no existe en placapp ";
        return json_encode($error_email);
    }

    static public function getErrorPasswordDontMatch()
    {
        $error = array();
        $error['error']['code'] = 226;
        $error['error']['title'] = "Password do not match";
        $error['error']['detail'] = "La contraseña que ingresaste no concuerda con el correo ingresado";
        return json_encode($error);
    }

    static public function getErrorEmptyField($name_field)
    {
        $error = array();

        $error['error']['code'] = 227;
        $error['error']['title'] = "Campo vacio";
        $error['error']['detail'] = "El campo " . $name_field . " debe ser ingresado";

        return json_encode($error);
    }

    static public function getErrorObjectNotExist($name_object)
    {
        $error = array();

        $error['error']['code'] = 228;
        $error['error']['title'] = "El objeto no existe";
        $error['error']['detail'] = "Este  " . $name_object . " no existe en la base de datos";

        return json_encode($error);
    }
}
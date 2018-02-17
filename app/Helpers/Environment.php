<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plac\Helpers;

/**
 * Description of Environment
 *
 * @author carlosarango
 */
class Environment {

    public static function getServerNameApiCurrentEnvironment() {
        $env = config("app.env");
        $serverName='http://api.placapp.com/';
        if ($env == 'local') {
            $serverName='http://apidev.placapp.com/';
        }
        return $serverName;
    }
    
    public static function getServerNameMainCurrentEnvironment() {
        $env = config("app.env");
        $serverName='https://www.placapp.com/';
        if ($env == 'local') {
            $serverName='https://www.placapp.com/';
        }
        return $serverName;
    }

}

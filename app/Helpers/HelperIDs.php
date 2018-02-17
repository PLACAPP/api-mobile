<?php

namespace Plac\Helpers;
class HelperIDs
{

    static function generateID()
    {
        $numLowerCase = 4;
        $numUpperCase = 3;
        $numNumbers = 3;
        //
        $listNumbers = '0123456789abcdefghijklmnopqrstuvwxyz';
        $listLowerCase = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $listUpperCase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return str_shuffle(
            substr(str_shuffle($listLowerCase), 0, $numLowerCase) .
            substr(str_shuffle($listUpperCase), 0, $numUpperCase) .
            substr(str_shuffle($listNumbers), 0, $numNumbers)
        );


    }
}
<?php

/**
 * Created by PhpStorm.
 * User: arang
 * Date: 04/06/2016
 * Time: 21:00
 */

namespace Plac\Helpers;

use DateTime;

class DateUtils {

    static public function getCurrentDate() {
        return date("Y-m-d H:i:s");
    }

    static public function getTimeAgo($currentDate, $oldDate) {

        $oldDate = new DateTime($oldDate);
        $sinceThen = $oldDate->diff($currentDate);


        if ($sinceThen->y != 0) {
            $years = $sinceThen->y;

            return "Hace " . $years . " año" . (self::getS($years, "years"));
        }
        if ($sinceThen->m != 0) {
            $month = $sinceThen->m;
            return "Hace " . $month . " mes" . (self::getS($month, "month"));
        }
        if ($sinceThen->d != 0) {
            $day = $sinceThen->d;
            return "Hace " . $day . " día" . (self::getS($day, "day"));
        }
        if ($sinceThen->h != 0) {
            $hours = $sinceThen->h;
            return "Hace " . $hours . " hora" . (self::getS($hours, "hours"));
        }
        if ($sinceThen->i != 0) {
            $minutes = $sinceThen->i;
            return "Hace " . $minutes . " minuto" . (self::getS($minutes, "minutes"));
        }
        if ($sinceThen->s != 0) {
            $seconds = $sinceThen->s;
            return "Hace " . $seconds . " segundo" . (self::getS($seconds, "seconds"));
        }
    }

    static function getS($value, $type) {
        $response = "";
        if ($value > 1) {
            $response = "s";
            if ($type == "month") {
                $response = "es";
            }
        }
        return $response;
    }

}

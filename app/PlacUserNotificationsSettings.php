<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PlacUserNotificationsSettings extends Model {

    public $table = "plac_users_notifications_settings";
    protected $primaryKey = 'notification_setting_id';
    public $incrementing = false;
    public $timestamps = true;

}

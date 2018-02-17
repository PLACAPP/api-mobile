<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class DeviceSession extends Model {

    protected $table = 'device_session';
    protected $primaryKey = 'device_session_id';
    public $incrementing = false;

}

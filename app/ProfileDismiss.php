<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class ProfileDismiss extends Model {

    public $timestamps = false;
    public $incrementing = false;
    protected $table = "profiles_dismisses";

}

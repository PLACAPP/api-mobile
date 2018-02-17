<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Installation extends Model {

    public $timestamps = true;
    protected $primaryKey = 'installation_id';
    protected $table = "installations";

}

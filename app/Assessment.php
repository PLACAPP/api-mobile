<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model {

    protected $table = 'assessment_place';
    protected $primaryKey = 'assessment_id';
    public $incrementing = false;
    public $timestamps = true;

}

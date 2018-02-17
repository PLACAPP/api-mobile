<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model {

    protected $table = 'billings';
    protected $primaryKey = 'billing_id';
    public $incrementing = false;

}

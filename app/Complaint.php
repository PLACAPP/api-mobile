<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complaint_categories';
    protected $primaryKey = 'complaint_id';
    public $incrementing = false;
}

<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class PostReports extends Model
{
    protected $table = 'post_complaints';
    protected $primaryKey = 'post_complaint_id';
    public $incrementing = false;
}

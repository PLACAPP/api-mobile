<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Breed extends Model
{
    protected $table = 'breeds';
    protected $primaryKey = 'breed_id';
    public $incrementing = false;
}

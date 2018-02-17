<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
   public $timestamps = true;

    public function profile()
    {
        return $this->belongsTo('Plac\Profile','id_profile','id_profile');
    }
}

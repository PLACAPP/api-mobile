<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class AdvertisementsLikes extends Model
{
    protected $table = 'advertisements_likes';
    protected $primaryKey = 'advertisement_like_id';
    public $incrementing = false;
}

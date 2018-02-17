<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Advertisements extends Model {

    protected $table = 'advertisements';
    protected $primaryKey = 'advertisements_id';
    public $incrementing = false;

    public function campaign()
    {
        return $this->belongsTo('Plac\Campaign', 'campaign_id', 'campaign_id');
    }



    public function comments()
    {
        return $this->hasMany('Plac\AdvertisementsComments', 'advertisement_id', 'advertisements_id');

    }

    public function commentsCount()
    {
        return $this->comments()
            ->selectRaw('advertisement_id,count(*) as count')
            ->groupBy('advertisement_id');
    }

    public function likes()
    {
        return $this->hasMany('Plac\AdvertisementsLikes', 'advertisement_id', 'advertisements_id');
    }


    public function likesCount()
    {
        return $this->likes()
            ->selectRaw('advertisement_id,count(*) as count')
            ->groupBy('advertisement_id');
    }

}

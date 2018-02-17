<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class Event extends Model {

    protected $table = 'events';
    protected $primaryKey = 'event_id';
    public $incrementing = false;

    protected $fillable=["event_id","place_id","event_name","event_description_short"];
    
    
    
     public function place()
    {
        return $this->belongsTo('Plac\Place','place_id','place_id');
    }
    
    
    public function city(){
         return $this->belongsTo('Plac\City','event_city','city_id'); 
    }
    
     public function assistants(){
         return $this->hasMany('Plac\EventAssistants','event_id','event_id'); 
    }
    
    
    

}

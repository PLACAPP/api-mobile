<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class ReportLostPet extends Model
{
    protected  $primaryKey="report_id";
    protected  $table="report_lost_pets";
    
     public function petBreed()
    {
        return $this->hasOne('Plac\Breed','breed_id','breed_id');
    }
    
}

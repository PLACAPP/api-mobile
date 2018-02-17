<?php

namespace Plac;

use Illuminate\Database\Eloquent\Model;

class EventAssistants extends Model
{
     protected $table = 'event_assistants';
    protected $primaryKey = 'event_assistant_id';
    public $incrementing = false;
}

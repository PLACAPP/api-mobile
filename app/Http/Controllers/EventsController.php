<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;
use Plac\Event;

class EventsController extends Controller {

    // get events paginate 10
    public function getEvents() {

        $events = Event::where("event_state", true)->with("city")->with("assistants")->paginate(10);
        return $events;
    }

    public function getEventsByType($type, $profileFromId) {
        
        if ($type == "free" || $type == "pay") {
         
            $events =  Event::where("event_state", 1 )
                            ->where("event_type", $type)
                            ->whereRaw("event_datetime_end > NOW()")
                            ->with("city")
                            ->with("assistants")
                            ->paginate(20);
            
            $events = $this->checkEventsAssistant($events, $profileFromId);
            $events= $this->getAssistantsNumber($events);
            return $events;
        } else {
            return "Error ejecutando la consulta";
        }
    }

    public function checkEventsAssistant($events, $profileFromId) {
        $i = 0;
        foreach ($events as $event) {

            $countAssistance = \Plac\EventAssistants::where("event_id", $event->event_id)->where("profile_id", $profileFromId)->count();
            if ($countAssistance == 1) {
                $events[$i]["profileWillAssist"] = 1;
            } else {
                $events[$i]["profileWillAssist"] = 0;
            }
            $i++;
        }

        return $events;
    }

    public function getAssistantsNumber($events) {
        $i = 0;
        foreach ($events as $event) {
            $countAssistants = \Plac\EventAssistants::where("event_id", $event->event_id)->count();
            $events[$i]["possiblesAssistants"] = $countAssistants;
            $i++;
        }
        return $events;
    }

}

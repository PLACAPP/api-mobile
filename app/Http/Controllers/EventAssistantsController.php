<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;

class EventAssistantsController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $response = ["save" => "ok", "message" => "Ya te encuentras registrado en este evento"];
        $event_id = $request['event_id'];
        $profile_id = $request['profile_id'];

        if (!$this->checkEventAssistant($event_id, $profile_id)) {
            $eventAssistant = new \Plac\EventAssistants();
            $eventAssistant->event_assistant_id = $this->generateUniqueId();
            $eventAssistant->event_id = $event_id;
            $eventAssistant->profile_id = $profile_id;
            $response['message'] = "Se ha confirmado la asistencia al evento";
            $eventAssistant->save();
        }

        return json_encode($response);
    }

    private function checkEventAssistant($event_id, $profile_id) {

        return \Plac\EventAssistants::where("event_id", $event_id)->where("profile_id", $profile_id)->first();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = \Plac\EventAssistants::where('event_assistant_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    public function manageEventAssistance($eventId, $profileId) {

        $type = "";
        $eventAssistant = $this->checkEventAssistant($eventId, $profileId);
        if ($eventAssistant == null) {
            $eventAssistant = new \Plac\EventAssistants();
            $eventAssistant->event_assistant_id = $this->generateUniqueId();
            $eventAssistant->event_id = $eventId;
            $eventAssistant->profile_id = $profileId;
            $eventAssistant->save();
            $type = "assistance_added";
        } else {
            $eventAssistant->delete();
            $type = "assistance_canceled";
        }

        return JsonObjects::createJsonObjectModel($type, $eventAssistant->event_assistant_id, $eventAssistant);
    }

}

<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;

class ProfileDismissController extends Controller {

    /**
     * Get dismissed profiles by profile
     *
     * @param  String profileFromId
     * @return Array<String> profiles id 
     */
    public function getProfilesDismissed($profileFromId) {
        $collection = collect(\Plac\ProfileDismiss::where('profile_from_id', $profileFromId)->get());
        $plucked = $collection->pluck('profile_to_id');
        return $plucked;
    }
    
    
      /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request) {
        $profileFromId=$request['profile_from_id'];
        $profileToId=$request['profile_to_id'];
        $profileDismiss=new \Plac\ProfileDismiss();
        $profileDismiss->profile_from_id=$profileFromId;
        $profileDismiss->profile_to_id=$profileToId;
        $profileDismiss->save();
        return $profileDismiss;

    }

}

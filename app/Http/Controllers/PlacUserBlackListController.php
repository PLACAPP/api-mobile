<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;
use Plac\Helpers\HelperIDs;
use Plac\Profile;
use Plac\Helpers\JsonObjects;
use Carbon\Carbon;

class PlacUserBlackListController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
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


        $placUserFromId = $request['plac_user_from_id'];
        $placUserToId = $request['plac_user_to_id'];
        $placUserBlackList = new \Plac\PlacUserBlackList();
        $placUserBlackList->plac_user_black_list_id = $this->generateUniqueId();
        $placUserBlackList->plac_user_from_id = $placUserFromId;
        $placUserBlackList->plac_user_to_id = $placUserToId;
        $placUserBlackList->created_at=Carbon::now();
        try {

            $placUserBlackList->save();
            $this->removeFollowings($placUserFromId, $placUserToId);
            return 'plac_user_added_black_list';
    } catch (\Illuminate\Database\QueryException $e) {
            echo 'plac_user_already_added';
        }
    }

    public function getProfilesByUser($placUserId) {

        return \Plac\Profile::where('plac_user_id', $placUserId)->get();
    }

    public function removeFollowings($placUserFromId, $placUserToId) {
        $profilesFrom = $this->getProfilesByUser($placUserFromId);
        $profilesTo = $this->getProfilesByUser($placUserToId);

        if (count($profilesFrom) > 0 && count($profilesTo) > 0) {

            foreach ($profilesFrom as $profileFrom) {
                foreach ($profilesTo as $profileTo) {
                    $follower = \Plac\Follower::
                                    where('profile_from_id', $profileFrom->profile_id)
                                    ->where('profile_to_id', $profileTo->profile_id)->first();
                    if ($follower != null) {
                        $follower->delete();
                    }
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        return \Plac\PlacUserBlackList::where('plac_user_from_id', $id)->with('placUserTo')->get();
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

    // MANAGE BLACK LIST ADD OR REMOVE 
    public function manageBlackList(Request $request) {
        $placUserFromId = $request['plac_user_from_id'];
        $placUserToId = $request['plac_user_to_id'];
        $type = "";
        $placUserBlackList = \Plac\PlacUserBlackList::
                          where('plac_user_from_id', $placUserFromId)
                        ->where('plac_user_to_id', $placUserToId)->first();
        if ($placUserBlackList == null) {

            $placUserBlackList = new \Plac\PlacUserBlackList();
            $placUserBlackList->plac_user_black_list_id = $this->generateUniqueId();
            $placUserBlackList->plac_user_from_id = $placUserFromId;
            $placUserBlackList->plac_user_to_id = $placUserToId;
            
            $placUserBlackList->save();
            $this->removeFollowings($placUserFromId, $placUserToId);
            $type= 'plac_user_added_black_list';
        } else {
            $placUserBlackList->delete();
            $type = 'plac_user_removed_black_list';
        }
        return JsonObjects::createJsonObjectModel($type, $placUserBlackList->plac_user_black_list_id, $placUserBlackList);
    }

    public function getPlacUserListBlackListFrom($placUserId) {
        $collection = collect(\Plac\PlacUserBlackList::where('plac_user_from_id', $placUserId)->get());
        $plucked = $collection->pluck('plac_user_to_id');

        return $plucked;
    }

    public function getPlacUserListBlackListTo($placUserId) {
        $collection = collect(\Plac\PlacUserBlackList::where('plac_user_to_id', $placUserId)->get());
        $plucked = $collection->pluck('plac_user_from_id');

        return $plucked;
    }

    public static function removeProfilesByBlackList($profileFromId, $profiles) {
        $placUserBlackList = new PlacUserBlackListController();
        $profile = Profile::where('profile_id', $profileFromId)->first();
        $placUserId = $profile->plac_user_id;

        $placUsersTo = $placUserBlackList->getPlacUserListBlackListFrom($placUserId);
        $placUsersFrom = $placUserBlackList->getPlacUserListBlackListTo($placUserId);


        $i = 0;
        foreach ($profiles as $profile) {
            $placUserId = $profile['plac_user']['plac_user_id'];
            foreach ($placUsersFrom as $placUserFrom) {
                if ($placUserId == $placUserFrom) {
                    $profiles[$i] = null;
                }
            }

            foreach ($placUsersTo as $placUserTo) {
                if ($placUserId == $placUserTo) {
                    $profiles[$i] = null;
                }
            }
            $i++;
        }



        return $profiles;
    }

    public function generateUniqueId() {

        $idGenerated = HelperIDs::generateID();
        $count_exist_id = \Plac\PlacUserBlackList::where('plac_user_black_list_id', $idGenerated)->count();

        if ($count_exist_id == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}

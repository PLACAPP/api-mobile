<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;
use Plac\Helpers\HelperIDs;

class PlacUserNotificationsSettingsController extends Controller {

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
        //
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $placUserId 
     * @return \Illuminate\Http\Response
     */
    public function updateNotificationStateByPlacUserId(Request $request, $placUserId) {
        
        $notificationType = $request->notification_type;
        $state = $request->value;
      
        $placUserNotificationSetting = \Plac\PlacUserNotificationsSettings::where('plac_user_id', $placUserId)->first();
        if ($placUserNotificationSetting != null) {
            $placUserNotificationSetting = $this->getNotificationTypeToUpdate($placUserNotificationSetting, $notificationType, $state);
            $placUserNotificationSetting->save();
            return \Plac\Helpers\JsonObjects::responseJsonObject('notification_settings', 'updated_notification', $placUserNotificationSetting, 'NOTIFICATION UPDATE');
        } else {
            $placUserNotificationSetting = new \Plac\PlacUserNotificationsSettings();
            $placUserNotificationSetting->notification_setting_id = $this->generateUniqueId();
            $placUserNotificationSetting->plac_user_id = $placUserId;
            $placUserNotificationSetting = $this->getNotificationTypeToUpdate($placUserNotificationSetting, $notificationType, $state);
            $placUserNotificationSetting->save();
            return \Plac\Helpers\JsonObjects::createJsonObjectCreated('notification_settings', true);
        }
    }

    public function getNotificationTypeToUpdate($placUserNotificationSetting, $notificationType, $state) {
        switch ($notificationType) {
            case 'notification_store_state':
                $placUserNotificationSetting->notification_store_state = $state;
                break;
            case 'notification_followers_state':
                $placUserNotificationSetting->notification_followers_state = $state;
                break;
            case 'notification_posts_state':
                $placUserNotificationSetting->notification_posts_state = $state;
                break;
        }
        return $placUserNotificationSetting;
    }

    public function getNotificationsSettingsByPlacUserId($placUserId) {
        $placUserNotificationSetting = \Plac\PlacUserNotificationsSettings::where('plac_user_id', $placUserId)->first();
        return \Plac\Helpers\JsonObjects::responseJsonObject('notification_settings', 'fetch_notification', $placUserNotificationSetting, 'NOTIFICATION all');
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id = \Plac\PlacUserNotificationsSettings::where('notification_setting_id', $idGenerated)->count();

        if ($count_exist_id == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}

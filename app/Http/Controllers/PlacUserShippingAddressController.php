<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Helpers\HelperIDs;

class PlacUserShippingAddressController extends Controller {


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
     
        $placUserName = $request->plac_user_name;
        $placUserTelephone = $request->plac_user_telephone;
        $placUserAddress = json_encode($request->plac_user_address);
        $placUserEmail = $request->plac_user_email;
        $placUserNeighborhood = $request->plac_user_neighborhood;
       
        $cityId = $request->city_id;
        $placUserId = $request->plac_user_id;

        $placUserShippingAddressId = $this->generateUniqueId();
        $placUserShippingAddress = new \Plac\PlacUserShippingAddress();
        $placUserShippingAddress->plac_user_shipping_address_id = $placUserShippingAddressId;
        $placUserShippingAddress->plac_user_name = $placUserName;
        $placUserShippingAddress->plac_user_email = $placUserEmail;
        $placUserShippingAddress->plac_user_telephone = $placUserTelephone;
        $placUserShippingAddress->city_id = $cityId;
        $placUserShippingAddress->plac_user_neighborhood = $placUserNeighborhood;
        $placUserShippingAddress->plac_user_address = $placUserAddress;
        if (isset($request->plac_user_additional_info)) {
            $placUserShippingAddress->plac_user_additional_info = $request->plac_user_additional_info;
        }      
        $placUserShippingAddress->plac_user_id = $placUserId;
    
        $placUserShippingAddress->isMain = true;
        $placUserShippingAddress->save();

        if ($this->updateShippingAddressMain($placUserShippingAddressId, $placUserId)) {
            return \Plac\Helpers\JsonObjects::responseJsonObject("shipping_address", "success", $placUserShippingAddress, "Dirección creada");
        }
    }
    
      /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        return \Plac\PlacUserShippingAddress::where('plac_user_id', $id)->with('city')->get();
    }


    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $placUserName = $request->plac_user_name;
        $placUserTelephone = $request->plac_user_telephone;
        $placUserAddress = $request->plac_user_address;
        $placUserEmail = $request->plac_user_email;
        $cityId = $request->city_id;
        $placUserShippingAddress = \Plac\PlacUserShippingAddress::where('plac_user_shipping_address_id', $id)->first();
        $placUserShippingAddress->plac_user_name = $placUserName;
        $placUserShippingAddress->plac_user_telephone = $placUserTelephone;
        $placUserShippingAddress->plac_user_address = $placUserAddress;
        $placUserShippingAddress->plac_user_email = $placUserEmail;
        $placUserShippingAddress->city_id = $cityId;
        $placUserShippingAddress->save();

        return \Plac\Helpers\JsonObjects::responseJsonObject("shipping_address", "updated", $placUserShippingAddress, "Dirección actualizada");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        if (\Plac\PlacUserShippingAddress::destroy($id) == 1) {
            return \Plac\Helpers\JsonObjects::responseJsonObject("shipping_address", "deleted", null, "Dirección eliminada");
        }
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id = \Plac\PlacUserShippingAddress::where('plac_user_shipping_address_id', $idGenerated)->count();

        if ($count_exist_id == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    /**
     * Update Shipping Address To Main
     *
     * @param  string  $placUserShippingAddressId
     * @param  string  $placUserId
     * @return $idGenerated
     */
    public function updateShippingAddressMain($placUserShippingAddressId, $placUserId) {
        $placUserShippingAddresses = \Plac\PlacUserShippingAddress::where("plac_user_id", $placUserId)
                ->get();
        if (count($placUserShippingAddresses) > 0) {
            for ($i = 0; $i < count($placUserShippingAddresses); $i++) {
                $placUserShippingAddress = $placUserShippingAddresses[$i];
                if ($placUserShippingAddressId == $placUserShippingAddress->plac_user_shipping_address_id) {
                    $placUserShippingAddress['isMain'] = true;
                } else {
                    $placUserShippingAddress['isMain'] = false;
                }
                $placUserShippingAddress->save();
            }
        }
        return $placUserShippingAddresses;
    }

}

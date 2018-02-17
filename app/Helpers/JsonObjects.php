<?php
/**
 * Created by PhpStorm.
 * User: arang
 * Date: 03/06/2016
 * Time: 16:50
 */

namespace Plac\Helpers;


class JsonObjects
{


    static public function createJsonObjectModel($type = null, $id, $attributes)
    {
        $jsonResponse = array("data" => "");
        $jsonResponse['data'] = ['type' => $type, 'id' => $id, 'attributes' => $attributes];
        return json_encode($jsonResponse);
    }

    
    
    static public function createJsonObjectsList($type = null, $list)
    {
        $jsonResponse = array("data" => "");
        $jsonResponse['data'] = ['type' => $type, 'list' => $list];
        return json_encode($jsonResponse);
    }
    
     static public function createJsonObjectCreated($type,$succes)
    {
        $jsonResponse = array("data" => "");
        $jsonResponse['data'] = ['type' => $type, 'success' =>$succes ];
        return json_encode($jsonResponse);
    }
      static public function responseJsonObject($type,$responseType,$object,$message)
    {
        $jsonResponse = array("data" => "");
        $jsonResponse['data'] = ['type' => $type, 'response_type'=>$responseType,'message' =>$message,"object"=>$object ];
        
        return json_encode($jsonResponse);
    }
    static public function createJsonObjectModelRelationShip($type = null, $id, $attributes, $arrayRelationsIn)
    {
        $jsonResponse = array("data" => "");
        $jsonResponse['data'] = ['type' => $type, 'id' => $id, 'attributes' => $attributes,'relationship' => $arrayRelationsIn];


        return json_encode($jsonResponse);


    }
    
}

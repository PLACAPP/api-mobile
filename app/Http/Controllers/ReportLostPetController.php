<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;
use Plac\Helpers\HelperIDs;
use Plac\ReportLostPet;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class ReportLostPetController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $reports = ReportLostPet::paginate(20);
        return \Plac\Helpers\JsonObjects::createJsonObjectsList("reports", $reports);
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


        $reportId = $this->generateUniqueId();
        $reportLostPet = new ReportLostPet();
        $reportLostPet->report_id = $reportId;
        $reportLostPet->pet_name = $request->pet_name;

        $reportImage = $request['report_image'];
    
        $reportLostPet->pet_type = $request->pet_type;
        $reportLostPet->breed_id = $request->pet_breed_id;
        $reportLostPet->user_name_who_report = $request->user_name_who_report;
        $reportLostPet->user_email_who_report = $request->user_email_who_report;
        $reportLostPet->user_telephone_who_report = $request->user_telephone_who_report;
        $reportLostPet->address_lost_pet = $request->address_lost_pet;
        $reportLostPet->latitude_lost_pet = $request->latitude_lost_pet;
        $reportLostPet->longitude_lost_pet = $request->longitude_lost_pet;
        $reportLostPet->description = $request->description;
        $reportLostPet->isPetFromUser = $request->isPetFromUser;
        $reportLostPet->user_id = $request->user_id;


        new CloudinaryController();
        //proccess image
        $filename = time() . '.' . $reportImage->getClientOriginalExtension();
        $path = public_path('images/profile/' . $filename);
        Image::make($reportImage->getRealPath())->save($path);
        ImageController::compressImage($path);

        $pathCloudinary = "reports/report";
        $imagePathCloudinary = ImageController::saveImageOnCloudinary($path, $reportId, $pathCloudinary);
        // delete image plac server
        unlink($path);
        $reportLostPet->pet_path_image = $imagePathCloudinary;
        $reportLostPet->save();

        echo \Plac\Helpers\JsonObjects::createJsonObjectCreated("report_lost_pet", true);
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
        
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist_id_user = ReportLostPet::where('report_id', $idGenerated)->count();

        if ($count_exist_id_user == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    public function getReportsAround($latitude, $longitude, $distance) {

        $reports = DB::select('SELECT rp.* ,br.breed_id,br.breed_name, ( 6371 * ACOS( COS( RADIANS(' . $latitude . ' ) ) * COS( RADIANS(  `latitude_lost_pet` ) ) * COS( RADIANS( `longitude_lost_pet` ) - RADIANS( ' . $longitude . ' ) ) + SIN( RADIANS( ' . $latitude . ' ) ) * SIN( RADIANS(  `latitude_lost_pet` ) ) ) ) AS calculated_distance
                                FROM report_lost_pets rp,breeds br
                                WHERE rp.breed_id=br.breed_id
                                HAVING calculated_distance <=' . $distance .
                        ' ORDER BY created_at DESC ');

        return \Plac\Helpers\JsonObjects::createJsonObjectsList("reports", $reports);
    }

    public function getPlacesAround($latitude, $longitude, $distance) {

        $reports = DB::select('SELECT rp.* ,br.breed_id,br.breed_name, ( 6371 * ACOS( COS( RADIANS(' . $latitude . ' ) ) * COS( RADIANS(  `latitude_lost_pet` ) ) * COS( RADIANS( `longitude_lost_pet` ) - RADIANS( ' . $longitude . ' ) ) + SIN( RADIANS( ' . $latitude . ' ) ) * SIN( RADIANS(  `latitude_lost_pet` ) ) ) ) AS calculated_distance
                                FROM report_lost_pets rp,breeds br
                                WHERE rp.breed_id=br.breed_id
                                HAVING calculated_distance <=' . $distance .
                        ' ORDER BY created_at DESC ');

        return \Plac\Helpers\JsonObjects::createJsonObjectsList("reports", $reports);
    }

    public function getReportsMadeByUser($user_id) {
        $reports = ReportLostPet::where("user_id", $user_id)->with('petBreed')->get();
        return \Plac\Helpers\JsonObjects::createJsonObjectsList("reports", $reports);
    }

}

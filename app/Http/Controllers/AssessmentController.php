<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Assessment;
use Plac\Helpers\HelperIDs;
use Plac\Sales;
use Carbon\Carbon;

class AssessmentController extends Controller {

    public function store(Request $request) {

        $assesments = $request->assessment;
        $assesmentArray = json_decode(\Plac\Helpers\Encrypt::decodedDataApp($assesments), true);
        $assessment = new Assessment();
        $assessment->assessment_id = $this->generateUniqueId();
        $assessment->order_id = $assesmentArray['order_id'];
        $assessment->assessment_type = $assesmentArray["assessment_type"];
        $assessment->assessment_quantity = $assesmentArray["assessment_quantity"];
        $assessment->save();
        return \Plac\Helpers\JsonObjects::responseJsonObject("assessment", 'sale_assess_added', $assessment, "La valoracion ");
    }

    /* Generate a unique id of assessment */

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count_exist = Assessment::where('assessment_id', $idGenerated)->count();
        if ($count_exist == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}

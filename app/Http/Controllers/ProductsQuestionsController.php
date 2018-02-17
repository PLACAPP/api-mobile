<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;

class ProductsQuestionsController extends Controller {

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


        $productQuestion = new \Plac\ProductQuestion();
        $productQuestion->question_id = $this->generateUniqueId();
        // get product
        $product = $request->product;
        $productQuestion->product_id = $product['product_id'];
        // get plac user
        $placUser = $request->plac_user;
        $productQuestion->plac_user_id = $placUser['plac_user_id'];
        // question txt
        $productQuestion->question_txt = $request->question_txt;
        $productQuestion->state = 'NEW';
        $productQuestion->save();
        $placeEmail = $product['place_location']['place']['place_email'];
        $productQuestion = json_encode($productQuestion);
        $productQuestion = json_decode($productQuestion, true);
        $productQuestion['product'] = $product;
        $productQuestion['plac_user'] = $placUser;
        NotificationStoreController::sendEmailNewQuestionToSeller($placeEmail, $productQuestion);
        $this->sendNotificationNewQuestionToPlace($productQuestion);
        return $productQuestion;
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

        $productQuestion = \Plac\ProductQuestion::where('question_id', $id)->with('placUser.installation');
        if ($productQuestion == null) {
            abort(404);
        }


        $productQuestion->answer_txt = $request->answer_txt;
        $productQuestion->state = 'ANSWERED';
        $productQuestion->save();

        return $productQuestion;
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
     * GET product Questions  by productId
     *
     * @param  varchar $productId
     * @return \Illuminate\Http\Response
     */
    public function getQuestionsByProduct($productId) {
        return \Plac\ProductQuestion::where('product_id', $productId)
                        ->with('placUser')->orderBy('created_at', 'desc')->get();
    }

    public function getQuestionsByUser($placUserId) {
        $productsQuestion = \Plac\ProductQuestion::where('plac_user_id', $placUserId)
                        ->with('product.placeLocation.place')->orderBy('created_at', 'desc')->get();
        $productsController = new ProductsController();
        $i = 0;
        $productsQuestion=json_decode($productsQuestion);
        foreach ($productsQuestion as $productQuestion) {
            $productArray[0] = $productQuestion->product;
            $product = $productsController->getProductsColumnsDecode($productArray)[0];
            $productsQuestion[$i]->product=$product;
            $i++;
        }


        return $productsQuestion;
    }

    public function sendNotificationNewQuestionToPlace($productQuestion) {
        $productQuestionId = $productQuestion['question_id'];
        $questionTxt = $productQuestion['question_txt'];
        $product = $productQuestion['product'];
        $placeId = $product['place_location']['place']['place_id'];
        $url = \Plac\Helpers\Environment::getServerNameMainCurrentEnvironment() . "productos/preguntas/usuario/nueva/" . $productQuestionId;
        $content = $questionTxt;
        $title = "Nueva pregunta sobre " . $product['product_name'];
        NotificationStoreController::sendNotificationToPlace($placeId, $url, $title, $content);
        $type = "product-questions";
        NotificationStoreController::saveNotification($url, $type, $title, $content, $placeId);
    }

    public function generateUniqueId() {
        $idGenerated = \Plac\Helpers\HelperIDs::generateID();
        $count = \Plac\ProductQuestion::where('question_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}

<?php


namespace Plac\Http\Controllers;

use Parse\ParseClient;
use Plac\Http\Requests;
use Plac\Http\Controllers\Controller;
use Auth;
use View;


class ParseTemporalController extends Controller
{
    public function __construct() {
        $PARSE_APP_KEY ="hbmdERnXEzYJaBbCw8iVQImj60pISpLlGJ4NTcSE";
        $PARSE_REST_KEY = "TnNDfkBEx5w0WnMNW6k9Fr0nulTSJHa5aGvBC5Gl";
        $PARSE_MASTER_KEY = "97CqVCLYn6BolTtaABPp9MhKkgag4wn4scqFotFO";
        ParseClient::initialize($PARSE_APP_KEY, $PARSE_REST_KEY, $PARSE_MASTER_KEY);
    }
}


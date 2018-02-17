<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;

use Plac\Http\Requests;

class CloudinaryController extends Controller
{

    public function __construct() {
        \Cloudinary::config(array(
            "cloud_name" =>  config("app.CLOUDINARY_CLOUD_NAME"),
            "api_key" => config("app.CLOUDINARY_API_KEY"),
            "api_secret" => config("app.CLOUDINARY_API_SECRET"),
        ));
    }
}

<?php

namespace Plac\Http\Controllers;

use Imagick;
use Plac\Http\Requests;

class ImageController extends Controller {

    public static function compressImage($path) {

        $img = new Imagick();
        $img->readImage($path);
        $img->setImageCompression(imagick::COMPRESSION_JPEG);
        $img->setImageCompressionQuality(70);
        $img->stripImage();
        $img->writeImage($path);
    }

    public static function saveImageOnCloudinary($path, $profile_id, $pathCloudinary) {
        new CloudinaryController();

        $arrayCloudinary = array("public_id" => $pathCloudinary . $profile_id);
        $data = \Cloudinary\Uploader::upload($path, $arrayCloudinary);
        return $data['url'];
    }
    
      public static  function base64_to_jpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb");

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);

        return $output_file;
    }

}

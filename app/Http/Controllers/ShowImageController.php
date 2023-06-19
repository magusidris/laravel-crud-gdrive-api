<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yaza\LaravelGoogleDriveStorage\Gdrive;
use File;

class ShowImageController extends Controller
{
    public function __invoke($file) 
    {
        // $image = Gdrive::get('dXI7SmWoaWrW5ujlZkTQj6tfEY4JMI4xcq8jhHlR.jpg');
        $image = Gdrive::get($file);

        return response($image->file, 200)->header('Content-Type', $image->ext);
    }
}

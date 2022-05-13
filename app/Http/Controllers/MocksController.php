<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;

class MocksController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $availableServices = array_filter(config("services"), function ($service){
            return $service['active'];
        });

        dd($availableServices);
    }

}

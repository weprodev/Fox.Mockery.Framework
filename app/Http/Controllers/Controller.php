<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function default(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Please add the service name as a prefix in the url.',
            'example' => '{domain}/service_name/*',
            'services' => [
                'description' => 'You can manage your services in config directory.',
                'available' => getAvailableServices(),
                'all' => getAllServices(),
            ],
            'data' => [
                'method' => $request->method(),
                'request' => $request->all(),
                'header' => $request->header(),
            ],
        ]);
    }
}

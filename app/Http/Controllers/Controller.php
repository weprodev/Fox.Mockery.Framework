<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function default(Request $request)
    {
        $services = [
            'description' => 'You can manage your services in config directory.',
            'available' => getAvailableServices(),
            'all' => getAllServices(),
        ];

        if ($request->wantsJson()) {

            $data = [
                'message' => 'Please add the service name as a prefix in the url.',
                'example' => '{domain}/service_name/*',
                'data' => [
                    'method' => $request->method(),
                    'request' => $request->all(),
                    'header' => $request->header(),
                ],
            ];
            $data['services'] = $services;

            return response()->json($data);
        }

        return view('home', compact('services'));
    }
}

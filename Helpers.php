<?php

if (!function_exists('getAvailableServices')) {

    function getAvailableServices()
    {
        return array_filter(getAllServices(), function ($service) {
            return $service['active'];
        });
    }
}

if (!function_exists('getAllServices')) {

    function getAllServices()
    {
        return config("services");
    }
}

if (!function_exists('getServiceConfig')) {

    function getServiceConfig(string $service_name)
    {
        $services = config("services");
        if (!in_array($service_name, array_keys($services))) {
            throw new \Exception("$service_name IS NOT EXIST!");
        }
        return $services[$service_name];
    }
}

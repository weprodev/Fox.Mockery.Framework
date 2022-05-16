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

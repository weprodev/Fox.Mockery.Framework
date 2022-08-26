<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

if (! function_exists('getAvailableServices')) {

    function getAvailableServices()
    {
        return array_filter(getAllServices(), function ($service) {
            return $service['active'];
        });
    }
}

if (! function_exists('getAllServices')) {

    function getAllServices()
    {
        return config('fox_services');
    }
}

if (! function_exists('getServiceConfig')) {

    function getServiceConfig(string $service_name)
    {
        $services = config('fox_services');
        if (! in_array($service_name, array_keys($services))) {
            header('Location: '.url('/'));
            exit;
        }

        return $services[$service_name];
    }
}

if (! function_exists('mergingTwoJsonFile')) {

    function mergingTwoJsonFile($first_json_file, $second_json_file): string
    {
        $firstArrayContent = json_decode($first_json_file, true);
        $secondArrayContent = json_decode($second_json_file, true);
        $mergedContents = array_merge($firstArrayContent ?? [], $secondArrayContent ?? []);

        return json_encode($mergedContents, JSON_PRETTY_PRINT);
    }
}

if (! function_exists('getSchemaService')) {

    function getSchemaService($serviceName): string
    {
        $baseMockDirectory = base_path(config('fox_settings.base_directory'));
        $schemaFilePath = $baseMockDirectory.'/'.$serviceName.'/index.json';

        if (! file_exists($schemaFilePath)) {
            return '{}';
            throw new \Exception('Schema File Does Not Exist Or We Couldn\'t Open The File! '.$schemaFilePath);
        }

        return File::get($schemaFilePath);
    }
}

if (! function_exists('jsonResponse')) {

    function jsonResponse(array $data, int $statusCode = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return response()->json($data, $statusCode, $headers);
    }
}

if (! function_exists('scanDirectoryAndReturnFiles')) {

    function scanDirectoryAndReturnFiles(string $pathDirectory, string $type = null): array
    {
        if (! is_dir($pathDirectory)) {
            return [];
        }

        $allPathFiles = scandir($pathDirectory, SCANDIR_SORT_ASCENDING);

        return array_filter($allPathFiles, function ($file_name) use ($pathDirectory, $type) {

            if ($type == 'all') {
                return ! in_array($file_name, ['.', '..']);
            }

            if (is_null($type)) {
                return is_dir(rtrim($pathDirectory, '/').'/'.$file_name) && (! in_array($file_name, ['.', '..']));
            }

            return str_contains($file_name, $type);
        });
    }
}

if (! function_exists('getBaseDirectoryOfJsonFilesForService')) {

    function getBaseDirectoryOfJsonFilesForService($serviceName): string
    {
        return rtrim(base_path(), '/').'/'.
            rtrim(config('fox_settings.base_directory'), '/').'/'.
            $serviceName;
    }
}

if (! function_exists('mergingJsonFilesInDirectory')) {

    function mergingJsonFilesInDirectory(string $pathDirectory): string
    {
        $jsonPathsFiles = scanDirectoryAndReturnFiles($pathDirectory, '.json');

        $jsonContent = json_encode([]);
        foreach ($jsonPathsFiles as $jsonFile) {
            $filePath = $pathDirectory.'/'.$jsonFile;
            $jsonContent = mergingTwoJsonFile($jsonContent, file_get_contents($filePath));
        }

        return $jsonContent;
    }

}

if (! function_exists('getBaseServicePath')) {

    function getBaseServicePath(): string
    {
        $baseServicePath = rtrim(base_path(), '/').'/'.
            \App\Http\Controllers\MocksHelper::getBaseDirectory().'/'.
            \App\Http\Controllers\MocksHelper::getServiceName();

        return rtrim($baseServicePath, '/');
    }
}

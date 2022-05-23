<?php

namespace App\Http\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class OpenApiSpecificationGenerator extends Generator
{
    protected string $stub = 'openapis/openapi';
    private string $version;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->name = $this->getServiceName();
        $this->version = $this->getOpenApiSpecVersion();
    }

    public function getPathConfigNode(): string
    {
        return 'openapis';
    }

    /**
     * Get destination path for generated file.
     */
    public function getPath(): string
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorPath($this->getPathConfigNode(), true) . '/' . $this->getName() . '/index.oas3.yml';
    }

    public function run(): int
    {
        $getGeneratedJsonPath = $this->mergingJsonFiles();
        $this->convertJsonToYaml($getGeneratedJsonPath);
        return true;
    }

    public function getServiceName(): string
    {
        return strtolower($this->options['service']);
    }

    public function getOpenApiSpecVersion(): string
    {
        return $this->options['version'] ?? config('openapis.version', '3.1.0');
    }

    private function convertJsonToYaml($jsonPath): void
    {
        $ymlDestinationPath = str_replace('.json', '.yml', $jsonPath);

        Artisan::call("convert:files $jsonPath $ymlDestinationPath");
    }

    private function mergingJsonFiles(): string
    {
        $requiredFields = $this->openApiSpecificationRequiredFields();
        $jsonBaseDirectory = $this->getBasePath() . '/' . config('openapis.base_directory') . '/' . $this->getServiceName();
        $versionBaseConfig = str_replace('.', '_', $this->version);
        $items = config("openapis.fields.{$versionBaseConfig}");

        if (!$items) {
            dump("VERSION: $this->version, THERE IS NO CONFIG FOR THIS VERSION, TAKE A LOOK AT THE CONFIG FILES.");
            dd('THE VERSION OF THE OPEN API SPECIFICATION IS WRONG! OR WE CAN\'T SUPPORT IT!');
        }

        $jsonsContent = json_encode([]);

        foreach ($items as $index => $value) {

            $filePath = $jsonBaseDirectory . '/' . $index . '.json';
            if (file_exists($filePath)) {
                $jsonsContent = $this->mergingTwoJsonFile($jsonsContent, file_get_contents($filePath));
            }

            if (in_array($index, array_keys($requiredFields)) && !file_exists($filePath)){
                $requiredFields = implode(',', array_keys($requiredFields));
                dump("YOU DON'T HAVE REQUIRED JSON FILES IN YOUR DIRECTORY FOR GENERATING OPEN API SPECIFICATION!");
                dd("REQUIRED JSON FILE FOR GENERATING OPEN API SPECIFICATION: $requiredFields");
            }

            if ($index == 'paths') {
                $pathsJsonContents = $this->mergingPathsJsonFiles($jsonBaseDirectory . '/' . $index);
                $jsonsContent = $this->mergingTwoJsonFile($jsonsContent, $pathsJsonContents);
            }

            if ($index == 'components') {
                $componentsJsonContent = $this->mergingComponentsJsonFiles($jsonBaseDirectory . '/' . $index);
                $jsonsContent = $this->mergingTwoJsonFile($jsonsContent, $componentsJsonContent);
            }
        }

        $this->filesystem->put($jsonBaseDirectory . '/index.json', $jsonsContent);

        return $jsonBaseDirectory . '/index.json';
    }

    private function mergingPathsJsonFiles($pathsDirectory): string
    {
        if(!is_dir($pathsDirectory)){
            return '{"paths": ""}';
        }

        $jsonPathsFiles = scandir($pathsDirectory, SCANDIR_SORT_ASCENDING);
        $jsonPathsFiles = array_filter($jsonPathsFiles, function ($file_name) {
            return str_contains($file_name, '.json');
        });

        $jsonPathsContent = json_encode([]);
        foreach ($jsonPathsFiles as $jsonFile) {
            $filePath = $pathsDirectory . '/' . $jsonFile;
            $jsonPathsContent = $this->mergingTwoJsonFile($jsonPathsContent, file_get_contents($filePath));
        }

        return '{"paths": ' . $jsonPathsContent . '}';
    }

    private function mergingComponentsJsonFiles($componentsDirectory): string
    {
        if(!is_dir($componentsDirectory)){
            return '{"components": ""}';
        }

        $jsonComponentsFiles = scandir($componentsDirectory, SCANDIR_SORT_ASCENDING);
        $jsonComponentsFiles = array_filter($jsonComponentsFiles, function ($file_name) {
            return str_contains($file_name, '.json');
        });

        $jsonComponentsContent = json_encode([]);
        foreach ($jsonComponentsFiles as $jsonFile) {
            $filePath = $componentsDirectory . '/' . $jsonFile;
            $jsonComponentsContent = $this->mergingTwoJsonFile($jsonComponentsContent, file_get_contents($filePath));
        }

        /// MERGING JSON SCHEMA FROM DIRECTORY
        $schemaDirectory = $componentsDirectory . '/schemas';
        if (is_dir($schemaDirectory)){
            $jsonSchemaContent = $this->mergingSchemaFiles($schemaDirectory);
            $jsonComponentsContent = $this->mergingTwoJsonFile($jsonComponentsContent, $jsonSchemaContent);
        }

        return '{"components": ' . $jsonComponentsContent . '}';
    }

    private function mergingSchemaFiles($schemaDirectory): string
    {
        $jsonSchemaContent = json_encode([]);
        if (is_dir($schemaDirectory)){
            $jsonSchemaFiles = scandir($schemaDirectory, SCANDIR_SORT_ASCENDING);
            $jsonSchemaFiles = array_filter($jsonSchemaFiles, function ($schema_file_name) {
                return str_contains($schema_file_name, '.json');
            });

            foreach ($jsonSchemaFiles as $jsonSchemaFile) {
                $schemaFilePath = $schemaDirectory . '/' . $jsonSchemaFile;
                $jsonSchemaContent = $this->mergingTwoJsonFile($jsonSchemaContent, file_get_contents($schemaFilePath));
            }
        }

        return '{"schemas": ' . $jsonSchemaContent . '}';
    }

    private function mergingTwoJsonFile($first_json_file, $second_json_file): string
    {
        $firstArrayContent = json_decode($first_json_file, true);
        $secondArrayContent = json_decode($second_json_file, true);
        $mergedContents = array_merge($firstArrayContent ?? [], $secondArrayContent ?? []);

        return json_encode($mergedContents, JSON_PRETTY_PRINT);
    }

    private function openApiSpecificationRequiredFields(): array
    {
        $fields = config("openapis.fields.main");
        return array_filter($fields, function($field){
            return str_contains($field, 'required');
        });
    }
}

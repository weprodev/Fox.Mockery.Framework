<?php

namespace App\Http\Generators;

use Illuminate\Support\Facades\Artisan;

class OpenApiSpecificationGenerator extends Generator
{
    protected string $stub = 'openapis/openapi';
    protected string $name;
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


    public function getDestinationPathGeneratedFile(): string
    {
        return $this->getBasePath() . '/' .
            parent::getConfigGeneratorPath($this->getPathConfigNode(), true) . '/' .
            $this->getName() . '/index.oas3.yml';
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
        return $this->options['version'] ?? config('fox_openapis.version', '3.1.0');
    }


    private function convertJsonToYaml($jsonPath): void
    {
        $ymlDestinationPath = str_replace('_temp.json', '.yml', $jsonPath);
        Artisan::call("convert:files $jsonPath $ymlDestinationPath");
        $this->filesystem->delete($jsonPath);
    }


    private function mergingJsonFiles(): string
    {
        $jsonBaseDirectory = $this->getBaseDirectoryOfJsonFiles();
        $items = config('fox_openapis.fields');

        if (!$items) {
            dump("THERE IS NO CONFIG FOR ITEMS, TAKE A LOOK AT THE CONFIG FILES.");
            dd('THE OPEN API SPECIFICATION CONFIG IS WRONG!');
        }

        $jsonsContent = json_encode([]);

        foreach ($items as $index => $value) {

            $dirPath = $jsonBaseDirectory . '/' . $index;
            $filePath = $dirPath . '.json';
            $this->checkRequiredFieldValidation($index, $filePath);

            if (file_exists($filePath)) {
                $jsonsContent = mergingTwoJsonFile($jsonsContent, file_get_contents($filePath));
            }

            if ($index == 'paths') {
                $pathsJsonContents = $this->mergingPathsJsonFiles($dirPath);
                $jsonsContent = mergingTwoJsonFile($jsonsContent, $pathsJsonContents);
            }

            if ($index == 'components') {
                $componentsJsonContent = $this->mergingComponentsJsonFiles($dirPath);
                $jsonsContent = mergingTwoJsonFile($jsonsContent, $componentsJsonContent);
            }
        }

        $this->filesystem->put($jsonBaseDirectory . '/index_temp.json', $jsonsContent);

        return $jsonBaseDirectory . '/index_temp.json';
    }


    private function checkRequiredFieldValidation(string $field, string $filePath): void
    {
        $requiredFields = config('fox_openapis.required_items');

        if (in_array($field, $requiredFields) && (!file_exists($filePath) && is_dir($field))) {
            dump('YOU DON\'T HAVE REQUIRED JSON FILES IN YOUR DIRECTORY FOR GENERATING OPEN API SPECIFICATION!');
            dd('REQUIRED JSON FILE FOR GENERATING OPEN API SPECIFICATION:' . implode(',', $requiredFields));
        }
    }


    private function mergingPathsJsonFiles(string $pathDirectory): string
    {
        if (!is_dir($pathDirectory)) {
            return '{"paths": ""}';
        }

        $jsonPathsContent = $this->mergingJsonFilesInDirectory($pathDirectory);

        return '{"paths": ' . $jsonPathsContent . '}';
    }


    private function mergingComponentsJsonFiles(string $componentsDirectory): string
    {
        if (!is_dir($componentsDirectory)) {
            return '{"components": ""}';
        }

        $componentsArrayContent = [];
        $this->crawlInnerDirectoriesAndMergingJsonFiles($componentsDirectory, $componentsArrayContent);
        $componentsJsonContent = $this->mergingJsonFilesInDirectory($componentsDirectory);
        $componentsJsonContent = mergingTwoJsonFile($componentsJsonContent, json_encode($componentsArrayContent));

        return '{"components": ' . $componentsJsonContent . '}';
    }


    private function crawlInnerDirectoriesAndMergingJsonFiles(
        string $pathDirectory,
        array  &$resultContent,
        string $parentKey = null): void
    {
        $innerDirectories = scanDirectoryAndReturnFiles($pathDirectory, 'all');

        foreach ($innerDirectories as $item) {

            $innerPathDirectory = $pathDirectory . '/' . $item;

            if (is_dir($innerPathDirectory)) {
                $this->crawlInnerDirectoriesAndMergingJsonFiles($innerPathDirectory, $resultContent, $item);
            }

            $allJsonFiles = scanDirectoryAndReturnFiles($innerPathDirectory, '.json');

            foreach ($allJsonFiles as $jsonFile) {
                $filePath = $innerPathDirectory . '/' . $jsonFile;
                $fileName = $this->filesystem->name($filePath);
                $jsonFileContent = $this->filesystem->get($filePath);

                if ($parentKey) {
                    $resultContent[$parentKey][$item][$fileName] = json_decode($jsonFileContent, true);
                    continue;
                }

                $resultContent[$item][$fileName] = json_decode($jsonFileContent, true);
            }

        }

    }


    private function getBaseDirectoryOfJsonFiles(): string
    {
        return $this->getBasePath() . '/' .
            rtrim(config('fox_settings.base_directory'), '/') . '/' .
            $this->getServiceName();
    }

}

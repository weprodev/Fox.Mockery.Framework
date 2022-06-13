<?php

namespace App\Http\Generators;

use App\Exceptions\ReferencePathException;

class JsonSchemaGenerator extends Generator
{
    protected string $stub = 'json-schema/json-schema.stub';
    protected string $name;


    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->name = $this->getServiceName();
    }


    public function getPathConfigNode(): string
    {
        return 'json-schema';
    }


    public function getDestinationPathGeneratedFile(): string
    {
        return $this->getBasePath() . '/' .
            parent::getConfigGeneratorPath($this->getPathConfigNode(), true) . '/' .
            $this->getName() . '/index.json';
    }


    public function getStub(): string
    {
        $jsonBaseDirectory = $this->getBaseServicePath();
        $items = config('settings.json_schema_fields');

        $jsonsContent = json_encode([]);
        foreach ($items as $index => $value) {

            $filePath = $jsonBaseDirectory . '/' . $index . '.json';

            if (file_exists($filePath)) {
                $jsonsContent = mergingTwoJsonFile($jsonsContent, file_get_contents($filePath));
            }

            if ($index == 'paths') {
                $pathsJsonContents = $this->mergingPathsJsonFiles($jsonBaseDirectory . '/' . $index);
                $jsonsContent = mergingTwoJsonFile($jsonsContent, $pathsJsonContents);
            }
        }

        return $jsonsContent;
    }


    public function getServiceName(): string
    {
        return strtolower($this->options['service']);
    }


    private function mergingPathsJsonFiles(string $pathDirectory): string
    {
        if (!is_dir($pathDirectory)) {
            return '{"paths": ""}';
        }

        $jsonPathsFiles = scanDirectoryAndReturnFiles($pathDirectory, '.json');

        $jsonPathsContent = json_encode([]);
        foreach ($jsonPathsFiles as $jsonFile) {
            $filePath = $pathDirectory . '/' . $jsonFile;
            $data = $this->prepareJsonPathItem($filePath);
            $jsonPathsContent = mergingTwoJsonFile($jsonPathsContent, json_encode($data));
        }

        return '{"paths": ' . $jsonPathsContent . '}';
    }


    private function prepareJsonPathItem(string $filePath): array
    {
        $arrayPathsJson = json_decode(file_get_contents($filePath), true);
        $referenceKeyRoutes = "";
        return $this->recursiveFunctionToReplaceReference($arrayPathsJson, $referenceKeyRoutes);
    }


    private function recursiveFunctionToReplaceReference(array $data, &$keyRoutes): array
    {
        foreach ($data as $key => $value) {

            if (is_array($value)) {
                $keyRoutes = ($keyRoutes != "") ? ($keyRoutes . '.' . $key) : $key;
                $data[$key] = $this->recursiveFunctionToReplaceReference($value, $keyRoutes);
            }

            if ($key === '$ref') {

                try {
                    return $this->getReferenceContent($value);
                } catch (ReferencePathException $e) {
                    throw new ReferencePathException($e->getMessage());
                }

            }
        }
        return $data;
    }


    private function getReferenceContent(string $referenceRoute): array
    {
        $referencePath = ltrim($referenceRoute, '/');
        $referencePath = $this->getBaseServicePath() . '/' . $referencePath;

        if (str_contains($referenceRoute, '#')) {
            $referencePath = ltrim(substr($referenceRoute, strpos($referenceRoute, '#') + 1), '/');
            $referencePath = $this->getBaseServicePath() . '/' . $referencePath;
        }
        $referencePath .= '.json';

        if (!file_exists($referencePath)) {
            throw new ReferencePathException('THE FILE WHICH YOU REFERENCED DOES NOT EXIST! <<' . $referencePath . '>>');
        }

        return json_decode(file_get_contents($referencePath), true);
    }

}

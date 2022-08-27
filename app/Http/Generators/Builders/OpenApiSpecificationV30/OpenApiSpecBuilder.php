<?php

declare(strict_types=1);

namespace App\Http\Generators\Builders\OpenApiSpecificationV30;

use App\Exceptions\FileDoesNotExistException;
use App\ValueObjects\OpenApiSpecV30;
use Illuminate\Filesystem\Filesystem;

final class OpenApiSpecBuilder
{
    public string $baseDirectoryOfJsonFiles;

    private string $serviceName;

    private Filesystem $filesystem;

    private OpenApiSpecV30 $openApiSpec;

    private string $version;

    public function __construct(string $serviceName)
    {
        $this->filesystem = new Filesystem;
        $this->openApiSpec = new OpenApiSpecV30;
        $this->serviceName = $serviceName;
        $this->baseDirectoryOfJsonFiles = getBaseDirectoryOfJsonFilesForService($serviceName);
    }

    public function withVersion(string $version): self
    {
        $this->openApiSpec->setOpenApi($version);

        return $this;
    }

    /**
     * @throws FileDoesNotExistException
     */
    public function withInfo(): self
    {
        $filePath = $this->baseDirectoryOfJsonFiles.'/'.'info.json';

        if (! file_exists($filePath)) {
            throw new FileDoesNotExistException('Info file is a required file and it doesn\'t exist!');
        }

        $infoJsonFile = file_get_contents($filePath);
        $this->openApiSpec->setInfo(json_decode($infoJsonFile, true));

        return $this;
    }

    public function withTags(): self
    {
        $tagFilePath = $this->baseDirectoryOfJsonFiles.'/'.'tags.json';

        if (! file_exists($tagFilePath)) {
            return $this;
        }

        $tagJsonFile = file_get_contents($tagFilePath);
        $this->openApiSpec->setTags(json_decode($tagJsonFile, true));

        return $this;
    }

    public function withPaths(): self
    {
        $pathDirectory = $this->baseDirectoryOfJsonFiles.'/'.'paths';
        if (! is_dir($pathDirectory)) {
            $this->openApiSpec->setPaths([]);

            return $this;
        }

        $jsonPathsContent = mergingJsonFilesInDirectory($pathDirectory);

        $this->openApiSpec->setPaths(json_decode($jsonPathsContent, true));

        return $this;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function withComponents(): self
    {
        $componentsDirectory = $this->baseDirectoryOfJsonFiles.'/'.'components';
        if (! is_dir($componentsDirectory)) {
            $this->openApiSpec->setComponents([]);

            return $this;
        }

        $componentsArrayContent = [];
        $this->crawlInnerDirectoriesAndMergingJsonFiles($componentsDirectory, $componentsArrayContent);
        $this->openApiSpec->setComponents($componentsArrayContent);

        return $this;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function crawlInnerDirectoriesAndMergingJsonFiles(
        string $pathDirectory,
        array &$resultContent,
        string $parentKey = null
    ): void {

        $innerDirectories = scanDirectoryAndReturnFiles($pathDirectory, 'all');

        foreach ($innerDirectories as $item) {

            $innerPathDirectory = $pathDirectory.'/'.$item;

            if (is_dir($innerPathDirectory)) {
                $this->crawlInnerDirectoriesAndMergingJsonFiles($innerPathDirectory, $resultContent, $item);

                continue;
            }

            if (file_exists($innerPathDirectory)) {
                $filePath = $innerPathDirectory;

                $fileName = $this->filesystem->name($filePath);
                $jsonFileContent = $this->filesystem->get($filePath);

                if ($parentKey) {
                    $resultContent[$parentKey][$fileName] = json_decode($jsonFileContent, true);

                    continue;
                }

                $resultContent[$fileName] = json_decode($jsonFileContent, true);
            }
        }

    }

    public function withExternalDocs(): self
    {
        $externalDocsPath = $this->baseDirectoryOfJsonFiles.'/'.'externalDocs.json';
        if (! file_exists($externalDocsPath)) {
            return $this;
        }

        $externalDocsJsonFile = file_get_contents($externalDocsPath);
        $this->openApiSpec->setExternalDocs(json_decode($externalDocsJsonFile, true));

        return $this;
    }

    public function withSecurity(): self
    {
        $securityPath = $this->baseDirectoryOfJsonFiles.'/'.'security.json';
        if (! file_exists($securityPath)) {
            $this->openApiSpec->setSecurity([]);

            return $this;
        }

        $securityJsonFile = file_get_contents($securityPath);
        $this->openApiSpec->setSecurity(json_decode($securityJsonFile, true));

        return $this;
    }

    public function withServers(): self
    {
        $serversPath = $this->baseDirectoryOfJsonFiles.'/'.'servers.json';
        if (! file_exists($serversPath)) {
            return $this;
        }

        $serversJsonFile = file_get_contents($serversPath);
        $this->openApiSpec->setServers(json_decode($serversJsonFile, true));

        return $this;
    }

    /**
     * @throws \App\Exceptions\ValidationException
     */
    public function build(): OpenApiSpecRequest
    {
        return new OpenApiSpecRequest($this->openApiSpec);
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }
}

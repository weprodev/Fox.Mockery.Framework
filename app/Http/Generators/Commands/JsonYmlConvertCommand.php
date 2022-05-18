<?php

namespace App\Http\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Composer\Json\JsonFile;
use Symfony\Component\Yaml\Yaml;

class JsonYmlConvertCommand extends Command
{
    protected $name = 'convert:files';

    protected $description = 'convert json content to yml.';

    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }

    public function fire(): void
    {
        $filesystem = new Filesystem;
        $from = $this->argument('from');
        $to = $this->argument('to');
        $fromFormat = pathinfo($from, PATHINFO_EXTENSION);
        $toFormat = pathinfo($to, PATHINFO_EXTENSION);
        $validFormats = ['json', 'yml'];

        if ($fromFormat == $toFormat || !in_array(strtolower($fromFormat), $validFormats) || !in_array(strtolower($toFormat), $validFormats)){
            throw new \InvalidArgumentException('SOURCE FILE FORMAT / DESTINATION FILE FORMAT ARE NOT VALID!');
        }

        if ($from && !file_exists($from)) {
            throw new \InvalidArgumentException(sprintf("The input file '%s' does not exist.", $from));
        }

        $sourceContent = file_get_contents($from);

        if ('json' === $fromFormat) {
            $outputContent = $this->convertToYaml($sourceContent);
            $filesystem->put($to, $outputContent);
            return;
        }

        $outputContent = $this->convertToJson($sourceContent);
        $filesystem->put($to, $outputContent);
    }

    private function convertToJson($content): string
    {
        $data = Yaml::parse($content);
        return JsonFile::encode($data, JSON_PRETTY_PRINT)."\n";
    }

    private function convertToYaml($content): string
    {
        $data = JsonFile::parseJson($content);
        return Yaml::dump($data, 20);
    }

    public function getArguments(): array
    {
        return [
            [
                'from',
                InputArgument::REQUIRED,
                'Source path with format (json, yml)',
                null
            ],
            [
                'to',
                InputArgument::REQUIRED,
                'Destination path with format (json, yml)',
                null
            ],
        ];
    }


    public function getOptions(): array
    {
        return [];
    }
}

<?php

namespace App\Http\Generators\Commands;

use App\Http\Generators\Exceptions\FileAlreadyExistsException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

class JsonToYmlConverterCommand extends Command
{
    protected $name = 'convert:json2yml';

    protected $description = 'convert json content to yml.';

    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }

    public function fire(): void
    {
        $cmd = 'docker run --rm -v "${PWD}":/workdir mikefarah/yq -P ' . $this->argument('filePath');
        try {
            $process = Process::fromShellCommandline($cmd);

            $processOutput = '';

            $captureOutput = function ($type, $line) use (&$processOutput) {
                $processOutput .= $line;
            };

            $process->setTimeout(null)->run($captureOutput);

            if ($process->getExitCode()) {
                $exception = new \Exception($cmd . " - " . $processOutput);
                report($exception);

                throw $exception;
            }

            $this->info($processOutput);

        } catch (\Exception $exception) {

            $exception = new \Exception($cmd . " - " . $exception->getMessage());
            report($exception);

            throw $exception;
        }
    }

    public function getArguments(): array
    {
        return [
            [
                'filePath',
                InputArgument::REQUIRED,
                'The path of the file.',
                null
            ],
        ];
    }


    public function getOptions(): array
    {
        return [];
    }
}

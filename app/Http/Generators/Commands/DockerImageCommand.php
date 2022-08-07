<?php

namespace App\Http\Generators\Commands;

use App\Http\Generators\DockerGenerator;
use App\Http\Generators\Exceptions\FileAlreadyExistsException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DockerImageCommand extends Command
{
    protected $name = 'make:docker-image';

    protected $description = 'Create a new docker container container for service.';

    protected ?Collection $generators = null;

    protected string $type = 'Docker Image';

    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }

    public function fire(): void
    {
        try {
            $dockerGenerator = new DockerGenerator([
                'service' => strtolower($this->argument('service')),
                'port' => $this->argument('port'),
                'force' => $this->option('force'),
            ]);
            $isItNewDocker = ! file_exists($dockerGenerator->getDestinationPathGeneratedFile()) || $this->option('force');

            $dockerGenerator->run();
            if ($isItNewDocker && file_exists($dockerGenerator->getDestinationPathGeneratedFile())) {
                $this->info($this->type.' created for '.$this->argument('service').' successfully.');
                $dockerGenerator->regeneratingDockerComposeFile();
            }

        } catch (FileAlreadyExistsException $e) {
            $this->error($this->type.' already exists!');

            return;
        }
    }

    public function getArguments(): array
    {
        return [
            [
                'service',
                InputArgument::REQUIRED,
                'The name of service being generated.',
                null,
            ],
            [
                'port',
                InputArgument::REQUIRED,
                'The port of the service being generated.',
                null,
            ],
        ];
    }

    public function getOptions(): array
    {
        return [
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the creation if file already exists.',
                null,
            ],
        ];
    }
}

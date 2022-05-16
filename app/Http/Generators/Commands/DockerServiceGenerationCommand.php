<?php

namespace App\Http\Generators\Commands;

use App\Http\Generators\Exceptions\FileAlreadyExistsException;
use Illuminate\Support\Facades\File;
use App\Http\Generators\DockerGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DockerServiceGenerationCommand extends Command
{
    protected $name = 'make:docker';

    protected $description = 'Generate all of the available services in docker.';
    protected ?Collection $generators = null;
    protected string $type = "Docker Service Generation";

    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }


    public function fire(): void
    {
        foreach (getAvailableServices() as $service_name => $service) {

            try {

                $this->call('make:docker-image', [
                    'service' => $service_name,
                    'port' => $service['port'],
                    '--force' => true
                ]);

            } catch (FileAlreadyExistsException $e) {
                $this->error($this->type . ' already exists!');
                return;
            }
        }

    }

    public function getArguments(): array
    {
        return [];
    }

    public function getOptions(): array
    {
        return [];
    }
}

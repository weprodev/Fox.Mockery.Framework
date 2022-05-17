<?php

namespace App\Http\Generators\Commands;

use App\Http\Generators\Exceptions\FileAlreadyExistsException;
use App\Http\Generators\OpenApiSpecificationGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class OpenApiSpecificationCommand extends Command
{
    protected $name = 'make:openapi';

    protected $description = 'Create/re-generate an OPEN API SPECIFICATION for service.';
    protected ?Collection $generators = null;
    protected string $type = "Open API Specification";

    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }


    public function fire(): void
    {
        try {
            $openApiSpecGenerator = new OpenApiSpecificationGenerator([
                'service' => strtolower($this->argument('service')),
                'version' => $this->argument('version'),
                'force' => $this->option('force'),
            ]);
            dd($openApiSpecGenerator->run());
//            $isItNewDocker = !file_exists($openApiSpecGenerator->getPath()) || $this->option('force');
//
//            $openApiSpecGenerator->run();
//            if ($isItNewDocker && file_exists($openApiSpecGenerator->getPath())) {
//                $this->info($this->type . ' created for '. $this->argument('service') .' successfully.');
//                $openApiSpecGenerator->regeneratingDockerComposeFile();
//            }

        } catch (FileAlreadyExistsException $e) {
            $this->error($this->type . ' already exists!');
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
                null
            ],
            [
                'version',
                InputArgument::OPTIONAL,
                'The version of the open api specification.',
                null
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
                null
            ]
        ];
    }
}

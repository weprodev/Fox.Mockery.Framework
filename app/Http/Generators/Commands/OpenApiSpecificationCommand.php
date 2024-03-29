<?php

namespace App\Http\Generators\Commands;

use App\Http\Generators\Exceptions\GenerateOpenApiSpecificationException;
use App\Http\Generators\OpenApiSpecificationGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class OpenApiSpecificationCommand extends Command
{
    protected $name = 'fox:openapi {--oas=3.0.3}';

    protected $description = 'Create/re-generate an OPEN API SPECIFICATION for service with default OAS Version.';

    protected string $type = 'Open API Specification';

    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }

    public function fire(): void
    {
        try {

            $serviceName = $this->argument('service');

            if ($serviceName) {

                $this->generateOpenApiSpec($serviceName);
                $this->info($this->type.' created for '.$serviceName.' successfully.');
            } else {

                foreach (getAvailableServices() as $serviceName => $service) {
                    $this->generateOpenApiSpec($serviceName);
                    $this->info($this->type.' CREATED FOR '.$serviceName.' SUCCESSFULLY!');
                }
            }

        } catch (GenerateOpenApiSpecificationException $exception) {
            $this->error($this->type.': '.$exception->getMessage());

            return;
        }
    }

    public function getArguments(): array
    {
        return [
            [
                'service',
                InputArgument::OPTIONAL,
                'The name of service being generated.',
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
            [
                'oas',
                'oas',
                InputArgument::OPTIONAL,
                'The version of the open api specification.',
                null,
            ],
        ];
    }

    private function generateOpenApiSpec($service_name)
    {

        $openApiGenerator = new OpenApiSpecificationGenerator([
            'service' => strtolower($service_name),
            'version' => $this->option('oas'),
            'force' => $this->option('force'),
        ]);
        $openApiGenerator->run();
    }
}

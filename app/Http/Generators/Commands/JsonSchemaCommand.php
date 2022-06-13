<?php

namespace App\Http\Generators\Commands;

use App\Http\Generators\JsonSchemaGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class JsonSchemaCommand extends Command
{
    protected $name = 'make:schema';

    protected $description = 'Create/re-generate a JSON Schema for services.';
    protected string $type = "JSON SCHEMA";


    public function handle(): void
    {
        $this->laravel->call([$this, 'fire'], func_get_args());
    }


    public function fire(): void
    {
        try {

            $serviceName = $this->argument('service');

            if ($serviceName) {

                $this->generateJsonSchema($serviceName);
                $this->info($this->type . ' created for ' . $serviceName . ' successfully.');

            } else {

                foreach (getAvailableServices() as $serviceName => $service) {
                    $this->generateJsonSchema($serviceName);
                    $this->info($this->type . ' CREATED FOR ' . $serviceName . ' SUCCESSFULLY.');
                }
            }


        } catch (\Exception $exception) {
            $this->error($this->type . ': ' . $exception->getMessage());
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
                null
            ]
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


    private function generateJsonSchema($service_name)
    {

        $openApiGenerator = new JsonSchemaGenerator([
            'service' => strtolower($service_name),
            'force' => $this->option('force'),
        ]);
        $openApiGenerator->run();
    }

}

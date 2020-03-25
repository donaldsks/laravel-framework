<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class StubPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stub:publish {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all stubs that are available for customization';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! is_dir($stubsPath = $this->laravel->basePath('stubs'))) {
            (new Filesystem)->makeDirectory($stubsPath);
        }

        $files = [
            __DIR__.'/stubs/job.queued.stub' => $stubsPath.'/job.queued.stub',
            __DIR__.'/stubs/job.stub' => $stubsPath.'/job.stub',
            __DIR__.'/stubs/model.pivot.stub' => $stubsPath.'/model.pivot.stub',
            __DIR__.'/stubs/model.stub' => $stubsPath.'/model.stub',
            __DIR__.'/stubs/request.stub' => $stubsPath.'/request.stub',
            __DIR__.'/stubs/test.stub' => $stubsPath.'/test.stub',
            __DIR__.'/stubs/test.unit.stub' => $stubsPath.'/test.unit.stub',
            realpath(__DIR__.'/../../Database/Migrations/stubs/migration.create.stub') => $stubsPath.'/migration.create.stub',
            realpath(__DIR__.'/../../Database/Migrations/stubs/migration.stub') => $stubsPath.'/migration.stub',
            realpath(__DIR__.'/../../Database/Migrations/stubs/migration.update.stub') => $stubsPath.'/migration.update.stub',
            realpath(__DIR__.'/../../Foundation/Console/stubs/policy.plain.stub') => $stubsPath.'/policy.plain.stub',
            realpath(__DIR__.'/../../Foundation/Console/stubs/policy.stub') => $stubsPath.'/policy.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.api.stub') => $stubsPath.'/controller.api.stub',
            realpath(__DIR__.'/../../Foundation/Console/stubs/console.stub') => $stubsPath.'/console.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.invokable.stub') => $stubsPath.'/controller.invokable.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.model.api.stub') => $stubsPath.'/controller.model.api.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.model.stub') => $stubsPath.'/controller.model.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.nested.api.stub') => $stubsPath.'/controller.nested.api.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.nested.stub') => $stubsPath.'/controller.nested.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.plain.stub') => $stubsPath.'/controller.plain.stub',
            realpath(__DIR__.'/../../Routing/Console/stubs/controller.stub') => $stubsPath.'/controller.stub',
        ];

        foreach ($files as $from => $to) {
            if (! file_exists($to) || $this->option('force')) {
                file_put_contents($to, file_get_contents($from));
            }
        }

        $this->info('Stubs published successfully.');
    }
}

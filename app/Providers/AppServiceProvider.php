<?php

namespace App\Providers;

use App\Domain\Workflow\Contracts\WorkflowRepositoryInterface;
use App\Infrastructure\Workflow\ConfigWorkflowRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WorkflowRepositoryInterface::class, function () {
            return new ConfigWorkflowRepository(config('ai-workflow.workflows', []));
        });
    }

    public function boot(): void
    {
        //
    }
}

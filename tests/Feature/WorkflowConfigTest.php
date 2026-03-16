<?php

namespace Tests\Feature;

use App\Domain\Workflow\Contracts\WorkflowRepositoryInterface;
use Tests\TestCase;

class WorkflowConfigTest extends TestCase
{
    public function test_can_resolve_workflow_by_slug_from_config(): void
    {
        $repo = $this->app->make(WorkflowRepositoryInterface::class);
        $workflow = $repo->findWorkflowBySlug('document_analysis');

        $this->assertNotNull($workflow);
        $this->assertSame('document_analysis', $workflow->slug());
        $this->assertSame('Document Analysis', $workflow->name());
        $steps = $workflow->steps();
        $this->assertCount(5, $steps);
        $stepKeys = array_column($steps, 'key');
        $this->assertContains('ingest', $stepKeys);
        $this->assertContains('plan', $stepKeys);
        $this->assertContains('summarize', $stepKeys);
    }
}

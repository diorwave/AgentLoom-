<?php

namespace Tests\Unit\Domain\Workflow;

use App\Domain\Workflow\Workflow;
use PHPUnit\Framework\TestCase;

class WorkflowTest extends TestCase
{
    public function test_workflow_exposes_steps_and_approval_keys(): void
    {
        $definition = [
            'steps' => [
                ['key' => 'plan', 'order' => 1, 'agent_role' => 'planner', 'max_retries' => 2],
                ['key' => 'analyse', 'order' => 2, 'agent_role' => 'analyst', 'max_retries' => 2],
            ],
            'approval_step_keys' => ['analyse'],
        ];
        $workflow = new Workflow(
            id: 'doc_analysis',
            name: 'Document Analysis',
            slug: 'document_analysis',
            description: 'Test',
            definition: $definition,
        );

        $this->assertSame('doc_analysis', $workflow->id());
        $this->assertSame('Document Analysis', $workflow->name());
        $this->assertCount(2, $workflow->steps());
        $this->assertTrue($workflow->requiresApprovalForStep('analyse'));
        $this->assertFalse($workflow->requiresApprovalForStep('plan'));
    }
}

<?php

namespace Tests\Unit\Domain\Workflow;

use App\Domain\Workflow\WorkflowStatus;
use PHPUnit\Framework\TestCase;

class WorkflowStatusTest extends TestCase
{
    public function test_pending_can_transition_to_running(): void
    {
        $this->assertTrue(WorkflowStatus::Pending->canTransitionTo(WorkflowStatus::Running));
    }

    public function test_pending_cannot_transition_to_completed(): void
    {
        $this->assertFalse(WorkflowStatus::Pending->canTransitionTo(WorkflowStatus::Completed));
    }

    public function test_running_can_transition_to_completed_or_failed(): void
    {
        $this->assertTrue(WorkflowStatus::Running->canTransitionTo(WorkflowStatus::Completed));
        $this->assertTrue(WorkflowStatus::Running->canTransitionTo(WorkflowStatus::Failed));
        $this->assertTrue(WorkflowStatus::Running->canTransitionTo(WorkflowStatus::AwaitingApproval));
    }

    public function test_completed_is_terminal(): void
    {
        $this->assertFalse(WorkflowStatus::Completed->canTransitionTo(WorkflowStatus::Running));
    }
}

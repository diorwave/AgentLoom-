<?php

namespace App\Domain\Workflow;

/**
 * Workflow run lifecycle status.
 */
enum WorkflowStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case AwaitingApproval = 'awaiting_approval';
    case RetryScheduled = 'retry_scheduled';

    public function canTransitionTo(self $other): bool
    {
        return match ($this) {
            self::Pending => $other === self::Running,
            self::Running => in_array($other, [self::Completed, self::Failed, self::AwaitingApproval, self::RetryScheduled], true),
            self::AwaitingApproval => $other === self::Running,
            self::RetryScheduled => $other === self::Running,
            self::Completed, self::Failed => false,
        };
    }
}

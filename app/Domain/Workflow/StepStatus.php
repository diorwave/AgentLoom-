<?php

namespace App\Domain\Workflow;

/**
 * Single step execution status.
 */
enum StepStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case AwaitingApproval = 'awaiting_approval';

    public function isTerminal(): bool
    {
        return $this === self::Completed || $this === self::Failed || $this === self::Skipped;
    }
}

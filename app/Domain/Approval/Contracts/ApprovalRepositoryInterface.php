<?php

namespace App\Domain\Approval\Contracts;

use App\Domain\Approval\ApprovalRequest;

interface ApprovalRepositoryInterface
{
    public function find(string $id): ?ApprovalRequest;

    /** @return list<ApprovalRequest> */
    public function findPendingByWorkflowRunId(string $workflowRunId): array;

    public function store(ApprovalRequest $approval): void;

    public function updateResolved(string $id, string $status, string $resolvedBy, ?string $comment = null): void;
}

<?php

namespace App\Domain\Tool\Contracts;

use App\Domain\Tool\ToolCallRequest;
use App\Domain\Tool\ToolCallResult;

interface ToolExecutionGatewayInterface
{
    /**
     * Validate and execute the tool request, or return approval_required.
     */
    public function execute(ToolCallRequest $request): ToolCallResult;
}

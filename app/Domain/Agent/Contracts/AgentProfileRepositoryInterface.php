<?php

namespace App\Domain\Agent\Contracts;

use App\Domain\Agent\Agent;
use App\Domain\Agent\AgentRole;

interface AgentProfileRepositoryInterface
{
    public function getByRole(AgentRole $role): ?Agent;

    /** @return array<string, Agent> */
    public function getByRoles(array $roles): array;
}

<?php

namespace App\Domain\Agent;

enum AgentRole: string
{
    case Planner = 'planner';
    case Analyst = 'analyst';
    case Reviewer = 'reviewer';
    case Summarizer = 'summarizer';
}

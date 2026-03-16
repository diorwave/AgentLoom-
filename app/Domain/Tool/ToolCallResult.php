<?php

namespace App\Domain\Tool;

/**
 * Value object: result of a tool execution (or approval-required).
 */
final class ToolCallResult
{
    public function __construct(
        private bool $success,
        private mixed $output,
        private ?string $errorMessage,
        private ?string $approvedBy,
        private ?\DateTimeInterface $executedAt,
    ) {}

    public static function success(mixed $output, ?\DateTimeInterface $executedAt = null): self
    {
        return new self(true, $output, null, null, $executedAt ?? new \DateTimeImmutable());
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, $errorMessage, null, null);
    }

    public static function approvalRequired(): self
    {
        return new self(false, null, 'approval_required', null, null);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function output(): mixed
    {
        return $this->output;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function approvedBy(): ?string
    {
        return $this->approvedBy;
    }

    public function executedAt(): ?\DateTimeInterface
    {
        return $this->executedAt;
    }

    public function isApprovalRequired(): bool
    {
        return $this->errorMessage === 'approval_required';
    }
}

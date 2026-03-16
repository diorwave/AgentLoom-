<?php

namespace App\Domain\Document\Contracts;

use App\Domain\Document\Document;

interface DocumentRepositoryInterface
{
    public function find(string $id): ?Document;

    /** @return list<Document> */
    public function findByIds(array $ids): array;

    public function store(Document $document): void;

    public function updateStatus(string $id, string $status): void;
}

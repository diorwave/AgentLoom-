<?php

namespace App\Domain\Document;

enum DocumentStatus: string
{
    case Uploaded = 'uploaded';
    case Parsing = 'parsing';
    case Chunking = 'chunking';
    case Embedding = 'embedding';
    case Ready = 'ready';
    case Failed = 'failed';
}

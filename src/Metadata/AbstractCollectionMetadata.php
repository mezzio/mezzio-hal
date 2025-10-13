<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

abstract class AbstractCollectionMetadata extends AbstractMetadata
{
    public const TYPE_PLACEHOLDER = 'placeholder';
    public const TYPE_QUERY       = 'query';

    protected string $collectionRelation;

    protected string $paginationParam;

    protected string $paginationParamType;

    public function getCollectionRelation(): string
    {
        return $this->collectionRelation;
    }

    public function getPaginationParam(): string
    {
        return $this->paginationParam;
    }

    public function getPaginationParamType(): string
    {
        return $this->paginationParamType;
    }
}

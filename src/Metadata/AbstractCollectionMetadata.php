<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

abstract class AbstractCollectionMetadata extends AbstractMetadata
{
    public const TYPE_PLACEHOLDER = 'placeholder';
    public const TYPE_QUERY       = 'query';

    /** @var string */
    protected $collectionRelation;

    /** @var string */
    protected $paginationParam;

    /** @var string */
    protected $paginationParamType;

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

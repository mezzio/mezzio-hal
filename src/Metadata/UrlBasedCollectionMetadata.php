<?php

declare(strict_types=1);

namespace Mezzio\Hal\Metadata;

use InvalidArgumentException;

use function in_array;
use function sprintf;

final class UrlBasedCollectionMetadata extends AbstractCollectionMetadata
{
    public function __construct(
        string $class,
        string $collectionRelation,
        /**
         * URL to use for the `self` relation of the collection.
         */
        private readonly string $url,
        string $paginationParam = 'page',
        string $paginationParamType = self::TYPE_QUERY
    ) {
        if ($collectionRelation === '') {
            throw new InvalidArgumentException('$collectionRelation MUST NOT be empty');
        }

        if ($paginationParam === '') {
            throw new InvalidArgumentException('$paginationParam MUST NOT be empty');
        }

        if (! in_array($paginationParamType, [self::TYPE_PLACEHOLDER, self::TYPE_QUERY], true)) {
            throw new InvalidArgumentException(sprintf(
                '$paginationParamType MUST be one of "%s" or "%s"; received "%s"',
                self::TYPE_PLACEHOLDER,
                self::TYPE_QUERY,
                $paginationParamType
            ));
        }

        $this->class               = $class;
        $this->collectionRelation  = $collectionRelation;
        $this->paginationParam     = $paginationParam;
        $this->paginationParamType = $paginationParamType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}

<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;

use function array_key_exists;
use function class_exists;
use function in_array;

/**
 * Class MetadataMap
 * @package Mezzio\Hal\Metadata
 */
class MetadataMap
{
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var array $map */
    private $map = [];

    /** @var array $entityClasses */
    private $entityClasses = [];

    /**
     * MetadataMap constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $metas = $em->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $this->entityClasses[] = $meta->getName();
            $this->entityClasses[] =
                $em->getConfiguration()->getProxyNamespace() . '\\' . Proxy::MARKER . '\\' . $meta->getName();
        }
    }

    /**
     * @throws Exception\DuplicateMetadataException if metadata matching the
     *     class of the provided metadata already exists in the map.
     * @throws Exception\UndefinedClassException if the class in the provided
     *     metadata does not exist.
     */
    public function add(AbstractMetadata $metadata) : void
    {
        $class = $metadata->getClass();
        if (isset($this->map[$class])) {
            throw Exception\DuplicateMetadataException::create($class);
        }

        if (! class_exists($class)) {
            throw Exception\UndefinedClassException::create($class);
        }

        $this->map[$class] = $metadata;
    }

    public function has(string $class) : bool
    {
        if (!array_key_exists($class, $this->map)) {
            if (in_array($class, $this->entityClasses)) {
                $class = $this->em->getClassMetadata($class)->getName();
            }
        }

        return isset($this->map[$class]);
    }

    /**
     * @throws Exception\UndefinedMetadataException if no metadata matching the
     *     provided class is found in the map.
     */
    public function get(string $class) : AbstractMetadata
    {
        if (!array_key_exists($class, $this->map)) {
            if (in_array($class, $this->entityClasses)) {
                $class = $this->em->getClassMetadata($class)->getName();
            }
        }

        if (! isset($this->map[$class])) {
            throw Exception\UndefinedMetadataException::create($class);
        }

        return $this->map[$class];
    }
}

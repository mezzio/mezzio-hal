<?php // phpcs:disable WebimpressCodingStandard.NamingConventions.Trait.Suffix

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal;

use Psr\Link\LinkInterface;

use function array_filter;
use function in_array;

/**
 * Properties and methods suitable for an
 * EvolvableLinkProviderInterface implementation.
 */
trait LinkCollection
{
    /** @var LinkInterface[] */
    private $links = [];

    /**
     * {@inheritDoc}
     *
     * @return LinkInterface[]
     * @psalm-return array<array-key, LinkInterface>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * {@inheritDoc}
     *
     * @return LinkInterface[]
     * @psalm-return array<array-key, LinkInterface>
     */
    public function getLinksByRel($rel): array
    {
        return array_filter($this->links, function (LinkInterface $link) use ($rel) {
            $rels = $link->getRels();
            return in_array($rel, $rels, true);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function withLink(LinkInterface $link): self
    {
        if (in_array($link, $this->links, true)) {
            return $this;
        }

        $new          = clone $this;
        $new->links[] = $link;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutLink(LinkInterface $link): self
    {
        if (! in_array($link, $this->links, true)) {
            return $this;
        }

        $new        = clone $this;
        $new->links = array_filter($this->links, function (LinkInterface $compare) use ($link) {
            return $link !== $compare;
        });
        return $new;
    }
}

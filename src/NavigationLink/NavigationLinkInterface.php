<?php
namespace StaticSiteExport\NavigationLink;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

interface NavigationLinkInterface
{
    /**
     * Set the Hugo-compatible menu entry for the passed Omeka navigation link.
     *
     * @see https://gohugo.io/content-management/menus/
     * @param JobInterface $job The export job, use for convenience methods
     * @param ArrayObject $menu The Hugo menu object
     * @param array $navLink The Omeka navigation link configuration array
     * @param string $id The auto-generated ID of this menu entry (set to `identifier`)
     * @param ?string $parentId The parent ID of this menu entry (set to `parent`)
     * @param ?int $weight The weight of this menu entry (set to `weight`)
     */
    public function setMenuEntry(
        JobInterface $job,
        ArrayObject $menu,
        array $navLink,
        string $id,
        ?string $parentId,
        ?int $weight
    ): void;
}

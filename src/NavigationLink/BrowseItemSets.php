<?php
namespace StaticSiteExport\NavigationLink;

use ArrayObject;
use Omeka\Job\JobInterface;

class BrowseItemSets implements NavigationLinkInterface
{
    public function setMenuEntry(
        JobInterface $job,
        ArrayObject $menu,
        array $navLink,
        string $id,
        ?string $parentId,
        ?int $weight
    ): void {
        $menu->append([
            'name' => $navLink['data']['label'] ?: $job->translate('Browse item sets'),
            'identifier' => $id,
            'parent' => $parentId,
            'pageRef' => '/item-sets',
            'weight' => $weight,
        ]);
    }
}

<?php
namespace StaticSiteExport\NavigationLink;

use ArrayObject;
use Omeka\Job\JobInterface;

class Browse implements NavigationLinkInterface
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
            'name' => $navLink['data']['label'] ?: 'Browse items',
            'identifier' => $id,
            'parent' => $parentId,
            'pageRef' => '/items',
            'weight' => $weight,
        ]);
    }
}

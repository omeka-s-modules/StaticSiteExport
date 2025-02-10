<?php
namespace StaticSiteExport\NavigationLink;

use ArrayObject;
use Omeka\Job\JobInterface;

class Unknown implements NavigationLinkInterface
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
            'name' => 'Unknown',
            'identifier' => $id,
            'parent' => $parentId,
            'pageRef' => '/',
            'weight' => $weight,
        ]);
    }
}

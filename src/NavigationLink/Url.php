<?php
namespace StaticSiteExport\NavigationLink;

use ArrayObject;
use Omeka\Job\JobInterface;

class Url implements NavigationLinkInterface
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
            'name' => $navLink['data']['label'] ?: $job->translate('URL'),
            'identifier' => $id,
            'parent' => $parentId,
            'url' => $navLink['data']['url'],
            'weight' => $weight,
        ]);
    }
}

<?php
namespace StaticSiteExport\NavigationLink;

use ArrayObject;
use Omeka\Job\JobInterface;

class Page implements NavigationLinkInterface
{
    public function setMenuEntry(
        JobInterface $job,
        ArrayObject $menu,
        array $navLink,
        string $id,
        ?string $parentId,
        ?int $weight
    ): void {
        $sitePage = $job->get('Omeka\ApiManager')
            ->read('site_pages', $navLink['data']['id'])
            ->getContent();
        $menu->append([
            'name' => $sitePage->title(),
            'identifier' => $id,
            'parent' => $parentId,
            'pageRef' => sprintf('/pages/%s', $sitePage->slug()),
            'weight' => $weight,
        ]);
    }
}

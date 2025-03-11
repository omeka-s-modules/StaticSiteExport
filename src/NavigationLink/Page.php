<?php
namespace StaticSiteExport\NavigationLink;

use ArrayObject;
use Omeka\Api\Exception\NotFoundException;
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
        try {
            $sitePage = $job->get('Omeka\ApiManager')
                ->read('site_pages', $navLink['data']['id'])
                ->getContent();
        } catch (NotFoundException $e) {
            $sitePage = null;
        }
        if ($sitePage) {
            $menu->append([
                'name' => $sitePage->title(),
                'identifier' => $id,
                'parent' => $parentId,
                'pageRef' => sprintf('/pages/%s', $sitePage->slug()),
                'weight' => $weight,
            ]);
        } else {
            $menu->append([
                'name' => $job->translate('[Missing Page]'),
                'identifier' => $id,
                'parent' => $parentId,
                'weight' => $weight,
            ]);
        }

    }
}

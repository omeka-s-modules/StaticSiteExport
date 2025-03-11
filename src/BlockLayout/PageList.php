<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class PageList implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $pageList = new ArrayObject;
        $recursePages = function ($pages) use (&$recursePages, $pageList, $job) {
            foreach ($pages as $page) {
                try {
                    $sitePage = $job->get('Omeka\ApiManager')
                        ->read('site_pages', $page['data']['data']['id'])
                        ->getContent();
                } catch (NotFoundException $e) {
                    $sitePage = null;
                }
                $pageList[] = '<li>';
                if ($sitePage) {
                    $pageList[] = sprintf(
                        '<a href="{{< ref "pages/%s" >}}">%s</a>',
                        $sitePage->slug(),
                        $sitePage->title()
                    );
                } else {
                    $pageList[] = sprintf('%s', $job->translate('[Missing Page]'));
                }
                if (isset($page['children']) && $page['children']) {
                    $pageList[] = '<ul>';
                    $recursePages($page['children']);
                    $pageList[] = '</ul>';
                }
                $pageList[] = '</li>';
            }
        };
        $pages = json_decode($block->dataValue('pagelist'), true);
        $recursePages($pages);
        return sprintf('{{< omeka-html >}}<ul>%s</ul>{{< /omeka-html >}}', implode('', $pageList->getArrayCopy()));
    }
}

<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class Asset implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $api = $job->get('Omeka\ApiManager');
        $markdown = [];
        foreach ($block->data() as $attachmentData) {
            try {
                $asset = $api->read('assets', $attachmentData['id'])->getContent();
            } catch (NotFoundException $e) {
                continue;
            }
            $page = $attachmentData['page'] ? $api->read('site_pages', $attachmentData['page'])->getContent() : null;
            $markdown[] = $job->getFigureShortcode([
                'type' => 'image',
                'filePage' => sprintf('/assets/%s', $asset->id()),
                'fileResource' => 'file',
                'imgPage' => sprintf('/assets/%s', $asset->id()),
                'imgResource' => 'file',
                'linkPage' => $page ? sprintf('/pages/%s', $page->slug()) : '',
                'caption' => $attachmentData['caption'] ? $job->escape(['"'], $attachmentData['caption']) : '',
            ]);
        }
        return implode("\n\n", $markdown);
    }
}

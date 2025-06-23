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
            $figureSpec = [
                'caption' => $attachmentData['caption'] ? $job->escape(['"'], $attachmentData['caption']) : '',
            ];
            try {
                $asset = $api->read('assets', $attachmentData['id'])->getContent();
                $page = $attachmentData['page'] ? $api->read('site_pages', $attachmentData['page'])->getContent() : null;
                $figureSpec['image'] = 'image';
                $figureSpec['fileResource'] = 'file';
                $figureSpec['filePage'] = sprintf('/assets/%s', $asset->id());
                $figureSpec['imgResource'] = 'file';
                $figureSpec['imgPage'] = sprintf('/assets/%s', $asset->id());
                $figureSpec['linkPage'] = $page ? sprintf('/pages/%s', $page->slug()) : '';
            } catch (NotFoundException $e) {
                // The attachment may not have an asset. This can happen if one
                // was assigned to the attachment and subsequently deleted. Even
                // so, the attachment may still have a caption, so export the
                // figure shortcode anyway.
            }
            $markdown[] = $job->getFigureShortcode($figureSpec);
        }
        return implode("\n\n", $markdown);
    }
}

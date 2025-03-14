<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class ItemWithMetadata implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $markdown = [];
        foreach ($block->attachments() as $attachment) {
            $item = $attachment->item();
            if (!$item) {
                continue;
            }
            $markdown[] = $job->getValuesMarkdown($item, $frontMatterPage, $frontMatterBlock);
            $markdown[] = $job->getItemSetListMarkdown($item);
            $markdown[] = $job->getMediaListMarkdown($item);
        }
        return implode("\n\n", $markdown);
    }
}

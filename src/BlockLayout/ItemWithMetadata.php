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
            $markdown[] = $job->getValuesMarkdown($item, $frontMatterPage, $frontMatterBlock);
            $markdown[] = sprintf("### %s\n", $job->translate('Item sets'));
            $markdown[] = $job->getItemSetListMarkdown($item, $frontMatterPage, $frontMatterBlock);
            $markdown[] = sprintf("### %s\n", $job->translate('Media'));
            $markdown[] = $job->getMediaListMarkdown($item, $frontMatterPage, $frontMatterBlock);
        }
        return implode("\n\n", $markdown);
    }
}

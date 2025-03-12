<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class BrowsePreview implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $blockData = $block->data();

        $sections = [
            'items' => 'items',
            'media' => 'media',
            'item_sets' => 'item-sets',
        ];
        $markdown = [];
        $blockHeading = $block->dataValue('heading');
        if ($blockHeading) {
            $markdown[] = sprintf('### %s', $blockHeading);
        }
        $markdown[] = sprintf(
            '{{< omeka-resource-list section="%s" limit="%s" >}}',
            $sections[$block->dataValue('resource_type', 'items')],
            $block->dataValue('limit', 12)
        );
        $blockLinkText = $block->dataValue('link-text') ?? $job->translate('Browse all');
        $markdown[] = sprintf(
            '[%s]({{< ref "/%s" >}})',
            $job->escape(['[', ']'], $blockLinkText),
            $sections[$block->dataValue('resource_type', 'items')]
        );
        return implode("\n\n", $markdown);
    }
}

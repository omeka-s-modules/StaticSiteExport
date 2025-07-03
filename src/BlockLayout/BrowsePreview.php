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

        $blockHeading = $block->dataValue('heading');
        $resourceType = $block->dataValue('resource_type', 'items');

        // Build the query and get the resource IDs.
        parse_str($block->dataValue('query'), $query);
        $query['limit'] = $block->dataValue('limit', 12);
        $query['site_id'] = $job->getStaticSite()->site()->id();
        if (!isset($query['sort_by'])) {
            $query['sort_by_default'] = '';
            $query['sort_by'] = 'created';
        }
        if (!isset($query['sort_order'])) {
            $query['sort_order_default'] = '';
            $query['sort_order'] = 'desc';
        }
        $resourceIds = $job->get('Omeka\ApiManager')->search($resourceType, $query, ['returnScalar' => 'id'])->getContent();

        // Build the markdown.
        $markdown = [];
        if ($blockHeading) {
            $markdown[] = sprintf('### %s', $blockHeading);
        }
        $markdown[] = sprintf(
            '{{< omeka-resource-list section="%s" resource-ids="%s" >}}',
            $sections[$block->dataValue('resource_type', 'items')],
            implode(',', $resourceIds)
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

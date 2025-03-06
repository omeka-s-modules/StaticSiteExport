<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class ItemSets implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $itemSets = $resource->itemSets();
        if (!$itemSets) {
            return '';
        }
        return sprintf(
            "## %s\n%s",
            $job->translate('Item sets'),
            $job->getItemSetListMarkdown($resource, $frontMatterPage, $frontMatterBlock)
        );
    }
}

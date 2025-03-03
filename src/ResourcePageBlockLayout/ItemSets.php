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
        $block = [sprintf("## %s\n", $job->translate('Item sets'))];
        foreach ($itemSets as $itemSet) {
            if (!in_array($itemSet->id(), $job->getItemSetIds())) {
                continue; // Item set not in site.
            }
            $block[] = sprintf(
                '- %s',
                $job->getLinkMarkdown($itemSet, [
                    'thumbnailType' => 'square',
                    'thumbnailHeight' => 40,
                ])
            );
        }
        return implode("\n", $block);
    }
}

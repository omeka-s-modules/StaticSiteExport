<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class ItemSets implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        AbstractResourceEntityRepresentation $resource
    ): string {
        $itemSets = $resource->itemSets();
        if (!$itemSets) {
            return '';
        }
        $block = [sprintf("## Item sets\n")];
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

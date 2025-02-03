<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class ItemSets implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        AbstractResourceEntityRepresentation $resource,
        JobInterface $job,
        ArrayObject $frontMatter
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
                '- [%s]({{< ref "/item-sets/%s" >}} "%s")',
                $job->escape(['[', ']'], $itemSet->displayTitle()),
                $itemSet->id(),
                $job->escape(['"'], $itemSet->displayTitle()),
            );
        }
        return implode("\n", $block);
    }
}

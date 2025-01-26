<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class ItemSets implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
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

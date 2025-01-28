<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaItem implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
        $item = $resource->item();
        $block = [sprintf("## Item\n")];
        $block[] = sprintf(
            '- %s[%s]({{< ref "/items/%s" >}} "%s")',
            $job->getThumbnailShortcode($item, 'square', 40),
            $job->escape(['[', ']'], $item->displayTitle()),
            $item->id(),
            $job->escape(['"'], $item->displayTitle()),
        );
        return implode("\n", $block);
    }
}

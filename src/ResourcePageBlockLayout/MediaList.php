<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaList implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
        $media = $resource->media();
        if (!$media) {
            return '';
        }
        $block = [sprintf("## Media\n")];
        foreach ($media as $media) {
            $block[] = sprintf(
                '- %s[%s]({{< ref "/media/%s" >}} "%s")',
                $job->getThumbnailShortcode($media, 'square', 40),
                $job->escape(['[', ']'], $media->displayTitle()),
                $media->id(),
                $job->escape(['"'], $media->displayTitle()),
            );
        }
        return implode("\n", $block);
    }
}

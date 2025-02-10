<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaList implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        AbstractResourceEntityRepresentation $resource
    ): string {
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

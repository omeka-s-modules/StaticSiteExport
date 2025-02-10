<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Youtube implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        MediaRepresentation $media
    ): string {
        $data = $media->mediaData();
        return sprintf('{{< youtube id="%s" >}}', $data['id']);
    }
}

<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Youtube implements MediaRendererInterface
{
    public function getMarkdown(
        MediaRepresentation $media,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $data = $media->mediaData();
        return sprintf('{{< youtube id="%s" >}}', $data['id']);
    }
}

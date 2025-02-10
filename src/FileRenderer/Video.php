<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Video implements FileRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        MediaRepresentation $media
    ): string {
        return sprintf(
            '{{< omeka-figure
                type="video"
                linkPage="/media/%s"
                linkResource="file"
                imgResource="thumbnails/video.png"
            >}}',
            $media->id(),
        );

    }
}

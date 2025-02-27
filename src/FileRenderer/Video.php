<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Video implements FileRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        return sprintf(
            '{{< omeka-figure
                type="video"
                filePage="/media/%s"
                fileResource="file"
                imgResource="/thumbnails/video.png"
                linkPage="/media/%s"
                linkResource="file"
            >}}',
            $media->id(),
            $media->id()
        );

    }
}

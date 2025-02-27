<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Audio implements FileRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        return sprintf(
            '{{< omeka-figure
                type="audio"
                filePage="/media/%s"
                fileResource="file"
                imgResource="/thumbnails/audio.png"
                linkPage="/media/%s"
                linkResource="file"
            >}}',
            $media->id(),
            $media->id()
        );
    }
}

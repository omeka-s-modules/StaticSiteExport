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
                linkPage="/media/%s"
                linkResource="file"
                imgResource="thumbnails/audio.png"
            >}}',
            $media->id(),
        );
    }
}

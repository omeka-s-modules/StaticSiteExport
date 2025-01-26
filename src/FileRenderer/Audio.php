<?php
namespace StaticSiteExport\FileRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Audio implements FileRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string
    {
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

<?php
namespace StaticSiteExport\FileRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Video implements FileRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string
    {
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
